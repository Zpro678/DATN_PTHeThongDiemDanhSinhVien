<?php

namespace App\Livewire\User;

use App\Models\CourseClass;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManagedClasses extends Component
{
    use WithFileUploads;
    // Trạng thái modal và file import lớp
    public bool $isImporting = false;
    public $importFile;
    public array $importErrors = [];
    public int $importSuccessCount = 0;
    public int $studentSuccessCount = 0;
    public ?string $importToken = null;
    public bool $isImportingStatus = false;
    public int $importProgress = 0;

    // Bộ lọc theo trạng thái của lớp học (Đang hoạt động, Đã kết thúc)
    public string $statusFilter = 'Đang hoạt động';

    // Từ khóa tìm kiếm lớp học theo tên hoặc mã lớp
    public string $search = '';

    // ID lớp đang chờ xác nhận kết thúc
    public ?string $confirmingEndClassId = null;

    public function confirmEndClass(string $classId): void
    {
        $this->confirmingEndClassId = $classId;
    }

    public function cancelEndClass(): void
    {
        $this->confirmingEndClassId = null;
    }

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
    }

    public function render(): View
    {
        $query = CourseClass::managedBy(auth()->id())
            ->withCount([
                'members as students_count' => fn ($q) => $q->where('status', \App\Models\ClassMember::STATUS_ACTIVE),
                'sessions as completed_sessions_count' => fn ($q) => $q->where('status', 'closed'),
            ])
            ->withCount(['meetings as studied_sessions' => fn ($query) => $query->whereHas('sessions', fn ($s) => $s->where('status', 'closed'))])
            ->withSum('attendanceSummaries as sum_present', 'total_present')
            ->withSum('attendanceSummaries as sum_late', 'total_late')
            ->withSum('attendanceSummaries as sum_absent', 'total_absent')
            ->withSum('attendanceSummaries as sum_excused', 'total_excused');

        if ($this->statusFilter === 'Đang hoạt động') {
            $query->where('status', 'active');
        } elseif ($this->statusFilter === 'Đã kết thúc') {
            $query->where('status', 'ended');
        }

        if (trim($this->search) !== '') {
            $search = str($this->search)->lower()->toString();
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(join_key) LIKE ?', ["%{$search}%"]);
            });
        }

        $classes = $query->orderByDesc('created_at')->get();

        return view('livewire.user.managed-classes', [
            'classes' => $classes,
        ])->layout('layouts.user', ['title' => 'Lớp tôi quản lý']);
    }

    public function endClass(string $classId): void
    {
        $class = CourseClass::where('id', $classId)
            ->where('owner_user_id', auth()->id())
            ->firstOrFail();
        
        if ($class->status !== 'ended') {
            $class->update(['status' => 'ended']);
        }

        $this->confirmingEndClassId = null;
    }

    public function openImport(): void
    {
        $this->isImporting = true;
        $this->importFile = null;
        $this->importErrors = [];
        $this->importSuccessCount = 0;
        $this->studentSuccessCount = 0;
        $this->importToken = null;
        $this->isImportingStatus = false;
    }

    public function closeImport(): void
    {
        $this->isImporting = false;
        $this->importFile = null;
        $this->importErrors = [];
        $this->importToken = null;
        $this->isImportingStatus = false;
    }

    public function downloadTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\MultiClassImportTemplateExport(),
            'Mau_Import_Nhieu_Lop_Hoc.xlsx'
        );
    }

    public function processImport()
    {
        $this->validate([
            'importFile' => ['required', 'file', 'extensions:xlsx,xls,csv', 'max:5120'],
        ], [
            'importFile.required' => 'Vui lòng chọn file Excel hoặc CSV.',
            'importFile.extensions' => 'File phải có định dạng .xlsx, .xls hoặc .csv.',
            'importFile.max' => 'File tối đa 5MB.',
        ]);

        $this->importErrors = [];
        $this->importSuccessCount = 0;
        $this->studentSuccessCount = 0;
        $this->isImportingStatus = true;

        $extension = $this->importFile->getClientOriginalExtension();
        $readerType = match (strtolower($extension)) {
            'csv' => \Maatwebsite\Excel\Excel::CSV,
            'xls' => \Maatwebsite\Excel\Excel::XLS,
            default => \Maatwebsite\Excel\Excel::XLSX,
        };

        try {
            // Lưu file vào storage/app/private/imports để chạy ngầm
            $storedRelativePath = $this->importFile->storeAs(
                'imports',
                \Illuminate\Support\Str::uuid()->toString() . '.' . strtolower($extension),
                'local'
            );
            $importAbsolutePath = \Illuminate\Support\Facades\Storage::disk('local')->path($storedRelativePath);

            // Tạo Bus Batch chạy ngầm
            $batch = \Illuminate\Support\Facades\Bus::batch([
                new \App\Jobs\ImportClassesJob(
                    $importAbsolutePath,
                    (int) auth()->id(),
                    $readerType,
                    $storedRelativePath
                )
            ])
            ->then(function (\Illuminate\Bus\Batch $batch) {
                // Đồng bộ chuyên cần khi toàn bộ các lớp & học viên đã import xong dưới nền
                $meetingIds = cache()->pull('import_meetings_' . $batch->id, []);
                if (!empty($meetingIds)) {
                    foreach ($meetingIds as $meetingId) {
                        $meeting = \App\Models\ClassMeeting::with(['courseClass', 'sessions'])->find($meetingId);
                        if ($meeting) {
                            \App\Services\AttendanceCalculator::syncSummaries($meeting);
                        }
                    }
                }
            })
            ->name('Import Classes and Students')
            ->dispatch();

            $this->importToken = $batch->id;
            $this->importProgress = 0;
            $this->isImporting = false; // Ẩn bảng modal ngay lập tức để người dùng làm việc khác
            $this->importFile = null;
        } catch (\Exception $e) {
            $this->importErrors[] = "Lỗi khi đưa file vào hàng chờ xử lý: " . $e->getMessage();
            $this->isImportingStatus = false;
            $this->importToken = null;
        }
    }

    public function checkImportProgress(): void
    {
        if (!$this->importToken) {
            return;
        }

        $batch = \Illuminate\Support\Facades\Bus::findBatch($this->importToken);
        if ($batch) {
            $this->importProgress = $batch->progress();
            if ($batch->finished()) {
                $this->finalizeImport();
            }
        }
    }

    protected function finalizeImport(): void
    {
        $this->importSuccessCount = (int) cache()->pull('class_import_success_' . $this->importToken, 0);
        $this->studentSuccessCount = (int) cache()->pull('import_success_' . $this->importToken, 0);

        $dbErrors = \App\Models\ImportError::where('import_token', $this->importToken)
            ->orderBy('id')
            ->get();

        if ($dbErrors->count() > 0) {
            $this->importErrors = $dbErrors->map(function ($error) {
                return ($error->row_index ? "Dòng {$error->row_index}: " : "") . $error->error_message;
            })->toArray();
        }

        \App\Models\ImportError::where('import_token', $this->importToken)->delete();

        $this->isImportingStatus = false;

        $message = "Đã nhập {$this->importSuccessCount} lớp học và {$this->studentSuccessCount} học viên thành công.";
        if (count($this->importErrors) > 0) {
            session()->flash('error', "Import hoàn tất, nhưng có lỗi xảy ra ở một số dòng/sheet.");
            $this->dispatch('toast', message: "Import hoàn tất nhưng có lỗi.", type: 'warning');
            
            // Reopen modal to show errors
            $this->isImporting = true;
        } else {
            session()->flash('status', $message);
            $this->dispatch('toast', message: $message, type: 'success');
            $this->closeImport();
        }

        $this->importToken = null;
        $this->importProgress = 0;
    }
}
