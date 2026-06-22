<?php

namespace App\Livewire\Lecturer\Students;

use App\Imports\StudentsImport;
use App\Models\ClassMember;
use App\Models\CourseClass;
use App\Services\LectureManageStudentService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class StudentIndex extends Component
{
    use WithFileUploads, WithPagination;

    // Từ khóa tìm kiếm sinh viên
    public string $search = '';

    // Bộ lọc theo ID lớp học ('all' là tất cả các lớp)
    #[Url(as: 'class_id')]
    public string $classFilter = 'all';

    // Tham số hành động từ URL (ví dụ: 'import' để tự động mở modal)
    #[Url(as: 'action')]
    public string $action = '';

    // Bộ lọc trạng thái sinh viên (đang học hoặc đã lưu trữ)
    public string $statusFilter = 'active';

    // ID của thành viên lớp đang được chỉnh sửa
    public ?int $editingMemberId = null;

    // Họ tên của sinh viên đang được chỉnh sửa
    public string $editingName = '';

    // Mã số sinh viên đang được chỉnh sửa
    public string $editingStudentCode = '';

    // Trạng thái của sinh viên đang được chỉnh sửa
    public string $editingStatus = 'active';

    // Trạng thái hiển thị form thêm sinh viên thủ công
    public bool $isAdding = false;

    // Họ tên sinh viên khi thêm mới
    public string $newName = '';

    // Mã số sinh viên khi thêm mới
    public string $newStudentCode = '';

    // ID lớp học mà sinh viên sẽ được thêm vào
    public string $newClassId = '';

    // ID sinh viên đang được chọn để lưu trữ (chờ xác nhận)
    public ?int $archivingMemberId = null;

    // Trạng thái hiển thị modal import Excel/CSV
    public bool $isImporting = false;

    // ID lớp học mà danh sách sinh viên sẽ được import vào
    public string $importClassId = '';

    // Đối tượng file Excel hoặc CSV được upload lên
    public $importFile;

    // Mảng lưu trữ các lỗi xuất hiện trong quá trình import
    public array $importErrors = [];

    // Số lượng sinh viên đã được import thành công
    public int $importSuccess = 0;

    // Tự động thêm vào các buổi điểm danh đã có
    public bool $syncAttendance = true;

    public function mount(): void
    {
        if ($this->action === 'import') {
            $this->openImport();
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedClassFilter(): void
    {
        $this->resetPage();
    }

    public function setStatusFilter(string $status): void
    {
        if (in_array($status, ['active', 'archived'], true)) {
            $this->statusFilter = $status;
            $this->resetPage();
        }
    }

    public function openEdit(int $memberId): void
    {
        $member = $this->ownedMember($memberId);

        $this->editingMemberId = $member->id;
        $this->editingName = $member->full_name;
        $this->editingStudentCode = $member->student_code;
        $this->editingStatus = $member->status;
    }

    public function closeEdit(): void
    {
        $this->reset(['editingMemberId', 'editingName', 'editingStudentCode']);
        $this->editingStatus = 'active';
        $this->resetValidation();
    }

    public function openAdd(): void
    {
        $this->isAdding = true;
        $this->newClassId = $this->classFilter !== 'all' ? $this->classFilter : '';
    }

    public function closeAdd(): void
    {
        $this->isAdding = false;
        $this->reset(['newName', 'newStudentCode', 'newClassId']);
        $this->resetValidation();
    }

    public function addMember(): void
    {
        $validated = $this->validate([
            'newClassId' => ['required', 'exists:classes,id'],
            'newName' => ['required', 'string', 'max:255'],
            'newStudentCode' => ['required', 'string', 'max:50'],
        ], [
            'newClassId.required' => 'Vui lòng chọn lớp học.',
            'newName.required' => 'Vui lòng nhập họ tên.',
            'newStudentCode.required' => 'Vui lòng nhập mã sinh viên.',
        ]);

        // Ensure the class belongs to the lecturer
        $courseClass = CourseClass::where('owner_user_id', auth()->id())->findOrFail($validated['newClassId']);

        $duplicateExists = ClassMember::query()
            ->where('class_id', $courseClass->id)
            ->where('student_code', $validated['newStudentCode'])
            ->exists();

        if ($duplicateExists) {
            $this->addError('newStudentCode', 'Mã sinh viên đã tồn tại trong lớp này.');

            return;
        }

        ClassMember::create([
            'class_id' => $courseClass->id,
            'full_name' => $validated['newName'],
            'student_code' => strtoupper($validated['newStudentCode']),
            'status' => 'active',
        ]);

        $this->closeAdd();
        session()->flash('status', 'Sinh viên đã được thêm vào lớp thành công.');
    }

    public function openImport(): void
    {
        $this->isImporting = true;
        $this->importClassId = $this->classFilter !== 'all' ? $this->classFilter : '';
        $this->reset(['importFile', 'importErrors', 'importSuccess']);
    }

    public function closeImport(): void
    {
        $this->isImporting = false;
        $this->reset(['importFile', 'importErrors', 'importSuccess', 'importClassId']);
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
            'importClassId' => ['required', 'exists:classes,id'],
            'importFile' => ['required', 'file', 'extensions:xlsx,xls,csv', 'max:5120'], // Max 5MB
        ], [
            'importClassId.required' => 'Vui lòng chọn lớp học.',
            'importFile.required' => 'Vui lòng chọn file Excel hoặc CSV.',
            'importFile.extensions' => 'Định dạng file không hỗ trợ. Vui lòng dùng .xlsx, .xls, .csv',
        ]);

        $courseClass = CourseClass::where('owner_user_id', auth()->id())->findOrFail($this->importClassId);

        $import = new StudentsImport($courseClass->id);

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
                if ($this->syncAttendance && $this->importClassId) {
                    $sessions = \App\Models\ClassSession::where('class_id', $this->importClassId)->get();
                    $members = \App\Models\ClassMember::where('class_id', $this->importClassId)->where('status', 'active')->get();
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
                session()->flash('status', "Đã nhập thành công {$this->importSuccess} sinh viên vào lớp.");
            }
        } catch (\Exception $e) {
            $this->addError('importFile', 'Có lỗi khi đọc file: '.$e->getMessage());
        }
    }

    public function saveMember(): void
    {
        $member = $this->ownedMember((int) $this->editingMemberId);

        $validated = $this->validate([
            'editingName' => ['required', 'string', 'max:255'],
            'editingStudentCode' => ['required', 'string', 'max:50'],
            'editingStatus' => ['required', 'in:active,dropped'],
        ]);

        $duplicateExists = ClassMember::query()
            ->where('class_id', $member->class_id)
            ->where('student_code', $validated['editingStudentCode'])
            ->whereKeyNot($member->id)
            ->exists();

        if ($duplicateExists) {
            $this->addError('editingStudentCode', 'Mã sinh viên đã tồn tại trong lớp này.');

            return;
        }

        $member->update([
            'full_name' => $validated['editingName'],
            'student_code' => strtoupper($validated['editingStudentCode']),
            'status' => $validated['editingStatus'],
        ]);

        // Nếu chuyển sang trạng thái "thôi học", tự động đưa vào mục lưu trữ
        if ($validated['editingStatus'] === 'dropped') {
            $member->delete();
        }

        $this->closeEdit();
        session()->flash('status', 'Thông tin sinh viên đã được cập nhật.');
    }

    public function confirmArchive(int $memberId): void
    {
        $this->archivingMemberId = $memberId;
    }

    public function closeArchiveConfirm(): void
    {
        $this->archivingMemberId = null;
    }

    public function archiveMember(): void
    {
        if (! $this->archivingMemberId) {
            return;
        }

        $member = $this->ownedMember($this->archivingMemberId);
        $member->update(['status' => 'dropped']);
        $member->delete();

        $this->closeArchiveConfirm();
        session()->flash('status', 'Sinh viên đã được chuyển vào lưu trữ.');
    }

    public function restoreMember(int $memberId): void
    {
        $member = $this->ownedMember($memberId, true);
        $member->restore();
        $member->update(['status' => 'active']);

        session()->flash('status', 'Sinh viên đã được khôi phục vào lớp.');
    }

    private function ownedMember(int $memberId, bool $withTrashed = false): ClassMember
    {
        $query = ClassMember::query()
            ->when($withTrashed, fn (Builder $query) => $query->withTrashed())
            ->whereHas('courseClass', fn (Builder $query) => $query->where('owner_user_id', auth()->id()));

        return $query->findOrFail($memberId);
    }

    public function render(): View
    {
        $studentService = app(LectureManageStudentService::class); // Gọi service xử lý thống kê chuyên cần sinh viên.

        $classes = CourseClass::query()
            ->where('owner_user_id', auth()->id()) // Chỉ lấy các lớp do giảng viên hiện tại quản lý.
            ->orderBy('name') // Sắp xếp lớp theo tên để dropdown dễ nhìn.
            ->get(['id', 'name', 'code']); // Chỉ lấy cột cần dùng cho bộ lọc lớp.

        $members = ClassMember::query()
            ->with(['courseClass:id,name,code', 'user:id,email,avatar', 'attendanceSummary'])
            ->whereHas('courseClass', fn (Builder $query) => $query->where('owner_user_id', auth()->id()))
            ->when(
                $this->statusFilter === 'archived',
                fn (Builder $query) => $query->onlyTrashed(),
                fn (Builder $query) => $query->where('status', 'active'),
            )
            ->when($this->classFilter !== 'all', fn (Builder $query) => $query->where('class_id', $this->classFilter))
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query->where('full_name', 'like', '%'.$this->search.'%')
                        ->orWhere('student_code', 'like', '%'.$this->search.'%')
                        ->orWhereHas('user', fn (Builder $query) => $query->where('email', 'like', '%'.$this->search.'%'));
                });
            })
            ->orderBy('full_name')
            ->paginate(12);

        $attendanceStats = $studentService->getStudentsAttendanceStats( // Tính chuyên cần cho từng sinh viên đang hiển thị trên trang hiện tại.
            $members->getCollection()->pluck('id') // Lấy id sinh viên trong page hiện tại sau khi phân trang.
        );

        $attendanceOverview = $studentService->getTotalAttendanceStats( // Tính tổng chuyên cần theo toàn bộ bộ lọc hiện tại.
            auth()->id(), // Giới hạn dữ liệu theo giảng viên đang đăng nhập.
            $this->classFilter !== 'all' ? (int) $this->classFilter : null // Nếu chọn một lớp thì chỉ thống kê lớp đó.
        );

        return view('livewire.lecturer.students.index', compact('classes', 'members', 'attendanceStats', 'attendanceOverview')) // Truyền dữ liệu lớp, sinh viên và chuyên cần sang Blade.
            ->layout('layouts.user', ['title' => 'Quản lý sinh viên']);
    }
}
