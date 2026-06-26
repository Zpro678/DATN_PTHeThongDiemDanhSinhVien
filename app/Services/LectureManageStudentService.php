<?php

namespace App\Services;

use App\Models\ClassMember;
use App\Models\ClassSession;

class LectureManageStudentService
{
    public function getStudentsAttendanceStats(iterable $memberIds): array
    {
        $memberIds = collect($memberIds)
            ->map(fn ($memberId) => (int) $memberId) // Ép toàn bộ id sinh viên về kiểu số nguyên.
            ->filter() // Bỏ các id rỗng hoặc không hợp lệ.
            ->values(); // Reset lại index của collection.

        if ($memberIds->isEmpty()) {
            return []; // Không có sinh viên thì trả mảng rỗng để view không bị lỗi.
        }

        $studiedLessonsQuery = ClassSession::query()
            ->join('classes', 'class_sessions.class_id', '=', 'classes.id') // Join bảng classes để lấy cài đặt của lớp.
            ->where('class_sessions.status', 'closed') // Chỉ tính các buổi điểm danh đã chốt.
            ->selectRaw('
                class_sessions.class_id,
                COALESCE(SUM(class_sessions.lesson_count), 0) as studied_lessons,
                MAX(classes.lates_per_absent) as lates_per_absent,
                MAX(classes.deduct_excused_absence) as deduct_excused_absence
            ') // Lấy tổng số tiết và cài đặt lớp.
            ->groupBy('class_sessions.class_id');

        return ClassMember::withTrashed()
            ->leftJoinSub($studiedLessonsQuery, 'studied', function ($join) {
                $join->on('class_members.class_id', '=', 'studied.class_id'); // Gắn tổng số tiết đã học của lớp vào từng sinh viên.
            })
            ->leftJoin('classes', 'class_members.class_id', '=', 'classes.id') // Join trực tiếp bảng classes để đọc cài đặt mới nhất dù lớp chưa có buổi học nào chốt.
            ->leftJoin('attendance_records', function ($join) {
                $join->on('class_members.id', '=', 'attendance_records.class_member_id')
                    ->whereNull('attendance_records.deleted_at'); // Không tính bản ghi điểm danh đã bị xóa mềm.
            })
            ->leftJoin('class_sessions', function ($join) {
                $join->on('attendance_records.class_session_id', '=', 'class_sessions.id')
                    ->where('class_sessions.status', '=', 'closed') // Chỉ tính điểm danh thuộc buổi đã chốt.
                    ->whereNull('class_sessions.deleted_at'); // Không tính buổi học đã bị xóa mềm.
            })
            ->whereIn('class_members.id', $memberIds) // Chỉ tính cho danh sách sinh viên cần hiển thị.
            ->select('class_members.id') // Dùng id sinh viên làm key trả về.
            ->selectRaw('COALESCE(studied.studied_lessons, 0) as studied_lessons') // Tổng số tiết lớp của sinh viên đã học.
            ->selectRaw('COALESCE(classes.lates_per_absent, 0) as lates_per_absent') // Đọc từ bảng classes (cài đặt mới nhất), fallback 0 = không quy đổi.
            ->selectRaw('COALESCE(classes.deduct_excused_absence, 0) as deduct_excused_absence') // Đọc từ bảng classes, fallback false.
            ->selectRaw('COALESCE(classes.total_lessons, 0) as planned_lessons') // Tổng tiết kế hoạch cả khóa học.
            ->selectRaw("
                COALESCE(SUM(CASE WHEN attendance_records.status = 'present' AND class_sessions.id IS NOT NULL THEN class_sessions.lesson_count ELSE 0 END), 0) as present_lessons,
                COALESCE(SUM(CASE WHEN attendance_records.status = 'late' AND class_sessions.id IS NOT NULL THEN class_sessions.lesson_count ELSE 0 END), 0) as late_lessons,
                COALESCE(SUM(CASE WHEN attendance_records.status = 'late' AND class_sessions.id IS NOT NULL THEN 1 ELSE 0 END), 0) as late_count,
                COALESCE(SUM(CASE WHEN attendance_records.status = 'absent' AND class_sessions.id IS NOT NULL THEN class_sessions.lesson_count ELSE 0 END), 0) as absent_lessons,
                COALESCE(SUM(CASE WHEN attendance_records.status = 'excused' AND class_sessions.id IS NOT NULL THEN class_sessions.lesson_count ELSE 0 END), 0) as excused_lessons
            ") // Cộng số tiết theo từng trạng thái điểm danh của sinh viên.
            ->groupBy('class_members.id', 'studied.studied_lessons', 'classes.lates_per_absent', 'classes.deduct_excused_absence', 'classes.total_lessons') // Gom theo từng sinh viên.
            ->get()
            ->mapWithKeys(function ($stats) {
                $plannedLessons = (int) $stats->planned_lessons; // Tổng tiết kế hoạch cả khóa (từ cài đặt lớp).
                $studiedLessons = (int) $stats->studied_lessons; // Tổng số tiết đã học của lớp sinh viên đó.
                $presentLessons = (int) $stats->present_lessons; // Số tiết sinh viên có mặt.
                $lateLessons = (int) $stats->late_lessons; // Số tiết sinh viên đi muộn.
                $lateCount = (int) $stats->late_count; // Số LẦN sinh viên đi muộn (để quy đổi 3 lần = 1 tiết vắng).
                $absentLessons = (int) $stats->absent_lessons; // Số tiết sinh viên vắng không phép.
                $excusedLessons = (int) $stats->excused_lessons; // Số tiết sinh viên vắng có phép.

                $latesPerAbsent = (int) $stats->lates_per_absent;
                $deductExcusedAbsence = (bool) $stats->deduct_excused_absence;

                // Dùng tổng tiết kế hoạch làm mẫu số; fallback về tiết đã học nếu chưa cấu hình.
                $effectivePlanned = $plannedLessons > 0 ? $plannedLessons : $studiedLessons;

                $countedLessons = AttendanceCalculator::countedLessons($effectivePlanned, $excusedLessons, $deductExcusedAbsence);
                $attendedLessons = AttendanceCalculator::attendedLessons($presentLessons, $lateLessons, $lateCount, $latesPerAbsent);
                $effectiveAbsent = AttendanceCalculator::effectiveAbsentLessons($absentLessons, $lateCount, $latesPerAbsent);

                // % chuyên cần = tiết có mặt / tổng tiết kế hoạch (ví dụ: 44/45 tiết).
                $attendancePercent = AttendanceCalculator::percentOfPlanned(
                    $effectivePlanned,
                    $excusedLessons,
                    $absentLessons,
                    $lateCount,
                    $latesPerAbsent,
                    $deductExcusedAbsence
                );

                $allowedAbsent = (int) floor($effectivePlanned * AttendanceCalculator::ABSENCE_LIMIT_RATIO);
                // Cấm thi: vắng vượt 20% tổng tiết hoặc chuyên cần < 80%.
                $isBanned = $effectivePlanned > 0 && ($effectiveAbsent > $allowedAbsent || $attendancePercent < AttendanceCalculator::MIN_ATTENDANCE_PERCENT);
                // Cảnh báo: chuyên cần trong khoảng 80-85% (vẫn chưa bị cấm nhưng gần ngưỡng).
                $isWarning = ! $isBanned && $attendancePercent < 85;

                return [
                    $stats->id => [
                        'planned_lessons' => $effectivePlanned, // Tổng tiết kế hoạch dùng làm mẫu số.
                        'studied_lessons' => $studiedLessons, // Tổng số tiết đã học của lớp.
                        'counted_lessons' => $countedLessons, // Tổng tiết tính chuyên cần (đã bỏ vắng có phép).
                        'present_lessons' => $presentLessons, // Tổng số tiết sinh viên có mặt.
                        'late_lessons' => $lateLessons, // Tổng số tiết sinh viên đi muộn.
                        'late_count' => $lateCount, // Tổng số lần sinh viên đi muộn.
                        'absent_lessons' => $absentLessons, // Tổng số tiết sinh viên vắng không phép.
                        'excused_lessons' => $excusedLessons, // Tổng số tiết sinh viên vắng có phép.
                        'effective_absent_lessons' => $effectiveAbsent, // Vắng tính cả muộn quy đổi.
                        'attended_lessons' => $attendedLessons, // Tiết có chuyên cần (có mặt + muộn còn lại).
                        'allowed_absent_lessons' => $allowedAbsent, // Số tiết được phép vắng (20% tổng tiết).
                        'attendance_percent' => $attendancePercent, // % chuyên cần trên tổng tiết kế hoạch.
                        'is_warning' => $isWarning, // Chuyên cần 80–85%: cảnh báo sắp đến ngưỡng cấm thi.
                        'is_banned' => $isBanned, // Vắng > 20% hoặc chuyên cần < 80%: nguy cơ cấm thi.
                    ],
                ];
            })
            ->toArray(); // Trả về mảng có key là id sinh viên để view lấy nhanh.
    }

    public function getTotalAttendanceStats(int $ownerUserId, ?int $classId = null): array
    {
        $memberIds = ClassMember::query()
            ->where('status', 'active') // Chỉ tính sinh viên đang học.
            ->whereHas('courseClass', function ($query) use ($ownerUserId, $classId) {
                $query->where('owner_user_id', $ownerUserId) // Chỉ lấy sinh viên thuộc lớp của giảng viên hiện tại.
                    ->when($classId, fn ($query) => $query->where('id', $classId)); // Nếu có lọc lớp thì chỉ tính lớp đó.
            })
            ->pluck('id'); // Lấy danh sách id sinh viên để tái dùng hàm tính từng sinh viên.

        $studentsStats = collect($this->getStudentsAttendanceStats($memberIds)); // Lấy thống kê từng sinh viên rồi tổng hợp.

        $totalStudents = $memberIds->count();
        $presentLessons = (int) $studentsStats->sum('present_lessons');
        $lateLessons = (int) $studentsStats->sum('late_lessons');
        $absentLessons = (int) $studentsStats->sum('absent_lessons');
        $excusedLessons = (int) $studentsStats->sum('excused_lessons');
        $effectiveAbsent = (int) $studentsStats->sum('effective_absent_lessons');
        $attendedLessons = (int) $studentsStats->sum('attended_lessons');

        // Trung bình chuyên cần = trung bình cộng % từng sinh viên (đã tính qua AttendanceCalculator::percentOfPlanned).
        $avgPercent = $studentsStats->isNotEmpty()
            ? round($studentsStats->avg('attendance_percent'), 2)
            : 0;

        return [
            'total_students' => $totalStudents,
            'present_lessons' => $presentLessons,
            'late_lessons' => $lateLessons,
            'absent_lessons' => $absentLessons,
            'excused_lessons' => $excusedLessons,
            'effective_absent_lessons' => $effectiveAbsent,
            'attended_lessons' => $attendedLessons,
            'attendance_percent' => $avgPercent, // Trung bình chuyên cần của tất cả sinh viên.
        ];
    }
}
