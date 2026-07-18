<?php

namespace App\Livewire\User;

use App\Models\CourseClass;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Services\SubscriptionService;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentsImport;

class CreateClass extends Component
{
    use WithFileUploads;

    // Tên của lớp học
    public string $name = '';

    // File Excel/CSV được chọn để import khi tạo lớp
    public $importFile;

    // Mảng lưu các lỗi import
    public array $importErrors = [];

    // Số lượng học viên import thành công
    public int $importSuccess = 0;

    // Mã lớp học phần do giảng viên tự nhập (VD: CS101, WEB-2026-01)
    public string $classCode = '';

    // Mã tham gia lớp — được sinh tự động khi mở form hoặc khi lưu.
    public string $generatedCode = '';

    // 4 số ngẫu nhiên được sinh ra khi load trang để ghép vào mã lớp
    public string $randomSuffix = '';

    // Mô tả thêm về lớp học
    public string $description = '';

    // Ngưỡng thời gian đi muộn (phút)

    // Quỹ vắng cho phép, tính theo % tổng số buổi. Ngưỡng cấm thi = 100 - giá trị này.
    public float $absenceLimitPercent = 20;

    // Tổng số buổi dự kiến của môn học (dùng để tính quỹ vắng và tiến độ).
    public int $totalSessions = 15;

    // Cấu hình bảng điểm trừ chuyên cần
    public array $attendanceRules = [];

    // Yêu cầu giảng viên duyệt khi sinh viên tham gia lớp bằng mã
    public bool $requireApproval = false;

    public function mount(): void
    {
        $this->randomSuffix = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $this->generatedCode = 'CLS'.$this->randomSuffix;
        $this->attendanceRules = (new CourseClass())->getAttendanceRules();
    }

