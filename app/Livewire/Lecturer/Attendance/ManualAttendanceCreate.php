<?php

namespace App\Livewire\Lecturer\Attendance;

use App\Livewire\Lecturer\Attendance\Concerns\OwnsAttendanceSessions;
use App\Models\AttendanceRecord;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\CourseClass;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class ManualAttendanceCreate extends Component
{
    use OwnsAttendanceSessions;

    public string $classId = '';

    public string $name = '';

    public string $date = '';

    public string $startTime = '07:00';

    public string $endTime = '09:30';

    public int $startPeriod = 1;

    public int $endPeriod = 3;

    public string $note = '';

    public function mount(): void
    {
        $this->date = now()->toDateString();
        
        $preselectedClassId = request()->query('class_id');
        if ($preselectedClassId && $this->availableClasses()->contains('id', $preselectedClassId)) {
            $this->classId = (string) $preselectedClassId;
        } else {
            $this->classId = (string) ($this->availableClasses()->first()?->id ?? '');
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'classId' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'startTime' => ['nullable', 'date_format:H:i'],
            'endTime' => ['nullable', 'date_format:H:i'],
            'startPeriod' => ['required', 'integer', 'min:1', 'max:12'],
            'endPeriod' => ['required', 'integer', 'min:1', 'max:12', 'gte:startPeriod'],
            'note' => ['nullable', 'string', 'max:2000'],
        ], [
            'classId.required' => 'Vui lòng chọn lớp học.',
            'classId.integer' => 'Lớp học không hợp lệ.',
            'name.required' => 'Vui lòng nhập tên buổi học.',
            'date.required' => 'Vui lòng chọn ngày học.',
            'date.date' => 'Ngày học không hợp lệ.',
            'startTime.date_format' => 'Giờ bắt đầu không hợp lệ.',
            'endTime.date_format' => 'Giờ kết thúc không hợp lệ.',
            'startPeriod.required' => 'Vui lòng chọn tiết bắt đầu.',
            'endPeriod.required' => 'Vui lòng chọn tiết kết thúc.',
            'endPeriod.gte' => 'Tiết kết thúc phải lớn hơn hoặc bằng tiết bắt đầu.',
        ]);

        $courseClass = $this->ownedClass((int) $validated['classId']);

        if ($courseClass->members()->where('status', 'active')->count() === 0) {
            $this->addError('classId', 'Vui lòng import danh sách lớp trước khi điểm danh.');
            $this->redirectRoute('lecturer.classes.show', ['ma_user' => auth()->id(), 'courseClass' => $courseClass->id, 'openImport' => 1], navigate: true);
            return;
        }

        $session = ClassSession::query()->create([
            'class_id' => $courseClass->id,
            'created_by' => auth()->id(),
            'name' => $validated['name'],
            'date' => $validated['date'],
            'start_time' => $validated['startTime'] ?: null,
            'end_time' => $validated['endTime'] ?: null,
            'lesson_count' => $validated['endPeriod'] - $validated['startPeriod'] + 1,
            'status' => 'active',
        ]);

        $courseClass->members()->where('status', 'active')->get()->each(fn ($member) => AttendanceRecord::query()->firstOrCreate([
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
        $classes = $this->availableClasses()
            ->loadCount(['members' => fn ($query) => $query->where('status', 'active')]);

        return view('livewire.lecturer.attendance.manual-create', compact('classes'))
            ->layout('layouts.user', ['title' => 'Điểm danh thủ công']);
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
        $code = 'DEMO-'.$userId.'-MANUAL';

        $courseClass = CourseClass::withTrashed()->firstOrCreate(
            ['code' => $code],
            [
                'owner_user_id' => $userId,
                'name' => 'Lớp demo điểm danh',
                'description' => 'Dữ liệu giả để kiểm thử trang tạo điểm danh thủ công.',
                'subject_code' => 'DEMO101',
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
            ['DEMO001', 'Nguyễn Văn An'],
            ['DEMO002', 'Trần Thị Bình'],
            ['DEMO003', 'Lê Minh Cường'],
            ['DEMO004', 'Phạm Thanh Duy'],
            ['DEMO005', 'Hoàng Thị Hà'],
            ['DEMO006', 'Vũ Quốc Huy'],
            ['DEMO007', 'Đặng Thu Lan'],
            ['DEMO008', 'Bùi Đức Long'],
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
