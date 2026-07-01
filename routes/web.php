<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Livewire\Admin\Users\UserIndex;
use App\Livewire\Lecturer\Attendance\AttendanceCreate;
use App\Livewire\Lecturer\Attendance\AttendanceIndex;
use App\Livewire\Lecturer\Attendance\ManualAttendanceCreate;
use App\Livewire\Lecturer\Attendance\ManualAttendanceSession;
use App\Livewire\Lecturer\Attendance\QrAttendanceCreate;
use App\Livewire\Lecturer\Attendance\QrAttendanceSession;
use App\Livewire\Lecturer\ClassSettings;
use App\Livewire\Lecturer\ClassStatistics;
use App\Livewire\Lecturer\Students\LeaveRequestIndex;
use App\Livewire\Lecturer\Students\LeaveRequestShow;
use App\Livewire\Lecturer\Students\StudentIndex;
use App\Livewire\Lecturer\Students\StudentShow;
use App\Livewire\Profile\EditProfile;
use App\Livewire\Student\AttendanceHistory as StudentAttendanceHistory;
use App\Livewire\Student\AttendanceStats as StudentAttendanceStats;
use App\Livewire\Student\ClassShow;
use App\Livewire\Student\JoinClass;
use App\Livewire\Student\LeaveRequestCreate;
use App\Livewire\Student\LeaveRequestEdit;
use App\Livewire\Student\LeaveRequestHistory;
use App\Livewire\Student\LeaveRequestShow as StudentLeaveRequestShow;
use App\Livewire\Student\Warnings;
use App\Livewire\User\Classes as UserClasses;
use App\Livewire\User\CreateClass;
use App\Livewire\User\Dashboard as UserDashboard;
use App\Livewire\User\JoinedClasses;
use App\Livewire\User\ManagedClasses;
use App\Livewire\User\Upgrade;
use App\Models\AuditLog;
use App\Models\CourseClass;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Google OAuth Routes
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.login');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

Route::get('/attendance/check-in/{token}', \App\Livewire\Student\AttendanceCheckIn::class)
    ->name('attendance.check-in.guest');

Route::post('/gps/token', [\App\Http\Controllers\GpsVerificationController::class, 'issueToken'])
    ->name('gps.token')
    ->middleware('throttle:10,1');

Route::post('/gps/verify', [\App\Http\Controllers\GpsVerificationController::class, 'verify'])
    ->name('gps.verify')
    ->middleware('throttle:5,1');

// MoMo gọi server-to-server: KHÔNG qua auth, được loại CSRF (xem bootstrap/app.php).
Route::post('/payment/momo/ipn', [\App\Http\Controllers\MomoController::class, 'ipn'])
    ->name('momo.ipn');

