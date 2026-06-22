<?php

namespace App\Livewire\Lecturer;

use App\Imports\StudentsImport;
use App\Models\CourseClass;
use App\Models\LeaveRequest;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class ClassShow extends Component
{
    use WithFileUploads;

    // Đối tượng chứa thông tin chi tiết của lớp học hiện tại
    public CourseClass $class;

    // Tổng số lượng sinh viên đang hoạt động trong lớp
    public int $studentsCount = 0;

    // Tổng số buổi học dự kiến của lớp
    public int $sessionsCount = 0;

    // Tổng số buổi học đã hoàn thành hoặc đang diễn ra
    public int $sessionsCompleted = 0;

    // Số lượng đơn xin phép nghỉ đang chờ duyệt của lớp này
    public int $pendingLeaveRequests = 0;

    // Import state
    // Trạng thái hiển thị modal import sinh viên
    public bool $isImporting = false;

    // Đối tượng file Excel/CSV được chọn để import
    public $importFile;

    // Mảng lưu trữ các lỗi phát sinh trong quá trình import
    public array $importErrors = [];

    // Số lượng sinh viên đã được import thành công vào lớp
    public int $importSuccess = 0;

    // Tự động thêm vào các buổi điểm danh đã có
    public bool $syncAttendance = true;

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
    }

    public function render()
    {
        return view('livewire.lecturer.class-show', [
            'recentSessions' => $this->class->sessions->take(5),
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

    public function downloadTemplate()
    {
        $csvContent = "Mã sinh viên,Họ và tên,22/06,23/06\nSV001,Nguyễn Văn A,c,m\nSV002,Trần Thị B,v,c";

        return response()->streamDownload(function () use ($csvContent) {
            echo "\xEF\xBB\xBF".$csvContent; // UTF-8 BOM cho Excel
        }, 'Danh_sach_sinh_vien_mau.csv');
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
