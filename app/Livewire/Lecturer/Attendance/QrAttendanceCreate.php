<?php

namespace App\Livewire\Lecturer\Attendance;

use App\Livewire\Lecturer\Attendance\Concerns\OwnsAttendanceSessions;
use App\Models\AttendanceRecord;
use App\Models\ClassMeeting;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Services\NotificationService;
use App\Services\SubscriptionService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Livewire\Component;

class QrAttendanceCreate extends Component
{
    use OwnsAttendanceSessions;

    public string $classId = '';

    public string $name = '';

    public string $date = '';

    public string $startTime = '07:00';

    public string $endTime = '09:30';

    public int $durationMinutes = 15;

    // Bán kính cho phép sinh viên điểm danh bằng GPS (tính bằng mét)
    public int $gpsRadius = 100;

    // Thời gian làm mới mã QR (tính bằng giây)
    public int $qrRefreshRate = 10;

    // Kích hoạt tính năng yêu cầu sinh viên bật GPS khi điểm danh
    public bool $gpsEnabled = true;

    // Kích hoạt tính năng kiểm tra thiết bị của sinh viên (ngăn chặn dùng nhiều thiết bị)
    public bool $deviceCheck = true;

    // Tọa độ vĩ độ (Latitude) điểm danh của giáo viên hoặc lớp học
    public ?float $gpsLatitude = null;

    // Tọa độ kinh độ (Longitude) điểm danh của giáo viên hoặc lớp học
    public ?float $gpsLongitude = null;

    public ?int $editSessionId = null;
    public ?int $cloneSessionId = null;
    public ?int $meetingId = null;

    public function mount(): void
    {
        $this->editSessionId = request()->query('edit_session');
        $this->cloneSessionId = request()->query('clone_session');
        $this->meetingId = request()->query('meeting');

        if ($this->meetingId) {
            $meeting = ClassMeeting::query()->with('courseClass')->findOrFail($this->meetingId);
            abort_unless($meeting->courseClass->owner_user_id === auth()->id(), 403);

            $this->classId = (string) $meeting->class_id;
            $this->loadConfigForClass($this->classId);

            $this->name = $meeting->name;
            $this->date = $meeting->date->format('Y-m-d');
            $this->startTime = $meeting->start_time ? \Carbon\Carbon::parse($meeting->start_time)->format('H:i') : '07:00';
            $this->endTime = $meeting->end_time ? \Carbon\Carbon::parse($meeting->end_time)->format('H:i') : '09:30';
        } elseif ($this->editSessionId) {
            $session = ClassSession::query()->findOrFail($this->editSessionId);
            abort_unless($session->created_by === auth()->id(), 403);

            $this->classId = (string) $session->class_id;
            $this->name = $session->name;
            $this->date = $session->date->format('Y-m-d');

            // Load other settings from cache for this class
            $this->loadConfigForClass($this->classId);

            // Override with actual saved DB values if they differ
            $this->gpsEnabled = $session->gps_latitude !== null;
            if ($this->gpsEnabled) {
                $this->gpsLatitude = $session->gps_latitude;
                $this->gpsLongitude = $session->gps_longitude;
                $this->gpsRadius = $session->gps_radius ?? $this->gpsRadius;
            }
            if ($session->qr_refresh_rate) {
                $this->qrRefreshRate = $session->qr_refresh_rate;
            }

        } elseif ($this->cloneSessionId) {
            $session = ClassSession::query()->findOrFail($this->cloneSessionId);
            abort_unless($session->created_by === auth()->id(), 403);

            $this->classId = (string) $session->class_id;
            
            $this->loadConfigForClass($this->classId);

            $this->name = $session->name;
            $this->date = $session->date->format('Y-m-d');
        } else {
            $preselectedDate = request()->query('date');
            $this->date = $preselectedDate ?: now()->toDateString();
            
            $preselectedClassId = request()->query('class_id');
            if ($preselectedClassId && $this->availableClasses()->contains('id', $preselectedClassId)) {
                $this->classId = (string) $preselectedClassId;
            } else {
                $firstClass = $this->availableClasses()->first();
                $this->classId = (string) ($firstClass?->id ?? '');
            }
            
            if ($this->classId) {
                $this->loadConfigForClass($this->classId);
            }
        }
    }

