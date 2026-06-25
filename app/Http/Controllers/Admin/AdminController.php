<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CourseClass;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    private function ensureAdmin()
    {
        abort_unless(auth()->user()?->is_admin, 403);
    }

    public function dashboard()
    {
        $this->ensureAdmin();

        $totalStudents = User::where('is_admin', false)->count();
        $activeClasses = CourseClass::where('status', 'active')->count();

        // Calculate attendance rate
        $summaries = \App\Models\AttendanceSummary::selectRaw('SUM(total_present) as present, SUM(total_late) as late, SUM(total_absent) as absent, SUM(total_excused) as excused')->first();
        // Bỏ vắng có phép khỏi mẫu số khi tính tỷ lệ chuyên cần.
        $totalSessions = ($summaries->present ?? 0) + ($summaries->late ?? 0) + ($summaries->absent ?? 0);
        $attendanceRate = $totalSessions > 0 ? round(((($summaries->present ?? 0) + ($summaries->late ?? 0)) / $totalSessions) * 100, 1) : 100;

        // Warning students (mẫu số đã bỏ vắng có phép).
        $warningStudentsQuery = \App\Models\AttendanceSummary::with(['classMember', 'courseClass'])
            ->whereRaw('(total_present + total_late + total_absent) > 0')
            ->whereRaw('((total_present + total_late) / (total_present + total_late + total_absent) * 100) < 80');

        $warningCount = $warningStudentsQuery->count();
        $warningStudents = $warningStudentsQuery->take(10)->get()->map(function ($summary) {
            $total = $summary->total_present + $summary->total_late + $summary->total_absent;
            $rate = $total > 0 ? round((($summary->total_present + $summary->total_late) / $total) * 100, 1) : 100;
            return [
                'mssv' => $summary->classMember->student_code ?? 'N/A',
                'name' => $summary->classMember->full_name ?? 'N/A',
                'class' => $summary->courseClass->code ?? 'N/A',
                'subject' => $summary->courseClass->name ?? 'N/A',
                'attendanceRate' => $rate,
                'level' => $rate < 70 ? 'Nguy cấp' : 'Cảnh cáo',
                'levelColor' => $rate < 70 ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-700 border-amber-200',
            ];
        })->sortBy('attendanceRate')->values()->toArray();

        // Realtime attendance: get latest 3 class sessions with attendance counts
        $realtimeAttendance = \App\Models\ClassSession::with(['courseClass.owner'])
            ->withCount(['attendanceRecords as checked_in_count' => function ($query) {
                $query->whereIn('status', ['present', 'late']);
            }])
            ->latest('created_at')
            ->take(3)
            ->get()->map(function ($session) {
                $total = \App\Models\ClassMember::where('class_id', $session->class_id)->count();
                $percentage = $total > 0 ? round(($session->checked_in_count / $total) * 100, 1) : 0;
                return [
                    'className' => $session->courseClass->name ?? 'N/A',
                    'room' => $session->courseClass->code ?? 'N/A', // Using code as room/identifier
                    'instructor' => $session->courseClass->owner->name ?? 'N/A',
                    'checkedIn' => $session->checked_in_count,
                    'total' => $total,
                    'startedAt' => $session->created_at->diffForHumans(),
                    'percentage' => $percentage,
                    'pin' => $session->qr_code ?? 'N/A',
                ];
            });

        // Attendance distribution for the chart
        $distribution = [
            'present' => $summaries->present ?? 0,
            'late' => $summaries->late ?? 0,
            'absent' => $summaries->absent ?? 0,
            'excused' => $summaries->excused ?? 0,
        ];

        // Attendance Chart Data (Last 6 months)
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months->push([
                'month' => $date->format('m/y'),
                'start' => $date->copy()->startOfMonth(),
                'end' => $date->copy()->endOfMonth(),
            ]);
        }

        $chartData = [];
        foreach ($months as $month) {
            $records = \App\Models\AttendanceRecord::whereBetween('created_at', [$month['start'], $month['end']])
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');
            
            // Bỏ vắng có phép (excused) ra khỏi mẫu số: không tính là chuyên cần cũng không tính là vắng.
            $excused = $records['excused'] ?? 0;
            $total = $records->sum() - $excused;
            $present = $records['present'] ?? 0;
            $late = $records['late'] ?? 0;
            $absent = $records['absent'] ?? 0;

            $chartData[] = [
                'name' => 'T' . $month['month'],
                'Chuyên cần' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
                'Đi muộn' => $total > 0 ? round(($late / $total) * 100, 1) : 0,
                'Vắng mặt' => $total > 0 ? round(($absent / $total) * 100, 1) : 0,
            ];
        }

        return view('admin.dashboard', compact(
            'totalStudents', 
            'activeClasses', 
            'attendanceRate', 
            'warningCount', 
            'warningStudents',
            'realtimeAttendance',
            'distribution',
            'chartData'
        ));
    }

















    public function reportsIndex()
    {
        $this->ensureAdmin();

        $totalRevenue = \App\Models\Transaction::where('status', 'success')->sum('amount');
        
        $overview = [
            'users' => User::count(),
            'classes' => CourseClass::count(),
            'plans' => Plan::count(),
            'transactions' => \App\Models\Transaction::where('status', 'success')->count(),
            'revenue' => $totalRevenue,
        ];

        // Doanh thu theo 6 tháng gần nhất (Mock hoặc thật nếu có dl)
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $amount = \App\Models\Transaction::where('status', 'success')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount');
            
            // Nếu chưa có giao dịch thật, tạo dữ liệu ảo để demo biểu đồ đẹp
            if ($amount == 0 && app()->isLocal()) {
                $amount = rand(500000, 5000000);
            }
                
            $monthlyRevenue[] = [
                'month' => $month->format('m/Y'),
                'amount' => $amount
            ];
        }

        $recentTransactions = \App\Models\Transaction::query()
            ->with(['user', 'plan'])
            ->latest('created_at')
            ->take(8)
            ->get();

        return view('admin.reports.index', compact('overview', 'monthlyRevenue', 'recentTransactions'));
    }

    public function settingsIndex()
    {
        $this->ensureAdmin();

        $system = [
            'app_name' => config('app.name'),
            'environment' => app()->environment(),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'admin_email' => auth()->user()?->email,
        ];

        return view('admin.settings.index', compact('system'));
    }
}
