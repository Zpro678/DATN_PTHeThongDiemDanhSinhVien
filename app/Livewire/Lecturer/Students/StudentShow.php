<?php

namespace App\Livewire\Lecturer\Students;

use App\Models\ClassMember;
use App\Services\LectureManageStudentService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class StudentShow extends Component
{
    use WithPagination;

    public int $memberId;

    public function mount(int $member): void
    {
        $this->memberId = $this->memberQuery()->findOrFail($member)->id;
    }

    private function memberQuery(): Builder
    {
        return ClassMember::query()
            ->withTrashed()
            ->whereHas('courseClass', fn (Builder $query) => $query->where('owner_user_id', auth()->id()));
    }

    public function render(): View
    {
        $member = $this->memberQuery()
            ->with(['user', 'courseClass'])
            ->findOrFail($this->memberId);

        // Dùng cùng nguồn dữ liệu với trang danh sách: query live từ attendance_records.
        $stats = app(LectureManageStudentService::class)
            ->getStudentsAttendanceStats([$this->memberId])[$this->memberId] ?? null;

        $records = $member->attendanceRecords()
            ->with('classSession:id,name,date,start_time,end_time')
            ->whereHas('classSession', fn ($q) => $q->where('status', 'closed'))
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('livewire.lecturer.students.show', compact('member', 'records', 'stats'))
            ->layout('layouts.user', ['title' => 'Chi tiết sinh viên']);
    }
}
