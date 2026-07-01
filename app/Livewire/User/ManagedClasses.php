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
    public ?string $confirmingEndClassId = null;

    public function confirmEndClass(string $classId): void
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
                'members as students_count' => fn ($q) => $q->where('status', \App\Models\ClassMember::STATUS_ACTIVE),
                'sessions as completed_sessions_count' => fn ($q) => $q->where('status', 'closed'),
            ])
            ->withCount(['meetings as studied_sessions' => fn ($query) => $query->whereHas('sessions', fn ($s) => $s->where('status', 'closed'))])
            ->withSum('attendanceSummaries as sum_present', 'total_present')
            ->withSum('attendanceSummaries as sum_late', 'total_late')
            ->withSum('attendanceSummaries as sum_absent', 'total_absent')
            ->withSum('attendanceSummaries as sum_excused', 'total_excused');

        if ($this->statusFilter === 'Đang hoạt động') {
            $query->where('status', 'active');
        } elseif ($this->statusFilter === 'Đã kết thúc') {
            $query->where('status', 'ended');
        }

        if (trim($this->search) !== '') {
            $search = str($this->search)->lower()->toString();
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(join_key) LIKE ?', ["%{$search}%"]);
            });
        }

        $classes = $query->orderByDesc('created_at')->get();

        // Cột học kỳ đã được lược bỏ khỏi schema; không còn bộ lọc theo học kỳ.
        $semesters = collect();

        return view('livewire.user.managed-classes', [
            'classes' => $classes,
            'semesters' => $semesters,
        ])->layout('layouts.user', ['title' => 'Lớp tôi quản lý']);
    }

    public function endClass(string $classId): void
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
