<?php

namespace App\Http\Controllers;

use App\Http\Requests\DoctorRequests\CreateDoctorRequest;
use App\Http\Requests\DoctorRequests\DoctorIndexRequest;
use App\Http\Requests\DoctorRequests\UpdateDoctorRequest;
use App\Models\Doctor;
use App\Queries\DoctorIndexQuery;
use App\Services\ClinicService;
use App\Services\DoctorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DoctorController extends Controller
{
    //
    public function __construct(private ClinicService $clinicService, private DoctorIndexQuery $doctorIndexQuery, private DoctorService $doctor_service) {}

    public function index(DoctorIndexRequest $request)
    {
        Gate::authorize('viewAny', Doctor::class);
        $clinics = $this->clinicService->getClinics();
        $doctors_counts = $this->doctor_service->getDoctorsCounts();

        $doctors = $this->doctorIndexQuery
            ->build($request->validated())
            ->paginate(12)
            ->withQueryString();

        return view('doctors.index', compact('doctors', 'clinics', 'doctors_counts'));
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
        $clinics = $this->clinicService->getClinics();

        return view('doctors.create', compact('clinics'));
    }

    public function store(CreateDoctorRequest $request)
    {
        Gate::authorize('create', Doctor::class);
        $data = $request->validated();
        $data['created_by'] = Auth::id();
        Doctor::create($data);
        Cache::forget('doctors_counts');
        Cache::forget('doctors_counts_by_clinic');

        return redirect()->route('doctors.index')->with('success', 'تم اضافة الطبيب بنجاح');
    }

    public function edit(Doctor $doctor)
    {
        Gate::authorize('update', $doctor);
        $clinics = $this->clinicService->getClinics();

        return view('doctors.edit', compact('doctor', 'clinics'));
    }

    public function update(UpdateDoctorRequest $request, Doctor $doctor)
    {
        Gate::authorize('update', $doctor);
        $data = $request->validated();
        $data['updated_by'] = Auth::id();
        $doctor->update($data);
        Cache::forget('doctors_counts');
        Cache::forget('doctors_counts_by_clinic');

        return redirect()->route('doctors.index')->with('success', 'تم تعديل الطبيب بنجاح');
    }

    public function destroy(Doctor $doctor)
    {
        Gate::authorize('delete', $doctor);

        /*
        | The visits keep the clinical history of their patients, and the foreign
        | key releases the doctor reference on each of them when the row goes.
        | A doctor who has seen patients is deactivated instead of deleted, so
        | the visits keep naming the doctor who saw them. The row is locked
        | before the check, so a visit cannot be attached between the check and
        | the delete.
        */
        $hasVisits = DB::transaction(function () use ($doctor): bool {
            $lockedDoctor = Doctor::query()
                ->whereKey($doctor->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedDoctor->visits()->exists()) {
                return true;
            }

            $lockedDoctor->delete();

            return false;
        });

        if ($hasVisits) {
            return redirect()->route('doctors.index')
                ->with('error', 'لا يمكن حذف الطبيب لوجود زيارات مرتبطة به. يمكن تعيين حالته إلى غير نشط بدلًا من ذلك.');
        }

        Cache::forget('doctors_counts');
        Cache::forget('doctors_counts_by_clinic');

        return redirect()->route('doctors.index')->with('success', 'تم حذف الطبيب بنجاح');
    }
}
