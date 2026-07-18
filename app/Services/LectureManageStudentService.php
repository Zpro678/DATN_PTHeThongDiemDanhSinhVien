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

        // Cài đặt lớp: tổng buổi dự kiến + cấu hình điểm trừ.
        $classes = CourseClass::query()
            ->whereIn('id', $members->pluck('class_id')->unique()->filter())
            ->get(['id', 'total_sessions', 'deduct_excused_absence', 'deduct_late', 'deduct_absent', 'deduct_excused',
                'absence_limit_percent', 'warning_margin_percent', 'near_absence_sessions'])
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

        // Số buổi điểm danh đã đóng của từng lớp
        $closedMeetingsCountByClass = DB::table('class_meetings')
            ->whereIn('class_id', $classes->keys())
            ->where('status', 'closed')
            ->whereNull('deleted_at')
            ->select('class_id', DB::raw('count(*) as count'))
            ->groupBy('class_id')
            ->pluck('count', 'class_id');

        return $members->mapWithKeys(function (ClassMember $member) use ($rowsByMember, $classes, $closedMeetingsCountByClass): array {
            $counts = AttendanceCalculator::consolidateByMeeting($rowsByMember->get($member->id, collect()));

            $classClosedMeetings = (int) $closedMeetingsCountByClass->get($member->class_id, 0);

            $studiedSessions = $counts['total']; // Số buổi đã diễn ra có bản ghi của sinh viên.
            $presentSessions = $counts['present'];
            $lateSessions = $counts['late'];
            $absentSessions = $counts['absent'];
            $excusedSessions = $counts['excused'];

            $class = $classes->get($member->class_id) ?? new CourseClass();
            $rules = $class->getAttendanceRules();
            $thresholds = $class->getAttendanceThresholds();

            // Nếu sinh viên bỏ lỡ buổi học hoàn toàn (không có bản ghi, VD: vào lớp sau khi buổi học đã đóng)
            if ($studiedSessions < $classClosedMeetings) {
                $missingMeetings = $classClosedMeetings - $studiedSessions;
                $absentSessions += $missingMeetings;
                $studiedSessions += $missingMeetings;
                $counts['total'] += $missingMeetings;
                $counts['absent'] += $missingMeetings;
                // Add deduction for the completely missed meetings
                $counts['deduction'] += $missingMeetings * AttendanceCalculator::deductionForStatus('absent', $rules);
            }

            $plannedSessions = (int) ($class?->total_sessions ?? 0);

            // Mẫu số = số buổi cơ sở: lớn nhất giữa dự kiến và số buổi đã diễn ra.
            // Nếu số buổi thực tế vượt dự kiến, quỹ vắng được tính lại trên số lớn hơn.
            $effectivePlanned = AttendanceCalculator::baseSessions($plannedSessions, $classClosedMeetings);

            $countedSessions = $effectivePlanned; // Trong hệ thống mới, luôn là tổng số buổi dự kiến, trừ điểm qua $rules
            $attendedSessions = $presentSessions + $lateSessions; // Số buổi có đến lớp (gồm cả muộn).
            $effectiveAbsent = AttendanceCalculator::effectiveAbsence($counts, $rules); // Vắng quy đổi theo quy tắc tổng kết.

            $attendancePercent = AttendanceCalculator::percentOfPlanned(
                $effectivePlanned,
                $counts,
                $rules
            );

            $allowedAbsent = AttendanceCalculator::allowedAbsentSessions($effectivePlanned, $thresholds['absence_limit_percent']);
            // Cấm thi: vắng quy đổi vượt quỹ vắng của lớp hoặc chuyên cần dưới ngưỡng tối thiểu.
            $isBanned = $effectivePlanned > 0 && ($effectiveAbsent > $allowedAbsent || $attendancePercent < $thresholds['min_attendance_percent']);
            // Cảnh báo: chuyên cần dưới ngưỡng cảnh báo của lớp nhưng chưa bị cấm.
            $isWarning = ! $isBanned && $attendancePercent < $thresholds['warning_percent'];

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
                    'effective_absent_sessions' => $effectiveAbsent, // Số buổi vắng quy đổi dùng xét cấm thi.
                    'attended_sessions' => $attendedSessions, // Số buổi có chuyên cần.
                    'allowed_absent_sessions' => $allowedAbsent, // Số buổi được phép vắng (theo quỹ vắng của lớp).
                    'attendance_percent' => $attendancePercent, // % chuyên cần trên tổng buổi dự kiến.
                    'is_warning' => $isWarning,
                    'is_banned' => $isBanned,
                ],
            ];
        })->toArray();
    }

    public function getTotalAttendanceStats(int $ownerUserId, ?string $classId = null): array
    {
        $memberIds = ClassMember::query()
            ->where('status', ClassMember::STATUS_ACTIVE)
            ->whereHas('courseClass', function ($query) use ($ownerUserId, $classId) {
                $query->managedBy($ownerUserId)
                    ->when($classId, fn ($query) => $query->where('id', $classId));
            })
            ->pluck('id');

        $studentsStats = collect($this->getStudentsAttendanceStats($memberIds));

        $totalStudents = $memberIds->count();
        $presentSessions = (int) $studentsStats->sum('present_sessions');
        $lateSessions = (int) $studentsStats->sum('late_sessions');
        $absentSessions = (int) $studentsStats->sum('absent_sessions');
        $excusedSessions = (int) $studentsStats->sum('excused_sessions');
        $effectiveAbsent = (float) $studentsStats->sum('effective_absent_sessions');
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
