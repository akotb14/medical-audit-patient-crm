<?php

namespace App\Http\Controllers;

use App\Http\Requests\VistRequests\CreateVisitRequest;
use App\Http\Requests\VistRequests\UpdateVisitRequest;
use App\Http\Requests\VistRequests\VisitIndexRequest;
use App\Models\FollowUp;
use App\Models\Visit;
use App\Queries\VisitIndexQuery;
use App\Services\DoctorService;
use App\Services\PatientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class VisitController extends Controller
{
    public function __construct(
        private VisitIndexQuery $visitIndexQuery,
        private PatientService $patientService,
        private DoctorService $doctorService
    ) {}

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
        $doctors = $this->doctorService->getDoctorOptions($doctorId === null ? null : (int) $doctorId);

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
        $data = $request->validated();
        $data['visit_status'] = 'created';
        $data['created_by'] = Auth::id();
        Visit::create($data);

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
        $data = $request->validated();

        /*
        | The status is re-read under the row lock. A visit can be completed or
        | cancelled between binding the model and writing the update, and the
        | request was validated against the status the row had at that moment.
        */
        DB::transaction(function () use ($visit, $data): void {
            $lockedVisit = Visit::query()
                ->whereKey($visit->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $wasClosed = in_array($visit->visit_status, ['completed', 'cancelled'], true);
            $isClosed = in_array($lockedVisit->visit_status, ['completed', 'cancelled'], true);

            /*
            | The submitted date, doctor and reason were validated against an open
            | visit. Writing them now would bypass the freeze that applies to a
            | closed visit, so the request is rejected and the user retries on the
            | fresh state.
            */
            if ($isClosed && ! $wasClosed) {
                throw ValidationException::withMessages([
                    'visit' => 'تم إغلاق الزيارة أثناء التعديل. يرجى إعادة المحاولة.',
                ]);
            }

            /*
            | The edit form submits the date without a time. Resubmitting it for a
            | closed visit is allowed, but the stored time is part of the medical
            | history: leaving the key out keeps the original timestamp instead of
            | writing midnight over it.
            */
            if ($isClosed) {
                unset($data['date']);
            }

            $data['updated_by'] = Auth::id();
            $lockedVisit->update($data);
        });

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
                'max:500',
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

        /*
        | The status is re-checked under the row lock. A completed visit is part
        | of the medical history and must not be removed even if it was open when
        | the request started.
        */
        $deleted = DB::transaction(function () use ($visit): bool {
            $lockedVisit = Visit::query()
                ->whereKey($visit->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedVisit->visit_status === 'completed') {
                return false;
            }

            $lockedVisit->delete();

            return true;
        });

        if (! $deleted) {
            return redirect()
                ->route('visits.index')
                ->with('error', 'لا يمكن حذف زيارة مكتملة.');
        }

        return redirect()->route('visits.index')->with('success', 'تم حذف الزيارة بنجاح');
    }
}
