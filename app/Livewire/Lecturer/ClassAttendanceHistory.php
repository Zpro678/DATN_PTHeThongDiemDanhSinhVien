<?php

namespace App\Livewire\Lecturer;

use App\Models\ClassSession;
use App\Models\CourseClass;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class ClassAttendanceHistory extends Component
{
    use WithPagination;

    public CourseClass $courseClass;
    public int $perPage = 10;

    public function mount(CourseClass $courseClass)
    {
        $this->courseClass = $courseClass;

        if ($this->courseClass->owner_user_id !== auth()->id()) {
            abort(403);
        }
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function closeSession(int $sessionId): void
    {
        $session = ClassSession::query()
            ->where('class_id', $this->courseClass->id)
            ->findOrFail($sessionId);

        $session->update(['status' => 'closed']);
        session()->flash('status', 'Phiên điểm danh đã được chốt.');
    }

    public function render(): View
    {
        $members = $this->courseClass->members()->where('status', 'active')->orderBy('student_code')->get();
        $sessions = ClassSession::query()
            ->where('class_id', $this->courseClass->id)
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
        
        $groupedSessions = $sessions->groupBy(function ($s) {
            return $s->date->format('Y-m-d') . '_' . $s->start_time . '_' . $s->end_time;
        });

        $records = \App\Models\AttendanceRecord::query()
            ->whereIn('class_session_id', $sessions->pluck('id'))
            ->whereIn('class_member_id', $members->pluck('id'))
            ->get()
            ->groupBy('class_member_id');

        $matrix = [];
        foreach ($members as $member) {
            $memberRecords = $records->get($member->id, collect())->keyBy('class_session_id');
            foreach ($groupedSessions as $groupKey => $daySessions) {
                $dayDetailsArr = [];
                $presentCount = 0;
                $absentCount = 0;
                $lateCount = 0;
                
                $iteration = 1;
                foreach ($daySessions as $session) {
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

                $lessonCount = $daySessions->max('lesson_count') ?? 0;
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

                $matrix[$member->id][$groupKey] = [
                    'status' => $finalStatus,
                    'text' => $finalText,
                    'details' => $dayDetailsArr,
                    'tooltip' => $tooltipStr,
                    'attendedLessons' => $attendedLessons,
                    'absentLessons' => $absentLessons
                ];
            }
        }

        $groupedSessionsInfo = $groupedSessions->map(function($sessions, $key) {
            $first = $sessions->first();
            $timeStr = $first->start_time ? \Carbon\Carbon::parse($first->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($first->end_time)->format('H:i') : '';
            
            $sessionCols = [];
            $iteration = 1;
            foreach($sessions as $s) {
                 $sessionCols[] = [
                      'iteration' => $iteration,
                      'time' => $s->start_time ? \Carbon\Carbon::parse($s->start_time)->format('H:i') : ''
                 ];
                 $iteration++;
            }

            return [
                'key' => $key,
                'date' => $first->date->format('d/m/Y'),
                'timeStr' => $timeStr,
                'startLesson' => $first->start_lesson,
                'endLesson' => $first->end_lesson,
                'lessonCount' => $first->lesson_count,
                'columns' => $sessionCols
            ];
        });

        $membersData = $members->map(fn($m) => [
            'id' => $m->id, 
            'full_name' => $m->full_name, 
            'student_code' => $m->student_code
        ])->keyBy('id');

        return view('livewire.lecturer.class-attendance-history', compact('members', 'groupedSessions', 'matrix', 'sessions', 'groupedSessionsInfo', 'membersData'))
            ->layout('layouts.user', ['title' => 'Lịch sử điểm danh: ' . $this->courseClass->name]);
    }
}
