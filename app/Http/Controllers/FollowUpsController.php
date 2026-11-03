<?php

namespace App\Http\Controllers;

use App\Http\Requests\FollowUpsRequests\CompleteFollowUpRequest;
use App\Http\Requests\FollowUpsRequests\FollowUpsIndexRequest;
use App\Http\Requests\FollowUpsRequests\UpdateFollowUpRequest;
use App\Http\Requests\FollowUpsRequests\UpdateFollowUpStatusRequest;
use App\Models\FollowUp;
use App\Services\FollowUpService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class FollowUpsController extends Controller
{
    public function __construct(private FollowUpService $followUpService) {}

    public function index(FollowUpsIndexRequest $request)
    {
        Gate::authorize('viewAny', FollowUp::class);

        return view('followups.index', $this->followUpService->paginateIndex($request->validated()));
    }

    public function show(FollowUp $followup)
    {
        Gate::authorize('view', $followup);
        $followup->load(['patient', 'visit.doctor.clinic', 'creator']);

        return view('followups.show', compact('followup'));
    }

    public function edit(FollowUp $followup)
    {
        Gate::authorize('update', $followup);
        $followup->load(['patient', 'visit.doctor.clinic', 'creator']);

        return view('followups.edit', compact('followup'));
    }

    public function update(
        UpdateFollowUpRequest $request,
        FollowUp $followup
    ) {
        Gate::authorize('update', $followup);

        if (! $this->followUpService->update($followup, $request->validated(), Auth::id())) {
            return redirect()
                ->route('follow_ups.index')
                ->with('error', 'لا يمكن تغيير حالة المتابعة');
        }

        return redirect()
            ->route('follow_ups.index')
            ->with('success', 'تم تحديث المتابعة بنجاح');
    }

    public function complete(CompleteFollowUpRequest $request, FollowUp $followup)
    {
        Gate::authorize('complete', $followup);

        if (! $this->followUpService->complete($followup, $request->validated('outcome_note'), Auth::id())) {
            return redirect()->route('follow_ups.index')->with('error', 'لا يمكن تغيير حالة المتابعة');
        }

        return redirect()->route('follow_ups.index')->with('success', 'تم إنجاز المتابعة بنجاح');
    }

    public function status(UpdateFollowUpStatusRequest $request, FollowUp $followup)
    {
        Gate::authorize('status', $followup);

        $result = $this->followUpService->changeStatus($followup, $request->validated('status'), Auth::id());

        if ($result['outcome'] === 'done') {
            return redirect()->route('follow_ups.index')->with('error', 'لا يمكن تغيير حالة المتابعة');
        }

        if ($result['outcome'] === 'invalid') {
            return redirect()->route('follow_ups.index')
                ->with('error', 'لا يمكن نقل المتابعة من حالة «'.$result['current_label'].'» إلى هذه الحالة.');
        }

        return redirect()->route('follow_ups.index')->with('success', 'تم تغيير حالة المتابعة بنجاح');
    }

    public function destroy(FollowUp $followup)
    {
        Gate::authorize('delete', $followup);

        $this->followUpService->delete($followup);

        return redirect()->route('follow_ups.index')->with('success', 'تم حذف المتابعة بنجاح');
    }
}
