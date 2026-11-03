<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComplaintRequests\AssignComplaintRequest;
use App\Http\Requests\ComplaintRequests\ComplaintIndexRequest;
use App\Http\Requests\ComplaintRequests\CreateComplaintRequest;
use App\Http\Requests\ComplaintRequests\UpdateComplaintRequest;
use App\Models\Complaint;
use App\Services\ComplaintService;
use App\Services\PatientService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ComplaintController extends Controller
{
    public function __construct(
        private ComplaintService $complaintService,
        private PatientService $patientService,
        private UserService $userService
    ) {}

    public function index(ComplaintIndexRequest $request)
    {
        Gate::authorize('viewAny', Complaint::class);

        return view('complaints.index', $this->complaintService->paginateIndex($request->validated()));
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

        /*
        | Assigning at creation takes the same ability as the dedicated assign
        | action, so it cannot be used to reach a stage the actor may not assign
        | into.
        */
        if (! empty($data['assigned_to'])) {
            Gate::authorize('assign', new Complaint);
        }

        $this->complaintService->create($data, Auth::id());

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

        $this->complaintService->update($complaint, $request->validated(), Auth::id());

        return redirect()->route('complaints.show', $complaint)
            ->with('success', 'تم تحديث بيانات الشكوى بنجاح.');
    }

    public function advance(Request $request, Complaint $complaint)
    {
        Gate::authorize('advance', $complaint);

        $this->complaintService->advance($complaint, $request->all(), Auth::id());

        return back()->with(
            'success',
            'تم تحديث مرحلة الشكوى بنجاح.'
        );
    }

    public function assign(AssignComplaintRequest $request, Complaint $complaint)
    {
        Gate::authorize('assign', $complaint);

        $assigneeName = $this->complaintService->assign($complaint, $request->validated(), Auth::id());

        return back()->with('success', "تم إسناد الشكوى إلى: {$assigneeName}");
    }

    public function reopen(Complaint $complaint)
    {
        Gate::authorize('reopen', $complaint);

        $this->complaintService->reopen($complaint, Auth::id());

        return back()->with(
            'success',
            'تمت إعادة فتح الشكوى وإعادتها إلى مرحلة التحقيق بنجاح.'
        );
    }

    public function destroy(Complaint $complaint)
    {
        Gate::authorize('delete', $complaint);

        $this->complaintService->delete($complaint);

        return redirect()->route('complaints.index')
            ->with('success', 'تم حذف الشكوى بنجاح.');
    }
}
