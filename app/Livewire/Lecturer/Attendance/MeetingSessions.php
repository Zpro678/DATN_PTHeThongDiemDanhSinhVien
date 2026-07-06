<?php

namespace App\Livewire\Lecturer\Attendance;

use App\Models\AttendanceRecord;
use App\Models\ClassMeeting;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class MeetingSessions extends Component
{
    public ClassMeeting $meeting;

    public function mount(ClassMeeting $meeting): void
    {
        $this->meeting = $meeting->load('courseClass');

        if (! $this->meeting->courseClass->isManagedBy(auth()->id())) {
            abort(403);
        }

        // Hết giờ kết thúc thì tự động chốt buổi.
        $this->meeting->closeIfExpired();
    }

    /**
     * "Thêm phiên" thủ công vào buổi này rồi chuyển sang trang điểm danh.
     */
    public function addManualSession(): void
    {
        if (! $this->meeting->canAddSession()) {
            session()->flash('error', 'Buổi điểm danh đã kết thúc, không thể thêm phiên mới.');
            return;
        }

        if ($this->meeting->courseClass->members()->where('status', \App\Models\ClassMember::STATUS_ACTIVE)->count() === 0) {
            session()->flash('error', 'Vui lòng import danh sách lớp trước khi điểm danh.');
            return;
        }

        $this->meeting->update(['status' => 'active']);
        $session = $this->meeting->createSession('active');

        $this->redirectRoute('lecturer.attendance.manual.session', ['ma_user' => auth()->id(), 'session' => $session->id], navigate: true);
    }

    public function render(): View
    {
        $sessions = $this->meeting->sessions()
            ->withCount([
                'attendanceRecords as present_count' => fn (Builder $query) => $query->where('status', 'present'),
                'attendanceRecords as late_count' => fn (Builder $query) => $query->where('status', 'late'),
                'attendanceRecords as absent_count' => fn (Builder $query) => $query->where('status', 'absent'),
                'attendanceRecords as excused_count' => fn (Builder $query) => $query->where('status', 'excused'),
                'attendanceRecords as pending_count' => fn (Builder $query) => $query->where('status', 'pending'),
            ])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('livewire.lecturer.attendance.meeting-sessions', compact('sessions'))
            ->layout('layouts.user', ['title' => 'Danh sách phiên điểm danh']);
    }
}
