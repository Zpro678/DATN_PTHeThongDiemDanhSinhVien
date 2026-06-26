<?php

namespace App\Livewire\User;

use App\Models\CourseClass;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ManagedClasses extends Component
{
    // Bộ lọc theo trạng thái của lớp học (Đang hoạt động, Đã kết thúc, Tất cả)
    public string $statusFilter = 'Tất cả';

    // Bộ lọc theo học kỳ của lớp học
    public string $semesterFilter = 'Tất cả học kỳ';

    // Từ khóa tìm kiếm lớp học theo tên, mã lớp hoặc mã môn
    public string $search = '';

    // ID lớp đang chờ xác nhận kết thúc
    public ?int $confirmingEndClassId = null;

    public function confirmEndClass(int $classId): void
    {
        $this->confirmingEndClassId = $classId;
    }

    public function cancelEndClass(): void
    {
        $this->confirmingEndClassId = null;
    }

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
    }

    public function render(): View
    {
        $query = CourseClass::where('owner_user_id', auth()->id())
            ->withCount([
                'members as students_count' => fn ($q) => $q->where('status', 'active'),
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
            $query->where('status', 'ended');
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

        $semesters = CourseClass::where('owner_user_id', auth()->id())
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

    public function endClass(int $classId): void
    {
        $class = CourseClass::where('id', $classId)
            ->where('owner_user_id', auth()->id())
            ->firstOrFail();
        
        if ($class->status !== 'ended') {
            $class->update(['status' => 'ended']);
        }

        $this->confirmingEndClassId = null;
    }
}
