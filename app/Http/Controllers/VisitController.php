<?php

namespace App\Http\Controllers;

use App\Http\Requests\VistRequests\CreateVisitRequest;
use App\Http\Requests\VistRequests\UpdateVisitRequest;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Visit;
use App\Queries\VisitIndexQuery;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    public function __construct( private VisitIndexQuery $visitIndexQuery) {}


    public function index(Request $request){
        $status = $request->query('visit_status', 'all');
        $date = $request->query('date');
        $doctorId = $request->query('doctor_id');
        $patientId = $request->query('patient_id');
        $search = $request->query('search');

        $query = Visit::with(['patient:id,fullname,mrn','doctor:id,fullname,clinic_id','doctor.clinic:id,name', 'creator:id,name'])
            ->latest('date');

        if ($status && $status !== 'all') {
            $query->where('visit_status', $status);
        }

        if ($date) {
            $query->whereDate('date', $date);
        }

        if ($doctorId) {
            $query->where('doctor_id', $doctorId);
        }

        if ($patientId) {
            $query->where('patient_id', $patientId);
        }

        if ($search) {
            $query->search($search);
        }

        $visits = $query->cursorPaginate(15)->withQueryString();

        $doctors = Doctor::with('clinic')->where('status', 'active')->get();
        // إحصائيات سريعة للبطاقات العلوية
       $stats = $this->visitIndexQuery->stats();
        return view('visits.index' ,compact('visits' , 'doctors' ,'stats'));
    }

    public function show(Visit $visit){
        $visit->load(['patient', 'doctor.clinic', 'creator']);
        return view('visits.show' , compact('visit'));
    }
    public function create(Request $request){
        $patient_id = $request->query('patient_id');
        $patients = Patient::query()->select('id', 'fullname', 'mrn');
        $doctors = Doctor::with('clinic')->where('status', 'active')->get();
        if($patient_id){
            $patients->where('id' , $patient_id);
        }
        $patients = $patients->get();
        return view('visits.create' , compact('patients' , 'doctors'));
    }
    public function store(CreateVisitRequest $request){

        $data = $request->validated();
        $data['visit_status'] = 'created';
        $data['created_by'] = 1;
        Visit::create($data);
        return redirect()->route('visits.index')->with('success' , 'تم انشاء زيارة جديد' );
    }

    public function edit (Visit $visit){
        $patients = Patient::select('id', 'fullname', 'mrn')->get();
        $doctors = Doctor::with('clinic')->where('status', 'active')->get();
        return view('visits.edit' , compact('visit' , 'patients' , 'doctors'));
    }

    public function update(Visit $visit , UpdateVisitRequest $request){
        $data = $request->validated();
        $visit->update($data);
        return redirect()->route('visits.index')->with('success' , 'تم تحديث بيانات الزيارة' );
    }
    public function updateStatus(Visit $visit , Request $request){
        $data = $request->validate(['visit_status' => 'required|in:created,completed,cancelled']);
       // dd($request);
        $visit->update($data);
        return back()->with('success' , 'تم تحديث حالة الزيارة' );
    }
    public function destroy(Visit $visit){
        $visit->delete();
        return redirect()->route('visits.index')->with('success' , 'تم حذف الزيارة بنجاح');
    }

}
