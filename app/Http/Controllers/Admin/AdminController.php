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
        $totalSessions = ($summaries->present ?? 0) + ($summaries->late ?? 0) + ($summaries->absent ?? 0) + ($summaries->excused ?? 0);
        $attendanceRate = $totalSessions > 0 ? round(((($summaries->present ?? 0) + ($summaries->late ?? 0)) / $totalSessions) * 100, 1) : 100;

        // Warning students
        $warningStudentsQuery = \App\Models\AttendanceSummary::with(['classMember', 'courseClass'])
            ->whereRaw('(total_present + total_late + total_absent + total_excused) > 0')
            ->whereRaw('((total_present + total_late) / (total_present + total_late + total_absent + total_excused) * 100) < 80');
        
        $warningCount = $warningStudentsQuery->count();
        $warningStudents = $warningStudentsQuery->take(10)->get()->map(function ($summary) {
            $total = $summary->total_present + $summary->total_late + $summary->total_absent + $summary->total_excused;
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
            
            $total = $records->sum();
            $present = $records['present'] ?? 0;
            $late = $records['late'] ?? 0;
            $absent = ($records['absent'] ?? 0) + ($records['excused'] ?? 0);
            
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

    public function createUser()
    {
        $this->ensureAdmin();
        return view('admin.users.create');
    }

    public function storeUser(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'is_admin' => ['required', 'boolean'],
            'status' => ['required', 'in:active,blocked'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('admin.users.index')->with('success', 'Thêm tài khoản người dùng thành công.');
    }

    public function showUser(User $user)
    {
        $this->ensureAdmin();

        $user->loadCount(['ownedClasses', 'joinedClasses', 'subscriptions', 'classJoinRequests']);
        $recentClasses = $user->ownedClasses()
            ->withCount(['users', 'sessions'])
            ->latest()
            ->take(4)
            ->get();
        $recentLogs = $user->auditLogs()->latest('created_at')->take(6)->get();

        return view('admin.users.show', compact('user', 'recentClasses', 'recentLogs'));
    }

    public function editUser(User $user)
    {
        $this->ensureAdmin();
        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, User $user)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'is_admin' => ['required', 'boolean'],
            'status' => ['required', 'in:active,blocked'],
        ]);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Bạn không thể tự thay đổi quyền hoặc trạng thái của chính mình.');
        }

        $user->update($data);

        return back()->with('success', 'Cập nhật thông tin người dùng thành công.');
    }

    public function packagesIndex()
    {
        $this->ensureAdmin();

        $packages = Plan::query()
            ->withCount('subscriptions')
            ->orderByDesc('price')
            ->get();

        return view('admin.packages.index', compact('packages'));
    }

    public function createPackage()
    {
        $this->ensureAdmin();
        return view('admin.packages.create');
    }

    public function showPackage(Plan $package)
    {
        $this->ensureAdmin();

        $package->loadCount('subscriptions');
        $subscriptions = $package->subscriptions()->with('user')->latest()->take(8)->get();

        return view('admin.packages.show', compact('package', 'subscriptions'));
    }

    public function editPackage(Plan $package)
    {
        $this->ensureAdmin();
        return view('admin.packages.edit', compact('package'));
    }

    public function attendanceIndex()
    {
        $this->ensureAdmin();

        $sessions = CourseClass::query()
            ->with(['owner', 'sessions'])
            ->latest()
            ->take(6)
            ->get();

        return view('admin.attendance.index', compact('sessions'));
    }

    public function reportsIndex()
    {
        $this->ensureAdmin();

        $overview = [
            'users' => User::count(),
            'classes' => CourseClass::count(),
            'plans' => Plan::count(),
            'subscriptions' => Subscription::count(),
            'logs' => AuditLog::count(),
        ];

        $recentActivity = AuditLog::query()
            ->with(['user', 'courseClass'])
            ->latest('created_at')
            ->take(8)
            ->get();

        return view('admin.reports.index', compact('overview', 'recentActivity'));
    }

    public function logsIndex(Request $request)
    {
        $this->ensureAdmin();

        $query = AuditLog::query()->with(['user', 'courseClass']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('action', 'like', "%{$search}%")
                ->orWhere('table_name', 'like', "%{$search}%")
                ->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        }

        $logs = $query->latest('created_at')->paginate(20)->withQueryString();

        return view('admin.logs.index', compact('logs'));
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
