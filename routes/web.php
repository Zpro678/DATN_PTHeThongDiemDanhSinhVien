<?php

use Illuminate\Support\Facades\Route;

// ─── Standalone pages (no sidebar layout) ─────────────────────────────────
Route::view('/presentation', 'pages.presentation')->name('presentation');
Route::view('/student-check-in', 'pages.student-checkin')->name('student-checkin');

// ─── Admin pages (with sidebar layout) ────────────────────────────────────
Route::view('/', 'pages.dashboard')->name('dashboard');
Route::view('/history', 'pages.history')->name('history');
Route::view('/requests', 'pages.requests')->name('requests');
Route::view('/stats', 'pages.stats')->name('stats');
Route::view('/settings', 'pages.settings')->name('settings');
Route::post('/settings', function () { return back(); })->name('settings.update');
Route::view('/upgrade', 'pages.upgrade')->name('upgrade');
Route::view('/start-session', 'pages.start-session')->name('sessions.create');

// ─── Logout placeholder ───────────────────────────────────────────────────
Route::post('/logout', function () {
    return redirect('/');
})->name('logout');
