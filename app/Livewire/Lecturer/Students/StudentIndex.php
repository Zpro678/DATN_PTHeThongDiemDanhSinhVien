<?php

namespace App\Livewire\Lecturer\Students;

use App\Models\ClassMember;
use App\Models\CourseClass;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentsImport;
use Illuminate\Support\Facades\Storage;

class StudentIndex extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';

    #[\Livewire\Attributes\Url(as: 'class_id')]
    public string $classFilter = 'all';

    #[\Livewire\Attributes\Url(as: 'action')]
    public string $action = '';

    public string $statusFilter = 'active';

    public ?int $editingMemberId = null;

    public string $editingName = '';

    public string $editingStudentCode = '';

    public string $editingStatus = 'active';

    // Add Student state
    public bool $isAdding = false;
    public string $newName = '';
    public string $newStudentCode = '';
    public string $newClassId = '';

    public ?int $archivingMemberId = null;

    // Import state
    public bool $isImporting = false;
    public string $importClassId = '';
    public $importFile;
    public array $importErrors = [];
    public int $importSuccess = 0;

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
        $csvContent = "Mã sinh viên,Họ và tên\nSV001,Nguyễn Văn A\nSV002,Trần Thị B";
        return response()->streamDownload(function () use ($csvContent) {
            echo "\xEF\xBB\xBF" . $csvContent; // UTF-8 BOM cho Excel
        }, 'Danh_sach_sinh_vien_mau.csv');
    }

    public function processImport(): void
    {
        $this->validate([
            'importClassId' => ['required', 'exists:classes,id'],
            'importFile' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'], // Max 5MB
        ], [
            'importClassId.required' => 'Vui lòng chọn lớp học.',
            'importFile.required' => 'Vui lòng chọn file Excel hoặc CSV.',
            'importFile.mimes' => 'Định dạng file không hỗ trợ. Vui lòng dùng .xlsx, .xls, .csv',
        ]);

        $courseClass = CourseClass::where('owner_user_id', auth()->id())->findOrFail($this->importClassId);

        $import = new StudentsImport($courseClass->id);

        try {
            Excel::import($import, $this->importFile);
            
            $this->importSuccess = $import->successCount;
            $this->importErrors = $import->errors;

            if (empty($this->importErrors)) {
                $this->closeImport();
                session()->flash('status', "Đã nhập thành công {$this->importSuccess} sinh viên vào lớp.");
            }
        } catch (\Exception $e) {
            $this->addError('importFile', 'Có lỗi khi đọc file: ' . $e->getMessage());
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
        if (!$this->archivingMemberId) {
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
        $classes = CourseClass::query()
            ->where('owner_user_id', auth()->id())
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

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

        return view('livewire.lecturer.students.index', compact('classes', 'members'))
            ->layout('layouts.user', ['title' => 'Quản lý sinh viên']);
    }
}
