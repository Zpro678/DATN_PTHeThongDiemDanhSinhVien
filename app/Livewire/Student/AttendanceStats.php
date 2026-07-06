<?php

namespace App\Livewire\Student;

use App\Exports\StudentAttendanceStatsExport;
use App\Services\StatisticalService;
use App\Services\SubscriptionService;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceStats extends Component
{
    public function exportExcel()
    {
        // Chỉ cho xuất khi gói hiện tại bật tính năng xuất Excel.
        if (! app(SubscriptionService::class)->canExportExcel(auth()->user())) {
            session()->flash('upgrade_required', 'Xuất báo cáo Excel là tính năng của gói Pro trở lên. Vui lòng nâng cấp để sử dụng.');

            return $this->redirectRoute('upgrade', navigate: true);
        }

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
            'canExportExcel' => app(SubscriptionService::class)->canExportExcel(auth()->user()),
        ])->layout('layouts.user', ['title' => 'Thống kê chuyên cần']);
    }
}
