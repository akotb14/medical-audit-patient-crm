<?php

use App\Enums\RoleEnum;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('auth.login');
    }

    return auth()->user()->hasRole(RoleEnum::ADMIN->value)
        ? redirect()->route('dashboard.index')
        : redirect()->route('clinics.index');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index')->middleware(['role:Admin']);

    require __DIR__.'/users.php';
    require __DIR__.'/patients.php';
    require __DIR__.'/clinics.php';
    require __DIR__.'/doctors.php';
    require __DIR__.'/visits.php';
    require __DIR__.'/followups.php';
    require __DIR__.'/surveys.php';
    require __DIR__.'/complaints.php';
});
