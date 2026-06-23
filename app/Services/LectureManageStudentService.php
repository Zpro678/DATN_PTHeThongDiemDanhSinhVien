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
            ->where('status', 'closed') // Chỉ tính các buổi điểm danh đã chốt.
            ->selectRaw('class_id, COALESCE(SUM(lesson_count), 0) as studied_lessons') // Cộng tổng số tiết đã học theo từng lớp.
            ->groupBy('class_id'); // Gom dữ liệu theo lớp để join ngược về sinh viên.

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
            ->selectRaw("
                COALESCE(SUM(CASE WHEN attendance_records.status = 'present' AND class_sessions.id IS NOT NULL THEN class_sessions.lesson_count ELSE 0 END), 0) as present_lessons,
                COALESCE(SUM(CASE WHEN attendance_records.status = 'late' AND class_sessions.id IS NOT NULL THEN class_sessions.lesson_count ELSE 0 END), 0) as late_lessons,
                COALESCE(SUM(CASE WHEN attendance_records.status = 'absent' AND class_sessions.id IS NOT NULL THEN class_sessions.lesson_count ELSE 0 END), 0) as absent_lessons,
                COALESCE(SUM(CASE WHEN attendance_records.status = 'excused' AND class_sessions.id IS NOT NULL THEN class_sessions.lesson_count ELSE 0 END), 0) as excused_lessons
            ") // Cộng số tiết theo từng trạng thái điểm danh của sinh viên.
            ->groupBy('class_members.id', 'studied.studied_lessons') // Gom theo từng sinh viên để mỗi sinh viên chỉ có một dòng thống kê.
            ->get()
            ->mapWithKeys(function ($stats) {
                $studiedLessons = (int) $stats->studied_lessons; // Tổng số tiết đã học của lớp sinh viên đó.
                $presentLessons = (int) $stats->present_lessons; // Số tiết sinh viên có mặt.
                $lateLessons = (int) $stats->late_lessons; // Số tiết sinh viên đi muộn.
                $absentLessons = (int) $stats->absent_lessons; // Số tiết sinh viên vắng không phép.
                $excusedLessons = (int) $stats->excused_lessons; // Số tiết sinh viên vắng có phép.
                $attendedLessons = $presentLessons + $lateLessons + $absentLessons; // Tính theo công thức (c + m + v)

                return [
                    $stats->id => [
                        'studied_lessons' => $studiedLessons, // Tổng số tiết đã học của lớp.
                        'present_lessons' => $presentLessons, // Tổng số tiết sinh viên có mặt.
                        'late_lessons' => $lateLessons, // Tổng số tiết sinh viên đi muộn.
                        'absent_lessons' => $absentLessons, // Tổng số tiết sinh viên vắng không phép.
                        'excused_lessons' => $excusedLessons, // Tổng số tiết sinh viên vắng có phép.
                        'attended_lessons' => $attendedLessons, // Tổng số tiết có mặt + đi muộn.
                        'attendance_percent' => $studiedLessons > 0
                            ? round(($absentLessons / $studiedLessons) * 100, 2)
                            : 0, // Phần trăm chuyên cần (vắng / tổng số tiết).
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
        $presentLessons = (int) $studentsStats->sum('present_lessons'); // Tổng số tiết có mặt của tất cả sinh viên.
        $lateLessons = (int) $studentsStats->sum('late_lessons'); // Tổng số tiết đi muộn của tất cả sinh viên.
        $absentLessons = (int) $studentsStats->sum('absent_lessons'); // Tổng số tiết vắng không phép của tất cả sinh viên.
        $excusedLessons = (int) $studentsStats->sum('excused_lessons'); // Tổng số tiết vắng có phép của tất cả sinh viên.
        $attendedLessons = $presentLessons + $lateLessons + $absentLessons; // Tính theo công thức (c + m + v)

        return [
            'total_students' => $memberIds->count(), // Tổng số sinh viên đang học trong phạm vi thống kê.
            'studied_lessons' => $studiedLessons, // Tổng số tiết đã học của tất cả sinh viên.
            'present_lessons' => $presentLessons, // Tổng số tiết có mặt của tất cả sinh viên.
            'late_lessons' => $lateLessons, // Tổng số tiết đi muộn của tất cả sinh viên.
            'absent_lessons' => $absentLessons, // Tổng số tiết vắng không phép của tất cả sinh viên.
            'excused_lessons' => $excusedLessons, // Tổng số tiết vắng có phép của tất cả sinh viên.
            'attended_lessons' => $attendedLessons, // Tổng số tiết có mặt + đi muộn.
            'present_percent' => $studiedLessons > 0 ? round(($presentLessons / $studiedLessons) * 100, 2) : 0,
            'late_percent' => $studiedLessons > 0 ? round(($lateLessons / $studiedLessons) * 100, 2) : 0,
            'absent_percent' => $studiedLessons > 0 ? round(($absentLessons / $studiedLessons) * 100, 2) : 0,
            'attendance_percent' => $studiedLessons > 0
                ? round(($absentLessons / $studiedLessons) * 100, 2)
                : 0, // Phần trăm chuyên cần tổng (vắng / tổng số tiết).
        ];
    }
}