    public function updatedClassId($value): void
    {
        if ($value) {
            $selectedClass = $this->ownedClass($value);
            $this->loadConfigForClass($value);
        }
    }

    private function loadConfigForClass(string $classId): void
    {
        $selectedClass = $this->ownedClass($classId);

        $config = cache()->get('qr_config_class_'.$classId);
        if ($config) {
            $this->startTime = $config['startTime'] ?? '07:00';
            $this->endTime = $config['endTime'] ?? '09:30';
            $this->durationMinutes = $config['durationMinutes'] ?? 15;
            $this->qrRefreshRate = $config['qrRefreshRate'] ?? 10;
            $this->deviceCheck = $config['deviceCheck'] ?? true;
            $this->gpsRadius = $config['gpsRadius'] ?? 100;
        }

        if ($selectedClass && $selectedClass->gps_latitude !== null) {
            $this->gpsEnabled = true;
            $this->gpsLatitude = (float) $selectedClass->gps_latitude;
            $this->gpsLongitude = (float) $selectedClass->gps_longitude;
            $this->gpsRadius = (int) ($selectedClass->gps_radius ?? 100);
        } elseif (! $config) {
            $this->gpsEnabled = false;
            $this->gpsRadius = 100;
            $this->gpsLatitude = null;
            $this->gpsLongitude = null;
        }
    }

    public function saveConfig(): void
    {
        if (! $this->classId) {
            return;
        }

        cache()->put('qr_config_class_'.$this->classId, [
            'startTime' => $this->startTime,
            'endTime' => $this->endTime,
            'durationMinutes' => $this->durationMinutes,
            'gpsRadius' => $this->gpsRadius,
            'qrRefreshRate' => $this->qrRefreshRate,
            'gpsEnabled' => $this->gpsEnabled,
            'deviceCheck' => $this->deviceCheck,
        ], now()->addDays(30));

        session()->flash('success_config', 'Đã lưu cấu hình làm mặc định.');
    }

