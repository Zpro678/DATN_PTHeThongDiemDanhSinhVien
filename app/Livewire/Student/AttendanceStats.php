<?php

namespace App\Livewire\Student;

use App\Exports\StudentAttendanceStatsExport;
use App\Services\StatisticalService;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceStats extends Component
{
    public function exportExcel()
    {
        $fileName = 'thong_ke_chuyen_can_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new StudentAttendanceStatsExport(auth()->id()), $fileName);
    }

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
