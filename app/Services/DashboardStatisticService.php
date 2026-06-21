<?php

namespace App\Services;

use App\Models\CourseClass;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\ClassJoinRequest;
use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use Illuminate\Support\Carbon;

class DashboardStatisticService
{
    /**
     * TỐI ƯU HÓA HÀM TÍNH TIẾN ĐỘ TỪNG LỚP:
     * 1. Chống lỗi N+1 Query: Sử dụng LEFT JOIN để gom dữ liệu của 'classes' và 'class_sessions' trong 1 lần gọi duy nhất.
     * 2. Đẩy logic tính toán xuống DB: Dùng SUM() và GROUP BY của SQL để tính tổng số tiết đã học (studied_lessons), giúp máy chủ PHP không bị tràn RAM do phải load hàng ngàn Model.
     * 3. Tối ưu bộ nhớ: Chỉ select chính xác các cột cần dùng ('id', 'name', 'total_lessons') thay vì lấy toàn bộ (*) các cột.
     * 4. Xử lý logic nhẹ nhàng bằng PHP: Sử dụng Collection (map) để tính phần trăm (%) và trừ số tiết còn lại. PHP xử lý toán học trên mảng rất nhanh, làm vậy giúp câu lệnh SQL gọn nhẹ và dễ bảo trì hơn.
     */
    private function getClassesLessonProgress(int $userId, ?int $classId = null): array
    {
        return CourseClass::query()
            ->leftJoin('class_sessions', function ($join) {
                $join->on('classes.id', '=', 'class_sessions.class_id')
                    ->where('class_sessions.status', '=', 'closed');
            })
            ->where('classes.owner_user_id', $userId)
            ->when($classId, function ($query) use ($classId) {
                $query->where('classes.id', $classId);
            })
            ->select([
                'classes.id',
                'classes.name',
                'classes.total_lessons', // Lấy tổng số tiết cần học
            ])
            // Cộng dồn số tiết của các buổi học đã đóng
            ->selectRaw('COALESCE(SUM(class_sessions.lesson_count), 0) as studied_lessons')
            ->groupBy(
                'classes.id',
                'classes.name',
                'classes.total_lessons'
            )
            ->orderBy('classes.name')
            ->get()
            ->map(function ($class) {
                $requiredLessons = (int) $class->total_lessons;
                
                // Đảm bảo số tiết đã học không vượt quá số tiết quy định
                $studiedLessons = min((int) $class->studied_lessons, $requiredLessons);
                
                // Tính số tiết còn lại (không để số âm)
                $remainingLessons = max($requiredLessons - $studiedLessons, 0);

                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'required_lessons' => $requiredLessons,
                    'studied_lessons' => $studiedLessons,
                    'remaining_lessons' => $remainingLessons,
                    'progress_percent' => $requiredLessons > 0
                        ? round(($studiedLessons / $requiredLessons) * 100, 2)
                        : 0,
                ];
            })
            ->values()
            ->toArray();
    }

    private function getUnexcusedAbsenceWarnings($classIds, float $attendanceThreshold = 80, float $nearMargin = 5): array
    {
        $maxUnexcusedAbsencePercent = 100 - $attendanceThreshold;
        $warningUnexcusedAbsencePercent = max(0, $maxUnexcusedAbsencePercent - $nearMargin);

        $studiedLessonsQuery = ClassSession::query()
            ->where('status', 'closed')
            ->selectRaw('class_id, COALESCE(SUM(lesson_count), 0) as studied_lessons')
            ->groupBy('class_id');

        $warningStudents = ClassMember::query()
            ->join('classes', 'class_members.class_id', '=', 'classes.id')
            ->leftJoinSub($studiedLessonsQuery, 'studied', function ($join) {
                $join->on('class_members.class_id', '=', 'studied.class_id');
            })
            ->leftJoin('attendance_records', 'class_members.id', '=', 'attendance_records.class_member_id')
            ->leftJoin('class_sessions', function ($join) {
                $join->on('attendance_records.class_session_id', '=', 'class_sessions.id')
                    ->where('class_sessions.status', '=', 'closed');
            })
            ->whereIn('class_members.class_id', $classIds)
            ->where('class_members.status', 'active')
            ->whereNull('classes.deleted_at')
            ->select([
                'class_members.id',
                'class_members.class_id',
                'class_members.student_code',
                'class_members.full_name',
                'classes.name as class_name',
            ])
            ->selectRaw('COALESCE(studied.studied_lessons, 0) as studied_lessons')
            ->selectRaw("
                COALESCE(SUM(CASE
                    WHEN attendance_records.status = 'absent' AND class_sessions.id IS NOT NULL
                    THEN class_sessions.lesson_count
                    ELSE 0
                END), 0) as unexcused_absent_lessons
            ")
            ->groupBy(
                'class_members.id',
                'class_members.class_id',
                'class_members.student_code',
                'class_members.full_name',
                'classes.name',
                'studied.studied_lessons'
            )
            ->get()
            ->map(function ($student) use ($attendanceThreshold, $maxUnexcusedAbsencePercent) {
                $studiedLessons = (int) $student->studied_lessons;
                $unexcusedAbsentLessons = (int) $student->unexcused_absent_lessons;

                if ($studiedLessons <= 0) {
                    return null;
                }

                $unexcusedAbsencePercent = round(($unexcusedAbsentLessons / $studiedLessons) * 100, 2);
                $attendancePercent = max(round(100 - $unexcusedAbsencePercent, 2), 0);

                return [
                    'id' => $student->id,
                    'class_id' => $student->class_id,
                    'class_name' => $student->class_name,
                    'student_code' => $student->student_code,
                    'full_name' => $student->full_name,
                    'studied_lessons' => $studiedLessons,
                    'unexcused_absent_lessons' => $unexcusedAbsentLessons,
                    'unexcused_absence_percent' => $unexcusedAbsencePercent,
                    'attendance_percent' => $attendancePercent,
                    'threshold_percent' => $attendanceThreshold,
                    'status' => $unexcusedAbsencePercent >= $maxUnexcusedAbsencePercent
                        ? 'exceeded'
                        : 'at_risk',
                ];
            })
            ->filter(fn (?array $student) => $student !== null
                && $student['unexcused_absence_percent'] >= $warningUnexcusedAbsencePercent)
            ->sortByDesc('unexcused_absence_percent')
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
     * 2. Tái sử dụng dữ liệu tính toán (Cắt giảm 2 truy vấn nặng): Gọi hàm getClassesLessonProgress() trước, sau đó dùng vòng lặp foreach trong PHP để cộng dồn tổng số tiết cần học và đã học. Loại bỏ hoàn toàn 2 câu query khổng lồ chọc vào DB.
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
                'total_required_lessons' => 0,
                'total_studied_lessons' => 0,
                'remaining_lessons' => 0,
                'lesson_progress_percent' => 0,
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
                'recent_activities' => [],
                'classes_progress' => [],
            ];
        }

        $totalClasses = $classIds->count();

        // 1. Lấy dữ liệu tiến độ của từng lớp trước (Để tái sử dụng, giảm query)
        $classesProgress = $this->getClassesLessonProgress($userId, $classId);

        // 2. Tính tổng số tiết (Cộng dồn bằng PHP)
        $totalRequiredLessons = 0;
        $totalStudiedLessons = 0;

        foreach ($classesProgress as $classStats) {
            $totalRequiredLessons += $classStats['required_lessons'];
            $totalStudiedLessons += $classStats['studied_lessons'];
        }

        $remainingLessons = max($totalRequiredLessons - $totalStudiedLessons, 0);

        // 3. Phần trăm tiến độ học của tất cả các lớp
        $lessonProgressPercent = $totalRequiredLessons > 0
            ? round(($totalStudiedLessons / $totalRequiredLessons) * 100, 2)
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

        // 6. Tính tổng số tiết sinh viên có mặt / vắng mặt.
        $attendance = AttendanceRecord::query()
            ->join('class_sessions', 'attendance_records.class_session_id', '=', 'class_sessions.id')
            ->whereIn('class_sessions.class_id', $classIds)
            ->where('class_sessions.status', 'closed')
            ->selectRaw("
                COALESCE(SUM(CASE WHEN attendance_records.status IN ('present', 'late') THEN class_sessions.lesson_count ELSE 0 END), 0) AS total_present,
                COALESCE(SUM(CASE WHEN attendance_records.status IN ('absent', 'excused') THEN class_sessions.lesson_count ELSE 0 END), 0) AS total_absent
            ")
            ->first();

        $totalPresent = (int) ($attendance->total_present ?? 0);
        $totalAbsent = (int) ($attendance->total_absent ?? 0);
        $absenceWarnings = $this->getUnexcusedAbsenceWarnings($classIds);
        $pendingLeaveRequestsCount = $this->getPendingLeaveRequestsCount($classIds);
        $recentActivities = $this->getRecentActivities($classIds, $absenceWarnings);

        return [
            'total_students' => $totalStudents, // Tổng sinh viên đang hoạt động trong các lớp của chủ lớp.
            'total_required_lessons' => $totalRequiredLessons, // Tổng số tiết phải học của tất cả lớp.
            'total_studied_lessons' => $totalStudiedLessons, // Tổng số tiết đã học, chỉ tính các buổi đã chốt.
            'remaining_lessons' => $remainingLessons, // Tổng số tiết còn lại phải học.
            'lesson_progress_percent' => $lessonProgressPercent, // Phần trăm tiến độ học chung của tất cả lớp.
            'total_present' => $totalPresent, // Tổng số tiết sinh viên có mặt hoặc đi trễ.
            'total_absent' => $totalAbsent, // Tổng số tiết sinh viên vắng hoặc vắng có phép.
            'total_classes' => $totalClasses, // Tổng số lớp do user hiện tại quản lý.
            'today_attendance_sessions' => $todayAttendanceSessions, // Số buổi điểm danh diễn ra trong hôm nay.
            'unclosed_attendance_sessions' => $unclosedAttendanceSessions, // Số buổi điểm danh chưa chốt sổ.
            'attendance_sessions_by_date' => $attendanceSessionsByDate, // Thống kê số buổi điểm danh theo từng ngày.
            'attendance_warning_students_count' => $absenceWarnings['count'], // Số sinh viên gần hoặc đã vượt ngưỡng nghỉ không phép.
            'attendance_exceeded_students_count' => $absenceWarnings['exceeded_count'], // Số sinh viên đã vượt ngưỡng chuyên cần 80%.
            'attendance_warning_students' => $absenceWarnings['students'], // Danh sách sinh viên cần cảnh báo chuyên cần.
            'pending_leave_requests_count' => $pendingLeaveRequestsCount, // Tổng số đơn xin nghỉ đang chờ chủ lớp duyệt.
            'recent_activities' => $recentActivities, // Danh sách hoạt động gần đây từ điểm danh, đơn nghỉ và cảnh báo chuyên cần.
            'classes_progress' => $classesProgress, // Tiến độ học chi tiết của từng lớp.
        ];
    }
}
