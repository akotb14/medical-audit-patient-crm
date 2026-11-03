<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientRequests\CreatePatientRequest;
use App\Http\Requests\PatientRequests\PatientIndexRequest;
use App\Http\Requests\PatientRequests\UpdatePatientRequest;
use App\Models\Patient;
use App\Services\PatientService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PatientController extends Controller
{
    public function __construct(private PatientService $patientService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(PatientIndexRequest $request)
    {
        Gate::authorize('viewAny', Patient::class);

        return view('patients.index', $this->patientService->paginateIndex($request->validated()));
    }

    /**
     * Display the archived (soft deleted) patient files.
     */
    public function archived()
    {
        Gate::authorize('restoreAny', Patient::class);

        return view('patients.archived', [
            'patients' => $this->patientService->paginateArchived(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', Patient::class);

        return view('patients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreatePatientRequest $request)
    {
        Gate::authorize('create', Patient::class);

        $this->patientService->register($request->validated(), Auth::id());

        return redirect()->route('patients.index')->with('success', 'تم اضافة المريض بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient)
    {
        Gate::authorize('view', $patient);

        return view('patients.show', [
            'patient' => $patient,
            ...$this->patientService->paginateVisitHistory($patient),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patient $patient)
    {
        Gate::authorize('update', $patient);

        return view('patients.edit', compact('patient'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        Gate::authorize('update', $patient);

        $patient->update([
            ...$request->validated(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('patients.index')->with('success', 'تم تحديث المريض بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        Gate::authorize('delete', $patient);

        $this->patientService->archive($patient, Auth::id());

        return redirect()->back()->with('success', 'تم أرشفة المريض بنجاح');
    }

    /**
     * Restore an archived patient file.
     */
    public function restore(Patient $patient)
    {
        Gate::authorize('restore', $patient);

        $this->patientService->restore($patient);

        return redirect()->route('patients.archived')->with('success', 'تم استعادة المريض بنجاح');
    }
}
