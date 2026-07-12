<?php

namespace App\Livewire\Admin;

use App\Models\CourseClass;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $totalStudents = User::where('role', User::ROLE_USER)->count();
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
                'name' => $summary->classMember?->full_name ?? 'N/A',
                'class' => $summary->courseClass?->join_key ?? 'N/A',
                'subject' => $summary->courseClass?->name ?? 'N/A',
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
                    'className' => $session->courseClass?->name ?? 'N/A',
                    'room' => $session->courseClass?->join_key ?? 'N/A', // Using code as room/identifier
                    'instructor' => $session->courseClass?->owner?->name ?? 'N/A',
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
            $date = now()->startOfMonth()->subMonths($i);
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
            $excused = $records->get('excused', 0);
            $total = $records->sum() - $excused;
            $present = $records->get('present', 0);
            $late = $records->get('late', 0);
            $absent = $records->get('absent', 0);

            $chartData[] = [
                'name' => 'T' . $month['month'],
                'Chuyên cần' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
                'Đi muộn' => $total > 0 ? round(($late / $total) * 100, 1) : 0,
                'Vắng mặt' => $total > 0 ? round(($absent / $total) * 100, 1) : 0,
            ];
        }

        return view('livewire.admin.dashboard', compact(
            'totalStudents', 
            'activeClasses', 
            'attendanceRate', 
            'warningCount', 
            'warningStudents',
            'realtimeAttendance',
            'distribution',
            'chartData'
        ))->layout('components.admin-layout');
    }
}
