<?php

namespace App\Livewire\Lecturer\Attendance;

use App\Livewire\Lecturer\Attendance\Concerns\OwnsAttendanceSessions;
use App\Models\AttendanceRecord;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ManualAttendanceSession extends Component
{
    use OwnsAttendanceSessions;

    public int $sessionId;

    public string $search = '';

    public string $statusFilter = 'all';

    public function mount(int $session): void
    {
        $this->sessionId = $this->ownedSession($session)->id;
    }

    public function setStatusFilter(string $status): void
    {
        abort_unless(in_array($status, ['all', 'pending', 'present', 'late', 'absent', 'excused'], true), 422);

        $this->statusFilter = $status;
    }

    public function searchStudents(): void
    {
        $this->search = trim($this->search);
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->statusFilter = 'all';
    }

    public function updateStatus(int $recordId, string $status): void
    {
        abort_unless(in_array($status, ['present', 'late', 'absent', 'excused'], true), 422);
        $this->ensureSessionIsOpen();

        $record = AttendanceRecord::query()
            ->where('class_session_id', $this->sessionId)
            ->whereHas('classSession.courseClass', fn ($query) => $query->where('owner_user_id', auth()->id()))
            ->findOrFail($recordId);

        $record->update([
            'status' => $status,
            'check_in_time' => in_array($status, ['present', 'late'], true) ? now() : null,
            'is_verified' => $record->classMember->user_id !== null,
        ]);
    }

    public function updateNote(int $recordId, string $note): void
    {
        $this->ensureSessionIsOpen();

        AttendanceRecord::query()
            ->where('class_session_id', $this->sessionId)
            ->whereHas('classSession.courseClass', fn ($query) => $query->where('owner_user_id', auth()->id()))
            ->findOrFail($recordId)
            ->update(['note' => trim($note) ?: null]);
    }

    public function markAllPresent(): void
    {
        $this->ensureSessionIsOpen();

        AttendanceRecord::query()
            ->where('class_session_id', $this->sessionId)
            ->where('status', 'pending')
            ->whereHas('classSession.courseClass', fn ($query) => $query->where('owner_user_id', auth()->id()))
            ->with('classMember:id,user_id')
            ->get()
            ->each(fn (AttendanceRecord $record) => $record->update([
                'status' => 'present',
                'check_in_time' => now(),
                'is_verified' => $record->classMember->user_id !== null,
            ]));

        session()->flash('status', 'Đã đánh dấu tất cả sinh viên chưa điểm danh là có mặt.');
    }

    public function closeSession(): void
    {
        $session = $this->ownedSession($this->sessionId);
        $session->update(['status' => 'closed']);

        session()->flash('status', 'Phiên điểm danh đã được chốt sổ.');
    }

    public function render(): View
    {
        $session = $this->ownedSession($this->sessionId)->load('courseClass');
        $records = $session->attendanceRecords()
            ->with('classMember.user')
            ->when($this->statusFilter !== 'all', fn (Builder $query) => $query->where('status', $this->statusFilter))
            ->when($this->search !== '', function (Builder $query): void {
                $query->whereHas('classMember', function (Builder $query): void {
                    $query->where('student_code', 'like', '%'.$this->search.'%')
                        ->orWhere('full_name', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('id')
            ->get();

        $stats = $session->attendanceRecords()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $summary = [
            'present' => (int) ($stats['present'] ?? 0),
            'late' => (int) ($stats['late'] ?? 0),
            'excused' => (int) ($stats['excused'] ?? 0),
            'absent' => (int) ($stats['absent'] ?? 0),
            'pending' => (int) ($stats['pending'] ?? 0),
            'total' => $session->attendanceRecords()->count(),
        ];

        $summary['present_percent'] = $summary['total'] > 0
            ? (int) round(($summary['present'] / $summary['total']) * 100)
            : 0;

        return view('livewire.lecturer.attendance.manual-session', compact('session', 'records', 'summary'))
            ->layout('layouts.user', ['title' => 'Điểm danh thủ công']);
    }

    private function ensureSessionIsOpen(): void
    {
        abort_if($this->ownedSession($this->sessionId)->status === 'closed', 403);
    }
}
