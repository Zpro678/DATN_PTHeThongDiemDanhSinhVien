<?php

use App\Http\Controllers\ProfileController;
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
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
