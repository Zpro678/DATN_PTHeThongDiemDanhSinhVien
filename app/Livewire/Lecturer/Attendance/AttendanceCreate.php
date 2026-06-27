<?php

namespace App\Livewire\Lecturer\Attendance;

use App\Livewire\Lecturer\Attendance\Concerns\OwnsAttendanceSessions;
use App\Models\AttendanceRecord;
use App\Models\ClassMeeting;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Services\SubscriptionService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Livewire\Component;

class AttendanceCreate extends Component
{
    use OwnsAttendanceSessions;

    // Trạng thái wizard (1: Tạo thông tin cơ bản, 2: Chọn phương thức, 3: Cấu hình QR)
    public int $step = 1;
    public ?int $sessionId = null;

    // --- STEP 1: THÔNG TIN CƠ BẢN ---
    public string $classId = '';
    public string $name = '';
    public string $date = '';
    public string $startTime = '07:00';
    public string $endTime = '09:30';

    // Giờ kết thúc buổi (chỉ nhập giờ kết thúc; ngày = hôm nay, bắt đầu = lúc tạo).
    public string $meetingEndTime = '';

    // --- STEP 3: CẤU HÌNH QR ---
    public int $durationMinutes = 15;
    public int $gpsRadius = 100;
    public int $qrRefreshRate = 10;
    public bool $gpsEnabled = true;
    public bool $deviceCheck = true;
    public ?float $gpsLatitude = null;
    public ?float $gpsLongitude = null;

    public ?int $cloneSessionId = null;

    public function mount(): void
    {
        $this->sessionId = request()->query('session_id');
        $this->cloneSessionId = request()->query('clone_session');

        if ($this->sessionId) {
            $session = ClassSession::query()->findOrFail($this->sessionId);
            abort_unless($session->created_by === auth()->id(), 403);

            $this->step = 2; // Nếu truyền session_id vào, mở màn hình chọn phương thức
            $this->classId = (string) $session->class_id;
            $this->name = $session->name;
            $this->date = $session->date->format('Y-m-d');
            $this->startTime = $session->start_time ? \Carbon\Carbon::parse($session->start_time)->format('H:i') : '07:00';
            $this->endTime = $session->end_time ? \Carbon\Carbon::parse($session->end_time)->format('H:i') : '09:30';
        } elseif ($this->cloneSessionId) {
            $session = ClassSession::query()->findOrFail($this->cloneSessionId);
            abort_unless($session->created_by === auth()->id(), 403);

            $this->classId = (string) $session->class_id;
            $this->name = $session->name;
            $this->date = $session->date->format('Y-m-d');
            $this->startTime = $session->start_time ? \Carbon\Carbon::parse($session->start_time)->format('H:i') : '07:00';
            $this->endTime = $session->end_time ? \Carbon\Carbon::parse($session->end_time)->format('H:i') : '09:30';
        } else {
            // Buổi luôn diễn ra hôm nay, bắt đầu tại thời điểm tạo; chỉ cần nhập giờ kết thúc.
            $this->date = now()->toDateString();
            $defaultEnd = now()->addMinutes(90)->second(0);
            $defaultEnd->minute(intdiv($defaultEnd->minute, 5) * 5); // Làm tròn xuống bội số 5 phút cho khớp lưới chọn.
            $this->meetingEndTime = $defaultEnd->format('H:i');

            $preselectedClassId = request()->query('class_id');
            if ($preselectedClassId && $this->availableClasses()->contains('id', $preselectedClassId)) {
                $this->classId = (string) $preselectedClassId;
            } else {
                $firstClass = $this->availableClasses()->first();
                $this->classId = (string) ($firstClass?->id ?? '');
            }
        }
    }

    // ========== BƯỚC 1: LƯU THÔNG TIN CƠ BẢN VÀ CHỌN PHƯƠNG THỨC ==========
    public function createManualSession(): void
    {
        $session = $this->createBaseSession('active');
        if (!$session) return;
        
        $this->redirectRoute('lecturer.attendance.manual.session', ['ma_user' => auth()->id(), 'session' => $session->id], navigate: true);
    }

    public function createQrSession(): void
    {
        $session = $this->createBaseSession('pending');
        if (!$session) return;

        $this->sessionId = $session->id;
        $this->loadConfigForClass($this->classId);
        $this->step = 3; // Chuyển thẳng sang bước cấu hình QR
    }

