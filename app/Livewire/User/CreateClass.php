<?php

namespace App\Livewire\User;

use App\Models\CourseClass;
use App\Services\NotificationService;
use App\Services\SubscriptionService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CreateClass extends Component
{
    // Tên của lớp học
    public string $name = '';

    // code không còn nhập tay — sinh tự động khi save hoặc khi nhập mã môn/học kỳ
    public string $generatedCode = '';

    // 4 số ngẫu nhiên được sinh ra khi load trang để ghép vào mã lớp
    public string $randomSuffix = '';

    // Mã môn học của lớp
    public string $subjectCode = '';

    // Học kỳ diễn ra lớp học (VD: HK1 2026)
    public string $semester = '';

    // Mô tả thêm về lớp học
    public string $description = '';

    // Ngưỡng thời gian đi muộn (phút)
    public int $lateThreshold = 15;

    // Cấu hình bảng điểm trừ chuyên cần
    public array $attendanceRules = [];

    // Yêu cầu giảng viên duyệt khi sinh viên tham gia lớp bằng mã
    public bool $requireApproval = false;

    public function mount(): void
    {
        $this->randomSuffix = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $this->attendanceRules = (new CourseClass())->getAttendanceRules();
    }

    /**
     * Mỗi khi subjectCode thay đổi,
     * tự động cập nhật preview mã lớp hiển thị cho người dùng.
     */
    public function updatedSubjectCode(): void
    {
        $this->generatedCode = $this->buildPreviewCode();
    }


    /**
     * Sinh preview mã với 4 số random đã tạo lúc load trang.
     * Ví dụ: subjectCode=INT3110, suffix=4829 → "INT4829"
     */
    private function buildPreviewCode(): string
    {
        $subPart = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $this->subjectCode), 0, 3));

        if (blank($subPart)) {
            return '';
        }

        return ($subPart ?: 'CLS').$this->randomSuffix;
    }

    /**
     * Sinh mã lớp thực sự khi lưu, dùng randomSuffix hiện tại nếu chưa bị trùng.
     */
    private function generateUniqueCode(): string
    {
        $subPart = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $this->subjectCode), 0, 3));
        $prefix = ($subPart ?: 'CLS');

        $attempts = 0;
        $suffix = $this->randomSuffix;
        do {
            $code = $prefix.$suffix;
            $exists = CourseClass::withTrashed()->where('join_key', $code)->exists();
            if ($exists) {
                $suffix = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            }
            $attempts++;
        } while ($exists && $attempts < 20);

        // Cập nhật lại suffix mới nếu có thay đổi (do trùng)
        $this->randomSuffix = $suffix;
        $this->generatedCode = $code;

        return $code;
    }

    public function save(): void
    {
        // Kiểm tra gói: số lớp được tạo bị giới hạn theo gói dịch vụ.
        $subscription = app(SubscriptionService::class);
        if (! $subscription->canCreateClass(auth()->user())) {
            $max = $subscription->maxClasses(auth()->user());
            $this->addError('name', "Gói hiện tại của bạn chỉ cho phép tạo tối đa {$max} lớp. Vui lòng nâng cấp gói để tạo thêm lớp.");

            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'subjectCode' => ['nullable', 'string', 'max:50'],
            'semester' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:5000'],
            'lateThreshold' => ['required', 'integer', 'min:0', 'max:300'],
            'attendanceRules' => ['required', 'array'],
            'attendanceRules.present' => ['required', 'numeric', 'max:0'],
            'attendanceRules.late' => ['required', 'numeric'],
            'attendanceRules.absent' => ['required', 'numeric'],
            'attendanceRules.excused' => ['required', 'numeric'],
            'requireApproval' => ['boolean'],
        ], [
            'name.required' => 'Vui lòng nhập tên lớp.',
        ]);

        $code = $this->generateUniqueCode();

        $courseClass = CourseClass::query()->create([
            'owner_user_id' => auth()->id(),
            'name' => $this->name,
            'join_key' => $code,
            'subject_code' => filled($this->subjectCode) ? strtoupper(trim($this->subjectCode)) : null,
            'semester' => filled($this->semester) ? trim($this->semester) : null,
            'description' => $this->description ?: null,
            'late_threshold' => $this->lateThreshold,
            'deduct_excused_absence' => ($this->attendanceRules['excused'] ?? 0) > 0,
            'total_sessions' => 0,
            'require_approval' => $this->requireApproval,
            'status' => 'active',
        ]);

        app(NotificationService::class)->classCreated((int) auth()->id(), $courseClass);

        session()->flash('status', 'Lớp học đã được tạo thành công.');

        $this->redirectRoute('managed-classes', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.user.create-class')
            ->layout('layouts.user', ['title' => 'Tạo lớp mới']);
    }
}
