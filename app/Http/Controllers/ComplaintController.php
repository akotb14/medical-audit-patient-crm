<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComplaintRequests\CreateComplaintRequest;
use App\Http\Requests\ComplaintRequests\UpdateComplaintRequest;
use App\Models\Complaint;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ComplaintController extends Controller
{
    //
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Complaint::class);

        $query = Complaint::with(['patient', 'survey.visit', 'assignee', 'creator'])
            ->latest('created_at');

        $stats = (clone $query)->stats()->first();

        $query->filter($request->query());

        $users = User::query()->orderBy('name')->get();


        $complaints = $query->paginate(15)->withQueryString();

        return view('complaints.index', compact('complaints', 'stats', 'users'));
    }
    public function create()
    {
        Gate::authorize('create', Complaint::class);
        $patients = Patient::query()->orderBy('fullname')->get();
        $users = User::query()->orderBy('name')->get();
        return view('complaints.create', compact('users', 'patients'));
    }

    public function store(CreateComplaintRequest $request)
    {
        Gate::authorize('create', Complaint::class);
        $data = $request->validated();
        $data['source'] = 'manual';
        $data['status'] = 'new';
        $data['created_by'] = Auth::id();
        Complaint::create($data);
        return redirect()->route('complaints.index');
    }

    public function show(Complaint $complaint)
    {
        Gate::authorize('view', $complaint);
        $complaint->load(['patient', 'survey.visit', 'assignee', 'creator']);
        $users = User::query()->orderBy('name')->get();
        return view('complaints.show', compact('complaint', 'users'));
    }

    public function edit(Complaint $complaint)
    {
        Gate::authorize('update', $complaint);
        $users = User::query()->orderBy('name')->get();

        return view('complaints.edit', compact('complaint', 'users'));
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

            $complaint->update([
                ...$data,
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
            'assigned_to' => 'required|exists:users,id',
        ]);

        $newStatus = ($complaint->status === 'new') ? 'assigned' : $complaint->status;

        $complaint->update([
            'assigned_to' => $validated['assigned_to'],
            'status' => $newStatus,
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', "تم إسناد الشكوى إلى: {$complaint->assignee->name}");
    }

    public function reopen(Request $request, Complaint $complaint)
    {
        Gate::authorize('reopen', $complaint);

        if (!$complaint->canReopen()) {
            throw ValidationException::withMessages([
                'status' => 'لا يمكن إعادة فتح الشكوى في حالتها الحالية.',
            ]);
        }


        $complaint->update([
            'status' => 'investigating',
            'updated_by' => Auth::id(),
        ]);

        return back()->with(
            'success',
            'تمت إعادة فتح الشكوى وإعادتها إلى مرحلة التحقيق بنجاح.'
        );
    }


    public function destroy(Complaint $complaint)
    {
        Gate::authorize('delete', $complaint);
        $complaint->delete();
        return redirect()->route('complaints.index');
    }
}