    private function createBaseSession(string $status): ?ClassSession
    {
        $validated = $this->validate([
            'classId' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'meetingEndTime' => ['required', 'date_format:H:i'],
        ], [
            'classId.required' => 'Vui lòng chọn lớp học.',
            'classId.integer' => 'Lớp học không hợp lệ.',
            'name.required' => 'Vui lòng nhập tiêu đề buổi học.',
            'meetingEndTime.required' => 'Vui lòng nhập giờ kết thúc buổi điểm danh.',
            'meetingEndTime.date_format' => 'Giờ kết thúc không hợp lệ.',
        ]);

        // Ngày = hôm nay, giờ bắt đầu = lúc tạo; chỉ nhận giờ kết thúc và phải cách hiện tại >= 10 phút.
        $date = now()->toDateString();
        $startTime = now()->format('H:i');
        $endsAt = \Carbon\Carbon::parse($date.' '.$validated['meetingEndTime'].':00');

        if ($endsAt->lessThanOrEqualTo(now()->addMinutes(10))) {
            $this->addError('meetingEndTime', 'Giờ kết thúc phải sau thời điểm hiện tại ít nhất 10 phút.');
            return null;
        }

        $courseClass = $this->ownedClass((int) $validated['classId']);

        if ($courseClass->members()->where('status', 'active')->count() === 0) {
            $this->addError('classId', 'Vui lòng import danh sách lớp trước khi điểm danh.');
            $this->redirectRoute('lecturer.classes.show', ['ma_user' => auth()->id(), 'courseClass' => $courseClass->id, 'openImport' => 1], navigate: true);
            return null;
        }

        // Giới hạn theo SỐ BUỔI dự kiến của lớp.
        $totalSessions = (int) $courseClass->total_sessions;
        $createdMeetings = (int) $courseClass->meetings()->count();

        if ($totalSessions > 0 && $createdMeetings >= $totalSessions) {
            $this->addError('name', "Đã tạo đủ {$createdMeetings}/{$totalSessions} buổi dự kiến. Vui lòng tăng tổng số buổi trong cài đặt lớp nếu cần tạo thêm.");
            return null;
        }

        // Mỗi lần "Tạo buổi điểm danh" tạo một BUỔI mới và phiên đầu tiên của buổi đó.
        $meeting = ClassMeeting::query()->create([
            'class_id' => $courseClass->id,
            'created_by' => auth()->id(),
            'name' => $validated['name'],
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $validated['meetingEndTime'],
            'status' => 'active',
        ]);

        $session = ClassSession::query()->create([
            'class_id' => $courseClass->id,
            'meeting_id' => $meeting->id,
            'created_by' => auth()->id(),
            'name' => $validated['name'],
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $validated['meetingEndTime'],
            'status' => $status,
        ]);

        // Phiên thủ công (status 'active') mặc định "Có mặt"; phiên QR (status 'pending') giữ "chưa điểm danh".
        $defaultStatus = $status === 'pending' ? 'pending' : 'present';

        $courseClass->members()->where('status', 'active')->get()->each(fn ($member) => AttendanceRecord::query()->firstOrCreate([
            'class_session_id' => $session->id,
            'class_member_id' => $member->id,
        ], [
            'status' => $defaultStatus,
            'is_verified' => $member->user_id !== null,
        ]));

        return $session;
    }

    private function loadConfigForClass(string $classId): void
    {
        $selectedClass = $this->ownedClass((int) $classId);

        $config = cache()->get('qr_config_class_'.$classId);
        if ($config) {
            $this->durationMinutes = $config['durationMinutes'] ?? 15;
            $this->qrRefreshRate = $config['qrRefreshRate'] ?? 10;
            $this->gpsEnabled = $config['gpsEnabled'] ?? true;
            $this->deviceCheck = $config['deviceCheck'] ?? true;
            $this->gpsRadius = $config['gpsRadius'] ?? 100;
        }

        if ($selectedClass->gps_latitude !== null && $selectedClass->gps_longitude !== null) {
            $this->gpsLatitude = $selectedClass->gps_latitude;
            $this->gpsLongitude = $selectedClass->gps_longitude;
            $this->gpsRadius = $selectedClass->gps_radius ?? 100;
            $this->gpsEnabled = true;
        } else {
            if (! $config || ! isset($config['gpsEnabled'])) {
                $this->gpsEnabled = false;
                $this->gpsLatitude = null;
                $this->gpsLongitude = null;
            }
        }
    }