Route::middleware(['auth', 'verified', 'user.route'])->group(function () {
    $ensureAdmin = function (): void {
        abort_unless(auth()->user()?->isAdmin(), 403);
    };

    Route::prefix('admin/{ma_user}')->name('admin.')->group(function () use ($ensureAdmin) {
        Route::get('/', [\App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');
        Route::redirect('/dashboard', '/admin')->name('dashboard.alias');

        Route::get('/users', UserIndex::class)->name('users.index');
        Route::redirect('/accounts', '/admin/users')->name('accounts.index');

        Route::get('/users/create', \App\Livewire\Admin\Users\UserCreate::class)->name('users.create');
        Route::get('/users/{user}', \App\Livewire\Admin\Users\UserShow::class)->whereNumber('user')->name('users.show');
        Route::get('/users/{user}/edit', \App\Livewire\Admin\Users\UserEdit::class)->whereNumber('user')->name('users.edit');

        Route::get('/packages', \App\Livewire\Admin\Packages\PackageIndex::class)->name('packages.index');
        Route::get('/packages/create', \App\Livewire\Admin\Packages\PackageCreate::class)->name('packages.create');
        Route::get('/packages/{package}', \App\Livewire\Admin\Packages\PackageShow::class)->whereNumber('package')->name('packages.show');
        Route::get('/packages/{package}/edit', \App\Livewire\Admin\Packages\PackageEdit::class)->whereNumber('package')->name('packages.edit');


        Route::get('/reports', [\App\Http\Controllers\Admin\AdminController::class, 'reportsIndex'])->name('reports.index');

        Route::get('/logs', \App\Livewire\Admin\Logs\LogIndex::class)->name('logs.index');

        Route::get('/settings', [\App\Http\Controllers\Admin\AdminController::class, 'settingsIndex'])->name('settings.index');
    });

    Route::prefix('user/{ma_user}')->group(function () {
        Route::get('/dashboard', UserDashboard::class)->name('dashboard');
        Route::get('/classes', UserClasses::class)->name('classes');
        Route::get('/managed-classes', ManagedClasses::class)->name('managed-classes');
        Route::get('/joined-classes', JoinedClasses::class)->name('joined-classes');
        Route::get('/managed-classes/create-class', CreateClass::class)->name('create-class');
        Route::get('/student/attendance/history', StudentAttendanceHistory::class)->name('student.attendance.history');
        Route::get('/student/attendance/stats', StudentAttendanceStats::class)->name('student.attendance.stats');
        Route::get('/student/leave-requests/create', LeaveRequestCreate::class)->name('student.leave-requests.create');
        Route::get('/student/leave-requests/history', LeaveRequestHistory::class)->name('student.leave-requests.history');
        Route::get('/student/leave-requests/{leaveRequest}', StudentLeaveRequestShow::class)
            ->whereNumber('leaveRequest')
            ->name('student.leave-requests.show');
        Route::get('/student/leave-requests/{leaveRequest}/edit', LeaveRequestEdit::class)
            ->whereNumber('leaveRequest')
            ->name('student.leave-requests.edit');
        Route::get('/student/warnings', Warnings::class)->name('student.warnings');
        Route::get('/upgrade', Upgrade::class)->name('upgrade');

        Route::get('/student/classes/{courseClass}', ClassShow::class)->name('student.classes.show');
        Route::get('/lecturer/classes/{courseClass}', App\Livewire\Lecturer\ClassShow::class)->name('lecturer.classes.show');
        Route::get('/lecturer/classes/{courseClass}/settings', ClassSettings::class)->name('lecturer.classes.settings');
        Route::get('/lecturer/classes/{courseClass}/attendance', \App\Livewire\Lecturer\ClassAttendanceHistory::class)->name('lecturer.classes.attendance');
        Route::get('/lecturer/classes/{class_id}/statistics', ClassStatistics::class)->name('lecturer.class.statistics');
        Route::get('/lecturer/attendance', AttendanceIndex::class)->name('lecturer.attendance.index');
        Route::get('/lecturer/attendance/meeting/{meeting}', \App\Livewire\Lecturer\Attendance\MeetingSessions::class)
            ->whereNumber('meeting')
            ->name('lecturer.attendance.meeting.sessions');
        Route::get('/lecturer/attendance/meeting/{meeting}/summary', \App\Livewire\Lecturer\Attendance\MeetingSummary::class)
            ->whereNumber('meeting')
            ->name('lecturer.attendance.meeting.summary');
        Route::get('/lecturer/attendance/create', AttendanceCreate::class)->name('lecturer.attendance.create');
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
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/support', \App\Livewire\User\SupportPage::class)->name('support');

    // Đánh dấu tất cả thông báo của người dùng hiện tại là đã đọc.
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'readAll'])
        ->name('notifications.read-all');

    // MoMo redirect trình duyệt người dùng về đây sau khi thanh toán (chỉ hiển thị kết quả).
    Route::get('/payment/momo/return', [\App\Http\Controllers\MomoController::class, 'return'])
        ->name('momo.return');
});

require __DIR__.'/auth.php';
