<?php

namespace App\Livewire\Lecturer\Students;

use App\Models\ClassMember;
use App\Models\CourseClass;
use App\Services\LectureManageStudentService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class StudentIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $classFilter = 'all';

    public string $statusFilter = 'active';

    public ?int $editingMemberId = null;

    public string $editingName = '';

    public string $editingStudentCode = '';

    public string $editingStatus = 'active';

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

        $this->closeEdit();
        session()->flash('status', 'Thông tin sinh viên đã được cập nhật.');
    }

    public function archiveMember(int $memberId): void
    {
        $member = $this->ownedMember($memberId);
        $member->update(['status' => 'dropped']);
        $member->delete();

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
