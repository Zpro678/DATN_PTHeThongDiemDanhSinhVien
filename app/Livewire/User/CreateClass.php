<?php

namespace App\Livewire\User;

use App\Models\CourseClass;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CreateClass extends Component
{
    public string $name = '';

    // code không còn nhập tay — sinh tự động khi save hoặc khi nhập mã môn/học kỳ
    public string $generatedCode = '';

    // 4 số ngẫu nhiên được sinh ra khi load trang để ghép vào mã lớp
    public string $randomSuffix = '';

    public string $subjectCode = '';

    public string $semester = '';

    public string $description = '';

    public int $totalLessons = 45;

    public bool $requireApproval = false;

    public function mount(): void
    {
        $this->randomSuffix = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Mỗi khi subjectCode hoặc semester thay đổi,
     * tự động cập nhật preview mã lớp hiển thị cho người dùng.
     */
    public function updatedSubjectCode(): void
    {
        $this->generatedCode = $this->buildPreviewCode();
    }

    public function updatedSemester(): void
    {
        $this->generatedCode = $this->buildPreviewCode();
    }

    /**
     * Sinh preview mã với 4 số random đã tạo lúc load trang.
     * Ví dụ: subjectCode=INT3110, semester=HK1 2026, suffix=4829 → "INTHK14829"
     */
    private function buildPreviewCode(): string
    {
        $subPart = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $this->subjectCode), 0, 3));
        $semPart = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $this->semester), 0, 3));

        if (blank($subPart) && blank($semPart)) {
            return '';
        }

        return ($subPart ?: '???') . ($semPart ?: '???') . $this->randomSuffix;
    }

    /**
     * Sinh mã lớp thực sự khi lưu, dùng randomSuffix hiện tại nếu chưa bị trùng.
     */
    private function generateUniqueCode(): string
    {
        $subPart = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $this->subjectCode), 0, 3));
        $semPart = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $this->semester), 0, 3));
        $prefix  = ($subPart ?: 'CLS') . ($semPart ?: 'SEM');

        $attempts = 0;
        $suffix = $this->randomSuffix;
        do {
            $code   = $prefix . $suffix;
            $exists = CourseClass::withTrashed()->where('code', $code)->exists();
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

       
         $validated =  $this->validate([
            'name'              => ['required', 'string', 'max:255'],
            'subjectCode'       => ['nullable', 'string', 'max:50'],
            'semester'          => ['nullable', 'string', 'max:50'],
            'description'       => ['nullable', 'string', 'max:5000'],
            'totalLessons'      => ['required', 'integer', 'min:1', 'max:300'],
            'requireApproval'   => ['boolean'],
        ], [
            'name.required'          => 'Vui lòng nhập tên lớp.',
            'totalLessons.min'      => 'Tổng số tiết phải lớn hơn 0.',

        ]);

        $code = $this->generateUniqueCode();

        CourseClass::query()->create([
            'owner_user_id'      => auth()->id(),
            'name'               => $this->name,
            'code'               => $code,
            'subject_code'       => filled($this->subjectCode) ? strtoupper($this->subjectCode) : null,
            'semester'           => $this->semester ?: null,
            'description'        => $this->description ?: null,
            'total_lessons'      => $this->totalLessons,
            'require_approval'   => $this->requireApproval,
            'status'             => 'active',
        ]);

        session()->flash('status', 'Lớp học đã được tạo thành công.');

        $this->redirectRoute('managed-classes', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.user.create-class')
            ->layout('layouts.user', ['title' => 'Tạo lớp mới']);
    }
}
