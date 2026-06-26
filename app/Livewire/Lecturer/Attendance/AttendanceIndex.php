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

    public function cloneAndStartManual(int $sessionId): void
    {
        $oldSession = ClassSession::query()
            ->with('courseClass')
            ->whereHas('courseClass', fn (Builder $query) => $query->where('owner_user_id', auth()->id()))
            ->findOrFail($sessionId);

        $courseClass = $oldSession->courseClass;

        if ($courseClass->members()->where('status', 'active')->count() === 0) {
            $this->addError('classId', 'Vui lòng import danh sách lớp trước khi điểm danh.');
            $this->redirectRoute('lecturer.classes.show', ['ma_user' => auth()->id(), 'courseClass' => $courseClass->id, 'openImport' => 1], navigate: true);
            return;
        }

        $session = ClassSession::query()->create([
            'class_id' => $courseClass->id,
            'created_by' => auth()->id(),
            'name' => $oldSession->name,
            'date' => $oldSession->date,
            'start_time' => $oldSession->start_time,
            'end_time' => $oldSession->end_time,
            'start_lesson' => $oldSession->start_lesson,
            'end_lesson' => $oldSession->end_lesson,
            'lesson_count' => 0,
            'status' => 'active',
        ]);

        $courseClass->members()->where('status', 'active')->get()->each(fn ($member) => \App\Models\AttendanceRecord::query()->firstOrCreate([
            'class_session_id' => $session->id,
            'class_member_id' => $member->id,
        ], [
            'status' => 'pending',
            'is_verified' => $member->user_id !== null,
        ]));

        $this->redirectRoute('lecturer.attendance.manual.session', ['ma_user' => auth()->id(), 'session' => $session->id], navigate: true);
    }

    public function render(): View
    {
        $latestSessionIdsQuery = ClassSession::query()
            ->selectRaw('MAX(id)')
            ->groupBy('class_id', 'date', 'start_time', 'end_time');

        $sessions = ClassSession::query()
            ->with(['courseClass:id,name,code,owner_user_id'])
            ->withCount([
                'attendanceRecords as present_count' => fn (Builder $query) => $query->whereIn('status', ['present', 'late', 'excused']),
                'attendanceRecords as absent_count' => fn (Builder $query) => $query->where('status', 'absent'),
                'attendanceRecords as total_records_count',
            ])
            ->whereHas('courseClass', fn (Builder $query) => $query->where('owner_user_id', auth()->id()))
            ->whereIn('id', $latestSessionIdsQuery)
            ->orderByDesc('created_at')
            ->orderByDesc('date')
            ->paginate($this->perPage);

        return view('livewire.lecturer.attendance.index', compact('sessions'))
            ->layout('layouts.user', ['title' => 'Quản lý điểm danh']);
    }
}
