<?php

namespace App\Livewire\Lecturer\Attendance;

use App\Models\ClassSession;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class AttendanceIndex extends Component
{
    use WithPagination;

    public $perPage = 10;

    public function closeSession(int $sessionId): void
    {
        $session = ClassSession::query()
            ->whereHas('courseClass', fn (Builder $query) => $query->where('owner_user_id', auth()->id()))
            ->findOrFail($sessionId);

        $session->update(['status' => 'closed']);
        session()->flash('status', 'Phiên điểm danh đã được chốt.');
    }

    public function render(): View
    {
        $sessions = ClassSession::query()
            ->with(['courseClass:id,name,code,owner_user_id'])
            ->withCount([
                'attendanceRecords as present_count' => fn (Builder $query) => $query->whereIn('status', ['present', 'late', 'excused']),
                'attendanceRecords as absent_count' => fn (Builder $query) => $query->where('status', 'absent'),
                'attendanceRecords as total_records_count',
            ])
            ->whereHas('courseClass', fn (Builder $query) => $query->where('owner_user_id', auth()->id()))
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        return view('livewire.lecturer.attendance.index', compact('sessions'))
            ->layout('layouts.user', ['title' => 'Quản lý điểm danh']);
    }
}
