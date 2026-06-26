<?php

namespace App\Livewire\Lecturer\Attendance;

use App\Models\ClassSession;
use App\Models\CourseClass;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class SessionGroupDetail extends Component
{
    use WithPagination;

    public CourseClass $courseClass;
    public string $groupKey;
    public int $perPage = 10;

    public function mount(CourseClass $courseClass, string $groupKey)
    {
        $this->courseClass = $courseClass;
        $this->groupKey = $groupKey;

        if ($this->courseClass->owner_user_id !== auth()->id()) {
            abort(403);
        }
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $parts = explode('_', $this->groupKey);
        if (count($parts) !== 3) abort(404);

        $date = $parts[0];
        $startTime = $parts[1] === '' ? null : $parts[1];
        $endTime = $parts[2] === '' ? null : $parts[2];

        $sessionsQuery = ClassSession::query()
            ->where('class_id', $this->courseClass->id)
            ->where('status', 'closed')
            ->whereDate('date', $date);

        if ($startTime) {
            $sessionsQuery->where('start_time', $startTime);
        } else {
            $sessionsQuery->whereNull('start_time');
        }

        if ($endTime) {
            $sessionsQuery->where('end_time', $endTime);
        } else {
            $sessionsQuery->whereNull('end_time');
        }

        $sessions = $sessionsQuery->orderBy('created_at', 'asc')->get();

        if ($sessions->isEmpty()) {
            abort(404);
        }

        $members = $this->courseClass->members()->where('status', 'active')->orderBy('student_code')->paginate($this->perPage);

        $records = \App\Models\AttendanceRecord::query()
            ->whereIn('class_session_id', $sessions->pluck('id'))
            ->whereIn('class_member_id', $members->pluck('id'))
            ->get()
            ->groupBy('class_member_id');

        $matrix = [];
        $dayDetailsArrForAll = [];
        
        // This is a single group, so we don't need a matrix by groupKey, just by member ID
        $groupInfo = [
            'date' => $sessions->first()->date->format('d/m/Y'),
            'timeStr' => $sessions->first()->start_time ? \Carbon\Carbon::parse($sessions->first()->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($sessions->first()->end_time)->format('H:i') : '',
            'startLesson' => $sessions->first()->start_lesson,
            'endLesson' => $sessions->first()->end_lesson,
            'lessonCount' => $sessions->first()->lesson_count,
            'columns' => []
        ];

        $iteration = 1;
        foreach ($sessions as $s) {
            $groupInfo['columns'][] = [
                'iteration' => $iteration,
                'time' => $s->start_time ? \Carbon\Carbon::parse($s->start_time)->format('H:i') : ''
            ];
            $iteration++;
        }

        foreach ($members as $member) {
            $memberRecords = $records->get($member->id, collect())->keyBy('class_session_id');
            
            $dayDetailsArr = [];
            $presentCount = 0;
            $absentCount = 0;
            $lateCount = 0;
            
            $iteration = 1;
            foreach ($sessions as $session) {
                $record = $memberRecords->get($session->id);
                $status = $record ? $record->status : 'pending';
                
                if (in_array($status, ['present', 'excused'])) {
                    $presentCount++;
                    $statusText = 'Có mặt';
                } elseif ($status === 'late') {
                    $lateCount++;
                    $statusText = 'Đi trễ';
                } elseif ($status === 'absent') {
                    $absentCount++;
                    $statusText = 'Vắng';
                } else {
                    $statusText = 'Chưa điểm danh';
                }

                $timeStr = $session->start_time ? \Carbon\Carbon::parse($session->start_time)->format('H:i') : 'Lần '.$iteration;
                $typeStr = $session->qr_token ? 'Quét QR' : 'Thủ công';
                
                $dayDetailsArr[] = [
                    'iteration' => $iteration,
                    'time' => $timeStr,
                    'type' => $typeStr,
                    'status' => $status,
                    'statusText' => $statusText
                ];
                $iteration++;
            }
            
            if ($presentCount == 0 && $absentCount == 0 && $lateCount == 0) {
                $finalStatus = 'pending';
                $finalText = 'Chưa điểm danh';
            } elseif ($absentCount > 0) {
                $finalStatus = 'absent';
                $finalText = 'Vắng';
            } elseif ($lateCount > 0) {
                $finalStatus = 'late';
                $finalText = 'Đi trễ';
            } else {
                $finalStatus = 'present';
                $finalText = 'Có mặt';
            }

            $lessonCount = $sessions->max('lesson_count') ?? 0;
            $attendedLessons = 0;
            $absentLessons = 0;

            if ($finalStatus === 'present' || $finalStatus === 'late') {
                $attendedLessons = $lessonCount;
            } elseif ($finalStatus === 'absent') {
                $absentLessons = $lessonCount;
            }

            $tooltipStr = collect($dayDetailsArr)
                ->map(fn($d) => "Lần {$d['iteration']} ({$d['time']}): {$d['statusText']}")
                ->join("\n") . "\nChốt: " . $finalText;

            $matrix[$member->id] = [
                'status' => $finalStatus,
                'text' => $finalText,
                'details' => $dayDetailsArr,
                'tooltip' => $tooltipStr,
                'attendedLessons' => $attendedLessons,
                'absentLessons' => $absentLessons
            ];
        }

        $membersData = collect($members->items())->map(fn($m) => [
            'id' => $m->id, 
            'full_name' => $m->full_name, 
            'student_code' => $m->student_code
        ])->keyBy('id');

        return view('livewire.lecturer.attendance.session-group-detail', compact('members', 'matrix', 'sessions', 'groupInfo', 'membersData'))
            ->layout('layouts.user', ['title' => 'Chi tiết phiên điểm danh']);
    }
}
