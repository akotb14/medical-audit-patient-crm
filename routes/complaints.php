<?php

use App\Http\Controllers\ComplaintController;
use Illuminate\Support\Facades\Route;

Route::get('complaints', [ComplaintController::class, 'index'])->name('complaints.index');
Route::get('complaints/create', [ComplaintController::class, 'create'])->name('complaints.create');
Route::post('complaints', [ComplaintController::class, 'store'])->name('complaints.store');
Route::get('complaints/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');
Route::get('complaints/{complaint}/edit', [ComplaintController::class, 'edit'])->name('complaints.edit');
Route::put('complaints/{complaint}', [ComplaintController::class, 'update'])->name('complaints.update');
Route::patch('complaints/{complaint}/advance', [ComplaintController::class, 'advance'])->name('complaints.advance');
Route::patch('complaints/{complaint}/assign', [ComplaintController::class, 'assign'])->name('complaints.assign');
Route::patch('/complaints/{complaint}/reopen',[ComplaintController::class, 'reopen'])->name('complaints.reopen');
Route::delete('complaints/{complaint}', [ComplaintController::class, 'destroy'])->name('complaints.destroy');
