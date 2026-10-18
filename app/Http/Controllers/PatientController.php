<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientRequests\CreatePatientRequest;
use App\Http\Requests\PatientRequests\PatientIndexRequest;
use App\Http\Requests\PatientRequests\UpdatePatientRequest;
use App\Models\Patient;
use App\Queries\PatientIndexQuery;
use Illuminate\Support\Str;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(PatientIndexRequest $request ,PatientIndexQuery $patientIndexQuery )
    {
        //

        $patients = $patientIndexQuery->build($request->validated())->where('status', 'active')
                ->paginate(20)->withQueryString();
        return view('patients.index', compact('patients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('patients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreatePatientRequest $request)
    {
        $data = $request->validated();

        do {
            $mrn = 'MRN-' . strtoupper(Str::random(8));
        } while (Patient::where('mrn', $mrn)->exists());

        $data['mrn'] = $mrn;

        Patient::create($data);
        return redirect()->route('patients.index')->with('success', 'تم اضافة المريض بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient)
    {

        return view('patients.show', compact('patient'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patient $patient)
    {
        //
        return view('patients.edit', compact('patient'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        //
        Patient::where('id', $patient->id)->update($request->validated());
        return redirect()->route('patients.index')->with('success', 'تم تحديث المريض بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        //
        Patient::where('id', $patient->id)->update(['status' => 'inactive']);
        return redirect()->back()->with('success', 'تم حذف المريض بنجاح');
    }
}
