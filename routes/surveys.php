<?php

use App\Http\Controllers\SurveyController;
use Illuminate\Support\Facades\Route;


Route::get('/surveys', [SurveyController::class, 'index'])->name('surveys.index');
// Route::get('/surveys/create', 'SurveyController@create')->name('surveys.create');
Route::post('/surveys', [SurveyController::class, 'store'])->name('surveys.store');
Route::get('/surveys/{survey}', [SurveyController::class, 'show'])->name('surveys.show');
Route::delete('/surveys/{survey}', [SurveyController::class, 'destroy'])->name('surveys.destroy');
