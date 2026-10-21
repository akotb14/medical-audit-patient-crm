<?php

namespace App\Http\Controllers;

use App\Http\Requests\VistRequests\CreateVisitRequest;
use App\Http\Requests\VistRequests\UpdateVisitRequest;
use App\Http\Requests\VistRequests\VisitIndexRequest;
use App\Models\Doctor;
use App\Models\FollowUp;
use App\Models\Patient;
use App\Models\Visit;
use App\Queries\VisitIndexQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VisitController extends Controller
{
    public function __construct(private VisitIndexQuery $visitIndexQuery) {}


    public function index(VisitIndexRequest $request)
    {
        $filters = $request->validated();

        $status = $filters['visit_status'] ?? 'all';
        $date = $filters['date'] ?? null;
        $doctorId = $filters['doctor_id'] ?? null;
        $patientId = $filters['patient_id'] ?? null;
        $search = $filters['search'] ?? null;

        $query = Visit::query()
            ->with([
                'patient:id,fullname,mrn',
                'doctor:id,fullname,clinic_id',
                'doctor.clinic:id,name',
                'creator:id,name',
            ])
            ->latest('date');

        if ($status !== 'all') {
            $query->status($status);
        }

        if ($date) {
            $query->forDate($date);
        }

        if ($doctorId) {
            $query->forDoctor($doctorId);
        }

        if ($patientId) {
            $query->forPatient($patientId);
        }

        if ($search) {
            $query->search($search);
        }


        $visits = $query->paginate(20)->withQueryString();

        $doctors = Doctor::with('clinic')->where('status', 'active')->get();
        // إحصائيات سريعة للبطاقات العلوية
        $stats = $this->visitIndexQuery->stats();
        return view('visits.index', compact('visits', 'doctors', 'stats'));
    }

    public function show(Visit $visit)
    {
        $visit->load(['patient', 'doctor.clinic', 'creator']);
        return view('visits.show', compact('visit'));
    }
    public function create(Request $request)
    {
        $patient_id = $request->query('patient_id');
        $patients = Patient::query()->select('id', 'fullname', 'mrn');
        $doctors = Doctor::with('clinic')->where('status', 'active')->get();
        if ($patient_id) {
            $patients->where('id', $patient_id);
        }
        $patients = $patients->get();
        return view('visits.create', compact('patients', 'doctors'));
    }
    public function store(CreateVisitRequest $request)
    {

        $data = $request->validated();
        $data['visit_status'] = 'created';
        $data['created_by'] = 1;
        Visit::create($data);
        return redirect()->route('visits.index')->with('success', 'تم انشاء زيارة جديد');
    }

    public function edit(Visit $visit)
    {
        $patients = Patient::select('id', 'fullname', 'mrn')->get();
        $doctors = Doctor::with('clinic')->where('status', 'active')->get();
        return view('visits.edit', compact('visit', 'patients', 'doctors'));
    }

    public function update(Visit $visit, UpdateVisitRequest $request)
    {
        $data = $request->validated();
        $visit->update($data);
        return redirect()->route('visits.index')->with('success', 'تم تحديث بيانات الزيارة');
    }
    public function updateStatus(Visit $visit, Request $request)
    {
        // منع التعامل مع زيارة مكتملة أو ملغاة
        if ($visit->visit_status !== 'created') {
            return back()->with(
                'error',
                'لا يمكن تنفيذ الإجراء؛ الزيارة ليست في حالة مجدولة.'
            );
        }
        $validated = $request->validate([
            'action' => [
                'required',
                'in:complete_only,complete_with_followup,cancel',
            ],

            'due_date' => [
                'required_if:action,complete_with_followup',
                'nullable',
                'date',
                'after:now',
            ],

            'reason' => [
                'required_if:action,complete_with_followup',
                'nullable',
                'string',
                'max:1000',
            ],
        ]);
        if ($validated['action'] === 'complete_only') {
            $visit->update([
                'visit_status' => 'completed',
            ]);

            return back()->with('success', 'تم إتمام الزيارة بنجاح');
        }

        // 2. إلغاء الزيارة
        if ($validated['action'] === 'cancel') {
            $visit->update([
                'visit_status' => 'cancelled',
            ]);

            return back()->with('success', 'تم إلغاء الزيارة بنجاح');
        }

        // 3. إتمام الزيارة وإنشاء متابعة
        DB::transaction(function () use ($visit, $validated) {
            if (FollowUp::where('visit_id', $visit->id)->exists()) {
                throw ValidationException::withMessages([
                    'followup' => 'توجد متابعة مسجلة لهذه الزيارة بالفعل.',
                ]);
            }

            $visit->update([
                'visit_status' => 'completed',
            ]);

            FollowUp::create([
                'visit_id'   => $visit->id,
                'patient_id' => $visit->patient_id,
                'status'     => 'scheduled',
                'due_date'   => $validated['due_date'],
                'reason'     => $validated['reason'],
                'created_by' => 1,
            ]);
        });

        return back()->with(
            'success',
            'تم إتمام الزيارة وجدولة المتابعة بنجاح'
        );
    }

    public function destroy(Visit $visit)
    {
        $visit->delete();
        return redirect()->route('visits.index')->with('success', 'تم حذف الزيارة بنجاح');
    }
}
