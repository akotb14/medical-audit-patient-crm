<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClinicRequests\CreateClinicRequest;
use App\Http\Requests\ClinicRequests\UpdateClinicRequest;
use App\Models\Clinic;
use App\Services\DoctorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class ClinicController extends Controller
{
    public function __construct(private DoctorService $doctor_service) {}

    //
    public function index()
    {
        Gate::authorize('viewAny', Clinic::class);
        $doctors_counts = $this->doctor_service->getDoctorsCounts();
        $doctors_counts_by_clinic = $this->doctor_service->getDoctorsCountsByClinic();

        $clinics = Clinic::all();

        return view('clinics.index', compact('clinics', 'doctors_counts', 'doctors_counts_by_clinic'));
    }

    public function show(Clinic $clinic)
    {
        Gate::authorize('view', $clinic);
        $doctors = $clinic->doctors()
            ->orderBy('id')
            ->paginate(20);

        return view('clinics.show', compact('clinic', 'doctors'));
    }

    public function create()
    {
        Gate::authorize('create', Clinic::class);

        return view('clinics.create');
    }

    public function store(CreateClinicRequest $request)
    {
        Gate::authorize('create', Clinic::class);
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        Clinic::create($data);
        Cache::forget('clinics.all');

        return redirect()->route('clinics.index')->with('success', 'تم اضافة العيادة بنجاح');
    }

    public function edit(Clinic $clinic)
    {
        Gate::authorize('update', $clinic);

        return view('clinics.edit', compact('clinic'));
    }

    public function update(UpdateClinicRequest $request, Clinic $clinic)
    {
        Gate::authorize('update', $clinic);
        $data = $request->validated();
        $data['updated_by'] = Auth::id();
        $clinic->update($data);
        Cache::forget('clinics.all');

        return redirect()->route('clinics.index')->with('success', 'تم تحديث العيادة بنجاح');
    }

    public function destroy(Clinic $clinic)
    {
        Gate::authorize('delete', $clinic);
        $clinic->delete();
        Cache::forget('clinics.all');

        return redirect()->route('clinics.index')->with('success', 'تم حذف العيادة بنجاح');
    }
}
