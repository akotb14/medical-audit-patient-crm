<?php

namespace App\Http\Controllers;

use App\Http\Requests\VistRequests\CreateVisitRequest;
use App\Http\Requests\VistRequests\UpdateVisitRequest;
use App\Http\Requests\VistRequests\UpdateVisitStatusRequest;
use App\Http\Requests\VistRequests\VisitIndexRequest;
use App\Models\Visit;
use App\Services\DoctorService;
use App\Services\PatientService;
use App\Services\VisitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class VisitController extends Controller
{
    public function __construct(
        private VisitService $visitService,
        private PatientService $patientService,
        private DoctorService $doctorService
    ) {}

    public function index(VisitIndexRequest $request)
    {
        Gate::authorize('viewAny', Visit::class);

        return view('visits.index', $this->visitService->paginateIndex($request->validated()));
    }

    public function show(Visit $visit)
    {
        Gate::authorize('view', $visit);
        $visit->load(['patient', 'doctor.clinic', 'creator']);

        return view('visits.show', compact('visit'));
    }

    public function create(Request $request)
    {
        Gate::authorize('create', Visit::class);
        $patientId = $request->query('patient_id');
        $patients = is_numeric($patientId)
            ? $this->patientService->getPatientOption((int) $patientId)
            : $this->patientService->getPatientOptions();
        $doctors = $this->doctorService->getDoctorOptions(
            is_numeric($request->old('doctor_id')) ? (int) $request->old('doctor_id') : null
        );

        return view('visits.create', compact('patients', 'doctors'));
    }

    public function store(CreateVisitRequest $request)
    {
        Gate::authorize('create', Visit::class);

        $this->visitService->create($request->validated(), Auth::id());

        return redirect()->route('visits.index')->with('success', 'تم انشاء زيارة جديد');
    }

    public function edit(Visit $visit)
    {
        Gate::authorize('update', $visit);
        $patients = $this->patientService->getPatientOptions($visit->patient_id);
        $doctors = $this->doctorService->getDoctorOptions($visit->doctor_id);

        return view('visits.edit', compact('visit', 'patients', 'doctors'));
    }

    public function update(Visit $visit, UpdateVisitRequest $request)
    {
        Gate::authorize('update', $visit);

        $this->visitService->update($visit, $request->validated(), Auth::id());

        return redirect()->route('visits.index')->with('success', 'تم تحديث بيانات الزيارة');
    }

    public function updateStatus(Visit $visit, UpdateVisitStatusRequest $request)
    {
        Gate::authorize('updateStatus', $visit);

        $action = $request->validated('action');

        $this->visitService->changeStatus($visit, $request->validated(), Auth::id());

        return match ($action) {
            'cancel' => back()->with(
                'success',
                'تم إلغاء الزيارة بنجاح'
            ),

            'complete_only' => back()
                ->with('success', 'تم إتمام الزيارة بنجاح')
                ->with('open_survey_modal', $visit->id),

            'complete_with_followup' => back()
                ->with('success', 'تم إتمام الزيارة وجدولة المتابعة بنجاح')
                ->with('open_survey_modal', $visit->id),
        };
    }

    public function destroy(Visit $visit)
    {
        Gate::authorize('delete', $visit);

        if (! $this->visitService->delete($visit)) {
            return redirect()
                ->route('visits.index')
                ->with('error', 'لا يمكن حذف زيارة مكتملة.');
        }

        return redirect()->route('visits.index')->with('success', 'تم حذف الزيارة بنجاح');
    }
}
