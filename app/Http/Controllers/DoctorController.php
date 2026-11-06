<?php

namespace App\Http\Controllers;

use App\Http\Requests\DoctorRequests\CreateDoctorRequest;
use App\Http\Requests\DoctorRequests\DoctorIndexRequest;
use App\Http\Requests\DoctorRequests\UpdateDoctorRequest;
use App\Models\Doctor;
use App\Services\ClinicService;
use App\Services\DoctorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class DoctorController extends Controller
{
    public function __construct(private ClinicService $clinicService, private DoctorService $doctorService) {}

    public function index(DoctorIndexRequest $request)
    {
        Gate::authorize('viewAny', Doctor::class);

        return view('doctors.index', [
            'doctors' => $this->doctorService->paginateIndex($request->validated()),
            'clinics' => $this->clinicService->getClinics(),
            'doctors_counts' => $this->doctorService->getDoctorsCounts(),
        ]);
    }

    public function show(Doctor $doctor)
    {
        Gate::authorize('view', $doctor);
        $visits = $doctor->visits()
            ->with([
                'patient:id,fullname,mrn,deleted_at',
                'doctor.clinic',
                'creator',
                'survey',
            ])
            ->latest()
            ->paginate(20);

        return view('doctors.show', compact('doctor', 'visits'));
    }

    public function create()
    {
        Gate::authorize('create', Doctor::class);

        return view('doctors.create', [
            'clinics' => $this->clinicService->getClinics(),
        ]);
    }

    public function store(CreateDoctorRequest $request)
    {
        Gate::authorize('create', Doctor::class);

        $this->doctorService->create($request->validated(), Auth::id());

        return redirect()->route('doctors.index')->with('success', 'تم اضافة الطبيب بنجاح');
    }

    public function edit(Doctor $doctor)
    {
        Gate::authorize('update', $doctor);

        return view('doctors.edit', [
            'doctor' => $doctor,
            'clinics' => $this->clinicService->getClinics(),
        ]);
    }

    public function update(UpdateDoctorRequest $request, Doctor $doctor)
    {
        Gate::authorize('update', $doctor);

        $this->doctorService->update($doctor, $request->validated(), Auth::id());

        return redirect()->route('doctors.index')->with('success', 'تم تعديل الطبيب بنجاح');
    }

    public function destroy(Doctor $doctor)
    {
        Gate::authorize('delete', $doctor);

        $this->doctorService->delete($doctor);

        return redirect()->route('doctors.index')->with('success', 'تم حذف الطبيب بنجاح');
    }
}
