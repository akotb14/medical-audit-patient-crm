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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class VisitController extends Controller
{
    public function __construct(private VisitIndexQuery $visitIndexQuery) {}

    public function index(VisitIndexRequest $request)
    {
        Gate::authorize('viewAny', Visit::class);
        $filters = $request->validated();

        $status = $filters['visit_status'] ?? 'all';
        $date = $filters['date'] ?? null;
        $doctorId = $filters['doctor_id'] ?? null;
        $patientId = $filters['patient_id'] ?? null;
        $search = $filters['search'] ?? null;

        $query = Visit::query()
            ->with([
                // `deleted_at` has to travel with the partial select: the row is
                // marked archived (and unlinked) from the model, not from a query.
                'patient:id,fullname,mrn,deleted_at',
                'doctor:id,fullname,clinic_id',
                'doctor.clinic:id,name',
                'creator:id,name',
                'survey',
            ])
            ->latest('date');
        // إحصائيات سريعة للبطاقات العلوية
        $stats = $this->visitIndexQuery->stats(clone $query);

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

        return view('visits.index', compact('visits', 'doctors', 'stats'));
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
        Gate::authorize('create', Visit::class);
        $data = $request->validated();
        $data['visit_status'] = 'created';
        $data['created_by'] = Auth::id();
        Visit::create($data);

        return redirect()->route('visits.index')->with('success', 'تم انشاء زيارة جديد');
    }

    public function edit(Visit $visit)
    {
        Gate::authorize('update', $visit);
        $patients = Patient::select('id', 'fullname', 'mrn')->get();
        $doctors = Doctor::with('clinic')->where('status', 'active')->get();

        return view('visits.edit', compact('visit', 'patients', 'doctors'));
    }

    public function update(Visit $visit, UpdateVisitRequest $request)
    {
        Gate::authorize('update', $visit);
        $data = $request->validated();
        $data['updated_by'] = Auth::id();
        $visit->update($data);

        return redirect()->route('visits.index')->with('success', 'تم تحديث بيانات الزيارة');
    }

    public function updateStatus(Visit $visit, Request $request)
    {
        Gate::authorize('updateStatus', $visit);

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
        ], [
            'due_date.required_if' => 'يجب تحديد تاريخ المتابعة',
            'reason.required_if' => 'يجب تحديد سبب المتابعة',
        ]);

        DB::transaction(function () use ($visit, $validated) {

            // 1. Lock the visit row
            $lockedVisit = Visit::query()
                ->whereKey($visit->id)
                ->lockForUpdate()
                ->firstOrFail();

            // 2. Check current status AFTER acquiring the lock
            if ($lockedVisit->visit_status !== 'created') {
                throw ValidationException::withMessages([
                    'visit' => 'لا يمكن تنفيذ الإجراء؛ الزيارة ليست في حالة مجدولة.',
                ]);
            }

            // 3. Cancel visit
            if ($validated['action'] === 'cancel') {
                $lockedVisit->update([
                    'visit_status' => 'cancelled',
                    'updated_by' => Auth::id(),
                ]);

                return;
            }

            // 4. Complete visit
            if ($validated['action'] === 'complete_with_followup') {

                if (
                    FollowUp::query()
                        ->where('visit_id', $lockedVisit->id)
                        ->exists()
                ) {
                    throw ValidationException::withMessages([
                        'followup' => 'توجد متابعة مسجلة لهذه الزيارة بالفعل.',
                    ]);
                }

                FollowUp::create([
                    'visit_id' => $lockedVisit->id,
                    'patient_id' => $lockedVisit->patient_id,
                    'status' => 'scheduled',
                    'due_date' => $validated['due_date'],
                    'reason' => $validated['reason'],
                    'created_by' => Auth::id(),
                ]);
            }

            // 5. Mark visit as completed
            $lockedVisit->update([
                'visit_status' => 'completed',
                'updated_by' => Auth::id(),
            ]);
        });

        // 6. Response after successful transaction
        return match ($validated['action']) {
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
        $visit->delete();

        return redirect()->route('visits.index')->with('success', 'تم حذف الزيارة بنجاح');
    }
}
