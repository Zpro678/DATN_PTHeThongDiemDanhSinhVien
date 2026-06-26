<?php

namespace App\Livewire\Lecturer;

use App\Models\ClassSession;
use App\Models\CourseClass;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class ClassAttendanceHistory extends Component
{
    use WithPagination;

    #[Url(as: 'group')]
    public ?string $initialGroupKey = null;

    public CourseClass $courseClass;
    public int $perPage = 20;

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
        $members = $this->courseClass->members()->where('status', 'active')->orderBy('student_code')->paginate($this->perPage);
        $sessions = ClassSession::query()
            ->where('class_id', $this->courseClass->id)
            ->where('status', 'closed')
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
        $totalAttended = [];
        foreach ($members as $member) {
            $memberRecords = $records->get($member->id, collect())->keyBy('class_session_id');
            $totalAttended[$member->id] = 0;
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
                
                $totalAttended[$member->id] += $attendedLessons;
            }
        }

        $dayIndex = 1;
        $groupedSessionsInfo = $groupedSessions->map(function($sessions, $key) use (&$dayIndex) {
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
                'name' => 'Buổi ' . $dayIndex++,
                'date' => $first->date->format('d/m/Y'),
                'timeStr' => $timeStr,
                'startLesson' => $first->start_lesson,
                'endLesson' => $first->end_lesson,
                'lessonCount' => $first->lesson_count,
                'columns' => $sessionCols
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

        $totalCourseLessons = $this->courseClass->total_lessons ?? 0;

        $membersData = collect($members->items())->map(function($m) use ($colors, $totalAttended, $totalCourseLessons) {
            $color = $colors[$m->id % count($colors)];
            return [
                'id' => $m->id, 
                'full_name' => $m->full_name, 
                'student_code' => $m->student_code,
                'avatar_bg' => $color['bg'],
                'avatar_text' => $color['text'],
                'avatar_border' => $color['border'],
                'total_attended_lessons' => $totalAttended[$m->id] ?? 0,
                'total_course_lessons' => $totalCourseLessons
            ];
        })->keyBy('id');

        return view('livewire.lecturer.class-attendance-history', compact('members', 'groupedSessions', 'matrix', 'sessions', 'groupedSessionsInfo', 'membersData'))
            ->layout('layouts.user', ['title' => 'Lịch sử điểm danh: ' . $this->courseClass->name]);
    }
}
