<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComplaintRequests\ComplaintIndexRequest;
use App\Http\Requests\ComplaintRequests\CreateComplaintRequest;
use App\Http\Requests\ComplaintRequests\UpdateComplaintRequest;
use App\Models\Complaint;
use App\Services\PatientService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ComplaintController extends Controller
{
    public function __construct(
        private PatientService $patientService,
        private UserService $userService
    ) {}

    public function index(ComplaintIndexRequest $request)
    {
        Gate::authorize('viewAny', Complaint::class);

        $query = Complaint::with(['patient', 'survey.visit', 'assignee.roles', 'creator'])
            ->latest('created_at');

        $stats = (clone $query)->stats()->first();

        $filters = $request->validated();

        $query->filter($filters);

        $users = $this->userService->getAssigneeOptions(
            is_numeric($filters['assigned_to'] ?? null) ? (int) $filters['assigned_to'] : null
        );

        $complaints = $query->paginate(15)->withQueryString();

        return view('complaints.index', compact('complaints', 'stats', 'users'));
    }

    public function create()
    {
        Gate::authorize('create', Complaint::class);
        $patients = $this->patientService->getPatientOptions();
        $users = $this->userService->getAssigneeOptions();

        return view('complaints.create', compact('users', 'patients'));
    }

    public function store(CreateComplaintRequest $request)
    {
        Gate::authorize('create', Complaint::class);
        $data = $request->validated();
        $data['source'] = 'manual';
        $data['created_by'] = Auth::id();

        /*
        | A complaint created with an assignee starts its workflow at `assigned`,
        | so the record never claims to be new while naming one. Assigning at
        | creation takes the same ability as the dedicated assign action, so it
        | cannot be used to reach a stage the actor may not assign into.
        */
        if (! empty($data['assigned_to'])) {
            Gate::authorize('assign', new Complaint);
            $data['status'] = 'assigned';
        } else {
            $data['assigned_to'] = null;
            $data['status'] = 'new';
        }

        Complaint::create($data);

        return redirect()->route('complaints.index')
            ->with('success', 'تم إنشاء الشكوى بنجاح.');
    }

    public function show(Complaint $complaint)
    {
        Gate::authorize('view', $complaint);
        $complaint->load(['patient', 'survey.visit', 'assignee.roles', 'creator']);
        $users = $this->userService->getAssigneeOptions($complaint->assigned_to);

        return view('complaints.show', compact('complaint', 'users'));
    }

    public function edit(Complaint $complaint)
    {
        Gate::authorize('update', $complaint);

        return view('complaints.edit', compact('complaint'));
    }

    public function update(UpdateComplaintRequest $request, Complaint $complaint)
    {
        Gate::authorize('update', $complaint);
        $data = $request->validated();

        $data['updated_by'] = Auth::id();
        $complaint->update($data);

        return redirect()->route('complaints.show', $complaint)
            ->with('success', 'تم تحديث بيانات الشكوى بنجاح.');
    }

    public function advance(Request $request, Complaint $complaint)
    {
        Gate::authorize('advance', $complaint);

        DB::transaction(function () use ($request, $complaint) {

            $complaint = Complaint::query()
                ->lockForUpdate()
                ->findOrFail($complaint->id);

            $nextStatus = $complaint->nextStatus();

            if ($nextStatus === null) {
                throw ValidationException::withMessages([
                    'status' => 'لا يمكن الانتقال من الحالة الحالية.',
                ]);
            }

            $rules = match ($complaint->status) {
                'new' => [
                    'assigned_to' => [
                        'required',
                        'integer',
                        'exists:users,id',
                    ],
                ],

                'investigating' => [
                    'resolution_note' => [
                        'required',
                        'string',
                        'max:2000',
                    ],
                ],

                default => [],
            };

            $data = $request->validate($rules);

            /*
            | The views read the stage timestamps, so the moment a complaint reaches
            | a stage is stored with the stage. A stage it already reached keeps its
            | original timestamp.
            */
            $timestamps = match ($nextStatus) {
                'resolved' => ['resolved_at' => $complaint->resolved_at ?? now()],
                'closed' => ['closed_at' => $complaint->closed_at ?? now()],
                default => [],
            };

            $complaint->update([
                ...$data,
                ...$timestamps,
                'status' => $nextStatus,
                'updated_by' => Auth::id(),
            ]);
        });

        return back()->with(
            'success',
            'تم تحديث مرحلة الشكوى بنجاح.'
        );
    }

    public function assign(Request $request, Complaint $complaint)
    {
        Gate::authorize('assign', $complaint);
        $validated = $request->validate([
            'assigned_to' => 'required|integer|exists:users,id',
        ]);

        /*
        | The status is checked again after the row is locked: a concurrent
        | assignment or advance must not both pass a check made on the copy the
        | route resolved. The name in the message comes from the locked row, so
        | it always names the assignee that was actually stored.
        */
        $assigneeName = DB::transaction(function () use ($validated, $complaint): string {
            $lockedComplaint = Complaint::query()
                ->whereKey($complaint->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedComplaint->status !== 'new') {
                throw ValidationException::withMessages([
                    'status' => 'لا يمكن إسناد الشكوى في حالتها الحالية.',
                ]);
            }

            $lockedComplaint->update([
                'assigned_to' => $validated['assigned_to'],
                'status' => 'assigned',
                'updated_by' => Auth::id(),
            ]);

            return (string) $lockedComplaint->assignee()->value('name');
        });

        return back()->with('success', "تم إسناد الشكوى إلى: {$assigneeName}");
    }

    public function reopen(Complaint $complaint)
    {
        Gate::authorize('reopen', $complaint);

        /*
        | A reopened complaint goes back under investigation, so the moments it
        | reached `resolved` and `closed` no longer describe the stage it is in and
        | are cleared with the status. The status is re-checked after the row is
        | locked, so a concurrent reopen or advance cannot both pass.
        */
        $reopened = DB::transaction(function () use ($complaint): bool {
            $lockedComplaint = Complaint::query()
                ->whereKey($complaint->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedComplaint->canReopen()) {
                return false;
            }

            $lockedComplaint->update([
                'status' => 'investigating',
                'resolved_at' => null,
                'closed_at' => null,
                'updated_by' => Auth::id(),
            ]);

            return true;
        });

        if (! $reopened) {
            throw ValidationException::withMessages([
                'status' => 'لا يمكن إعادة فتح الشكوى في حالتها الحالية.',
            ]);
        }

        return back()->with(
            'success',
            'تمت إعادة فتح الشكوى وإعادتها إلى مرحلة التحقيق بنجاح.'
        );
    }

    public function destroy(Complaint $complaint)
    {
        Gate::authorize('delete', $complaint);
        $complaint->delete();

        return redirect()->route('complaints.index')
            ->with('success', 'تم حذف الشكوى بنجاح.');
    }
}
