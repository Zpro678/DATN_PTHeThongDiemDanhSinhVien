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
    public int $perPage = 5;

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
        $sessions = ClassSession::query()
            ->where('class_id', $this->courseClass->id)
            ->withCount([
                'attendanceRecords as present_count' => fn (Builder $query) => $query->whereIn('status', ['present', 'late', 'excused']),
                'attendanceRecords as absent_count' => fn (Builder $query) => $query->where('status', 'absent'),
                'attendanceRecords as total_records_count',
            ])
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        return view('livewire.lecturer.class-attendance-history', compact('sessions'))
            ->layout('layouts.user', ['title' => 'Lịch sử điểm danh: ' . $this->courseClass->name]);
    }
}
