<?php

namespace App\Livewire\Lecturer;

use App\Imports\StudentsImport;
use App\Models\CourseClass;
use App\Models\LeaveRequest;
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

    public bool $syncAttendance = false;

    public bool $isEditingCode = false;
    public string $newClassCode = '';

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

        $this->studentsCount   = $courseClass->members()->where('status', 'active')->count();
        $this->sessionsCount = $courseClass->total_lessons;
        $this->sessionsCompleted = $courseClass->sessions()->whereIn('status', ['closed', 'active'])->count();
        $this->pendingLeaveRequests = LeaveRequest::whereHas('classSession', function ($q) use ($courseClass) {
            $q->where('class_id', $courseClass->id);
        })->where('status', 'pending')->count();
        $this->pendingMembersCount = $courseClass->members()->where('status', 'pending')->count();
        
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
            $this->redirectRoute('lecturer.attendance.manual.create', ['ma_user' => auth()->id(), 'class_id' => $this->class->id], navigate: true);
        }
    }

    public function toggleEditCode(): void
    {
        $this->isEditingCode = !$this->isEditingCode;
        if ($this->isEditingCode) {
            $this->newClassCode = $this->class->code;
        } else {
            $this->resetValidation('newClassCode');
        }
    }

    public function updateClassCode(): void
    {
        $this->validate([
            'newClassCode' => 'required|string|max:50|unique:classes,code,' . $this->class->id,
        ], [
            'newClassCode.required' => 'Mã lớp không được để trống.',
            'newClassCode.unique' => 'Mã lớp này đã tồn tại trong hệ thống.',
            'newClassCode.max' => 'Mã lớp không được vượt quá 50 ký tự.',
        ]);

        $this->class->update([
            'code' => $this->newClassCode,
        ]);

        $this->isEditingCode = false;
        session()->flash('status', 'Đã cập nhật mã lớp thành công.');
    }

    public function generateRandomCode(): void
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (CourseClass::where('code', $code)->where('id', '!=', $this->class->id)->exists());

        $this->newClassCode = $code;
    }

    public function render()
    {
        return view('livewire.lecturer.class-show', [
            'recentSessions' => $this->recentSessions,
            'students' => $this->class->members()->with(['user', 'attendanceSummary'])->where('status', 'active')->paginate(5),
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
        $csvContent = "Mã học viên,Họ và tên,Email,22/06,23/06\nHV001,Nguyễn Văn A,nva@email.com,c,m\nHV002,Trần Thị B,ttb@email.com,v,c";

        return response()->streamDownload(function () use ($csvContent) {
            echo "\xEF\xBB\xBF".$csvContent; // UTF-8 BOM cho Excel
        }, 'Danh_sach_hoc_vien_mau_day_du.csv');
    }

    public function downloadBasicTemplate()
    {
        $csvContent = "Mã học viên,Họ và tên,Email\nHV001,Nguyễn Văn A,nva@email.com\nHV002,Trần Thị B,ttb@email.com";

        return response()->streamDownload(function () use ($csvContent) {
            echo "\xEF\xBB\xBF".$csvContent; // UTF-8 BOM cho Excel
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

        $import = new StudentsImport($this->class->id);
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

            if (empty($this->importErrors)) {
                if ($this->syncAttendance) {
                    $sessions = \App\Models\ClassSession::where('class_id', $this->class->id)->get();
                    $members = \App\Models\ClassMember::where('class_id', $this->class->id)->where('status', 'active')->get();
                    $recordsToInsert = [];
                    $now = now();
                    foreach ($sessions as $session) {
                        foreach ($members as $member) {
                            $recordsToInsert[] = [
                                'class_session_id' => $session->id,
                                'class_member_id' => $member->id,
                                'status' => 'pending',
                                'is_verified' => $member->user_id !== null,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    }
                    if (!empty($recordsToInsert)) {
                        \App\Models\AttendanceRecord::insertOrIgnore($recordsToInsert);
                    }
                }

                $this->closeImport();

                // Cập nhật lại số sinh viên
                $this->studentsCount = $this->class->members()->where('status', 'active')->count();

                session()->flash('status', "Đã nhập thành công {$this->importSuccess} sinh viên vào lớp.");
            }
        } catch (\Exception $e) {
            $this->addError('importFile', 'Có lỗi khi đọc file: '.$e->getMessage());
        }
    }
}
