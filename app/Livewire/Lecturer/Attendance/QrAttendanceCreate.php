<?php

namespace App\Livewire\Lecturer\Attendance;

use App\Livewire\Lecturer\Attendance\Concerns\OwnsAttendanceSessions;
use App\Models\AttendanceRecord;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\CourseClass;
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

    public int $gpsRadius = 100;

    public int $startLesson = 1;

    public int $endLesson = 3;

    public int $qrRefreshRate = 10;

    public bool $gpsEnabled = true;

    public bool $deviceCheck = true;

    public function mount(): void
    {
        $this->date = now()->toDateString();
        $firstClass = $this->availableClasses()->first();
        $this->classId = (string) ($firstClass?->id ?? '');
        $this->name = 'Buổi '.(($firstClass?->sessions()->count() ?? 0) + 1).' - Điểm danh QR';
    }

    public function save(): void
    {
        $validated = $this->validate([
            'classId' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'startTime' => ['nullable', 'date_format:H:i'],
            'endTime' => ['nullable', 'date_format:H:i'],
            'durationMinutes' => ['required', 'integer', 'min:1', 'max:180'],
            'gpsRadius' => ['required', 'integer', 'min:10', 'max:2000'],
            'startLesson' => ['required', 'integer', 'min:1', 'max:15'],
            'endLesson' => ['required', 'integer', 'min:1', 'max:15', 'gte:startLesson'],
            'qrRefreshRate' => ['required', 'integer', 'in:5,10,15,30'],
            'gpsEnabled' => ['boolean'],
            'deviceCheck' => ['boolean'],
        ], [
            'classId.required' => 'Vui lòng chọn lớp học.',
            'classId.integer' => 'Lớp học không hợp lệ.',
            'name.required' => 'Vui lòng nhập tiêu đề buổi học.',
            'date.required' => 'Vui lòng chọn ngày học.',
            'durationMinutes.required' => 'Vui lòng nhập thời lượng mở QR.',
            'gpsRadius.required' => 'Vui lòng nhập bán kính GPS.',
            'endLesson.gte' => 'Tiết kết thúc phải lớn hơn hoặc bằng tiết bắt đầu.',
        ]);

        $courseClass = $this->ownedClass((int) $validated['classId']);

        $session = ClassSession::query()->create([
            'class_id' => $courseClass->id,
            'created_by' => auth()->id(),
            'name' => $validated['name'],
            'date' => $validated['date'],
            'start_time' => $validated['startTime'] ?: null,
            'end_time' => $validated['endTime'] ?: null,
            'qr_token' => Str::upper(Str::random(24)),
            'token_expires_at' => now()->addMinutes($validated['durationMinutes']),
            'gps_radius' => $validated['gpsEnabled'] ? $validated['gpsRadius'] : null,
            'status' => 'active',
        ]);

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
                'total_sessions' => 15,
                'lessons_per_session' => 3,
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
