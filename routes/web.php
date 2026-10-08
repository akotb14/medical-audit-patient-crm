<?php

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;

use function Termwind\render;

Route::get('/', function () {
    $p = true;
    return view('welcome');
});
