<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::get('/complaints', function () {
        return view('complaints.index');
    })->name('complaints.index');
    require __DIR__.'/users.php';
    require __DIR__.'/patients.php';
    require __DIR__.'/clinics.php';
    require __DIR__.'/doctors.php';
    require __DIR__.'/visits.php';
    require __DIR__.'/followups.php';
    require __DIR__.'/surveys.php';
    require __DIR__.'/complaints.php';
});
