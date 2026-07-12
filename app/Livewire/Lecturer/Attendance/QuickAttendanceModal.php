<?php

namespace App\Livewire\Lecturer\Attendance;

use App\Livewire\Lecturer\Attendance\Concerns\OwnsAttendanceSessions;
use App\Models\ClassMeeting;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Services\NotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class QuickAttendanceModal extends Component
{
    use OwnsAttendanceSessions;

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

    public bool $lockClassSelector = false;
    public bool $lockMeetingSelector = false;

    public bool $selectedClassHasStudents = true;

    #[On('open-quick-attendance-modal')]
    public function open(string $type = 'qr', ?string $classId = null, int|string|null $meetingId = null): void
    {
        $this->resetValidation();
        $this->resetQuickState($type);

        if ($meetingId) {
            $meeting = ClassMeeting::query()
                ->with('courseClass')
                ->whereHas('courseClass', fn ($query) => $query->managedBy(auth()->id()))
                ->findOrFail($meetingId);

            if (! $meeting->canAddSession()) {
                $this->dispatch('toast', message: 'Buổi điểm danh đã kết thúc, không thể thêm phiên mới.', type: 'error');
                return;
            }

            if (! $this->classHasStudents($meeting->courseClass)) {
                $this->dispatch('toast', message: 'Vui lòng import danh sách lớp trước khi điểm danh.', type: 'error');
                return;
            }

            $this->quickClassId = (string) $meeting->class_id;
            $this->quickMeetingId = (string) $meeting->id;
            $this->lockClassSelector = true;
            $this->lockMeetingSelector = true;
            $this->meetingEndTime = $meeting->end_time
                ? \Carbon\Carbon::parse($meeting->end_time)->format('H:i')
                : $this->defaultMeetingEndTime();
            $this->sessionName = $this->nextSessionName($meeting);
            $this->showQuickStart = true;

            return;
        }

        if ($classId) {
            $courseClass = $this->ownedClass($classId);

            if (! $this->classHasStudents($courseClass)) {
                $this->dispatch('toast', message: 'Vui lòng import danh sách lớp trước khi điểm danh.', type: 'error');
                return;
            }

            $this->quickClassId = (string) $courseClass->id;
            $this->lockClassSelector = true;
        }

        $this->showQuickStart = true;
    }

    public function updatedQuickClassId(): void
    {
        $this->quickMeetingId = '';
        $this->newMeetingName = '';
        $this->sessionName = 'Phiên 1';
        $this->resetErrorBag('quickClassId');

        if ($this->quickClassId) {
            $courseClass = $this->ownedClass($this->quickClassId);
            $this->selectedClassHasStudents = $this->classHasStudents($courseClass);
            
            if (! $this->selectedClassHasStudents) {
                $this->addError('quickClassId', 'Vui lòng import danh sách lớp trước khi điểm danh.');
            }
        } else {
            $this->selectedClassHasStudents = true;
        }
    }

    public function updatedQuickMeetingId(): void
    {
        if ($this->quickMeetingId === '' || $this->quickMeetingId === 'NEW') {
            $this->sessionName = 'Phiên 1';
            return;
        }

        $meeting = ClassMeeting::query()
            ->where('class_id', $this->quickClassId)
            ->find($this->quickMeetingId);

        $this->sessionName = $meeting ? $this->nextSessionName($meeting) : 'Phiên 1';
    }

    public function startQuick(): void
    {
        $rules = [
            'quickClassId' => ['required', 'string', 'exists:classes,id'],
            'sessionName' => ['required', 'string', 'max:255'],
            'meetingEndTime' => ['required', 'date_format:H:i'],
        ];

        $messages = [
            'quickClassId.required' => 'Vui lòng chọn lớp học.',
            'quickClassId.exists' => 'Lớp học không hợp lệ.',
            'sessionName.required' => 'Vui lòng nhập tên phiên.',
            'meetingEndTime.required' => 'Vui lòng chọn thời gian kết thúc.',
            'meetingEndTime.date_format' => 'Thời gian kết thúc không hợp lệ.',
        ];

        if ($this->quickMeetingId === 'NEW') {
            $rules['newMeetingName'] = ['required', 'string', 'max:255'];
            $messages['newMeetingName.required'] = 'Vui lòng nhập tên buổi học mới.';
        } else {
            $rules['quickMeetingId'] = ['required', 'integer', 'exists:class_meetings,id'];
            $messages['quickMeetingId.required'] = 'Vui lòng chọn buổi học.';
            $messages['quickMeetingId.exists'] = 'Buổi học không hợp lệ.';
        }

        if ($this->quickStartType === 'qr') {
            $rules['durationMinutes'] = ['required', 'integer', 'min:1'];
            $rules['qrRefreshRate'] = ['required', 'integer', 'in:5,10,15,30'];
            $rules['gpsRadius'] = ['required', 'integer', 'min:20'];

            if ($this->gpsEnabled) {
                $rules['gpsLatitude'] = ['required', 'numeric'];
                $rules['gpsLongitude'] = ['required', 'numeric'];
                $messages['gpsLatitude.required'] = 'Vui lòng cấp quyền truy cập vị trí GPS để chống gian lận.';
                $messages['gpsLongitude.required'] = 'Vui lòng cấp quyền truy cập vị trí GPS để chống gian lận.';
            }
        }

        $this->validate($rules, $messages);

        $courseClass = $this->ownedClass($this->quickClassId);
        if (! $this->classHasStudents($courseClass)) {
            $this->addError('quickClassId', 'Vui lòng import danh sách lớp trước khi điểm danh.');
            return;
        }

        $meeting = $this->resolveMeeting($courseClass);
        if (! $meeting) {
            return;
        }

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

            $this->redirectRoute('lecturer.attendance.manual.session', [
                'ma_user' => auth()->id(),
                'session' => $session->id,
            ], navigate: true);

            return;
        }

        $session = $meeting->createSession('active', [
            'name' => $this->sessionName,
            'qr_token' => ClassSession::generateQrToken(),
            'token_expires_at' => ClassSession::qrTokenExpiryFor((int) $this->qrRefreshRate),
            'qr_refresh_rate' => $this->qrRefreshRate,
            'gps_latitude' => $this->gpsEnabled ? $this->gpsLatitude : null,
            'gps_longitude' => $this->gpsEnabled ? $this->gpsLongitude : null,
            'gps_radius' => $this->gpsEnabled ? $this->gpsRadius : null,
            'device_check' => true,
        ]);

        app(NotificationService::class)->attendanceSessionCreated((int) auth()->id(), $session, isQr: true);

        $this->redirectRoute('lecturer.attendance.qr.session', [
            'ma_user' => auth()->id(),
            'session' => $session->id,
        ], navigate: true);
    }

    #[Computed]
    public function activeClasses(): Collection
    {
        if ($this->quickClassId) {
            $courseClass = CourseClass::query()
                ->managedBy(auth()->id())
                ->find($this->quickClassId);

            return $courseClass ? collect([$courseClass]) : collect();
        }

        return $this->ownedClasses();
    }

    #[Computed]
    public function classMeetings(): Collection
    {
        if (! $this->quickClassId) {
            return collect();
        }

        return ClassMeeting::query()
            ->where('class_id', $this->quickClassId)
            ->whereDate('date', now()->toDateString())
            ->orderByDesc('created_at')
            ->get()
            ->filter(fn (ClassMeeting $meeting) => ! $meeting->isExpired())
            ->values();
    }

    public function render(): View
    {
        return view('livewire.lecturer.attendance.quick-attendance-modal');
    }

    private function resetQuickState(string $type): void
    {
        $this->quickStartType = in_array($type, ['manual', 'qr'], true) ? $type : 'qr';
        $this->quickClassId = '';
        $this->quickMeetingId = '';
        $this->newMeetingName = '';
        $this->meetingEndTime = $this->defaultMeetingEndTime();
        $this->sessionName = 'Phiên 1';
        $this->durationMinutes = 15;
        $this->gpsRadius = 100;
        $this->qrRefreshRate = 10;
        $this->gpsEnabled = true;
        $this->gpsLatitude = null;
        $this->gpsLongitude = null;
        $this->lockClassSelector = false;
        $this->lockMeetingSelector = false;
        $this->selectedClassHasStudents = true;
    }

    private function defaultMeetingEndTime(): string
    {
        return now()->addMinutes(120)->format('H:i');
    }

    private function nextSessionName(ClassMeeting $meeting): string
    {
        return 'Phiên '.($meeting->sessions()->count() + 1);
    }

    private function classHasStudents(CourseClass $courseClass): bool
    {
        return $courseClass->members()
            ->where('status', ClassMember::STATUS_ACTIVE)
            ->exists();
    }

    private function resolveMeeting(CourseClass $courseClass): ?ClassMeeting
    {
        if ($this->quickMeetingId === 'NEW') {
            return ClassMeeting::query()->create([
                'class_id' => $courseClass->id,
                'user_Created' => auth()->id(),
                'name' => $this->newMeetingName,
                'date' => now()->toDateString(),
                'start_time' => now()->format('H:i'),
                'end_time' => $this->meetingEndTime,
                'status' => 'active',
            ]);
        }

        return ClassMeeting::query()
            ->where('class_id', $courseClass->id)
            ->findOrFail($this->quickMeetingId);
    }
}
