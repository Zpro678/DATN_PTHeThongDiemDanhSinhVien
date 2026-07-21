<?php

namespace App\Livewire\Student;

use App\Livewire\Concerns\DeniesAccess;
use App\Models\CourseClass;
use App\Models\ClassMember;
use App\Services\StatisticalService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ClassShow extends Component
{
    use DeniesAccess;

    public CourseClass $class;

    public array $attendanceDetail = [];

    public bool $fromAttendanceStats = false;

    public function mount(CourseClass $courseClass): void
    {
        $isActiveMember = $courseClass->members()
            ->where('user_id', auth()->id())
            ->where('status', ClassMember::STATUS_ACTIVE)
            ->exists();

        if (! $isActiveMember) {
            $this->denyAccess('joined-classes', 'Bạn không có quyền xem lớp học này.');
            return;
        }

        if ($courseClass->status === 'archived') {
            $this->denyAccess('joined-classes', 'Lớp học này đã bị lưu trữ do giới hạn gói cước.');
            return;
        }

        $this->class = $courseClass->load(['owner','coOwners']);
        $this->fromAttendanceStats = request()->query('from') === 'attendance-stats';
        
        // Remember the last viewed class for the attendance history filter
        session(['last_student_class_id' => $this->class->id]);

        $statisticalService = app(StatisticalService::class);
        $statistics = $statisticalService->getStudentAttendanceStatistics((int) auth()->id());

        $this->attendanceDetail = collect($statistics['subjects'])
            ->firstWhere('class_id', $this->class->id)
            ?? $statisticalService->emptyStudentClassAttendanceDetail($this->class);
    }

    public function render(): View
    {
        $layoutData = ['title' => 'Thông tin: '.$this->class->name];

        if ($this->fromAttendanceStats) {
            $layoutData['activeNav'] = 'student.attendance.stats';
        }

        return view('livewire.student.class-show', [
            'class' => $this->class,
            'attendanceDetail' => $this->attendanceDetail,
            'fromAttendanceStats' => $this->fromAttendanceStats,
        ])->layout('layouts.user', $layoutData);
    }
}
