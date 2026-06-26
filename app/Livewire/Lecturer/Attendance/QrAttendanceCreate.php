<?php

namespace App\Livewire\Lecturer\Attendance;

use App\Livewire\Lecturer\Attendance\Concerns\OwnsAttendanceSessions;
use App\Models\AttendanceRecord;
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

    public int $startLesson = 1;

    public int $endLesson = 3;

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

    public function mount(): void
    {
        $this->editSessionId = request()->query('edit_session');

        if ($this->editSessionId) {
            $session = ClassSession::query()->findOrFail($this->editSessionId);
            abort_unless($session->created_by === auth()->id(), 403);

            $this->classId = (string) $session->class_id;
            $this->name = $session->name;
            $this->date = $session->date->format('Y-m-d');

            // Load other settings from cache for this class
            $this->loadConfigForClass($this->classId);

            // Phản ánh đúng số tiết đã lưu của buổi (DB chỉ lưu lesson_count, không lưu tiết bắt đầu/kết thúc).
            $savedLessonCount = max(1, (int) $session->lesson_count);
            $this->startLesson = 1;
            $this->endLesson = $savedLessonCount;

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

        } else {
            $this->date = now()->toDateString();
            
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
            $selectedClass = $this->ownedClass((int) $value);
            $this->loadConfigForClass($value);
        }
    }

    private function loadConfigForClass(string $classId): void
    {
        $selectedClass = $this->ownedClass((int) $classId);

        $config = cache()->get('qr_config_class_'.$classId);
        if ($config) {
            $this->startTime = $config['startTime'] ?? '07:00';
            $this->endTime = $config['endTime'] ?? '09:30';
            $this->durationMinutes = $config['durationMinutes'] ?? 15;
            $this->startLesson = $config['startLesson'] ?? 1;
            $this->endLesson = $config['endLesson'] ?? 3;
            $this->qrRefreshRate = $config['qrRefreshRate'] ?? 10;
            $this->gpsEnabled = $config['gpsEnabled'] ?? true;
            $this->deviceCheck = $config['deviceCheck'] ?? true;
            $this->gpsRadius = $config['gpsRadius'] ?? 100;
        } else {
            $this->gpsEnabled = false;
            $this->gpsRadius = 100;
        }

        // Tọa độ GPS sẽ luôn được lấy động từ vị trí hiện tại của thiết bị giáo viên khi bật
        $this->gpsLatitude = null;
        $this->gpsLongitude = null;
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
            'startLesson' => $this->startLesson,
            'endLesson' => $this->endLesson,
            'qrRefreshRate' => $this->qrRefreshRate,
            'gpsEnabled' => $this->gpsEnabled,
            'deviceCheck' => $this->deviceCheck,
        ], now()->addDays(30));

        session()->flash('success_config', 'Đã lưu cấu hình làm mặc định.');
    }

    public function save(): void
    {
        $validated = $this->validate([
            'classId' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'startTime' => ['nullable', 'date_format:H:i'],
            'endTime' => ['nullable', 'date_format:H:i'],
            'durationMinutes' => ['required', 'integer', 'in:10,15,20'],
            'gpsRadius' => ['required', 'integer', 'min:5', 'max:2500'],
            'startLesson' => ['required', 'integer', 'min:1', 'max:15'],
            'endLesson' => ['required', 'integer', 'min:1', 'max:15', 'gte:startLesson'],
            'qrRefreshRate' => ['required', 'integer', 'in:5,10,15,30'],
            'gpsEnabled' => ['boolean'],
            'gpsLatitude' => ['required_if:gpsEnabled,true', 'nullable', 'numeric'],
            'gpsLongitude' => ['required_if:gpsEnabled,true', 'nullable', 'numeric'],
            'deviceCheck' => ['boolean'],
        ], [
            'classId.required' => 'Vui lòng chọn lớp học.',
            'classId.integer' => 'Lớp học không hợp lệ.',
            'name.required' => 'Vui lòng nhập tiêu đề buổi học.',
            'date.required' => 'Vui lòng chọn ngày học.',
            'durationMinutes.required' => 'Vui lòng nhập thời lượng mở QR.',
            'gpsRadius.required' => 'Vui lòng nhập bán kính GPS.',
            'gpsRadius.min' => 'Bán kính tối thiểu là 5m.',
            'gpsRadius.max' => 'Bán kính tối đa là 2500m.',
            'gpsLatitude.required_if' => 'Vui lòng cho phép trình duyệt truy cập vị trí hiện tại để xác minh GPS.',
            'gpsLongitude.required_if' => 'Vui lòng cho phép trình duyệt truy cập vị trí hiện tại để xác minh GPS.',
            'endLesson.gte' => 'Tiết kết thúc phải lớn hơn hoặc bằng tiết bắt đầu.',
        ]);

        // Kiểm tra gói: giới hạn bán kính định vị GPS theo gói dịch vụ.
        if ($validated['gpsEnabled']) {
            $maxRadius = app(SubscriptionService::class)->maxGpsRadius(auth()->user());
            if ((int) $validated['gpsRadius'] > $maxRadius) {
                $this->addError('gpsRadius', "Gói hiện tại chỉ cho phép bán kính GPS tối đa {$maxRadius}m. Vui lòng giảm bán kính hoặc nâng cấp gói.");

                return;
            }
        }

        $courseClass = $this->ownedClass((int) $validated['classId']);
        $this->saveConfig();

        if ($courseClass->members()->where('status', 'active')->count() === 0) {
            $this->addError('classId', 'Vui lòng import danh sách lớp trước khi điểm danh.');
            $this->redirectRoute('lecturer.classes.show', ['ma_user' => auth()->id(), 'courseClass' => $courseClass->id, 'openImport' => 1], navigate: true);
            return;
        }

        if ($this->editSessionId) {
            $session = ClassSession::query()->findOrFail($this->editSessionId);
            abort_unless($session->created_by === auth()->id(), 403);

            $session->update([
                'class_id' => $courseClass->id,
                'name' => $validated['name'],
                'date' => $validated['date'],
                'start_time' => $validated['startTime'] ?: null,
                'end_time' => $validated['endTime'] ?: null,
                'lesson_count' => $validated['endLesson'] - $validated['startLesson'] + 1,
                'token_expires_at' => now()->addMinutes($validated['durationMinutes']),
                'qr_refresh_rate' => $validated['qrRefreshRate'],
                'gps_latitude' => $validated['gpsEnabled'] ? $this->gpsLatitude : null,
                'gps_longitude' => $validated['gpsEnabled'] ? $this->gpsLongitude : null,
                'gps_radius' => $validated['gpsEnabled'] ? $validated['gpsRadius'] : null,
            ]);

            session()->flash('success_config', 'Đã cập nhật thiết lập phiên điểm danh.');
        } else {
            $session = ClassSession::query()->create([
                'class_id' => $courseClass->id,
                'created_by' => auth()->id(),
                'name' => $validated['name'],
                'date' => $validated['date'],
                'start_time' => $validated['startTime'] ?: null,
                'end_time' => $validated['endTime'] ?: null,
                'lesson_count' => $validated['endLesson'] - $validated['startLesson'] + 1,
                'qr_token' => Str::upper(Str::random(24)),
                'token_expires_at' => now()->addMinutes($validated['durationMinutes']),
                'qr_refresh_rate' => $validated['qrRefreshRate'],
                'gps_latitude' => $validated['gpsEnabled'] ? $this->gpsLatitude : null,
                'gps_longitude' => $validated['gpsEnabled'] ? $this->gpsLongitude : null,
                'gps_radius' => $validated['gpsEnabled'] ? $validated['gpsRadius'] : null,
                'status' => 'active',
            ]);

            app(NotificationService::class)->attendanceSessionCreated((int) auth()->id(), $session, isQr: true);
        }

        $courseClass->members()->where('status', 'active')->get()->each(fn ($member) => AttendanceRecord::query()->firstOrCreate([
            'class_session_id' => $session->id,
            'class_member_id' => $member->id,
        ], [
            'status' => 'pending',
            'is_verified' => $member->user_id !== null,
        ]));

        $this->redirectRoute('lecturer.attendance.qr.session', ['ma_user' => auth()->id(), 'session' => $session->id], navigate: true);
    }

    public function render(): View
    {
        $classes = $this->availableClasses()
            ->loadCount(['members' => fn ($query) => $query->where('status', 'active')]);
        $selectedClass = $classes->firstWhere('id', (int) $this->classId) ?? $classes->first();
        $students = $selectedClass
            ? $selectedClass->members()->where('status', 'active')->orderBy('full_name')->get()
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
            ['code' => $code],
            [
                'owner_user_id' => $userId,
                'name' => 'Lớp demo điểm danh QR',
                'description' => 'Dữ liệu giả để kiểm thử trang điểm danh bằng QR.',
                'subject_code' => 'QR101',
                'semester' => 'HK2 2025-2026',
                'require_approval' => false,
                'status' => 'active',
                'total_lessons' => 45,
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
