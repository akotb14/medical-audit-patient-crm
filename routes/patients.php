<?php

use Illuminate\Support\Facades\Route;

Route::get('/patients', function () {
    return view('patients.index');
})->name('patients.index');

Route::get('/patients/create', function () {
    return view('patients.create');
})->name('patients.create');

Route::post('/patients', function () {})->name('patients.store');

Route::get('/patients/{patient}', function ($patient) {
    return view('patients.show', compact('patient'));
})->name('patients.show');

Route::get('/patients/{patient}/edit', function ($patient) {
    return view('patients.edit', compact('patient'));
})->name('patients.edit');
Route::put('/patients/{patient}', function ($patient) {
    // update patient
})->name('patients.update');

Route::get('/patients/{patient}/delete', function ($patient) {
    return view('patients.delete', compact('patient'));
})->name('patients.delete');