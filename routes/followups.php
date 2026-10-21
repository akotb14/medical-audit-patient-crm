<?php

use App\Http\Controllers\FollowUpsController;
use Illuminate\Support\Facades\Route;



Route::get('/follow_ups',[FollowUpsController::class,'index'])->name('follow_ups.index');

Route::get('/follow_ups/{followup}',[FollowUpsController::class,'show'])->name('follow_ups.show');
Route::get('/follow_ups/{followup}/edit',[FollowUpsController::class,'edit'])->name('follow_ups.edit');
Route::put('/follow_ups/{followup}',[FollowUpsController::class,'update'])->name('follow_ups.update');
Route::patch('/follow_ups/{followup}',[FollowUpsController::class,'complete'])->name('follow_ups.complete');
Route::patch('/follow_ups/{followup}/status',[FollowUpsController::class,'status'])->name('follow_ups.status');
Route::delete('/follow_ups/{followup}',[FollowUpsController::class,'destroy'])->name('follow_ups.destroy');
