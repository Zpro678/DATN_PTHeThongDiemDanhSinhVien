<?php

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
use App\Livewire\User\Classes as UserClasses;
use App\Livewire\User\CreateClass;
use App\Livewire\User\Dashboard as UserDashboard;
use App\Livewire\User\JoinedClasses;
use App\Livewire\User\ManagedClasses;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', UserDashboard::class)->name('dashboard');
    Route::get('/classes', UserClasses::class)->name('classes');
    Route::get('/managed-classes', ManagedClasses::class)->name('managed-classes');
    Route::get('/joined-classes', JoinedClasses::class)->name('joined-classes');
    Route::get('/create-class', CreateClass::class)->name('create-class');
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

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
