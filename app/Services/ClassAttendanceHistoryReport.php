<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\CourseClass;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class ClassAttendanceHistoryReport
{
    public function activeMembersQuery(CourseClass $courseClass): HasMany
    {
        return $courseClass->members()
            ->where('class_members.status', ClassMember::STATUS_ACTIVE)
            ->leftJoin('class_member_profiles', 'class_member_profiles.class_member_id', '=', 'class_members.id')
            ->orderBy('class_member_profiles.student_code')
            ->select('class_members.*')
            ->with(['profile', 'user']);
    }

    public function closedSessions(CourseClass $courseClass): Collection
    {
        return ClassSession::query()
            ->where('class_id', $courseClass->id)
            ->where('status', 'closed')
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * @param  iterable<ClassMember>  $members
     * @return array<string, mixed>
     */
    public function build(CourseClass $courseClass, iterable $members, ?Collection $sessions = null): array
    {
        $members = collect($members)->values();
        $sessions ??= $this->closedSessions($courseClass);

        // Gop theo BUOI (meeting_id): moi buoi la 1 cot trong luoi lich su.
        $groupedSessions = $sessions->groupBy('meeting_id');
        $rules = $courseClass->getAttendanceRules();

        $records = AttendanceRecord::query()
            ->whereIn('class_session_id', $sessions->pluck('id'))
            ->whereIn('class_member_id', $members->pluck('id'))
            ->get()
            ->groupBy('class_member_id');

        $matrix = [];
        $totalAttended = [];
        $memberStats = [];

        foreach ($members as $member) {
            $memberRecords = $records->get($member->id, collect())->keyBy('class_session_id');
            $counts = ['present' => 0, 'late' => 0, 'excused' => 0, 'absent' => 0];

            foreach ($groupedSessions as $groupKey => $daySessions) {
                $ordered = $daySessions->sortBy('id')->values();
                $dayDetailsArr = [];
                $statuses = [];
                $hasRecord = false;

                $iteration = 1;
                foreach ($ordered as $session) {
                    $record = $memberRecords->get($session->id);
                    if ($record) {
                        $hasRecord = true;
                    }

                    $status = AttendanceCalculator::interpretStatus($record?->status ?? 'pending', $session->qr_token !== null);
                    $statuses[] = $status;

                    $dayDetailsArr[] = [
                        'iteration' => $iteration,
                        'time' => $session->start_time ? Carbon::parse($session->start_time)->format('H:i') : 'Lần '.$iteration,
                        'type' => $session->qr_token ? 'Quét QR' : 'Thủ công',
                        'status' => $status,
                        'statusText' => AttendanceCalculator::statusLabel($status),
                    ];
                    $iteration++;
                }

                $result = AttendanceCalculator::consolidateStatuses($statuses, $rules);
                $finalStatus = $result['status'];
                $finalText = $result['label'];

                if ($hasRecord) {
                    $counts[$finalStatus] = ($counts[$finalStatus] ?? 0) + 1;
                }

                $tooltipStr = collect($dayDetailsArr)
                    ->map(fn ($d) => "Lần {$d['iteration']} ({$d['time']}): {$d['statusText']}")
                    ->join("\n")."\nChốt: ".$finalText;

                $matrix[$member->id][$groupKey] = [
                    'status' => $finalStatus,
                    'text' => $finalText,
                    'details' => $dayDetailsArr,
                    'tooltip' => $tooltipStr,
                    'attendedSessions' => in_array($finalStatus, ['present', 'late', 'excused'], true) ? 1 : 0,
                    'absentSessions' => $finalStatus === 'absent' ? 1 : 0,
                ];
            }

            $studied = array_sum($counts);
            $planned = AttendanceCalculator::baseSessions((int) ($courseClass->total_sessions ?? 0), $studied);
            $totalAttended[$member->id] = $counts['present'] + $counts['late'] + $counts['excused'];
            $memberStats[$member->id] = AttendanceCalculator::percentOfPlanned($planned, $counts, $rules);
        }

        $dayIndex = 1;
        $groupedSessionsInfo = $groupedSessions->map(function ($sessions, $key) use (&$dayIndex) {
            $first = $sessions->first();
            $timeStr = $first->start_time
                ? Carbon::parse($first->start_time)->format('H:i').' - '.Carbon::parse($first->end_time)->format('H:i')
                : '';

            $sessionCols = [];
            $iteration = 1;
            foreach ($sessions as $session) {
                $sessionCols[] = [
                    'iteration' => $iteration,
                    'time' => $session->start_time ? Carbon::parse($session->start_time)->format('H:i') : '',
                ];
                $iteration++;
            }

            return [
                'key' => $key,
                'name' => 'Buổi '.$dayIndex++,
                'date' => $first->date->format('d/m/Y'),
                'timeStr' => $timeStr,
                'columns' => $sessionCols,
            ];
        });

        $colors = [
            ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200/60'],
            ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200/60'],
            ['bg' => 'bg-rose-100', 'text' => 'text-rose-700', 'border' => 'border-rose-200/60'],
            ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'border' => 'border-amber-200/60'],
            ['bg' => 'bg-sky-100', 'text' => 'text-sky-700', 'border' => 'border-sky-200/60'],
            ['bg' => 'bg-fuchsia-100', 'text' => 'text-fuchsia-700', 'border' => 'border-fuchsia-200/60'],
            ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'border' => 'border-orange-200/60'],
            ['bg' => 'bg-teal-100', 'text' => 'text-teal-700', 'border' => 'border-teal-200/60'],
            ['bg' => 'bg-violet-100', 'text' => 'text-violet-700', 'border' => 'border-violet-200/60'],
            ['bg' => 'bg-pink-100', 'text' => 'text-pink-700', 'border' => 'border-pink-200/60'],
        ];

        $totalCourseSessions = $courseClass->total_sessions ?? 0;

        $membersData = $members->map(function ($member) use ($colors, $totalAttended, $totalCourseSessions, $memberStats) {
            $color = $colors[$member->id % count($colors)];

            return [
                'id' => $member->id,
                'full_name' => $member->full_name,
                'student_code' => $member->student_code,
                'email' => $member->email,
                'avatar_bg' => $color['bg'],
                'avatar_text' => $color['text'],
                'avatar_border' => $color['border'],
                'avatar_url' => $member->user && $member->user->avatar ? asset('storage/'.$member->user->avatar) : null,
                'total_attended_sessions' => $totalAttended[$member->id] ?? 0,
                'total_course_sessions' => $totalCourseSessions,
                'attendance_percent' => $memberStats[$member->id] ?? 100,
            ];
        })->keyBy('id');

        return [
            'sessions' => $sessions,
            'groupedSessions' => $groupedSessions,
            'matrix' => $matrix,
            'groupedSessionsInfo' => $groupedSessionsInfo,
            'membersData' => $membersData,
        ];
    }
}
