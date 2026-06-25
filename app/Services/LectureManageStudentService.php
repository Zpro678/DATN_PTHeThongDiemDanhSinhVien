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
            ->groupBy('class_sessions.class_id'); // Gom dữ liệu theo lớp.

        return ClassMember::withTrashed()
            ->leftJoinSub($studiedLessonsQuery, 'studied', function ($join) {
                $join->on('class_members.class_id', '=', 'studied.class_id'); // Gắn tổng số tiết đã học của lớp vào từng sinh viên.
            })
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
            ->selectRaw('COALESCE(studied.lates_per_absent, 3) as lates_per_absent') // Lấy cài đặt quy đổi đi muộn.
            ->selectRaw('COALESCE(studied.deduct_excused_absence, 1) as deduct_excused_absence') // Lấy cài đặt vắng có phép.
            ->selectRaw("
                COALESCE(SUM(CASE WHEN attendance_records.status = 'present' AND class_sessions.id IS NOT NULL THEN class_sessions.lesson_count ELSE 0 END), 0) as present_lessons,
                COALESCE(SUM(CASE WHEN attendance_records.status = 'late' AND class_sessions.id IS NOT NULL THEN class_sessions.lesson_count ELSE 0 END), 0) as late_lessons,
                COALESCE(SUM(CASE WHEN attendance_records.status = 'late' AND class_sessions.id IS NOT NULL THEN 1 ELSE 0 END), 0) as late_count,
                COALESCE(SUM(CASE WHEN attendance_records.status = 'absent' AND class_sessions.id IS NOT NULL THEN class_sessions.lesson_count ELSE 0 END), 0) as absent_lessons,
                COALESCE(SUM(CASE WHEN attendance_records.status = 'excused' AND class_sessions.id IS NOT NULL THEN class_sessions.lesson_count ELSE 0 END), 0) as excused_lessons
            ") // Cộng số tiết theo từng trạng thái điểm danh của sinh viên.
            ->groupBy('class_members.id', 'studied.studied_lessons', 'studied.lates_per_absent', 'studied.deduct_excused_absence') // Gom theo từng sinh viên.
            ->get()
            ->mapWithKeys(function ($stats) {
                $studiedLessons = (int) $stats->studied_lessons; // Tổng số tiết đã học của lớp sinh viên đó.
                $presentLessons = (int) $stats->present_lessons; // Số tiết sinh viên có mặt.
                $lateLessons = (int) $stats->late_lessons; // Số tiết sinh viên đi muộn.
                $lateCount = (int) $stats->late_count; // Số LẦN sinh viên đi muộn (để quy đổi 3 lần = 1 tiết vắng).
                $absentLessons = (int) $stats->absent_lessons; // Số tiết sinh viên vắng không phép.
                $excusedLessons = (int) $stats->excused_lessons; // Số tiết sinh viên vắng có phép.

                $latesPerAbsent = (int) $stats->lates_per_absent;
                $deductExcusedAbsence = (bool) $stats->deduct_excused_absence;

                // Tính toán với cài đặt lớp
                $countedLessons = AttendanceCalculator::countedLessons($studiedLessons, $excusedLessons, $deductExcusedAbsence);
                $attendedLessons = AttendanceCalculator::attendedLessons($presentLessons, $lateLessons, $lateCount, $latesPerAbsent);
                $effectiveAbsent = AttendanceCalculator::effectiveAbsentLessons($absentLessons, $lateCount, $latesPerAbsent);

                return [
                    $stats->id => [
                        'studied_lessons' => $studiedLessons, // Tổng số tiết đã học của lớp.
                        'counted_lessons' => $countedLessons, // Tổng tiết tính chuyên cần (đã bỏ vắng có phép).
                        'present_lessons' => $presentLessons, // Tổng số tiết sinh viên có mặt.
                        'late_lessons' => $lateLessons, // Tổng số tiết sinh viên đi muộn.
                        'late_count' => $lateCount, // Tổng số lần sinh viên đi muộn.
                        'absent_lessons' => $absentLessons, // Tổng số tiết sinh viên vắng không phép.
                        'excused_lessons' => $excusedLessons, // Tổng số tiết sinh viên vắng có phép.
                        'effective_absent_lessons' => $effectiveAbsent, // Vắng tính cả muộn quy đổi.
                        'attended_lessons' => $attendedLessons, // Tiết có chuyên cần (có mặt + muộn còn lại).
                        'attendance_percent' => AttendanceCalculator::percent(
                            $presentLessons,
                            $lateLessons,
                            $excusedLessons,
                            $studiedLessons,
                            $lateCount,
                            $latesPerAbsent,
                            $deductExcusedAbsence
                        ), // Phần trăm chuyên cần theo công thức chung.
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

        $studentsStats = collect($this->getStudentsAttendanceStats($memberIds)); // Lấy thống kê từng sinh viên rồi cộng lại.

        $studiedLessons = (int) $studentsStats->sum('studied_lessons'); // Tổng số tiết đã học tính theo tất cả sinh viên.
        $countedLessons = (int) $studentsStats->sum('counted_lessons'); // Tổng tiết tính chuyên cần (đã bỏ vắng có phép).
        $presentLessons = (int) $studentsStats->sum('present_lessons'); // Tổng số tiết có mặt của tất cả sinh viên.
        $lateLessons = (int) $studentsStats->sum('late_lessons'); // Tổng số tiết đi muộn của tất cả sinh viên.
        $absentLessons = (int) $studentsStats->sum('absent_lessons'); // Tổng số tiết vắng không phép của tất cả sinh viên.
        $excusedLessons = (int) $studentsStats->sum('excused_lessons'); // Tổng số tiết vắng có phép của tất cả sinh viên.
        $effectiveAbsent = (int) $studentsStats->sum('effective_absent_lessons'); // Vắng tính cả muộn quy đổi.
        $attendedLessons = (int) $studentsStats->sum('attended_lessons'); // Tổng tiết có chuyên cần (có mặt + muộn còn lại).

        return [
            'total_students' => $memberIds->count(), // Tổng số sinh viên đang học trong phạm vi thống kê.
            'studied_lessons' => $studiedLessons, // Tổng số tiết đã học của tất cả sinh viên.
            'counted_lessons' => $countedLessons, // Tổng tiết tính chuyên cần (đã bỏ vắng có phép).
            'present_lessons' => $presentLessons, // Tổng số tiết có mặt của tất cả sinh viên.
            'late_lessons' => $lateLessons, // Tổng số tiết đi muộn của tất cả sinh viên.
            'absent_lessons' => $absentLessons, // Tổng số tiết vắng không phép của tất cả sinh viên.
            'excused_lessons' => $excusedLessons, // Tổng số tiết vắng có phép của tất cả sinh viên.
            'effective_absent_lessons' => $effectiveAbsent, // Vắng tính cả muộn quy đổi.
            'attended_lessons' => $attendedLessons, // Tổng tiết có chuyên cần.
            'present_percent' => $studiedLessons > 0 ? round(($presentLessons / $studiedLessons) * 100, 2) : 0,
            'late_percent' => $studiedLessons > 0 ? round(($lateLessons / $studiedLessons) * 100, 2) : 0,
            'absent_percent' => $studiedLessons > 0 ? round(($absentLessons / $studiedLessons) * 100, 2) : 0,
            'attendance_percent' => $countedLessons > 0
                ? round(($attendedLessons / $countedLessons) * 100, 2)
                : 0, // Phần trăm chuyên cần tổng theo công thức chung.
        ];
    }
}
