<?php

namespace App\Livewire\Student;

use App\Models\CourseClass;
use App\Models\ClassMember;
use App\Services\StatisticalService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ClassShow extends Component
{
    public CourseClass $class;

    public array $attendanceDetail = [];

    public bool $fromAttendanceStats = false;

    public function mount(CourseClass $courseClass): void
    {
        abort_unless(
            $courseClass->members()
                ->where('user_id', auth()->id())
                ->where('status', ClassMember::STATUS_ACTIVE)
                ->exists(),
            403,
        );

        abort_if(
            $courseClass->status === 'archived',
            403,
            'Lớp học này đã bị lưu trữ do giới hạn gói cước.'
        );

        $this->class = $courseClass->load('owner');
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
