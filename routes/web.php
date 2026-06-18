<?php

use App\Models\AuditLog;
use App\Models\CourseClass;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Http\Controllers\ProfileController;
use App\Livewire\Lecturer\Attendance\AttendanceCreate;
use App\Livewire\Lecturer\Attendance\AttendanceIndex;
use App\Livewire\Lecturer\Attendance\ManualAttendanceCreate;
use App\Livewire\Lecturer\Attendance\ManualAttendanceSession;
use App\Livewire\Lecturer\Attendance\QrAttendanceCreate;
use App\Livewire\Lecturer\Attendance\QrAttendanceSession;
use App\Livewire\Lecturer\Students\LeaveRequestIndex;
use App\Livewire\Lecturer\Students\LeaveRequestShow;
use App\Livewire\Lecturer\Students\StudentIndex;
use App\Livewire\Lecturer\Students\StudentShow;
use App\Livewire\Student\AttendanceHistory as StudentAttendanceHistory;
use App\Livewire\Student\AttendanceStats as StudentAttendanceStats;
use App\Livewire\User\Classes as UserClasses;
use App\Livewire\User\CreateClass;
use App\Livewire\User\Dashboard as UserDashboard;
use App\Livewire\User\JoinedClasses;
use App\Livewire\User\ManagedClasses;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::middleware(['auth', 'verified', 'user.route'])->group(function () {
    $ensureAdmin = function (): void {
        abort_unless(auth()->user()?->is_admin, 403);
    };

    Route::prefix('admin/{ma_user}')->name('admin.')->group(function () use ($ensureAdmin) {
        Route::get('/', function () use ($ensureAdmin) {
            $ensureAdmin();

            return view('admin.dashboard');
        })->name('dashboard');

        Route::redirect('/dashboard', '/admin')->name('dashboard.alias');

        Route::get('/users', function () use ($ensureAdmin) {
            $ensureAdmin();

            $users = User::query()
                ->withCount(['ownedClasses', 'joinedClasses', 'subscriptions', 'classJoinRequests'])
                ->when(request('search'), function ($query, string $search) {
                    $query->where(function ($query) use ($search) {
                        $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
                })
                ->when(request('role'), function ($query, string $role) {
                    if ($role === 'admin') {
                        $query->where('is_admin', true);
                    } elseif (in_array($role, ['teacher', 'student'], true)) {
                        $query->where('is_admin', false);
                    }
                })
                ->when(request('status'), fn ($query, string $status) => $query->where('status', $status))
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString();

            return view('admin.users.index', compact('users'));
        })->name('users.index');

        Route::redirect('/accounts', '/admin/users')->name('accounts.index');

        Route::get('/users/{user}', function (User $user) use ($ensureAdmin) {
            $ensureAdmin();

            $user->loadCount(['ownedClasses', 'joinedClasses', 'subscriptions', 'classJoinRequests']);
            $recentClasses = $user->ownedClasses()
                ->withCount(['users', 'sessions'])
                ->latest()
                ->take(4)
                ->get();
            $recentLogs = $user->auditLogs()->latest('created_at')->take(6)->get();

            return view('admin.users.show', compact('user', 'recentClasses', 'recentLogs'));
        })->whereNumber('user')->name('users.show');

        Route::get('/users/{user}/edit', function (User $user) use ($ensureAdmin) {
            $ensureAdmin();

            return view('admin.users.edit', compact('user'));
        })->whereNumber('user')->name('users.edit');

        Route::get('/packages', function () use ($ensureAdmin) {
            $ensureAdmin();

            $packages = Plan::query()
                ->withCount('subscriptions')
                ->orderByDesc('price')
                ->get();

            return view('admin.packages.index', compact('packages'));
        })->name('packages.index');

        Route::get('/packages/create', function () use ($ensureAdmin) {
            $ensureAdmin();

            return view('admin.packages.create');
        })->name('packages.create');

        Route::get('/packages/{package}', function (Plan $package) use ($ensureAdmin) {
            $ensureAdmin();

            $package->loadCount('subscriptions');
            $subscriptions = $package->subscriptions()->with('user')->latest()->take(8)->get();

            return view('admin.packages.show', compact('package', 'subscriptions'));
        })->whereNumber('package')->name('packages.show');

        Route::get('/packages/{package}/edit', function (Plan $package) use ($ensureAdmin) {
            $ensureAdmin();

            return view('admin.packages.edit', compact('package'));
        })->whereNumber('package')->name('packages.edit');

        Route::get('/attendance', function () use ($ensureAdmin) {
            $ensureAdmin();

            $sessions = CourseClass::query()
                ->with(['owner', 'sessions'])
                ->latest()
                ->take(6)
                ->get();

            return view('admin.attendance.index', compact('sessions'));
        })->name('attendance.index');

        Route::get('/reports', function () use ($ensureAdmin) {
            $ensureAdmin();

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
        })->name('reports.index');

        Route::get('/logs', function () use ($ensureAdmin) {
            $ensureAdmin();

            $logs = AuditLog::query()
                ->with(['user', 'courseClass'])
                ->latest('created_at')
                ->take(20)
                ->get();

            return view('admin.logs.index', compact('logs'));
        })->name('logs.index');

        Route::get('/settings', function () use ($ensureAdmin) {
            $ensureAdmin();

            $system = [
                'app_name' => config('app.name'),
                'environment' => app()->environment(),
                'timezone' => config('app.timezone'),
                'locale' => config('app.locale'),
                'admin_email' => auth()->user()?->email,
            ];

            return view('admin.settings.index', compact('system'));
        })->name('settings.index');
    });

    Route::prefix('user/{ma_user}')->group(function () {
        Route::get('/dashboard', UserDashboard::class)->name('dashboard');
        Route::get('/classes', UserClasses::class)->name('classes');
    Route::get('/managed-classes', ManagedClasses::class)->name('managed-classes');
    Route::get('/joined-classes', JoinedClasses::class)->name('joined-classes');
    Route::get('/managed-classes/create-class', CreateClass::class)->name('create-class');
    Route::get('/student/attendance/history', StudentAttendanceHistory::class)->name('student.attendance.history');
    Route::get('/student/attendance/stats', StudentAttendanceStats::class)->name('student.attendance.stats');
    Route::get('/student/leave-requests/create', \App\Livewire\Student\LeaveRequestCreate::class)->name('student.leave-requests.create');
    Route::get('/student/leave-requests/history', \App\Livewire\Student\LeaveRequestHistory::class)->name('student.leave-requests.history');
    Route::get('/student/warnings', \App\Livewire\Student\Warnings::class)->name('student.warnings');
    Route::get('/student/join-class', \App\Livewire\Student\JoinClass::class)->name('student.classes.join');
    Route::get('/student/classes/{courseClass}', \App\Livewire\Student\ClassShow::class)->name('student.classes.show');
    Route::get('/lecturer/classes/{courseClass}/settings', \App\Livewire\Lecturer\ClassSettings::class)->name('lecturer.classes.settings');
    Route::get('/lecturer/classes/{courseClass}', \App\Livewire\Lecturer\ClassShow::class)->name('lecturer.classes.show');
    Route::get('/lecturer/classes/{class_id}/statistics', \App\Livewire\Lecturer\ClassStatistics::class)->name('lecturer.class.statistics');
    Route::get('/lecturer/attendance', AttendanceIndex::class)->name('lecturer.attendance.index');
    Route::get('/lecturer/attendance/create', AttendanceCreate::class)->name('lecturer.attendance.create');
    Route::get('/lecturer/attendance/manual/create', ManualAttendanceCreate::class)->name('lecturer.attendance.manual.create');
    Route::get('/lecturer/attendance/manual/{session}', ManualAttendanceSession::class)
        ->whereNumber('session')
        ->name('lecturer.attendance.manual.session');
    Route::get('/lecturer/attendance/qr/create', QrAttendanceCreate::class)->name('lecturer.attendance.qr.create');
    Route::get('/lecturer/attendance/qr/{session}', QrAttendanceSession::class)
        ->whereNumber('session')
        ->name('lecturer.attendance.qr.session');
    Route::get('/lecturer/students', StudentIndex::class)->name('lecturer.students.index');
    Route::get('/lecturer/students/leave', LeaveRequestIndex::class)
        ->defaults('status', 'pending')
        ->name('lecturer.leave-requests.index');
    Route::get('/lecturer/students/leave/approved', LeaveRequestIndex::class)
        ->defaults('status', 'approved')
        ->name('lecturer.leave-requests.approved');
    Route::get('/lecturer/students/leave/rejected', LeaveRequestIndex::class)
        ->defaults('status', 'rejected')
        ->name('lecturer.leave-requests.rejected');
    Route::get('/lecturer/students/leave/{leaveRequest}', LeaveRequestShow::class)
        ->whereNumber('leaveRequest')
        ->name('lecturer.leave-requests.show');
    Route::get('/lecturer/students/{member}', StudentShow::class)
        ->whereNumber('member')
        ->name('lecturer.students.show');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', \App\Livewire\Profile\EditProfile::class)->name('profile.edit');
});

require __DIR__.'/auth.php';
