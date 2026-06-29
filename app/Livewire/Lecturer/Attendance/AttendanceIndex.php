<?php

namespace App\Livewire\Lecturer\Attendance;

use App\Models\AttendanceRecord;
use App\Models\ClassMeeting;
use App\Models\ClassSession;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class AttendanceIndex extends Component
{
    use WithPagination;

    public $perPage = 10;

    public bool $showQuickStart = false;
    public string $quickStartType = 'manual';
    public string $quickClassId = '';
    public string $quickMeetingId = '';

    public function updatedQuickClassId()
    {
        $this->quickMeetingId = '';
    }

    #[Computed]
    public function activeClasses()
    {
        return \App\Models\CourseClass::where('owner_user_id', auth()->id())
            ->where('status', 'active')
            ->get();
    }

    #[Computed]
    public function classMeetings()
    {
        if (!$this->quickClassId) {
            return collect();
        }
        return \App\Models\ClassMeeting::where('class_id', $this->quickClassId)
            ->orderBy('date', 'desc')
            ->get()
            ->filter(fn ($meeting) => !$meeting->isExpired());
    }

    public function openQuickStart(string $type)
    {
        $this->quickStartType = $type;
        $this->showQuickStart = true;
    }

    public function startQuick()
    {
        $this->validate([
            'quickClassId' => 'required',
            'quickMeetingId' => 'required',
        ], [
            'quickClassId.required' => 'Vui lòng chọn lớp học.',
            'quickMeetingId.required' => 'Vui lòng chọn buổi điểm danh.',
        ]);
        
        if ($this->quickStartType === 'manual') {
            $this->cloneAndStartManual((int) $this->quickMeetingId);
        } else {
            $this->redirectRoute('lecturer.attendance.qr.create', ['meeting' => $this->quickMeetingId], navigate: true);
        }
    }

    public function closeSession(int $sessionId): void
    {
        $meeting = ClassMeeting::query()
            ->whereHas('courseClass', fn (Builder $query) => $query->where('owner_user_id', auth()->id()))
            ->findOrFail($sessionId);

        $meeting->sessions()->update(['status' => 'closed']);
        $meeting->update(['status' => 'closed']);
        session()->flash('status', 'Buổi điểm danh đã được chốt.');
    }

    /**
     * "Thêm phiên": tạo một phiên thủ công mới trong buổi đã chốt.
     */
    public function cloneAndStartManual(int $meetingId): void
    {
        $meeting = ClassMeeting::query()
            ->with('courseClass')
            ->whereHas('courseClass', fn (Builder $query) => $query->where('owner_user_id', auth()->id()))
            ->findOrFail($meetingId);

        $courseClass = $meeting->courseClass;

        if (! $meeting->canAddSession()) {
            session()->flash('status', 'Buổi điểm danh đã kết thúc, không thể thêm phiên mới.');
            return;
        }

        if ($courseClass->members()->where('status', 'active')->count() === 0) {
            $this->addError('classId', 'Vui lòng import danh sách lớp trước khi điểm danh.');
            $this->redirectRoute('lecturer.classes.show', ['ma_user' => auth()->id(), 'courseClass' => $courseClass->id, 'openImport' => 1], navigate: true);
            return;
        }

        $meeting->update(['status' => 'active']);
        $session = $meeting->createSession('active');

        $this->redirectRoute('lecturer.attendance.manual.session', ['ma_user' => auth()->id(), 'session' => $session->id], navigate: true);
    }

    public function render(): View
    {
        $meetings = ClassMeeting::query()
            ->with(['courseClass:id,name,code,owner_user_id', 'sessions'])
            ->whereHas('courseClass', fn (Builder $query) => $query->where('owner_user_id', auth()->id()))
            ->orderByDesc('created_at')
            ->orderByDesc('date')
            ->paginate($this->perPage);

        // Hết giờ kết thúc thì tự động chốt buổi (và các phiên).
        foreach ($meetings as $meeting) {
            $meeting->closeIfExpired();
        }

        // Gộp số "đã ghi nhận" / "vắng" mức buổi qua tất cả phiên của buổi.
        $allSessions = $meetings->getCollection()->flatMap->sessions;
        $sessionToMeeting = $allSessions->pluck('meeting_id', 'id');

        $recordsByMeeting = AttendanceRecord::query()
            ->whereIn('class_session_id', $allSessions->pluck('id'))
            ->whereHas('classMember')
            ->get(['id', 'class_session_id', 'class_member_id', 'status'])
            ->groupBy(fn (AttendanceRecord $record) => $sessionToMeeting[$record->class_session_id] ?? null);

        foreach ($meetings as $meeting) {
            $meetingRecords = ($recordsByMeeting->get($meeting->id) ?? collect())->groupBy('class_member_id');
            $counts = ClassMeeting::consolidateCounts($meetingRecords);
            $meeting->present_count = $counts['present'];
            $meeting->absent_count = $counts['absent'];
        }

        return view('livewire.lecturer.attendance.index', compact('meetings'))
            ->layout('layouts.user', ['title' => 'Quản lý điểm danh']);
    }
}