    public function save(): void
    {
        $validated = $this->validate([
            'classId' => ['required', 'string', 'exists:classes,id'],
            'name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'startTime' => ['nullable', 'date_format:H:i'],
            'endTime' => ['nullable', 'date_format:H:i'],
            'durationMinutes' => ['required', 'integer', 'in:10,15,20'],
            'gpsRadius' => ['required', 'integer', 'min:5', 'max:2500'],
            'qrRefreshRate' => ['required', 'integer', 'in:5,10,15,30'],
            'gpsEnabled' => ['boolean'],
            'gpsLatitude' => ['required_if:gpsEnabled,true', 'nullable', 'numeric'],
            'gpsLongitude' => ['required_if:gpsEnabled,true', 'nullable', 'numeric'],
            'deviceCheck' => ['boolean'],
        ], [
            'classId.required' => 'Vui lòng chọn lớp học.',
            'classId.exists' => 'Lớp học không hợp lệ.',
            'name.required' => 'Vui lòng nhập tiêu đề buổi học.',
            'date.required' => 'Vui lòng chọn ngày học.',
            'durationMinutes.required' => 'Vui lòng nhập thời lượng mở QR.',
            'gpsRadius.required' => 'Vui lòng nhập bán kính GPS.',
            'gpsRadius.min' => 'Bán kính tối thiểu là 5m.',
            'gpsRadius.max' => 'Bán kính tối đa là 2500m.',
            'gpsLatitude.required_if' => 'Vui lòng cho phép trình duyệt truy cập vị trí hiện tại để xác minh GPS.',
            'gpsLongitude.required_if' => 'Vui lòng cho phép trình duyệt truy cập vị trí hiện tại để xác minh GPS.',
        ]);

        // Kiểm tra gói: giới hạn bán kính định vị GPS theo gói dịch vụ.
        if ($validated['gpsEnabled']) {
            $maxRadius = app(SubscriptionService::class)->maxGpsRadius(auth()->user());
            if ((int) $validated['gpsRadius'] > $maxRadius) {
                $this->addError('gpsRadius', "Gói hiện tại chỉ cho phép bán kính GPS tối đa {$maxRadius}m. Vui lòng giảm bán kính hoặc nâng cấp gói.");

                return;
            }
        }

        $courseClass = $this->ownedClass($validated['classId']);

        if ($courseClass->members()->where('status', \App\Models\ClassMember::STATUS_ACTIVE)->count() === 0) {
            $this->addError('classId', 'Vui lòng import danh sách lớp trước khi điểm danh.');
            $this->redirectRoute('lecturer.classes.show', ['ma_user' => auth()->id(), 'courseClass' => $courseClass->id, 'openImport' => 1], navigate: true);
            return;
        }

        $this->saveConfig();

        $qrFields = [
            'qr_token' => ClassSession::generateQrToken(),
            // Hạn token ban đầu ngắn theo nhịp làm mới; sau đó refreshToken sẽ xoay liên tục.
            'token_expires_at' => ClassSession::qrTokenExpiryFor((int) $validated['qrRefreshRate']),
            'qr_refresh_rate' => $validated['qrRefreshRate'],
            'gps_latitude' => $validated['gpsEnabled'] ? $this->gpsLatitude : null,
            'gps_longitude' => $validated['gpsEnabled'] ? $this->gpsLongitude : null,
            'gps_radius' => $validated['gpsEnabled'] ? $validated['gpsRadius'] : null,
            'device_check' => (bool) ($validated['deviceCheck'] ?? true),
        ];

        // ===== Thêm phiên QR vào buổi đã có =====
        if ($this->meetingId) {
            $meeting = ClassMeeting::query()
                ->whereHas('courseClass', fn ($query) => $query->where('owner_user_id', auth()->id()))
                ->findOrFail($this->meetingId);

            if (! $meeting->canAddSession()) {
                $this->addError('name', 'Buổi điểm danh đã kết thúc, không thể thêm phiên mới.');
                return;
            }

            $meeting->update(['status' => 'active']);
            $session = $meeting->createSession('active', $qrFields);

            app(NotificationService::class)->attendanceSessionCreated((int) auth()->id(), $session, isQr: true);
            $this->redirectRoute('lecturer.attendance.qr.session', ['ma_user' => auth()->id(), 'session' => $session->id], navigate: true);
            return;
        }

        // ===== Chỉnh sửa thiết lập phiên QR hiện có =====
        if ($this->editSessionId) {
            $session = ClassSession::query()->findOrFail($this->editSessionId);
            abort_unless($session->created_by === auth()->id(), 403);

            $meetingFields = [
                'name' => $validated['name'],
                'date' => $validated['date'],
            ];

            $session->update(array_merge($meetingFields, $qrFields));

            // Đồng bộ thông tin buổi để các trang khác hiển thị nhất quán.
            $session->meeting?->update($meetingFields);

            $courseClass->members()->where('status', \App\Models\ClassMember::STATUS_ACTIVE)->get()->each(fn ($member) => AttendanceRecord::query()->firstOrCreate([
                'class_session_id' => $session->id,
                'class_member_id' => $member->id,
            ], [
                'status' => 'pending',
                'is_account' => $member->user_id !== null,
            ]));

            session()->flash('success_config', 'Đã cập nhật thiết lập phiên điểm danh.');
            $this->redirectRoute('lecturer.attendance.qr.session', ['ma_user' => auth()->id(), 'session' => $session->id], navigate: true);
            return;
        }

        // ===== Tạo buổi điểm danh QR mới (buổi + phiên đầu tiên) =====
        $totalSessions = (int) $courseClass->total_sessions;
        $createdMeetings = (int) $courseClass->meetings()->count();

        if ($totalSessions > 0 && $createdMeetings >= $totalSessions) {
            $this->addError('name', "Đã tạo đủ {$createdMeetings}/{$totalSessions} buổi dự kiến. Vui lòng tăng tổng số buổi trong cài đặt lớp nếu cần tạo thêm.");
            return;
        }

        // Buổi luôn diễn ra hôm nay, bắt đầu lúc tạo; giờ kết thúc tự động tính (mặc định +90 phút).
        $date = now()->toDateString();
        $startTime = now()->format('H:i');
        $endsAt = now()->copy()->addMinutes(max(90, (int)$validated['durationMinutes']))->second(0);
        $endTime = $endsAt->format('H:i');

        if ($endsAt->lessThanOrEqualTo(now()->addMinutes(10))) {
            $this->addError('name', 'Giờ kết thúc không hợp lệ, vui lòng thử lại.');
            return;
        }

        $meeting = ClassMeeting::query()->create([
            'class_id' => $courseClass->id,
            'user_Created' => auth()->id(),
            'name' => $validated['name'],
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'active',
        ]);

        $session = $meeting->createSession('active', $qrFields);

        app(NotificationService::class)->attendanceSessionCreated((int) auth()->id(), $session, isQr: true);
        $this->redirectRoute('lecturer.attendance.qr.session', ['ma_user' => auth()->id(), 'session' => $session->id], navigate: true);
    }

