<?php

use App\Http\Controllers\Lecture\AttendanceSessionController;
use App\Http\Controllers\Lecture\DashboardLectureController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\ClassMemberController;
use App\Http\Controllers\ImportStudentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/preview/{variant}', function (string $variant) {
    return view('preview.layout', ['variant' => $variant]);
})->where('variant', 'admin|lecturer|student')->name('preview.layout');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ============================================================
// TV2 — Lớp học, Sinh viên, Import
// Nguyễn Tuấn Khanh | feature/khanh-class-student
// ============================================================
Route::middleware(['auth'])->group(function () {

    // ---- Lớp học (CRUD) ----
    Route::resource('classes', ClassController::class);

    // Lưu trữ lớp (chuyển sang archived)
    Route::patch('classes/{class}/archive', [ClassController::class, 'archive'])
        ->name('classes.archive');

    // Tạo lại mã lớp mới
    Route::patch('classes/{class}/regenerate-code', [ClassController::class, 'regenerateCode'])
        ->name('classes.regenerate-code');

    // ---- Sinh viên trong lớp ----
    Route::resource('classes.members', ClassMemberController::class);

    // ---- Import sinh viên từ Excel/CSV ----
    Route::get('classes/{class}/import', [ImportStudentController::class, 'form'])
        ->name('classes.import.form');

    Route::post('classes/{class}/import', [ImportStudentController::class, 'store'])
        ->name('classes.import.store');

    // Tải file mẫu import
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





require __DIR__ . '/auth.php';
require __DIR__.'/auth.php';

