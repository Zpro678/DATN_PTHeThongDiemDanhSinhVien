<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Models\LeaveRequest;
use Illuminate\Support\Carbon;
use App\Services\AttendanceCalculator;

class DashboardStatisticService
{
    /**
     * TỐI ƯU HÓA HÀM TÍNH TIẾN ĐỘ TỪNG LỚP:
     * 1. Chống lỗi N+1 Query: Sử dụng LEFT JOIN để gom dữ liệu của 'classes' và 'class_sessions' trong 1 lần gọi duy nhất.
     * 2. Đẩy logic tính toán xuống DB: Dùng SUM() và GROUP BY của SQL để tính tổng số tiết đã học (studied_sessions), giúp máy chủ PHP không bị tràn RAM do phải load hàng ngàn Model.
     * 3. Tối ưu bộ nhớ: Chỉ select chính xác các cột cần dùng ('id', 'name', 'total_sessions') thay vì lấy toàn bộ (*) các cột.
     * 4. Xử lý logic nhẹ nhàng bằng PHP: Sử dụng Collection (map) để tính phần trăm (%) và trừ số tiết còn lại. PHP xử lý toán học trên mảng rất nhanh, làm vậy giúp câu lệnh SQL gọn nhẹ và dễ bảo trì hơn.
     */
    private function getClassesSessionProgress(int $userId, ?int $classId = null): array
    {
        $classes = CourseClass::query()
            ->where('owner_user_id', $userId)
            ->when($classId, fn ($query) => $query->where('id', $classId))
            ->orderBy('name')
            ->get(['id', 'name', 'total_sessions']);

        if ($classes->isEmpty()) {
            return [];
        }

        // Số buổi đã chốt (đếm distinct buổi qua các phiên đã chốt) cho mỗi lớp.
        $studiedByClass = ClassSession::query()
            ->whereIn('class_id', $classes->pluck('id'))
            ->where('status', 'closed')
            ->whereNotNull('meeting_id')
            ->selectRaw('class_id, COUNT(DISTINCT meeting_id) as studied_sessions')
            ->groupBy('class_id')
            ->pluck('studied_sessions', 'class_id');

        return $classes
            ->map(function ($class) use ($studiedByClass) {
                $requiredSessions = (int) $class->total_sessions;
                $studiedSessions = (int) ($studiedByClass[$class->id] ?? 0);

                if ($requiredSessions > 0) {
                    $studiedSessions = min($studiedSessions, $requiredSessions);
                }

                $remainingSessions = max($requiredSessions - $studiedSessions, 0);

                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'required_sessions' => $requiredSessions,
                    'studied_sessions' => $studiedSessions,
                    'remaining_sessions' => $remainingSessions,
                    'progress_percent' => $requiredSessions > 0
                        ? round(($studiedSessions / $requiredSessions) * 100, 2)
                        : 0,
                ];
            })
            ->values()
            ->toArray();
    }

    private function getUnexcusedAbsenceWarnings($classIds, float $nearMarginPercent = 5): array
    {
        // Ngưỡng vắng tối đa theo AttendanceCalculator (20% tổng tiết kế hoạch).
        $absenceLimitRatio   = AttendanceCalculator::ABSENCE_LIMIT_RATIO;        // 0.20
        $warningLimitRatio   = max(0.0, $absenceLimitRatio - $nearMarginPercent / 100); // 0.15

        $members = ClassMember::query()
            ->join('classes', 'class_members.class_id', '=', 'classes.id')
            ->whereIn('class_members.class_id', $classIds)
            ->where('class_members.status', 'active')
            ->whereNull('classes.deleted_at')
            ->get([
                'class_members.id',
                'class_members.class_id',
                'class_members.student_code',
                'class_members.full_name',
                'classes.name as class_name',
                'classes.total_sessions as planned_sessions',
                'classes.attendance_rules as attendance_rules',
            ]);

        if ($members->isEmpty()) {
            return ['students' => [], 'count' => 0, 'exceeded_count' => 0];
        }

        // Bản ghi điểm danh ở phiên đã chốt, kèm meeting_id để gộp theo buổi.
        $rowsByMember = \Illuminate\Support\Facades\DB::table('attendance_records as ar')
            ->join('class_sessions as cs', 'cs.id', '=', 'ar.class_session_id')
            ->whereIn('ar.class_member_id', $members->pluck('id'))
            ->where('cs.status', 'closed')
            ->whereNotNull('cs.meeting_id')
            ->whereNull('ar.deleted_at')
            ->whereNull('cs.deleted_at')
            ->get(['ar.class_member_id', 'cs.meeting_id', 'ar.class_session_id', 'cs.qr_token', 'ar.status'])
            ->groupBy('class_member_id');

        $warningStudents = $members
            ->map(function ($student) use ($rowsByMember, $absenceLimitRatio, $warningLimitRatio) {
                $counts = AttendanceCalculator::consolidateByMeeting($rowsByMember->get($student->id, collect()));

                $plannedSessions  = (int) $student->planned_sessions;
                $absentSessions   = $counts['absent'];
                $lateSessions     = $counts['late'];
                $excusedSessions  = $counts['excused'];
                $rules            = is_string($student->attendance_rules) ? (json_decode($student->attendance_rules, true) ?? []) : (array) ($student->attendance_rules ?? []);
                $rules            = array_merge((new \App\Models\CourseClass())->getAttendanceRules(), $rules);

                $counted         = AttendanceCalculator::countedSessions($plannedSessions, $excusedSessions, $rules);
                $effectiveAbsent = AttendanceCalculator::effectiveAbsence($counts, $rules); // Vắng quy đổi (đủ 6 trạng thái).

                if ($counted <= 0 || $plannedSessions <= 0) {
                    return null;
                }

                $absenceRatio      = $effectiveAbsent / $counted;
                $attendancePercent = AttendanceCalculator::percentOfPlanned($plannedSessions, $counts, $rules);

                if ($absenceRatio < $warningLimitRatio) {
                    return null;
                }

                return [
                    'id'                       => $student->id,
                    'class_id'                 => $student->class_id,
                    'class_name'               => $student->class_name,
                    'student_code'             => $student->student_code,
                    'full_name'                => $student->full_name,
                    'planned_sessions'          => $plannedSessions,
                    'absent_sessions'           => $absentSessions,
                    'excused_sessions'          => $excusedSessions,
                    'effective_absent_sessions' => $effectiveAbsent,
                    'present_of_planned'       => max($plannedSessions - $excusedSessions - $effectiveAbsent, 0),
                    'attendance_percent'       => $attendancePercent,
                    'absence_ratio_percent'    => round($absenceRatio * 100, 1),
                    'status'                   => $absenceRatio >= $absenceLimitRatio ? 'exceeded' : 'at_risk',
                ];
            })
            ->filter(fn (?array $s) => $s !== null)
            ->sortByDesc('absence_ratio_percent')
            ->values();

        return [
            'students' => $warningStudents->take(5)->values()->toArray(),
            'count' => $warningStudents->count(),
            'exceeded_count' => $warningStudents
                ->where('status', 'exceeded')
                ->count(),
        ];
    }

    private function getPendingLeaveRequestsCount($classIds): int
    {
        return LeaveRequest::query()
            ->join('class_members', 'leave_requests.class_member_id', '=', 'class_members.id')
            ->whereIn('class_members.class_id', $classIds)
            ->where('leave_requests.status', 'pending')
            ->count('leave_requests.id');
    }

    private function getUnclosedSessionsList($classIds, int $limit = 5): array
    {
        return ClassSession::query()
            ->join('classes', 'class_sessions.class_id', '=', 'classes.id')
            ->whereIn('class_sessions.class_id', $classIds)
            ->where('class_sessions.status', '!=', 'closed')
            ->whereNull('classes.deleted_at')
            ->select([
                'class_sessions.id',
                'class_sessions.name',
                'class_sessions.date',
                'class_sessions.status',
                'class_sessions.class_id',
                'class_sessions.qr_token',
                'classes.name as class_name',
                'classes.code as class_code',
            ])
            ->orderByDesc('class_sessions.date')
            ->take($limit)
            ->get()
            ->map(fn ($s) => [
                'id'         => $s->id,
                'name'       => $s->name,
                'date'       => $s->date,
                'status'     => $s->status,
                'class_id'   => $s->class_id,
                'is_qr'      => ! empty($s->qr_token),
                'class_name' => $s->class_name,
                'class_code' => $s->class_code,
            ])
            ->toArray();
    }

    private function getPendingLeaveRequestsList($classIds, int $limit = 5): array
    {
        return LeaveRequest::query()
            ->join('class_members', 'leave_requests.class_member_id', '=', 'class_members.id')
            ->join('classes', 'class_members.class_id', '=', 'classes.id')
            ->leftJoin('class_sessions', 'leave_requests.class_session_id', '=', 'class_sessions.id')
            ->whereIn('class_members.class_id', $classIds)
            ->where('leave_requests.status', 'pending')
            ->whereNull('classes.deleted_at')
            ->select([
                'leave_requests.id',
                'leave_requests.class_member_id',
                'class_members.student_code',
                'class_members.full_name',
                'class_members.class_id',
                'classes.name as class_name',
                'class_sessions.date as session_date',
                'class_sessions.name as session_name',
            ])
            ->orderByDesc('leave_requests.created_at')
            ->take($limit)
            ->get()
            ->map(fn ($r) => [
                'id'           => $r->id,
                'member_id'    => $r->class_member_id,
                'student_code' => $r->student_code,
                'full_name'    => $r->full_name,
                'class_id'     => $r->class_id,
                'class_name'   => $r->class_name,
                'session_date' => $r->session_date,
                'session_name' => $r->session_name,
            ])
            ->toArray();
    }

    private function formatActivityTime($value): string
    {
        if (empty($value)) {
            return 'Vừa cập nhật';
        }

        $time = Carbon::parse($value);

        if ($time->isFuture()) {
            return 'Vừa cập nhật';
        }

        return $time->locale('vi')->diffForHumans();
    }

    private function getActivitySortTime($value): int
    {
        if (empty($value)) {
            return now()->timestamp;
        }

        return min(Carbon::parse($value)->timestamp, now()->timestamp);
    }

    private function getRecentActivities($classIds, array $absenceWarnings, int $limit = 5): array
    {
        $activities = collect();

        AttendanceRecord::query()
            ->join('class_sessions', 'attendance_records.class_session_id', '=', 'class_sessions.id')
            ->join('class_members', 'attendance_records.class_member_id', '=', 'class_members.id')
            ->join('classes', 'class_sessions.class_id', '=', 'classes.id')
            ->whereIn('class_sessions.class_id', $classIds)
            ->whereIn('attendance_records.status', ['present', 'late'])
            ->whereNotNull('attendance_records.check_in_time')
            ->whereNull('class_sessions.deleted_at')
            ->whereNull('classes.deleted_at')
            ->orderByDesc('attendance_records.check_in_time')
            ->take($limit)
            ->get([
                'attendance_records.status as record_status',
                'attendance_records.check_in_time',
                'class_members.student_code',
                'class_members.full_name',
                'classes.name as class_name',
            ])
            ->each(function ($record) use ($activities): void {
                $statusLabel = $record->record_status === 'late' ? 'đi muộn' : 'thành công';

                $activities->push([
                    'text' => "{$record->student_code} {$record->full_name} điểm danh {$statusLabel} lớp {$record->class_name}",
                    'time' => $this->formatActivityTime($record->check_in_time),
                    'icon' => 'user',
                    'bg' => $record->record_status === 'late' ? 'bg-secondary' : 'bg-tertiary',
                    'sort_time' => $this->getActivitySortTime($record->check_in_time),
                ]);
            });

        ClassSession::query()
            ->join('classes', 'class_sessions.class_id', '=', 'classes.id')
            ->whereIn('class_sessions.class_id', $classIds)
            ->whereNull('classes.deleted_at')
            ->select([
                'class_sessions.name',
                'class_sessions.status as session_status',
                'class_sessions.created_at',
                'class_sessions.updated_at',
                'classes.name as class_name',
            ])
            ->selectRaw('COALESCE(class_sessions.updated_at, class_sessions.created_at) as activity_at')
            ->orderByDesc('activity_at')
            ->take($limit)
            ->get()
            ->each(function ($session) use ($activities): void {
                $statusLabel = match ($session->session_status) {
                    'closed' => 'Đã chốt sổ',
                    'active' => 'Đang mở điểm danh',
                    default => 'Đã tạo',
                };

                $activities->push([
                    'text' => "{$statusLabel} buổi {$session->name} - {$session->class_name}",
                    'time' => $this->formatActivityTime($session->activity_at),
                    'icon' => $session->session_status === 'closed' ? 'check-square' : 'calendar-plus',
                    'bg' => $session->session_status === 'closed' ? 'bg-primary' : 'bg-secondary',
                    'sort_time' => $this->getActivitySortTime($session->activity_at),
                ]);
            });

        LeaveRequest::query()
            ->join('class_members', 'leave_requests.class_member_id', '=', 'class_members.id')
            ->join('classes', 'class_members.class_id', '=', 'classes.id')
            ->whereIn('class_members.class_id', $classIds)
            ->whereNull('classes.deleted_at')
            ->select([
                'leave_requests.status',
                'leave_requests.created_at',
                'leave_requests.reviewed_at',
                'class_members.student_code',
                'class_members.full_name',
                'classes.name as class_name',
            ])
            ->selectRaw('COALESCE(leave_requests.reviewed_at, leave_requests.created_at) as activity_at')
            ->orderByDesc('activity_at')
            ->take($limit)
            ->get()
            ->each(function ($request) use ($activities): void {
                $text = match ($request->status) {
                    'approved' => "Đơn nghỉ của {$request->student_code} {$request->full_name} đã được duyệt",
                    'rejected' => "Đơn nghỉ của {$request->student_code} {$request->full_name} đã bị từ chối",
                    default => "{$request->student_code} {$request->full_name} gửi đơn xin nghỉ lớp {$request->class_name}",
                };

                $activities->push([
                    'text' => $text,
                    'time' => $this->formatActivityTime($request->activity_at),
                    'icon' => match ($request->status) {
                        'approved' => 'check-circle',
                        'rejected' => 'x-circle',
                        default => 'file-text',
                    },
                    'bg' => match ($request->status) {
                        'approved' => 'bg-tertiary',
                        'rejected' => 'bg-error',
                        default => 'bg-secondary',
                    },
                    'sort_time' => $this->getActivitySortTime($request->activity_at),
                ]);
            });

        if ($absenceWarnings['count'] > 0) {
            $activities->push([
                'text' => "Có {$absenceWarnings['count']} sinh viên gần hoặc vượt ngưỡng nghỉ không phép",
                'time' => 'Vừa cập nhật',
                'icon' => 'alert-triangle',
                'bg' => 'bg-error',
                'sort_time' => now()->timestamp,
            ]);
        }

        if ($activities->isEmpty()) {
            return [[
                'text' => 'Chưa có hoạt động gần đây',
                'time' => 'Khi có điểm danh hoặc đơn nghỉ mới, hệ thống sẽ hiển thị tại đây.',
                'icon' => 'activity',
                'bg' => 'bg-primary',
            ]];
        }

        return $activities
            ->sortByDesc('sort_time')
            ->take($limit)
            ->values()
            ->map(function (array $activity): array {
                unset($activity['sort_time']);

                return $activity;
            })
            ->toArray();
    }

    /**
     * TỐI ƯU HÓA HÀM TỔNG HỢP DASHBOARD:
     * 1. Tái sử dụng Query: Dùng (clone) $ownedClassesQuery để lấy danh sách ID mà không phải viết lại điều kiện where, code DRY (Don't Repeat Yourself).
     * 2. Tái sử dụng dữ liệu tính toán (Cắt giảm 2 truy vấn nặng): Gọi hàm getClassesSessionProgress() trước, sau đó dùng vòng lặp foreach trong PHP để cộng dồn tổng số tiết cần học và đã học. Loại bỏ hoàn toàn 2 câu query khổng lồ chọc vào DB.
     * 3. Đếm trực tiếp từ DB: Việc đếm học viên (count) diễn ra thẳng ở Database, không tải dữ liệu rác về PHP.
     * 4. Gộp truy vấn điểm danh: Dùng kỹ thuật Pivot với CASE WHEN bên trong lệnh SUM(). Thay vì phải chạy 1 query để tính Có mặt, 1 query để tính Vắng mặt, chúng ta gom cả 2 vào duy nhất 1 truy vấn quét qua bảng attendance_records.
     */
    public function getOwnerOverview(int $userId, ?int $classId = null): array
    {
        $ownedClassesQuery = CourseClass::query()
            ->where('owner_user_id', $userId)
            ->when($classId, function ($query) use ($classId) {
                $query->where('id', $classId);
            });

        $classIds = (clone $ownedClassesQuery)->pluck('id');

        // Trả về 0 toàn bộ nếu giáo viên không có lớp nào (Ngăn chặn các truy vấn vô ích chạy xuống DB)
        if ($classIds->isEmpty()) {
            return [
                'total_students' => 0,
                'total_required_sessions' => 0,
                'total_studied_sessions' => 0,
                'remaining_sessions' => 0,
                'session_progress_percent' => 0,
                'total_present' => 0,
                'total_absent' => 0,
                'total_classes' => 0,
                'today_attendance_sessions' => 0,
                'unclosed_attendance_sessions' => 0,
                'attendance_sessions_by_date' => [],
                'attendance_warning_students_count' => 0,
                'attendance_exceeded_students_count' => 0,
                'attendance_warning_students' => [],
                'pending_leave_requests_count' => 0,
                'unclosed_sessions_list' => [],
                'pending_leave_requests_list' => [],
                'recent_activities' => [],
                'classes_progress' => [],
            ];
        }

        $totalClasses = $classIds->count();

        // 1. Lấy dữ liệu tiến độ của từng lớp trước (Để tái sử dụng, giảm query)
        $classesProgress = $this->getClassesSessionProgress($userId, $classId);

        // 2. Tính tổng số tiết (Cộng dồn bằng PHP)
        $totalRequiredSessions = 0;
        $totalStudiedSessions = 0;

        foreach ($classesProgress as $classStats) {
            $totalRequiredSessions += $classStats['required_sessions'];
            $totalStudiedSessions += $classStats['studied_sessions'];
        }

        $remainingSessions = max($totalRequiredSessions - $totalStudiedSessions, 0);

        // 3. Phần trăm tiến độ học của tất cả các lớp
        $sessionProgressPercent = $totalRequiredSessions > 0
            ? round(($totalStudiedSessions / $totalRequiredSessions) * 100, 2)
            : 0;

        // 4. Đếm tổng học viên đang hoạt động
        $totalStudents = ClassMember::query()
            ->whereIn('class_id', $classIds)
            ->where('status', 'active')
            ->count();

        // 5. Thống kê buổi điểm danh theo ngày.
        $todayAttendanceSessions = ClassSession::query()
            ->whereIn('class_id', $classIds)
            ->whereDate('date', today())
            ->count();

        $unclosedAttendanceSessions = ClassSession::query()
            ->whereIn('class_id', $classIds)
            ->where('status', '!=', 'closed')
            ->count();

        $attendanceSessionsByDate = ClassSession::query()
            ->whereIn('class_id', $classIds)
            ->selectRaw('DATE(date) as attendance_date')
            ->selectRaw('COUNT(*) as total_sessions')
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END), 0) as closed_sessions")
            ->selectRaw("COALESCE(SUM(CASE WHEN status != 'closed' THEN 1 ELSE 0 END), 0) as unclosed_sessions")
            ->groupByRaw('DATE(date)')
            ->orderByDesc('attendance_date')
            ->take(7)
            ->get()
            ->map(fn ($dateStats) => [
                'date' => $dateStats->attendance_date,
                'total_sessions' => (int) $dateStats->total_sessions,
                'closed_sessions' => (int) $dateStats->closed_sessions,
                'unclosed_sessions' => (int) $dateStats->unclosed_sessions,
            ])
            ->toArray();

        // 6. Tính tổng số buổi sinh viên có mặt / vắng (gộp theo buổi từng sinh viên).
        $activeMemberIds = ClassMember::query()
            ->whereIn('class_id', $classIds)
            ->where('status', 'active')
            ->pluck('id');

        $rowsByMember = AttendanceRecord::query()
            ->join('class_sessions', 'attendance_records.class_session_id', '=', 'class_sessions.id')
            ->whereIn('attendance_records.class_member_id', $activeMemberIds)
            ->where('class_sessions.status', 'closed')
            ->whereNotNull('class_sessions.meeting_id')
            ->whereNull('attendance_records.deleted_at')
            ->whereNull('class_sessions.deleted_at')
            ->get([
                'attendance_records.class_member_id as class_member_id',
                'class_sessions.meeting_id as meeting_id',
                'attendance_records.class_session_id as class_session_id',
                'class_sessions.qr_token as qr_token',
                'attendance_records.status as status',
            ])
            ->groupBy('class_member_id');

        $totalPresent = 0;
        $totalAbsent = 0;
        foreach ($rowsByMember as $memberRows) {
            $counts = AttendanceCalculator::consolidateByMeeting($memberRows);
            $totalPresent += $counts['present'] + $counts['late'];
            $totalAbsent += $counts['absent'] + $counts['excused'];
        }
        $absenceWarnings = $this->getUnexcusedAbsenceWarnings($classIds);
        $pendingLeaveRequestsCount = $this->getPendingLeaveRequestsCount($classIds);
        $recentActivities = $this->getRecentActivities($classIds, $absenceWarnings);
        $unclosedSessionsList = $this->getUnclosedSessionsList($classIds);
        $pendingLeaveRequestsList = $this->getPendingLeaveRequestsList($classIds);

        return [
            'total_students' => $totalStudents,
            'total_required_sessions' => $totalRequiredSessions,
            'total_studied_sessions' => $totalStudiedSessions,
            'remaining_sessions' => $remainingSessions,
            'session_progress_percent' => $sessionProgressPercent,
            'total_present' => $totalPresent,
            'total_absent' => $totalAbsent,
            'total_classes' => $totalClasses,
            'today_attendance_sessions' => $todayAttendanceSessions,
            'unclosed_attendance_sessions' => $unclosedAttendanceSessions,
            'attendance_sessions_by_date' => $attendanceSessionsByDate,
            'attendance_warning_students_count' => $absenceWarnings['count'],
            'attendance_exceeded_students_count' => $absenceWarnings['exceeded_count'],
            'attendance_warning_students' => $absenceWarnings['students'],
            'pending_leave_requests_count' => $pendingLeaveRequestsCount,
            'unclosed_sessions_list' => $unclosedSessionsList,
            'pending_leave_requests_list' => $pendingLeaveRequestsList,
            'recent_activities' => $recentActivities,
            'classes_progress' => $classesProgress,
        ];
    }
}
