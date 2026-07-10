<?php

namespace App\Livewire\Admin\Reports;

use App\Models\CourseClass;
use App\Models\Plan;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ReportIndex extends Component
{
    public function render()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $totalRevenue = \App\Models\Transaction::whereIn('status', ['PAID', 'SUCCESS', 'paid', 'success'])->sum('amount');
        
        $overview = [
            'users' => User::count(),
            'classes' => CourseClass::count(),
            'plans' => Plan::count(),
            'transactions' => \App\Models\Transaction::whereIn('status', ['PAID', 'SUCCESS', 'paid', 'success'])->count(),
            'revenue' => $totalRevenue,
        ];

        // Doanh thu theo 6 tháng gần nhất (Mock hoặc thật nếu có dl)
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->startOfMonth()->subMonths($i);
            $amount = \App\Models\Transaction::whereIn('status', ['PAID', 'SUCCESS', 'paid', 'success'])
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount');
            
            $monthlyRevenue[] = [
                'month' => $month->format('m/Y'),
                'amount' => $amount
            ];
        }

        $recentTransactions = \App\Models\Transaction::query()
            ->whereIn('status', ['PAID', 'SUCCESS', 'paid', 'success'])
            ->with(['user', 'plan'])
            ->latest('created_at')
            ->take(8)
            ->get();

        return view('livewire.admin.reports.report-index', compact('overview', 'monthlyRevenue', 'recentTransactions'))
            ->layout('components.admin-layout');
    }
}
