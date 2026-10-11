<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClinicController extends Controller
{
    //
    public function index(){
        return view('clinic.index');
    }
    public function create(){
        return view('clinic.create');
    }
    public
}