    public function render(): View
    {
        $classes = $this->availableClasses()
            ->loadCount(['members' => fn ($query) => $query->where('status', \App\Models\ClassMember::STATUS_ACTIVE)]);
        $selectedClass = $classes->firstWhere('id', $this->classId) ?? $classes->first();
        $students = $selectedClass
            ? $selectedClass->members()->where('status', \App\Models\ClassMember::STATUS_ACTIVE)->with('profile')->get()->sortBy(fn ($m) => $m->display_name)->values()
            : collect();

        return view('livewire.lecturer.attendance.qr-create', compact('classes', 'selectedClass', 'students'))
            ->layout('layouts.user', ['title' => 'Điểm danh QR']);
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
        $code = 'DEMO-'.$userId.'-QR';

        $courseClass = CourseClass::withTrashed()->firstOrCreate(
            ['join_key' => $code],
            [
                'owner_user_id' => $userId,
                'name' => 'Lớp demo điểm danh QR',
                'description' => 'Dữ liệu giả để kiểm thử trang điểm danh bằng QR.',
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
            ['QR001', 'Nguyễn Minh Anh'],
            ['QR002', 'Trần Gia Bảo'],
            ['QR003', 'Lê Hoàng Nam'],
            ['QR004', 'Phạm Thùy Linh'],
            ['QR005', 'Võ Quốc Việt'],
            ['QR006', 'Đặng Phương Thảo'],
            ['QR007', 'Hoàng Đức Huy'],
            ['QR008', 'Bùi Khánh Vy'],
        ])->each(function (array $student) use ($courseClass): void {
            $member = ClassMember::withTrashed()
                ->where('class_id', $courseClass->id)
                ->whereHas('profile', fn ($p) => $p->where('student_code', $student[0]))
                ->first();

            if (! $member) {
                $member = ClassMember::create([
                    'class_id' => $courseClass->id,
                    'user_id' => null,
                    'status' => ClassMember::STATUS_ACTIVE,
                ]);
            } else {
                $member->restore();
                $member->update(['status' => ClassMember::STATUS_ACTIVE]);
            }

            $member->syncProfile(['student_code' => $student[0], 'full_name' => $student[1]]);
        });
    }
}
