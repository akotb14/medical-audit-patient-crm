<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClinicRequests\CreateClinicRequest;
use App\Http\Requests\ClinicRequests\UpdateClinicRequest;
use App\Models\Clinic;
use Illuminate\Support\Facades\Cache;

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
        $doctors = $clinic->doctors()
            ->orderBy('id')
            ->cursorPaginate(10);       
        return view('clinics.show', compact('clinic' ,'doctors'));
    }
    public function create()
    {
        return view('clinics.create');
    }
    public function store(CreateClinicRequest $request)
    {
        Clinic::create($request->validated());
        Cache::forget('clinics.all');
        Cache::forget('doctors_counts');
        return redirect()->route('clinics.index')->with('success', 'تم اضافة العيادة بنجاح');
    }
    public function edit(Clinic $clinic)
    {
        return view('clinics.edit', compact('clinic'));
    }
    public function update(UpdateClinicRequest $request, Clinic $clinic)
    {
        $clinic->update($request->validated());
        Cache::forget('clinics.all');
        Cache::forget('doctors_counts');

        return redirect()->route('clinics.index')->with('success', 'تم تحديث العيادة بنجاح');
    }
    public function destroy(Clinic $clinic)
    {
        $clinic->delete();
        Cache::forget('clinics.all');
        Cache::forget('doctors_counts');

        return redirect()->route('clinics.index')->with('success', 'تم حذف العيادة بنجاح');
    }
}