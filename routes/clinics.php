<?php

use App\Http\Controllers\ClinicController;
use Illuminate\Support\Facades\Route;
Route::get('clinics',[ClinicController::class,'index'])->name('clinics.index'); 
Route::get('clinics/create',[ClinicController::class,'create'])->name('clinics.create');
Route::post('clinics',[ClinicController::class,'store'])->name('clinics.store');
Route::get('clinics/{clinic}',[ClinicController::class,'show'])->name('clinics.show');
Route::get('clinics/{clinic}/edit',[ClinicController::class,'edit'])->name('clinics.edit');
Route::put('clinics/{clinic}',[ClinicController::class,'update'])->name('clinics.update');
Route::delete('clinics/{clinic}',[ClinicController::class,'destroy'])->name('clinics.destroy');