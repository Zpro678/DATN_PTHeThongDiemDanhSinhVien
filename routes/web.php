<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\ClassMemberController;
use App\Http\Controllers\ImportStudentController;
use App\Http\Controllers\Lecture\AttendanceSessionController;
use App\Http\Controllers\Lecture\DashboardLectureController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;

use App\Http\Controllers\StudentClassController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::view('/', 'welcome')->name('home');

Route::prefix('preview')->name('preview.')->group(function () {
    Route::get('/{variant}', function (string $variant) {
        return view('preview.layout', ['variant' => $variant]);
    })->where('variant', 'admin|lecturer|student')->name('layout');

});

/*
|--------------------------------------------------------------------------
| Authenticated common routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('teacher')) {
            return redirect()->route('lecturer.dashboard');
        }

        if ($user->hasRole('student')) {
            return redirect()->route('student.dashboard');
        }

        return view('dashboard');
    })->middleware('verified')->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::redirect('/dashboard', '/admin')->name('dashboard.alias');
    });

/*
|--------------------------------------------------------------------------
| Lecturer routes
|--------------------------------------------------------------------------
|
| The database role for lecturers is still named "teacher".
|
*/

Route::middleware(['auth', 'verified', 'role:teacher'])
    ->prefix('lecturer')
    ->group(function () {
        Route::redirect('/courses', '/lecturer/classes')->name('lecturer.courses');
        Route::get('/attendance', [AttendanceSessionController::class, 'index'])->name('lecturer.attendance');
        Route::view('/analytics', 'lecture.analytics.main')->name('lecturer.analytics');

        Route::resource('classes', ClassController::class);

        Route::patch('classes/{class}/archive', [ClassController::class, 'archive'])
            ->name('classes.archive');

        Route::patch('classes/{class}/regenerate-code', [ClassController::class, 'regenerateCode'])
            ->name('classes.regenerate-code');

        Route::resource('classes.members', ClassMemberController::class);

        Route::get('classes/{class}/import', [ImportStudentController::class, 'form'])
            ->name('classes.import.form');

        Route::post('classes/{class}/import', [ImportStudentController::class, 'store'])
            ->name('classes.import.store');

        Route::get('import/template', [ImportStudentController::class, 'downloadTemplate'])
            ->name('import.template');
    });



// =============================================================================================== //

// lecturer
Route::get('/lecturer/dashboard', [DashboardLectureController::class, 'index'])->name('dashboard');

Route::get('/lecturer/attendance', [AttendanceSessionController::class, 'index'])->name('attendance');

Route::get('/lecturer/courses', function () {
    return view('lecture.class.index');
});

Route::get('/lecturer/analytics', function () {
    return view('lecture.analytics.main');
});
Route::get('/lecturer/students', function () {
    return view('lecture.students.index');
});


Route::get('/lecturer/students/warning', function () {
    return view('lecture.students.warning');
});

Route::get('/lecturer/students/archived', function () {
    return view('lecture.students.archived');
});

// Leave Request Routes
Route::get('/lecturer/students/leave', function () {
    return view('lecture.students.leaveRequest.leaveRequest');
});

Route::get('/lecturer/students/leave/reject', function () {
    return view('lecture.students.leaveRequest.leaveReject');
});

Route::get('/lecturer/students/leave/approve', function () {
    return view('lecture.students.leaveRequest.leaveApprove');
});

Route::get('/lecturer/students/leave/{id}', function ($id) {
    return view('lecture.students.leaveRequest.leaveDetail', ['id' => $id]);
})->where('id', '[0-9]+');

require __DIR__ . '/auth.php';

// ============================================================
// TV2 — Giao diện Sinh viên (Student Portal)
// Nguyễn Tuấn Khanh | feature/khanh-class-student
// Đường dẫn gốc: /student/*
// ============================================================
Route::middleware(['auth'])->prefix('student')->name('student.')->group(function () {

    // --- Dashboard tổng quan ---
    Route::get('/', [StudentClassController::class, 'dashboard'])
        ->name('dashboard');                          // /student

    // --- Lớp học ---
    Route::get('/classes', [StudentClassController::class, 'index'])
        ->name('classes.list');                       // /student/classes

    Route::get('/classes/{class}', [StudentClassController::class, 'show'])
        ->name('classes.detail');                     // /student/classes/{id}

    // --- Lịch sử điểm danh ---
    Route::get('/history', function () {
        return view('student.attendance.studentAttendanceHistory');
    })->name('history');                              // /student/history

    // --- Thống kê chuyên cần ---
    Route::get('/stats', function () {
        return view('student.attendance.studentAttendanceStat');
    })->name('stats');                                // /student/stats

    // --- Thông báo ---
    Route::get('/notifications', function () {
        return view('student.notification.studentNotificationList');
    })->name('notifications');                        // /student/notifications

    // --- Hồ sơ cá nhân ---
    Route::get('/profile', function () {
        return view('student.profile.studentProfile');
    })->name('profile');                              // /student/profile
});



