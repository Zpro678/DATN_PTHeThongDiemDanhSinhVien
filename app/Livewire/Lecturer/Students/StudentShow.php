<?php

namespace App\Livewire\Lecturer\Students;

use App\Models\ClassMember;
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
            ->with(['user', 'courseClass', 'attendanceSummary'])
            ->findOrFail($this->memberId);

        $records = $member->attendanceRecords()
            ->with('classSession:id,name,date,start_time,end_time')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('livewire.lecturer.students.show', compact('member', 'records'))
            ->layout('layouts.user', ['title' => 'Chi tiết sinh viên']);
    }
}
