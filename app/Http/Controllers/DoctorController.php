<?php

namespace App\Http\Controllers;

use App\Http\Requests\DoctorRequests\CreateDoctorRequest;
use App\Http\Requests\DoctorRequests\DoctorIndexRequest;
use App\Http\Requests\DoctorRequests\UpdateDoctorRequest;
use App\Models\Doctor;
use App\Queries\DoctorIndexQuery;
use App\Services\ClinicService;
use App\Services\DoctorService;
use Illuminate\Support\Facades\Cache;

class DoctorController extends Controller
{
    //
    public function __construct(private ClinicService $clinicService ,private DoctorIndexQuery $doctorIndexQuery , private DoctorService $doctor_service)
    {
    }
    public function index(DoctorIndexRequest $request)
    {
        $clinics = $this->clinicService->getClinics();
        $doctors_counts = $this->doctor_service->getDoctorsCounts();

        $doctors = $this->doctorIndexQuery
        ->build($request->validated())
        ->paginate(12)
        ->withQueryString();
        return view('doctors.index', compact('doctors' ,'clinics' , 'doctors_counts'));
    }
    public function show(Doctor $doctor)
    {
        $doctor->load(['visits.patient','visits.creator']);
        return view('doctors.show', compact('doctor'));
    }

    public function create()
    {
        $clinics = $this->clinicService->getClinics();
        return view('doctors.create',compact('clinics'));
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
        $clinics = $this->clinicService->getClinics();
        return view('doctors.edit', compact('doctor','clinics'));
    }
    public function update(UpdateDoctorRequest $request,  Doctor $doctor)
    {
        $doctor->update($request->validated());
        Cache::forget('doctors_counts');
        Cache::forget('doctors_counts_by_clinic');
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
