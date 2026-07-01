<?php

namespace App\Livewire\Lecturer;

use App\Imports\StudentsImport;
use App\Models\CourseClass;
use App\Models\LeaveRequest;
use App\Services\LectureManageStudentService;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class ClassShow extends Component
{
    use WithFileUploads, WithPagination;

    // Đối tượng chứa thông tin chi tiết của lớp học hiện tại
    public CourseClass $class;

    public function getRecentSessionsProperty()
    {
        return $this->class->sessions()
            ->withCount([
                'attendanceRecords as present_count' => function ($query) {
                    $query->where('status', 'present');
                },
                'attendanceRecords as absent_count' => function ($query) {
                    $query->where('status', 'absent');
                },
                'attendanceRecords as late_count' => function ($query) {
                    $query->where('status', 'late');
                },
                'attendanceRecords as excused_count' => function ($query) {
                    $query->where('status', 'excused');
                }
            ])
            ->latest('date')
            ->latest('start_time')
            ->take(5)
            ->get();
    }

    // Thống kê hiển thị trên trang
    public int $studentsCount = 0;
    
    // Tổng số buổi học dự kiến của lớp
    public int $sessionsCount = 0;

    // Tổng số buổi học đã hoàn thành hoặc đang diễn ra
    public int $sessionsCompleted = 0;

    // Số lượng đơn xin phép nghỉ đang chờ duyệt của lớp này
    public int $pendingLeaveRequests = 0;

    // Số lượng sinh viên đang chờ duyệt vào lớp
    public int $pendingMembersCount = 0;

    // Import state
    // Trạng thái hiển thị modal import sinh viên
    public bool $isImporting = false;

    // Đối tượng file Excel/CSV được chọn để import
    public $importFile;

    // Mảng lưu trữ các lỗi phát sinh trong quá trình import
    public array $importErrors = [];

    // Số lượng sinh viên đã được import thành công vào lớp
    public int $importSuccess = 0;

    public bool $showNoStudentsPopup = false;

    public bool $isEditingCode = false;
    public string $newClassCode = '';

    // Các thuộc tính phục vụ theo dõi tiến trình import dạng chunk qua Cache/Polling
    public ?string $importToken = null;
    public bool $isImportingStatus = false;
    public int $importTotalRows = 0;
    public int $importProcessedRows = 0;
    public int $importQuietTicks = 0;

    public function checkImportProgress(): void
    {
        if (!$this->importToken) {
            return;
        }

        $progress = \Illuminate\Support\Facades\Cache::get("import_progress_{$this->importToken}");
        if ($progress) {
            $this->importTotalRows = $progress['total_rows'];
            
            if ($progress['processed_rows'] === $this->importProcessedRows) {
                $this->importQuietTicks++;
            } else {
                $this->importProcessedRows = $progress['processed_rows'];
                $this->importQuietTicks = 0;
            }

            if ($progress['status'] === 'completed') {
                $this->finalizeImport();
                return;
            }

            // Nếu sau 30 giây (60 lần poll 500ms) không thấy tiến trình chạy (do Queue Worker không chạy)
            if ($this->importQuietTicks >= 60) {
                // Tự động chuyển sang xử lý đồng bộ để tránh bị treo
                $this->finalizeImport();
            }
        }
    }

    protected function finalizeImport(): void
    {
        $count = $this->importSuccess;
        
        // Cập nhật lại số sinh viên và số buổi
        $this->studentsCount = $this->class->members()->where('status', \App\Models\ClassMember::STATUS_ACTIVE)->count();
        $this->sessionsCount = $this->class->sessions()->count();
        $this->sessionsCompleted = $this->class->sessions()->whereIn('status', ['closed', 'active'])->count();
        
        if (empty($this->importErrors)) {
            $progress = $this->importToken ? \Illuminate\Support\Facades\Cache::get("import_progress_{$this->importToken}") : null;
            if ($progress && $progress['status'] !== 'completed') {
                $message = "Hệ thống đang xử lý ngầm {$count} sinh viên. Vui lòng tải lại trang sau ít phút.";
                $this->dispatch('toast', message: $message, type: 'info');
            } else {
                $message = "Đã nhập thành công {$count} sinh viên vào lớp.";
                $this->dispatch('toast', message: $message, type: 'success');
            }
        } else {
            // Có cảnh báo/lỗi thì mở lại modal để người dùng đọc
            $this->isImporting = true;
            $message = "Đã tiếp nhận {$count} sinh viên, nhưng có một số lỗi. Vui lòng xem chi tiết.";
            $this->dispatch('toast', message: $message, type: 'warning');
        }

        $this->reset(['importToken', 'isImportingStatus', 'importTotalRows', 'importProcessedRows', 'importQuietTicks']);
    }

    public function openImportFromPopup(): void
    {
        $this->showNoStudentsPopup = false;
        $this->openImport();
    }

    public function mount(CourseClass $courseClass): void
    {
        // Kiểm tra quyền — chỉ chủ lớp mới được xem
        abort_unless(
            $courseClass->owner_user_id === auth()->id(),
            403,
            'Bạn không có quyền xem lớp học này.'
        );

        $this->class = $courseClass->load(['sessions' => function ($q) {
            $q->orderByDesc('date')->orderByDesc('created_at');
        }]);

        $this->studentsCount   = $courseClass->members()->where('status', \App\Models\ClassMember::STATUS_ACTIVE)->count();
        $this->sessionsCount = $courseClass->sessions()->count();
        $this->sessionsCompleted = $courseClass->sessions()->whereIn('status', ['closed', 'active'])->count();
        $this->pendingLeaveRequests = LeaveRequest::whereHas('classSession', function ($q) use ($courseClass) {
            $q->where('class_id', $courseClass->id);
        })->where('status', 'pending')->count();
        $this->pendingMembersCount = $courseClass->joinRequests()->where('status', 'pending')->count();
        
        if (request()->has('openImport')) {
            $this->openImport();
            session()->flash('status', 'Vui lòng import danh sách lớp trước khi điểm danh.');
        }
    }
    
    public function checkBeforeAttendance(string $type): void
    {
        \Illuminate\Support\Facades\Log::info("checkBeforeAttendance called: type={$type}, students={$this->studentsCount}");
        if ($this->studentsCount === 0) {
            $this->showNoStudentsPopup = true;
            return;
        }

        if ($type === 'qr') {
            $this->redirectRoute('lecturer.attendance.qr.create', ['ma_user' => auth()->id(), 'class_id' => $this->class->id], navigate: true);
        } else {
            $this->redirectRoute('lecturer.attendance.create', ['ma_user' => auth()->id(), 'class_id' => $this->class->id], navigate: true);
        }
    }

    public function toggleEditCode(): void
    {
        $this->isEditingCode = !$this->isEditingCode;
        if ($this->isEditingCode) {
            $this->newClassCode = $this->class->join_key;
        } else {
            $this->resetValidation('newClassCode');
        }
    }

    public function updateClassCode(): void
    {
        $this->validate([
            'newClassCode' => 'required|string|max:50|unique:classes,join_key,' . $this->class->id,
        ], [
            'newClassCode.required' => 'Mã lớp không được để trống.',
            'newClassCode.unique' => 'Mã lớp này đã tồn tại trong hệ thống.',
            'newClassCode.max' => 'Mã lớp không được vượt quá 50 ký tự.',
        ]);

        $this->class->update([
            'join_key' => $this->newClassCode,
        ]);

        $this->isEditingCode = false;
        session()->flash('success', 'Đã cập nhật mã lớp thành công.');
    }

    public function generateRandomCode(): void
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (CourseClass::where('join_key', $code)->where('id', '!=', $this->class->id)->exists());

        $this->newClassCode = $code;
    }

    public function render()
    {
        $students = $this->class->members()
            ->with(['user'])
            ->where('status', 'active')
            ->paginate(5);

        // Dùng service để tính chuyên cần đồng nhất với trang danh sách học viên.
        $memberIds = $students->pluck('id')->all();
        $statsMap  = $memberIds
            ? app(LectureManageStudentService::class)->getStudentsAttendanceStats($memberIds)
            : [];

        return view('livewire.lecturer.class-show', [
            'recentSessions' => $this->recentSessions,
            'students'       => $students,
            'statsMap'       => $statsMap,
        ])->layout('layouts.user', ['title' => $this->class->name]);
    }

    public function openImport(): void
    {
        $this->isImporting = true;
        $this->reset(['importFile', 'importErrors', 'importSuccess']);
    }

    public function closeImport(): void
    {
        $this->isImporting = false;
        $this->reset(['importFile', 'importErrors', 'importSuccess']);
    }

    public function downloadFullTemplate()
    {
        $lines = [
            "M\u00e3 h\u1ecdc vi\u00ean,H\u1ecd v\u00e0 t\u00ean,Email,22/06,23/06,24/06",
            "HV001,Nguy\u1ec5n V\u0103n A,nva@email.com,c,m,c",
            "HV002,Tr\u1ea7n Th\u1ecb B,ttb@email.com,v,c,v",
            "HV003,L\u00ea V\u0103n C,lvc@email.com,c,v,p",
            "",
            "Ch\u00fa th\u00edch k\u00fd hi\u1ec7u:,c=C\u00f3 m\u1eb7t,m=\u0110i mu\u1ed9n,v=V\u1eafng kh\u00f4ng ph\u00e9p,p=V\u1eafng c\u00f3 ph\u00e9p",
        ];
        $csvContent = implode("\n", $lines);

        return response()->streamDownload(function () use ($csvContent) {
            echo "\xEF\xBB\xBF" . $csvContent; // UTF-8 BOM cho Excel
        }, 'Danh_sach_hoc_vien_mau_day_du.csv');
    }

    public function downloadBasicTemplate()
    {
        $lines = [
            "M\u00e3 h\u1ecdc vi\u00ean,H\u1ecd v\u00e0 t\u00ean,Email",
            "HV001,Nguy\u1ec5n V\u0103n A,nva@email.com",
            "HV002,Tr\u1ea7n Th\u1ecb B,ttb@email.com",
            "HV003,L\u00ea V\u0103n C,lvc@email.com",
        ];
        $csvContent = implode("\n", $lines);

        return response()->streamDownload(function () use ($csvContent) {
            echo "\xEF\xBB\xBF" . $csvContent; // UTF-8 BOM cho Excel
        }, 'Danh_sach_hoc_vien_mau_co_ban.csv');
    }

    public function processImport(): void
    {
        $this->validate([
            'importFile' => ['required', 'file', 'extensions:xlsx,xls,csv', 'max:5120'], // Max 5MB
        ], [
            'importFile.required' => 'Vui lòng chọn file Excel hoặc CSV.',
            'importFile.extensions' => 'Định dạng file không hỗ trợ. Vui lòng dùng .xlsx, .xls, .csv',
        ]);

        $this->importToken = \Illuminate\Support\Str::uuid()->toString();
        $this->isImportingStatus = true;
        $this->importTotalRows = 0;
        $this->importProcessedRows = 0;
        $this->importQuietTicks = 0;

        $import = new StudentsImport($this->class->id, $this->importToken, false);
        $extension = $this->importFile->getClientOriginalExtension();
        $readerType = match (strtolower($extension)) {
            'csv' => \Maatwebsite\Excel\Excel::CSV,
            'xls' => \Maatwebsite\Excel\Excel::XLS,
            default => \Maatwebsite\Excel\Excel::XLSX,
        };

        try {
            Excel::import($import, $this->importFile->getRealPath(), null, $readerType);

            $this->importSuccess = $import->successCount;
            $this->importErrors = $import->errors;

            if ($this->importSuccess > 0) {
                // Đóng popup để người dùng rảnh tay, hiện thanh tiến trình chạy ngầm
                $this->isImporting = false;
            } else {
                $this->isImportingStatus = false;
                $this->importToken = null;
            }
        } catch (\Exception $e) {
            $this->isImportingStatus = false;
            $this->importToken = null;
            $this->addError('importFile', 'Có lỗi khi đọc file: '.$e->getMessage());
        }
    }
}
