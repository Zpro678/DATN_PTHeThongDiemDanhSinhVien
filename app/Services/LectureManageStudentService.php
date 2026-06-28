<?php

namespace App\Services;

use App\Models\ClassMember;
use App\Models\CourseClass;
use Illuminate\Support\Facades\DB;

class LectureManageStudentService
{
    public function getStudentsAttendanceStats(iterable $memberIds): array
    {
        $memberIds = collect($memberIds)
            ->map(fn ($memberId) => (int) $memberId)
            ->filter()
            ->values();

        if ($memberIds->isEmpty()) {
            return [];
        }

        $members = ClassMember::withTrashed()
            ->whereIn('id', $memberIds)
            ->get(['id', 'class_id']);

        // Cài đặt lớp: tổng buổi dự kiến + có trừ chuyên cần khi vắng có phép.
        $classes = CourseClass::query()
            ->whereIn('id', $members->pluck('class_id')->unique()->filter())
            ->get(['id', 'total_sessions', 'deduct_excused_absence'])
            ->keyBy('id');

        // Bản ghi điểm danh ở phiên đã chốt, kèm meeting_id để gộp theo buổi.
        $rowsByMember = DB::table('attendance_records as ar')
            ->join('class_sessions as cs', 'cs.id', '=', 'ar.class_session_id')
            ->whereIn('ar.class_member_id', $memberIds)
            ->where('cs.status', 'closed')
            ->whereNotNull('cs.meeting_id')
            ->whereNull('ar.deleted_at')
            ->whereNull('cs.deleted_at')
            ->get(['ar.class_member_id', 'cs.meeting_id', 'ar.class_session_id', 'cs.qr_token', 'ar.status'])
            ->groupBy('class_member_id');

        return $members->mapWithKeys(function (ClassMember $member) use ($rowsByMember, $classes): array {
            $counts = AttendanceCalculator::consolidateByMeeting($rowsByMember->get($member->id, collect()));

            $studiedSessions = $counts['total']; // Số buổi đã diễn ra của sinh viên.
            $presentSessions = $counts['present'];
            // Gộp để hiển thị 4 nhóm: vắng giữa giờ ~ muộn (−0.5); về sớm ~ vắng (−1).
            $lateSessions = $counts['late'] + $counts['partial'];
            $absentSessions = $counts['absent'] + $counts['early_leave'];
            $excusedSessions = $counts['excused'];

            $class = $classes->get($member->class_id);
            $deductExcusedAbsence = (bool) ($class?->deduct_excused_absence ?? true);
            $plannedSessions = (int) ($class?->total_sessions ?? 0);

            // Mẫu số là tổng buổi dự kiến; fallback về số buổi đã diễn ra nếu chưa cấu hình.
            $effectivePlanned = $plannedSessions > 0 ? $plannedSessions : $studiedSessions;

            $countedSessions = AttendanceCalculator::countedSessions($effectivePlanned, $excusedSessions, $deductExcusedAbsence);
            $attendedSessions = $presentSessions + $lateSessions; // Số buổi có đến lớp (gồm cả muộn).
            $effectiveAbsent = $absentSessions; // Số buổi vắng (hiển thị).
            $absenceForBan = AttendanceCalculator::effectiveAbsence($counts); // Vắng quy đổi (đủ 6 trạng thái).

            $attendancePercent = AttendanceCalculator::percentOfPlanned(
                $effectivePlanned,
                $counts,
                $deductExcusedAbsence
            );

            $allowedAbsent = AttendanceCalculator::allowedAbsentSessions($effectivePlanned);
            // Cấm thi: vắng quy đổi vượt 20% tổng buổi hoặc chuyên cần < 80%.
            $isBanned = $effectivePlanned > 0 && ($absenceForBan > $allowedAbsent || $attendancePercent < AttendanceCalculator::MIN_ATTENDANCE_PERCENT);
            // Cảnh báo: chuyên cần dưới 85% nhưng chưa bị cấm.
            $isWarning = ! $isBanned && $attendancePercent < AttendanceCalculator::WARNING_PERCENT;

            return [
                $member->id => [
                    'planned_sessions' => $effectivePlanned, // Tổng buổi dự kiến (mẫu số).
                    'studied_sessions' => $studiedSessions, // Số buổi đã diễn ra.
                    'counted_sessions' => $countedSessions, // Số buổi tính chuyên cần.
                    'present_sessions' => $presentSessions, // Số buổi có mặt.
                    'late_sessions' => $lateSessions, // Số buổi đi muộn.
                    'late_count' => $lateSessions, // Số buổi đi muộn (tương thích view cũ).
                    'absent_sessions' => $absentSessions, // Số buổi vắng không phép.
                    'excused_sessions' => $excusedSessions, // Số buổi vắng có phép.
                    'effective_absent_sessions' => $effectiveAbsent, // Số buổi vắng dùng xét cấm thi.
                    'attended_sessions' => $attendedSessions, // Số buổi có chuyên cần.
                    'allowed_absent_sessions' => $allowedAbsent, // Số buổi được phép vắng (20%).
                    'attendance_percent' => $attendancePercent, // % chuyên cần trên tổng buổi dự kiến.
                    'is_warning' => $isWarning,
                    'is_banned' => $isBanned,
                ],
            ];
        })->toArray();
    }

    public function getTotalAttendanceStats(int $ownerUserId, ?int $classId = null): array
    {
        $memberIds = ClassMember::query()
            ->where('status', 'active')
            ->whereHas('courseClass', function ($query) use ($ownerUserId, $classId) {
                $query->where('owner_user_id', $ownerUserId)
                    ->when($classId, fn ($query) => $query->where('id', $classId));
            })
            ->pluck('id');

        $studentsStats = collect($this->getStudentsAttendanceStats($memberIds));

        $totalStudents = $memberIds->count();
        $presentSessions = (int) $studentsStats->sum('present_sessions');
        $lateSessions = (int) $studentsStats->sum('late_sessions');
        $absentSessions = (int) $studentsStats->sum('absent_sessions');
        $excusedSessions = (int) $studentsStats->sum('excused_sessions');
        $effectiveAbsent = (int) $studentsStats->sum('effective_absent_sessions');
        $attendedSessions = (int) $studentsStats->sum('attended_sessions');

        // Trung bình chuyên cần = trung bình cộng % từng sinh viên.
        $avgPercent = $studentsStats->isNotEmpty()
            ? round($studentsStats->avg('attendance_percent'), 2)
            : 0;

        return [
            'total_students' => $totalStudents,
            'present_sessions' => $presentSessions,
            'late_sessions' => $lateSessions,
            'absent_sessions' => $absentSessions,
            'excused_sessions' => $excusedSessions,
            'effective_absent_sessions' => $effectiveAbsent,
            'attended_sessions' => $attendedSessions,
            'attendance_percent' => $avgPercent,
        ];
    }
}
