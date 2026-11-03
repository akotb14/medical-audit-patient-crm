<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClinicRequests\CreateClinicRequest;
use App\Http\Requests\ClinicRequests\UpdateClinicRequest;
use App\Models\Clinic;
use App\Services\ClinicService;
use App\Services\DoctorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ClinicController extends Controller
{
    public function __construct(private ClinicService $clinicService, private DoctorService $doctorService) {}

    public function index()
    {
        Gate::authorize('viewAny', Clinic::class);

        return view('clinics.index', [
            'clinics' => $this->clinicService->getClinicsWithDoctors(),
            'doctors_counts' => $this->doctorService->getDoctorsCounts(),
            'doctors_counts_by_clinic' => $this->doctorService->getDoctorsCountsByClinic(),
        ]);
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

        $this->clinicService->create($request->validated(), Auth::id());

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

        $this->clinicService->update($clinic, $request->validated(), Auth::id());

        return redirect()->route('clinics.index')->with('success', 'تم تحديث العيادة بنجاح');
    }

    public function destroy(Clinic $clinic)
    {
        Gate::authorize('delete', $clinic);

        if (! $this->clinicService->delete($clinic)) {
            return redirect()->route('clinics.index')
                ->with('error', 'لا يمكن حذف العيادة لوجود أطباء أو سجلات مرتبطة بها. يمكن تعيين حالتها إلى غير نشط بدلًا من ذلك.');
        }

        return redirect()->route('clinics.index')->with('success', 'تم حذف العيادة بنجاح');
    }
}
