<?php

namespace App\Livewire\Student;

use App\Services\StatisticalService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AttendanceStats extends Component
{
    public function render(): View
    {
        $statistics = app(StatisticalService::class)
            ->getStudentAttendanceStatistics((int) auth()->id());

        return view('livewire.student.attendance-stats', [
            'subjects' => $statistics['subjects'],
            'totals' => $statistics['totals'],
            'isDemo' => $statistics['isDemo'],
        ])->layout('layouts.user', ['title' => 'Thống kê chuyên cần']);
    }
}
