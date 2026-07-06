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
    public int $lateThreshold = 15;

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
            'lateThreshold' => ['required', 'integer', 'min:0', 'max:300'],
            'attendanceRules' => ['required', 'array'],
            'attendanceRules.present' => ['required', 'numeric', 'max:0'],
            'attendanceRules.late' => ['required', 'numeric'],
            'attendanceRules.absent' => ['required', 'numeric'],
            'attendanceRules.excused' => ['required', 'numeric'],
            'requireApproval' => ['boolean'],
            'importFile' => ['required', 'file', 'extensions:xlsx,xls,csv', 'max:5120'],
        ], [
            'name.required' => 'Vui lòng nhập tên lớp.',
            'importFile.required' => 'Vui lòng chọn tệp danh sách sinh viên Excel/CSV để import.',
            'importFile.extensions' => 'Định dạng file import không hỗ trợ. Vui lòng dùng .xlsx, .xls, .csv',
        ]);

        $code = $this->generateUniqueCode();

        $courseClass = CourseClass::query()->create([
            'owner_user_id' => auth()->id(),
            'name' => $this->name,
            'join_key' => $code,
            'class_code' => $this->classCode ?: $code, // fallback sang join_key nếu không nhập
            'description' => $this->description ?: null,
            'late_threshold' => $this->lateThreshold,
            'deduct_excused_absence' => ($this->attendanceRules['excused'] ?? 0) > 0,
            'total_sessions' => 0,
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

            try {
                // Bước 1: Phân tích header trước (đọc 10 dòng đầu)
                $headingImport = new \App\Imports\HeadingRowImport($courseClass->id);
                Excel::import($headingImport, $this->importFile->getRealPath(), null, $readerType);

                if (!empty($headingImport->errors)) {
                    session()->flash('import_errors', $headingImport->errors);
                    session()->flash('status', "Tạo lớp học thành công, nhưng đọc file có lỗi.");
                    $this->redirectRoute('lecturer.classes.show', ['courseClass' => $courseClass->id], navigate: true);
                    return;
                }

                $meetingHeaders = array_unique($headingImport->meetingHeaders);

                // Bước 2: Tạo Bus::batch và StartImportJob
                $batch = \Illuminate\Support\Facades\Bus::batch([
                    new \App\Jobs\StartImportJob(
                        $this->importFile->getRealPath(),
                        $courseClass->id,
                        $headingImport->dateHeaders,
                        $headingImport->meetingHeaders,
                        $headingImport->emailColIndex,
                        $headingImport->nameColIndex,
                        $headingImport->codeColIndex,
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
                ->name('Import Students')
                ->dispatch();

                session()->flash('status', "Tạo lớp học thành công. Đang tiến hành xử lý ngầm file danh sách sinh viên.");
                $this->redirectRoute('lecturer.classes.show', ['courseClass' => $courseClass->id, 'importToken' => $batch->id], navigate: true);
                return;
            } catch (\Exception $e) {
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
