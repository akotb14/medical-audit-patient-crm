<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientRequests\CreatePatientRequest;
use App\Http\Requests\PatientRequests\PatientIndexRequest;
use App\Http\Requests\PatientRequests\UpdatePatientRequest;
use App\Models\Patient;
use App\Queries\PatientIndexQuery;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(PatientIndexRequest $request, PatientIndexQuery $patientIndexQuery)
    {
        //
        Gate::authorize('viewAny', Patient::class);
        $query = $patientIndexQuery->build($request->validated())->where('status', 'active');

        $stats = Patient::stats()->first();
        $patients = $query->paginate(20)->withQueryString();

        return view('patients.index', compact('patients', 'stats'));
    }

    /**
     * Display the archived (soft deleted) patient files.
     */
    public function archived()
    {
        Gate::authorize('restoreAny', Patient::class);

        $patients = Patient::onlyTrashed()
            ->with('deleter')
            ->latest('deleted_at')
            ->paginate(20);

        return view('patients.archived', compact('patients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', Patient::class);

        return view('patients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreatePatientRequest $request)
    {
        Gate::authorize('create', Patient::class);
        $data = $request->validated();

        $data['created_by'] = Auth::id();

        /*
        | `patients.status` has no database default and the create form does not
        | submit one, so a newly registered file always starts active.
        */
        $data['status'] = 'active';

        $attempts = 0;

        while (true) {
            $attempts++;

            do {
                $mrn = 'MRN-'.strtoupper(Str::random(8));
            } while (Patient::withTrashed()->where('mrn', $mrn)->exists());

            $data['mrn'] = $mrn;

            try {
                Patient::create($data);

                return redirect()->route('patients.index')->with('success', 'تم اضافة المريض بنجاح');
            } catch (UniqueConstraintViolationException $exception) {
                /*
                | Another registration can take the generated MRN between the
                | check above and the insert. Only that collision is retried; a
                | violation on another unique column is rethrown unchanged.
                */
                if (! Patient::withTrashed()->where('mrn', $mrn)->exists()) {
                    throw $exception;
                }

                if ($attempts >= 5) {
                    throw ValidationException::withMessages([
                        'mrn' => 'تعذر توليد رقم ملف طبي فريد. يرجى إعادة المحاولة.',
                    ]);
                }
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient)
    {
        Gate::authorize('view', $patient);

        $visitsQuery = $patient->visits();

        /*
        | The visit tiles describe the patient's whole history, so they cannot be
        | read off the paginator. One grouped aggregate keeps them independent of
        | the page being displayed.
        */
        $countsByStatus = (clone $visitsQuery)
            ->toBase()
            ->selectRaw('visit_status, COUNT(*) as aggregate')
            ->groupBy('visit_status')
            ->pluck('aggregate', 'visit_status');

        /** @var array{created: int, completed: int, cancelled: int} $visitCounts */
        $visitCounts = [
            'created' => (int) ($countsByStatus['created'] ?? 0),
            'completed' => (int) ($countsByStatus['completed'] ?? 0),
            'cancelled' => (int) ($countsByStatus['cancelled'] ?? 0),
        ];

        $visits = $visitsQuery
            ->with([
                'patient:id,fullname,mrn,deleted_at',
                'doctor.clinic',
                'creator',
                'survey',
            ])
            ->latest()
            ->paginate(20);

        return view('patients.show', compact('patient', 'visits', 'visitCounts'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patient $patient)
    {
        //
        Gate::authorize('update', $patient);

        return view('patients.edit', compact('patient'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        Gate::authorize('update', $patient);
        $data = $request->validated();
        $data['updated_by'] = Auth::id();
        $patient->update($data);

        return redirect()->route('patients.index')->with('success', 'تم تحديث المريض بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        Gate::authorize('delete', $patient);

        /*
        | Archiving is two writes: `runSoftDelete()` only persists deleted_at and
        | updated_at, so the audit column is stored on its own first. Both inside
        | one transaction, or a patient ends up archived with nobody attributed.
        */
        DB::transaction(function () use ($patient): void {
            $patient->forceFill(['deleted_by' => Auth::id()])->save();
            $patient->delete();
        });

        return redirect()->back()->with('success', 'تم أرشفة المريض بنجاح');
    }

    /**
     * Restore an archived patient file.
     */
    public function restore(Patient $patient)
    {
        Gate::authorize('restore', $patient);

        // `restore()` runs a single UPDATE, so both columns clear together.
        $patient->forceFill(['deleted_by' => null]);
        $patient->restore();

        return redirect()->route('patients.archived')->with('success', 'تم استعادة المريض بنجاح');
    }
}
