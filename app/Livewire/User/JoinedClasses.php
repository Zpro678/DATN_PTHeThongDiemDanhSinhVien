<?php

namespace App\Livewire\User;

use App\Models\CourseClass;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class JoinedClasses extends Component
{
    public string $statusFilter = 'Tất cả';

    public string $search = '';

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
    }

    public function render(): View
    {
        $query = CourseClass::whereHas('members', function ($q) {
            $q->where('user_id', auth()->id())->where('status', 'active');
        })
            ->with(['owner:id,name', 'members' => function ($q) {
                $q->where('user_id', auth()->id())->where('status', 'active')->with('attendanceSummary');
            }]);

        if ($this->statusFilter === 'Đang học') {
            $query->where('status', 'active');
        } elseif ($this->statusFilter === 'Đã kết thúc') {
            $query->where('status', 'ended');
        }

        if (trim($this->search) !== '') {
            $search = str($this->search)->lower()->toString();
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(subject_code) LIKE ?', ["%{$search}%"]);
            });
        }

        $courseClasses = $query->orderByDesc('created_at')->get();

        $classes = $courseClasses->map(function ($class) {
            $member = $class->members->first();
            $summary = $member?->attendanceSummary;

            $present = $summary?->total_present ?? 0;
            $absent = $summary?->total_absent ?? 0;
            $late = $summary?->total_late ?? 0;

            // Calc attendance percent
            $totalRecorded = $present + $absent + $late;
            $attendance = $totalRecorded > 0 ? round((($present + $late) / $totalRecorded) * 100) : 100;

            $isWarning = $attendance < 80;

            // Handle warning filter
            if ($this->statusFilter === 'Cảnh báo chuyên cần' && ! $isWarning) {
                return null;
            }

            return [
                'id' => $class->id,
                'title' => $class->name,
                'code' => $class->subject_code ?? $class->code,
                'join_code' => $class->code,
                'teacher' => $class->owner->name ?? 'Không xác định',
                'schedule' => $class->semester ?? 'Không xác định',
                'attendance' => $attendance,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'warning' => $isWarning,
                'ended' => $class->status === 'ended',
            ];
        })->filter()->values()->toArray();

        return view('livewire.user.joined-classes', compact('classes'))
            ->layout('layouts.user', ['title' => 'Lớp tôi tham gia']);
    }
}