    /**
     * Sinh mã lớp thực sự khi lưu, dùng randomSuffix hiện tại nếu chưa bị trùng.
     */
    private function generateUniqueCode(): string
    {
        $prefix = 'CLS';

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
            'classCode' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:5000'],
            'totalSessions' => ['required', 'integer', 'min:1', 'max:200'],
            'absenceLimitPercent' => ['required', 'numeric', 'min:0', 'max:100'],
            'attendanceRules' => ['required', 'array'],
            'attendanceRules.late' => ['required', 'numeric', 'min:0', 'max:10'],
            'attendanceRules.absent' => ['required', 'numeric', 'min:0', 'max:10'],
            'attendanceRules.excused' => ['required', 'numeric', 'min:0', 'max:10'],
            'requireApproval' => ['boolean'],
            // Import danh sách là TÙY CHỌN khi tạo lớp; nếu chưa import, giảng viên sẽ được
            // nhắc import khi tạo điểm danh (lớp phải có sinh viên mới tạo được buổi điểm danh).
            'importFile' => ['nullable', 'file', 'extensions:xlsx,xls,csv', 'max:5120'],
        ], [
            'name.required' => 'Vui lòng nhập tên lớp.',
            'totalSessions.required' => 'Vui lòng nhập tổng số buổi dự kiến.',
            'totalSessions.min' => 'Tổng số buổi dự kiến phải từ 1 trở lên.',
            'totalSessions.max' => 'Tổng số buổi dự kiến tối đa là 200.',
            'absenceLimitPercent.required' => 'Vui lòng nhập ngưỡng vắng cho phép.',
            'absenceLimitPercent.min' => 'Ngưỡng vắng cho phép không được nhỏ hơn 0%.',
            'absenceLimitPercent.max' => 'Ngưỡng vắng cho phép tối đa là 100%.',
            'importFile.extensions' => 'File danh sách phải có định dạng .xlsx, .xls hoặc .csv.',
            'importFile.max' => 'File danh sách tối đa 5MB.',
        ]);

        $code = $this->generateUniqueCode();

        $courseClass = CourseClass::query()->create([
            'owner_user_id' => auth()->id(),
            'name' => $this->name,
            'join_key' => $code,
            'class_code' => $this->classCode ?: $code, // fallback sang join_key nếu không nhập
            'description' => $this->description ?: null,
            'deduct_late' => (float) ($this->attendanceRules['late'] ?? 0.5),
            'deduct_absent' => (float) ($this->attendanceRules['absent'] ?? 1.0),
            'deduct_excused' => (float) ($this->attendanceRules['excused'] ?? 0.0),
            'deduct_excused_absence' => ((float) ($this->attendanceRules['excused'] ?? 0)) > 0,
            'total_sessions' => $this->totalSessions,
            'absence_limit_percent' => $this->absenceLimitPercent,
            'require_approval' => $this->requireApproval,
            'status' => 'active',
        ]);

        app(NotificationService::class)->classCreated((int) auth()->id(), $courseClass);

        app(AuditLogService::class)->log('class_created', [
            'class_id'   => $courseClass->id,
            'table_name' => 'classes',
            'row_id'     => null,
            'new_values' => [
                'name'       => $courseClass->name,
                'join_key'   => $courseClass->join_key,
                'class_code' => $courseClass->class_code,
            ],
        ]);

        // Tiến hành import file nếu có tải lên
        if ($this->importFile) {
            $extension = $this->importFile->getClientOriginalExtension();
            $readerType = match (strtolower($extension)) {
                'csv' => \Maatwebsite\Excel\Excel::CSV,
                'xls' => \Maatwebsite\Excel\Excel::XLS,
                default => \Maatwebsite\Excel\Excel::XLSX,
            };

            // Khoá: mỗi tài khoản chỉ 1 lượt import chạy tại một thời điểm. Lớp đã tạo
            // xong nên chỉ bỏ qua phần import (người dùng có thể import lại sau).
            $importUserId = (int) auth()->id();
            if (! \App\Support\ImportLock::acquire($importUserId)) {
                session()->flash('status', 'Lớp đã được tạo. Bạn đang có một lượt import khác đang chạy — vui lòng import danh sách khi lượt đó hoàn tất.');
                $this->redirectRoute('lecturer.classes.show', ['courseClass' => $courseClass->id], navigate: true);
                return;
            }

            try {
                // Lưu file vào ổ đĩa cố định (storage/app/imports) trước khi đẩy vào hàng đợi.
                // BẮT BUỘC cho xử lý bất đồng bộ: worker redis chạy ở tiến trình/thời điểm khác,
                // lúc đó file tạm của Livewire đã bị dọn -> phải có bản lưu bền vững.
                $storedRelativePath = $this->importFile->storeAs(
                    'imports',
                    \Illuminate\Support\Str::uuid()->toString().'.'.strtolower($extension),
                    'local'
                );
                $importAbsolutePath = \Illuminate\Support\Facades\Storage::disk('local')->path($storedRelativePath);

                // Bước 1: Phân tích header trước (đọc 10 dòng đầu)
                $headingImport = new \App\Imports\HeadingRowImport($courseClass->id);
                Excel::import($headingImport, $importAbsolutePath, null, $readerType);

                if (!empty($headingImport->errors)) {
                    session()->flash('import_errors', $headingImport->errors);
                    session()->flash('status', "Tạo lớp học thành công, nhưng đọc file có lỗi.");
                    \Illuminate\Support\Facades\Storage::disk('local')->delete($storedRelativePath);
                    \App\Support\ImportLock::release($importUserId);
                    $this->redirectRoute('lecturer.classes.show', ['courseClass' => $courseClass->id], navigate: true);
                    return;
                }

                $meetingHeaders = array_unique($headingImport->meetingHeaders);

                // Bước 2: Tạo Bus::batch và StartImportJob
                $batch = \Illuminate\Support\Facades\Bus::batch([
                    new \App\Jobs\StartImportJob(
                        $importAbsolutePath,
                        $courseClass->id,
                        $headingImport->dateHeaders,
                        $headingImport->meetingHeaders,
                        $headingImport->emailColIndex,
                        $headingImport->nameColIndex,
                        $headingImport->headerRowNumber,
                        auth()->id(),
                        $readerType,
                        null
                    )
                ])
                ->then(function (\Illuminate\Bus\Batch $batch) use ($meetingHeaders) {
                    foreach ($meetingHeaders as $meetingId) {
                        $meeting = \App\Models\ClassMeeting::with(['courseClass', 'sessions'])->find($meetingId);
                        if ($meeting) {
                            \App\Services\AttendanceCalculator::syncSummaries($meeting);
                        }
                    }
                })
                ->finally(function () use ($storedRelativePath, $importUserId) {
                    // Dọn file import + nhả khoá import sau khi xử lý xong (kể cả khi có job thất bại).
                    \Illuminate\Support\Facades\Storage::disk('local')->delete($storedRelativePath);
                    \App\Support\ImportLock::release($importUserId);
                })
                ->name('Import Students')
                ->dispatch();

                session()->flash('status', "Tạo lớp học thành công. Đang tiến hành xử lý ngầm file danh sách sinh viên.");
                $this->redirectRoute('lecturer.classes.show', ['courseClass' => $courseClass->id, 'importToken' => $batch->id], navigate: true);
                return;
            } catch (\Exception $e) {
                // Dọn file đã lưu + nhả khoá nếu lỗi trước khi batch được điều phối.
                if (isset($storedRelativePath)) {
                    \Illuminate\Support\Facades\Storage::disk('local')->delete($storedRelativePath);
                }
                \App\Support\ImportLock::release($importUserId);
                session()->flash('status', 'Tạo lớp thành công nhưng lỗi khi đọc file import: ' . $e->getMessage());
                $this->redirectRoute('lecturer.classes.show', ['courseClass' => $courseClass->id], navigate: true);
                return;
            }
        }

        session()->flash('status', 'Lớp học đã được tạo thành công.');
        $this->redirectRoute('managed-classes', navigate: true);
    }

    public function downloadBasicTemplate()
    {
        return Excel::download(new \App\Exports\ImportTemplateExport(), 'Danh_sach_sinh_vien_mau.xlsx');
    }

    public function render(): View
    {
        return view('livewire.user.create-class')
            ->layout('layouts.user', ['title' => 'Tạo lớp mới']);
    }
}
