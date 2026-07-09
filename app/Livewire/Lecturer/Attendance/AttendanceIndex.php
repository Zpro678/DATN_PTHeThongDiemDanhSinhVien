<?php

namespace App\Livewire\Lecturer\Attendance;

use App\Models\AttendanceRecord;
use App\Models\ClassMeeting;
use App\Models\ClassSession;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class AttendanceIndex extends Component
{
    use WithPagination;

    public $perPage = 10;

    // Bộ lọc danh sách. 'unclosed' = chỉ hiện buổi chưa chốt (đồng bộ với thẻ dashboard).
    #[Url]
    public string $filter = '';

    // Từ khóa tìm kiếm theo tên buổi học / tên lớp / mã lớp.
    #[Url]
    public string $search = '';

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public bool $showQuickStart = false;
    public string $quickStartType = 'manual';
    public string $quickClassId = '';
    public string $quickMeetingId = '';
    public string $newMeetingName = '';
    public string $meetingEndTime = '';

    public string $sessionName = '';
    public int $durationMinutes = 15;
    public int $gpsRadius = 100;
    public int $qrRefreshRate = 10;
    public bool $gpsEnabled = true;
    public ?float $gpsLatitude = null;
    public ?float $gpsLongitude = null;

    public function mount()
    {
        if (request()->has('action') && request()->has('class_id')) {
            $this->quickClassId = request()->query('class_id');
            $this->openQuickStart(request()->query('action'));
        }
    }

    public function updatedQuickClassId()
    {
        $this->quickMeetingId = '';
        $this->sessionName = 'Phiên 1';
    }

    public function updatedQuickMeetingId()
    {
        if ($this->quickMeetingId) {
            $meeting = \App\Models\ClassMeeting::find($this->quickMeetingId);
            if ($meeting) {
                $count = $meeting->sessions()->count();
                $this->sessionName = 'Phiên ' . ($count + 1);
            }
        } else {
            $this->sessionName = 'Phiên 1';
        }
    }

    #[Computed]
    public function activeClasses()
    {
        return \App\Models\CourseClass::managedBy(auth()->id())
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
            ->whereDate('date', now()->toDateString())
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(fn ($meeting) => !$meeting->isExpired());
    }

    public function createTodayMeeting()
    {
        if (!$this->quickClassId) {
            $this->addError('quickClassId', 'Vui lòng chọn lớp học trước.');
            return;
        }

        $courseClass = \App\Models\CourseClass::findOrFail($this->quickClassId);

        if ($courseClass->members()->where('status', \App\Models\ClassMember::STATUS_ACTIVE)->count() === 0) {
            $this->addError('quickClassId', 'Vui lòng import danh sách lớp trước khi điểm danh.');
            return;
        }
        
        $date = now()->toDateString();
        $startTime = now()->format('H:i');
        $endTime = $this->meetingEndTime ?: now()->addMinutes(120)->format('H:i');

        $meetingCount = $courseClass->meetings()->count() + 1;
        $meetingName = 'Buổi ' . $meetingCount;

        $meeting = ClassMeeting::create([
            'class_id' => $courseClass->id,
            'user_Created' => auth()->id(),
            'name' => $meetingName,
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'active',
        ]);

        $this->quickMeetingId = (string) $meeting->id;
        $this->sessionName = 'Phiên 1';
    }

    public function openQuickStart(string $type)
    {
        $this->quickStartType = $type;
        $this->showQuickStart = true;
        
        $this->sessionName = 'Phiên 1';
        $this->durationMinutes = 15;
        $this->gpsRadius = 100;
        $this->qrRefreshRate = 10;
        $this->gpsEnabled = true;
        $this->gpsLatitude = null;
        $this->gpsLongitude = null;
        $this->meetingEndTime = now()->addMinutes(120)->format('H:i');
    }

    public function startQuick()
    {
        $rules = [
            'quickClassId' => 'required',
            'sessionName' => 'required|string|max:255',
        ];
        $messages = [
            'quickClassId.required' => 'Vui lòng chọn lớp học.',
            'sessionName.required' => 'Vui lòng nhập tên phiên.',
            'meetingEndTime.required' => 'Vui lòng chọn thời gian kết thúc.',
        ];

        $rules['meetingEndTime'] = 'required|date_format:H:i';

        if ($this->quickMeetingId === 'NEW') {
            $rules['newMeetingName'] = 'required|string|max:255';
            $messages['newMeetingName.required'] = 'Vui lòng nhập tên buổi học mới.';
        } else {
            $rules['quickMeetingId'] = 'required';
            $messages['quickMeetingId.required'] = 'Vui lòng chọn buổi học.';
        }

        if ($this->quickStartType === 'qr') {
            $rules['durationMinutes'] = 'required|integer|min:1';
            $rules['qrRefreshRate'] = 'required|integer|min:5';
            $rules['gpsRadius'] = 'required|integer|min:5';
            
            if ($this->gpsEnabled) {
                $rules['gpsLatitude'] = 'required|numeric';
                $rules['gpsLongitude'] = 'required|numeric';
                $messages['gpsLatitude.required'] = 'Vui lòng cấp quyền truy cập vị trí GPS để chống gian lận.';
            }
        }

        $this->validate($rules, $messages);
        
        $courseClass = \App\Models\CourseClass::findOrFail($this->quickClassId);
        if ($courseClass->members()->where('status', \App\Models\ClassMember::STATUS_ACTIVE)->count() === 0) {
            $this->addError('quickClassId', 'Vui lòng import danh sách lớp trước khi điểm danh.');
            return;
        }
        
        if ($this->quickMeetingId === 'NEW') {
            $date = now()->toDateString();
            $startTime = now()->format('H:i');
            $endTime = $this->meetingEndTime ?: now()->addMinutes(120)->format('H:i');
            
            $meeting = \App\Models\ClassMeeting::create([
                'class_id' => $courseClass->id,
                'user_Created' => auth()->id(),
                'name' => $this->newMeetingName,
                'date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => 'active',
            ]);
            
            $this->quickMeetingId = (string) $meeting->id;
        }

        $meeting = ClassMeeting::query()
            ->with('courseClass')
            ->whereHas('courseClass', fn ($query) => $query->managedBy(auth()->id()))
            ->findOrFail($this->quickMeetingId);

        if (! $meeting->canAddSession()) {
            $this->addError('quickMeetingId', 'Buổi điểm danh đã kết thúc, không thể thêm phiên mới.');
            return;
        }

        $meeting->update([
            'status' => 'active',
            'end_time' => $this->meetingEndTime ?: $meeting->end_time,
        ]);

        if ($this->quickStartType === 'manual') {
            $session = $meeting->createSession('active', [
                'name' => $this->sessionName,
            ]);
            $this->redirectRoute('lecturer.attendance.manual.session', ['ma_user' => auth()->id(), 'session' => $session->id], navigate: true);
        } else {
            $session = $meeting->createSession('active', [
                'name' => $this->sessionName,
                'qr_token' => \App\Models\ClassSession::generateQrToken(),
                'token_expires_at' => \App\Models\ClassSession::qrTokenExpiryFor((int) $this->qrRefreshRate),
                'qr_refresh_rate' => $this->qrRefreshRate,
                'gps_latitude' => $this->gpsEnabled ? $this->gpsLatitude : null,
                'gps_longitude' => $this->gpsEnabled ? $this->gpsLongitude : null,
                'gps_radius' => $this->gpsEnabled ? $this->gpsRadius : null,
            ]);
            
            app(\App\Services\NotificationService::class)->attendanceSessionCreated((int) auth()->id(), $session, isQr: true);
            $this->redirectRoute('lecturer.attendance.qr.session', ['ma_user' => auth()->id(), 'session' => $session->id], navigate: true);
        }
    }

    public function closeSession(int $sessionId): void
    {
        $meeting = ClassMeeting::query()
            ->whereHas('courseClass', fn (Builder $query) => $query->managedBy(auth()->id()))
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
            ->whereHas('courseClass', fn (Builder $query) => $query->managedBy(auth()->id()))
            ->findOrFail($meetingId);

        $courseClass = $meeting->courseClass;

        if (! $meeting->canAddSession()) {
            session()->flash('status', 'Buổi điểm danh đã kết thúc, không thể thêm phiên mới.');
            return;
        }

        if ($courseClass->members()->where('status', \App\Models\ClassMember::STATUS_ACTIVE)->count() === 0) {
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
            ->with(['courseClass:id,name,join_key,owner_user_id', 'sessions'])
            ->whereHas('courseClass', fn (Builder $query) => $query->managedBy(auth()->id()))
            ->when($this->filter === 'unclosed', fn (Builder $query) => $query->where('status', '!=', 'closed'))
            ->when($this->filter === 'closed', fn (Builder $query) => $query->where('status', 'closed'))
            ->when($this->search !== '', function (Builder $query) {
                $term = '%'.$this->search.'%';
                $query->where(function (Builder $sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhereHas('courseClass', fn (Builder $c) => $c
                            ->where('name', 'like', $term)
                            ->orWhere('join_key', 'like', $term));
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('date')
            ->paginate($this->perPage);

        // Hết giờ kết thúc thì tự động chốt buổi (và các phiên).
        foreach ($meetings as $meeting) {
            $meeting->closeIfExpired();
        }

        // Gộp số có mặt / đi muộn / vắng mức buổi qua tất cả phiên của buổi.
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
            $meeting->present_count = $counts['present'] ?? 0;
            $meeting->late_count = $counts['late'] ?? 0;
            $meeting->absent_count = $counts['absent'] ?? 0;
        }

        return view('livewire.lecturer.attendance.index', compact('meetings'))
            ->layout('layouts.user', ['title' => 'Quản lý điểm danh']);
    }
}
