<?php

namespace App\Services;

use App\Models\CourseClass;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\ClassJoinRequest;
use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;

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
            'classes_progress' => $classesProgress, // Tiến độ học chi tiết của từng lớp.
        ];
    }
}
