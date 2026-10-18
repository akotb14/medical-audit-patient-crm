<?php

use App\Http\Controllers\VisitController;
use Illuminate\Support\Facades\Route;

Route::get('/visits', [VisitController::class, 'index'])->name('visits.index');

Route::get('/visits/create', [VisitController::class, 'create'])->name('visits.create');

Route::post('/visits', [VisitController::class, 'store'])->name('visits.store');

Route::get('/visits/{visit}', [VisitController::class, 'show'])->name('visits.show');

Route::get('/visits/{visit}/edit', [VisitController::class, 'edit'])->name('visits.edit');
Route::put('/visits/{visit}', [VisitController::class, 'update'])->name('visits.update');
Route::patch('/visits/{visit}/status', [VisitController::class, 'updateStatus'])->name('visits.status');


Route::delete('/visits/{visit}', [VisitController::class, 'destroy'])->name('visits.destroy');
