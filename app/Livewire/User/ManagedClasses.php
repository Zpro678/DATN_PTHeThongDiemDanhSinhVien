<?php

namespace App\Livewire\User;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class ManagedClasses extends Component
{
    public string $statusFilter = 'Tất cả';

    public string $semesterFilter = 'Tất cả học kỳ';

    public string $search = '';

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
    }

    public function toggleArchive(int $classId): void
    {
        $class = \App\Models\CourseClass::where('owner_user_id', auth()->id())->findOrFail($classId);
        
        if ($class->status === 'archived') {
            $class->update(['status' => 'active']);
        } else {
            $class->update(['status' => 'archived']);
        }
    }

    public function render(): View
    {
        $query = \App\Models\CourseClass::where('owner_user_id', auth()->id())
            ->withCount([
                'members as students_count',
                'sessions as completed_sessions_count' => fn ($q) => $q->where('status', 'closed'),
            ])
            ->withSum(['sessions as studied_lessons' => fn ($query) => $query->where('status', 'closed')], 'lesson_count')
            ->withSum('attendanceSummaries as sum_present', 'total_present')
            ->withSum('attendanceSummaries as sum_late', 'total_late')
            ->withSum('attendanceSummaries as sum_absent', 'total_absent')
            ->withSum('attendanceSummaries as sum_excused', 'total_excused');


        if ($this->statusFilter === 'Đang hoạt động') {
            $query->where('status', 'active');
        } elseif ($this->statusFilter === 'Đã kết thúc') {
            $query->whereIn('status', ['ended', 'archived']);
        }

        if ($this->semesterFilter !== 'Tất cả học kỳ') {
            $query->where('semester', $this->semesterFilter);
        }

        if (trim($this->search) !== '') {
            $search = str($this->search)->lower()->toString();
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(subject_code) LIKE ?', ["%{$search}%"]);
            });
        }

        $classes = $query->orderByDesc('created_at')->get();

        $semesters = \App\Models\CourseClass::where('owner_user_id', auth()->id())
            ->whereNotNull('semester')
            ->distinct()
            ->pluck('semester')
            ->filter()
            ->values();

        return view('livewire.user.managed-classes', [
            'classes' => $classes,
            'semesters' => $semesters,
        ])->layout('layouts.user', ['title' => 'Lớp tôi quản lý']);
    }
}
