<?php

namespace App\Livewire\Lecturer;

use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Services\AttendanceCalculator;
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
        
        // Gộp theo BUỔI (meeting_id) — mỗi buổi là 1 cột trong lưới.
        $groupedSessions = $sessions->groupBy('meeting_id');
        $deductExcused = (bool) $this->courseClass->deduct_excused_absence;

        $records = \App\Models\AttendanceRecord::query()
            ->whereIn('class_session_id', $sessions->pluck('id'))
            ->whereIn('class_member_id', $members->pluck('id'))
            ->get()
            ->groupBy('class_member_id');

        $matrix = [];
        $totalAttended = [];
        $memberStats = [];
        foreach ($members as $member) {
            $memberRecords = $records->get($member->id, collect())->keyBy('class_session_id');
            $counts = ['present' => 0, 'late' => 0, 'partial' => 0, 'early_leave' => 0, 'excused' => 0, 'absent' => 0];

            foreach ($groupedSessions as $groupKey => $daySessions) {
                // Sắp phiên theo id (~ thời gian) để xác định "phiên cuối quyết định".
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

                    // Diễn giải "chưa điểm danh": phiên thủ công -> có mặt; phiên QR chưa quét -> vắng.
                    $status = AttendanceCalculator::interpretStatus($record?->status ?? 'pending', $session->qr_token !== null);
                    $statuses[] = $status;

                    $dayDetailsArr[] = [
                        'iteration' => $iteration,
                        'time' => $session->start_time ? \Carbon\Carbon::parse($session->start_time)->format('H:i') : 'Lần '.$iteration,
                        'type' => $session->qr_token ? 'Quét QR' : 'Thủ công',
                        'status' => $status,
                        'statusText' => AttendanceCalculator::statusLabel($status),
                    ];
                    $iteration++;
                }

                // Gộp cả buổi theo quy tắc tổng kết (phiên cuối quyết định).
                $result = AttendanceCalculator::consolidateStatuses($statuses, $deductExcused);
                $finalStatus = $result['status']; // present / late / absent / excused
                $finalText = $result['label'];     // Có mặt / Đi muộn / Về sớm / Vắng / Có phép

                // Chỉ tính vào chuyên cần các buổi mà sinh viên thực sự có bản ghi.
                if ($hasRecord) {
                    $counts[$finalStatus] = ($counts[$finalStatus] ?? 0) + 1;
                }

                $attendedSessions = in_array($finalStatus, ['present', 'late', 'excused'], true) ? 1 : 0;
                $absentSessions = in_array($finalStatus, ['absent', 'early_leave'], true) ? 1 : 0;

                $tooltipStr = collect($dayDetailsArr)
                    ->map(fn($d) => "Lần {$d['iteration']} ({$d['time']}): {$d['statusText']}")
                    ->join("\n") . "\nChốt: " . $finalText;

                $matrix[$member->id][$groupKey] = [
                    'status' => $finalStatus,
                    'text' => $finalText,
                    'details' => $dayDetailsArr,
                    'tooltip' => $tooltipStr,
                    'attendedSessions' => $attendedSessions,
                    'absentSessions' => $absentSessions,
                ];
            }

            // % chuyên cần chuẩn (suy từ điểm trừ, đủ 6 trạng thái).
            $studied = array_sum($counts);
            $planned = max((int) ($this->courseClass->total_sessions ?? 0), $studied);
            $totalAttended[$member->id] = $counts['present'] + $counts['late'] + $counts['partial'] + $counts['excused'];
            $memberStats[$member->id] = AttendanceCalculator::percentOfPlanned($planned, $counts, $deductExcused);
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

        $totalCourseSessions = $this->courseClass->total_sessions ?? 0;

        $membersData = collect($members->items())->map(function($m) use ($colors, $totalAttended, $totalCourseSessions, $memberStats) {
            $color = $colors[$m->id % count($colors)];
            return [
                'id' => $m->id,
                'full_name' => $m->full_name,
                'student_code' => $m->student_code,
                'avatar_bg' => $color['bg'],
                'avatar_text' => $color['text'],
                'avatar_border' => $color['border'],
                'total_attended_sessions' => $totalAttended[$m->id] ?? 0,
                'total_course_sessions' => $totalCourseSessions,
                'attendance_percent' => $memberStats[$m->id] ?? 100,
            ];
        })->keyBy('id');

        return view('livewire.lecturer.class-attendance-history', compact('members', 'groupedSessions', 'matrix', 'sessions', 'groupedSessionsInfo', 'membersData'))
            ->layout('layouts.user', ['title' => 'Lịch sử điểm danh: ' . $this->courseClass->name]);
    }
}
