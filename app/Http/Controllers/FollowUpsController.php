<?php

namespace App\Http\Controllers;

use App\Http\Requests\FollowUpsRequests\FollowUpsIndexRequest;
use App\Http\Requests\FollowUpsRequests\UpdateFollowUpRequest;
use App\Models\FollowUp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class FollowUpsController extends Controller
{
    //
    public function index(FollowUpsIndexRequest $request)
    {
        Gate::authorize('viewAny', FollowUp::class);
        $filters = $request->validated();

        $status = $filters['status'] ?? 'all';
        $patientId = $filters['patient_id'] ?? null;
        $search = $filters['search'] ?? null;

        $query = FollowUp::with(['patient', 'visit.doctor.clinic', 'creator'])
            ->latest('due_date');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($patientId) {
            $query->where('patient_id', $patientId);
        }

        if ($search) {
            $query->search($search);
        }

        $followups = $query->paginate(15)->withQueryString();

        // إحصائيات سريعة للبطاقات ومؤشرات الأداء

        $stats = $this->getFollowUpStats();

        return view('followups.index', [
            'followups' => $followups,
            'stats' => $stats,
            'filters' => [
                'status' => $status,
                'patientId' => $patientId,
                'search' => $search,
            ],
        ]);
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

        $data = $request->validated();

        /*
        | `patient_id` and `visit_id` identify the association the follow-up was
        | created with. They are permanent, so any submitted value is dropped
        | before the row is written.
        */
        unset($data['patient_id'], $data['visit_id']);

        /*
        | The transition is re-checked after the row is locked: a concurrent
        | completion must not slip between the request validation and the write.
        */
        $updated = DB::transaction(function () use ($followup, $data): bool {
            $lockedFollowUp = FollowUp::query()
                ->whereKey($followup->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedFollowUp->canTransitionTo((string) $data['status'])) {
                return false;
            }

            $data['completed_at'] = $data['status'] === 'done'
                ? ($lockedFollowUp->completed_at ?? now())
                : null;
            $data['updated_by'] = Auth::id();
            $lockedFollowUp->update($data);

            return true;
        });

        if (! $updated) {
            return redirect()
                ->route('follow_ups.index')
                ->with('error', 'لا يمكن تغيير حالة المتابعة');
        }

        return redirect()
            ->route('follow_ups.index')
            ->with('success', 'تم تحديث المتابعة بنجاح');
    }

    public function complete(Request $request, FollowUp $followup)
    {
        Gate::authorize('complete', $followup);
        $request->validate([
            'outcome_note' => 'nullable|string|max:500',
        ], [
            'outcome_note.max' => 'يجب أن لا يتجاوز ملاحظة النتيجة 500 حرف.',
            'outcome_note.string' => 'ملاحظة النتيجة يجب أن تكون نصًا.',
        ]);

        /*
        | The status is checked under the row lock, so two concurrent completions
        | cannot both pass and overwrite the outcome and completion moment.
        */
        $completed = DB::transaction(function () use ($followup, $request): bool {
            $lockedFollowUp = FollowUp::query()
                ->whereKey($followup->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedFollowUp->status !== 'scheduled') {
                return false;
            }

            $lockedFollowUp->update([
                'outcome_note' => $request->input('outcome_note'),
                'status' => 'done',
                'completed_at' => now(),
                'updated_by' => Auth::id(),
            ]);

            return true;
        });

        if (! $completed) {
            return redirect()->route('follow_ups.index')->with('error', 'لا يمكن تغيير حالة المتابعة');
        }

        return redirect()->route('follow_ups.index')->with('success', 'تم إنجاز المتابعة بنجاح');
    }

    public function status(Request $request, FollowUp $followup)
    {
        Gate::authorize('status', $followup);
        $validated = $request->validate(
            [
                'status' => 'required|string|in:scheduled,done,missed,cancelled',
            ],
            [
                'status.required' => 'يرجى تحديد حالة المتابعة.',
                'status.string' => 'حالة المتابعة المحددة غير صحيحة.',
                'status.in' => 'حالة المتابعة المحددة غير صحيحة.',
            ]
        );

        /*
        | The current status is read after the row is locked, so two concurrent
        | status changes cannot both pass the transition check. The completion
        | moment is written with the status, exactly as the update and complete
        | actions do.
        */
        $currentLabel = '';
        $outcome = DB::transaction(function () use ($followup, $validated, &$currentLabel): string {
            $lockedFollowUp = FollowUp::query()
                ->whereKey($followup->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $currentLabel = $lockedFollowUp->status_label;

            if ($lockedFollowUp->status === 'done') {
                return 'done';
            }

            if (! $lockedFollowUp->canTransitionTo((string) $validated['status'])) {
                return 'invalid';
            }

            $lockedFollowUp->update([
                'status' => $validated['status'],
                'completed_at' => $validated['status'] === 'done' ? ($lockedFollowUp->completed_at ?? now()) : null,
                'updated_by' => Auth::id(),
            ]);

            return 'updated';
        });

        if ($outcome === 'done') {
            return redirect()->route('follow_ups.index')->with('error', 'لا يمكن تغيير حالة المتابعة');
        }

        if ($outcome === 'invalid') {
            return redirect()->route('follow_ups.index')
                ->with('error', 'لا يمكن نقل المتابعة من حالة «'.$currentLabel.'» إلى هذه الحالة.');
        }

        return redirect()->route('follow_ups.index')->with('success', 'تم تغيير حالة المتابعة بنجاح');
    }

    public function destroy(FollowUp $followup)
    {
        Gate::authorize('delete', $followup);
        $followup->delete();

        return redirect()->route('follow_ups.index')->with('success', 'تم حذف المتابعة بنجاح');
    }

    private function getFollowUpStats()
    {
        return FollowUp::query()
            ->selectRaw('
            COUNT(*) AS total,

            COALESCE(SUM(CASE
                WHEN status = ? THEN 1 ELSE 0
            END), 0) AS scheduled,

            COALESCE(SUM(CASE
                WHEN status = ? THEN 1 ELSE 0
            END), 0) AS done,

            COALESCE(SUM(CASE
                WHEN status = ? THEN 1 ELSE 0
            END), 0) AS missed,

            COALESCE(SUM(CASE
                WHEN status = ? THEN 1 ELSE 0
            END), 0) AS cancelled,

            COALESCE(SUM(CASE
                WHEN status = ? AND due_date < ? THEN 1 ELSE 0
            END), 0) AS overdue
        ', [
                'scheduled',
                'done',
                'missed',
                'cancelled',
                'scheduled',
                now(),
            ])
            ->firstOrFail();
    }
}
