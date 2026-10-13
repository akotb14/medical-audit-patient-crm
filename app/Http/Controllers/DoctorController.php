<?php

namespace App\Http\Controllers;

use App\Http\Requests\DoctorRequests\CreateDoctorRequest;
use App\Http\Requests\DoctorRequests\DoctorIndexRequest;
use App\Http\Requests\DoctorRequests\UpdateDoctorRequest;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Queries\DoctorIndexQuery;
use Illuminate\Support\Facades\Cache;

class DoctorController extends Controller
{
    //
    public function index(DoctorIndexRequest $request,
    DoctorIndexQuery $doctorIndexQuery)
    {
        $doctors = $doctorIndexQuery
        ->build($request->validated())
        ->cursorPaginate(12)
        ->withQueryString();
        
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

    public function store(CreateDoctorRequest $request)
    {
        Doctor::create($request->validated());
        Cache::forget('doctors_counts');
        Cache::forget('doctors_counts_by_clinic');
        return redirect()->route('doctors.index')->with('success', 'تم اضافة الطبيب بنجاح');
    }
    public function edit(Doctor $doctor)
    {

        return view('doctors.edit', compact('doctor'));
    }
    public function update(UpdateDoctorRequest $request,  Doctor $doctor)
    {
        $doctor->update($request->validated());
        Cache::forget('doctors_counts');
        return redirect()->route('doctors.index')->with('success', 'تم تعديل الطبيب بنجاح');
    }
    public function destroy(Doctor $doctor)
    {
        $doctor->delete();
        Cache::forget('doctors_counts');
        Cache::forget('doctors_counts_by_clinic');
        return redirect()->route('doctors.index')->with('success', 'تم حذف الطبيب بنجاح');
    }
}
