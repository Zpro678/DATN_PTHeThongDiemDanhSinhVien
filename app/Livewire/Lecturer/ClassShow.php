<?php

namespace App\Livewire\Lecturer;

use App\Models\CourseClass;
use App\Models\ClassSession;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentsImport;

class ClassShow extends Component
{
    use WithFileUploads;

    public CourseClass $class;

    // Thống kê hiển thị trên trang
    public int $studentsCount = 0;
    public int $sessionsCount = 0;
    public int $sessionsCompleted = 0;
    public int $pendingLeaveRequests = 0;

    // Import state
    public bool $isImporting = false;
    public $importFile;
    public array $importErrors = [];
    public int $importSuccess = 0;

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

        $this->studentsCount   = $courseClass->members()->count();
        $this->sessionsCount   = $courseClass->total_sessions;
        $this->sessionsCompleted = $courseClass->sessions()->whereIn('status', ['closed', 'active'])->count();
        $this->pendingLeaveRequests = \App\Models\LeaveRequest::whereHas('classSession', function ($q) use ($courseClass) {
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
        $csvContent = "Mã sinh viên,Họ và tên\nSV001,Nguyễn Văn A\nSV002,Trần Thị B";
        return response()->streamDownload(function () use ($csvContent) {
            echo "\xEF\xBB\xBF" . $csvContent; // UTF-8 BOM cho Excel
        }, 'Danh_sach_sinh_vien_mau.csv');
    }

    public function processImport(): void
    {
        $this->validate([
            'importFile' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'], // Max 5MB
        ], [
            'importFile.required' => 'Vui lòng chọn file Excel hoặc CSV.',
            'importFile.mimes' => 'Định dạng file không hỗ trợ. Vui lòng dùng .xlsx, .xls, .csv',
        ]);

        $import = new StudentsImport($this->class->id);

        try {
            Excel::import($import, $this->importFile);
            
            $this->importSuccess = $import->successCount;
            $this->importErrors = $import->errors;

            if (empty($this->importErrors)) {
                $this->closeImport();
                
                // Cập nhật lại số sinh viên
                $this->studentsCount = $this->class->members()->count();

                session()->flash('status', "Đã nhập thành công {$this->importSuccess} sinh viên vào lớp.");
            }
        } catch (\Exception $e) {
            $this->addError('importFile', 'Có lỗi khi đọc file: ' . $e->getMessage());
        }
    }
}
