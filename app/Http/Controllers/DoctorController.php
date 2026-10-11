<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    //
    public function index()
    {
        $doctors = Doctor::cursorPaginate(10);
        return view('doctors.index', compact('doctors'));
    }
    public function show(Doctor $doctor)
    {
        return view('doctors.show', compact('doctor'));
    }
    public function create()
    {
        return view('doctors.create');
    }
    public function store(Request $request){
        
    }
}