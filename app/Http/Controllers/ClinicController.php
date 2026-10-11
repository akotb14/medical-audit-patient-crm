<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClinicRequests\CreateClinicRequest;
use App\Http\Requests\ClinicRequests\UpdateClinicRequest;
use App\Models\Clinic;

class ClinicController extends Controller
{
    //
    public function index()
    {
        $clinics = Clinic::cursorPaginate(10);
        return view('clinics.index', compact('clinics'));
    }
    public function show(Clinic $clinic)
    {
        return view('clinics.show', compact('clinic'));
    }
    public function create()
    {
        return view('clinics.create');
    }
    public function store(CreateClinicRequest $request)
    {
        Clinic::create($request->validated());
        return redirect()->route('clinics.index')->with('success', 'تم اضافة العيادة بنجاح');
    }
    public function edit(Clinic $clinic)
    {
        return view('clinics.edit', compact('clinic'));
    }
    public function update(UpdateClinicRequest $request, Clinic $clinic)
    {
        $clinic->update($request->validated());
        return redirect()->route('clinics.index')->with('success', 'تم تحديث العيادة بنجاح');
    }
    public function destroy(Clinic $clinic)
    {
        $clinic->delete();
        return redirect()->route('clinics.index')->with('success', 'تم حذف العيادة بنجاح');
    }
}