<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;

use function Termwind\render;

Route::get('/dashboard', function () {
    return view('dashboard.index');
})->name('dashboard.index');
Route::get('/appointments', function () {
    return view('dashboard.index');
})->name('appointments.index');
Route::get('/followups', function () {
    return view('followups.index');
})->name('followups.index');
Route::get('/complaints', function () {
    return view('complaints.index');
})->name('complaints.index');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

require __DIR__ . '/users.php';
require __DIR__ . '/patients.php';
require __DIR__ . '/clinics.php';
require __DIR__ . '/doctors.php';