    public function saveConfig(): void
    {
        if (! $this->classId) {
            return;
        }

        cache()->put('qr_config_class_'.$this->classId, [
            'durationMinutes' => $this->durationMinutes,
            'gpsRadius' => $this->gpsRadius,
            'qrRefreshRate' => $this->qrRefreshRate,
            'gpsEnabled' => $this->gpsEnabled,
            'deviceCheck' => $this->deviceCheck,
        ], now()->addDays(30));

        session()->flash('success_config', 'Đã lưu cấu hình làm mặc định.');
    }

    // ========== BƯỚC 3: CẤU HÌNH QR VÀ BẮT ĐẦU ==========
    public function setupQr(): void
    {
        $validated = $this->validate([
            'durationMinutes' => ['required', 'integer', 'in:10,15,20'],
            'gpsRadius' => ['required', 'integer', 'min:5', 'max:2500'],
            'qrRefreshRate' => ['required', 'integer', 'in:5,10,15,30'],
            'gpsEnabled' => ['boolean'],
            'gpsLatitude' => ['nullable', 'numeric'],
            'gpsLongitude' => ['nullable', 'numeric'],
            'deviceCheck' => ['boolean'],
        ]);

        if ($validated['gpsEnabled']) {
            $maxRadius = app(SubscriptionService::class)->maxGpsRadius(auth()->user());
            if ((int) $validated['gpsRadius'] > $maxRadius) {
                $this->addError('gpsRadius', "Gói hiện tại chỉ cho phép bán kính GPS tối đa {$maxRadius}m. Vui lòng giảm bán kính hoặc nâng cấp gói.");
                return;
            }
        }

        $this->saveConfig();

        $session = ClassSession::query()->findOrFail($this->sessionId);
        $session->update([
            'qr_token' => Str::upper(Str::random(24)),
            'token_expires_at' => now()->addMinutes($validated['durationMinutes']),
            'qr_refresh_rate' => $validated['qrRefreshRate'],
            'gps_latitude' => $validated['gpsEnabled'] ? $this->gpsLatitude : null,
            'gps_longitude' => $validated['gpsEnabled'] ? $this->gpsLongitude : null,
            'gps_radius' => $validated['gpsEnabled'] ? $validated['gpsRadius'] : null,
            'status' => 'active',
        ]);

        $this->redirectRoute('lecturer.attendance.qr.session', ['ma_user' => auth()->id(), 'session' => $session->id], navigate: true);
    }

    public function backToStep(int $step): void
    {
        $this->step = $step;
    }

    // ========== RENDERING ==========
    public function render(): View
    {
        $classes = $this->availableClasses()
            ->loadCount(['members' => fn ($query) => $query->where('status', 'active')]);
            
        $selectedClass = $classes->firstWhere('id', (int) $this->classId) ?? $classes->first();

        return view('livewire.lecturer.attendance.create', compact('classes', 'selectedClass'))
            ->layout('layouts.user', ['title' => 'Tạo buổi điểm danh']);
    }

    private function availableClasses(): Collection
    {
        $classes = $this->ownedClasses();

        if ($classes->isNotEmpty()) {
            return $classes;
        }

        $this->createDemoClassForCurrentUser();

        return $this->ownedClasses();
    }

    private function createDemoClassForCurrentUser(): void
    {
        $userId = auth()->id();
        $code = 'DEMO-'.$userId.'-ATT';

        $courseClass = CourseClass::withTrashed()->firstOrCreate(
            ['code' => $code],
            [
                'owner_user_id' => $userId,
                'name' => 'Lớp demo điểm danh',
                'description' => 'Dữ liệu giả để kiểm thử trang tạo điểm danh.',
                'subject_code' => 'DEMO101',
                'semester' => 'HK2 2025-2026',
                'require_approval' => false,
                'status' => 'active',
                'total_sessions' => 15,
            ],
        );

        $courseClass->restore();
        $courseClass->update([
            'owner_user_id' => $userId,
            'status' => 'active',
        ]);

        collect([
            ['DEMO001', 'Nguyễn Văn An'],
            ['DEMO002', 'Trần Thị Bình'],
            ['DEMO003', 'Lê Minh Cường'],
            ['DEMO004', 'Phạm Thanh Duy'],
        ])->each(function (array $student) use ($courseClass): void {
            $member = ClassMember::withTrashed()->firstOrNew([
                'class_id' => $courseClass->id,
                'student_code' => $student[0],
            ]);

            $member->fill([
                'full_name' => $student[1],
                'user_id' => null,
                'status' => 'active',
            ]);
            $member->save();
            $member->restore();
        });
    }
}
