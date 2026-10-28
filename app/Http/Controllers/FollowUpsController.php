<?php

namespace App\Http\Controllers;

use App\Http\Requests\FollowUpsRequests\FollowUpsIndexRequest;
use App\Http\Requests\FollowUpsRequests\UpdateFollowUpRequest;
use App\Models\FollowUp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if (($data['status'] ?? null) === 'done') {
            $data['completed_at'] = $followup->completed_at ?? now();
        } else {
            $data['completed_at'] = null;
        }
        $data['updated_by'] = Auth::id();
        $followup->update($data);

        return redirect()
            ->route('follow_ups.index')
            ->with('success', 'تم تحديث المتابعة بنجاح');
    }

    public function complete(Request $request, FollowUp $followup)
    {
        Gate::authorize('complete', $followup);
        if ($followup->status !== 'scheduled') {
            return redirect()->route('follow_ups.index')->with('error', 'لا يمكن تغيير حالة المتابعة');
        }
        $request->validate([
            'outcome_note' => 'nullable|max:2000',
        ], [
            'outcome_note.max' => 'يجب أن لا يتجاوز ملاحظة النتيجة 2000 حرف.',
        ]);
        $followup->update([
            'outcome_note' => $request->outcome_note,
            'status' => 'done',
            'completed_at' => now(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('follow_ups.index')->with('success', 'تم إنجاز المتابعة بنجاح');
    }

    public function status(Request $request, FollowUp $followup)
    {
        Gate::authorize('status', $followup);
        if ($followup->status === 'done') {
            return redirect()->route('follow_ups.index')->with('error', 'لا يمكن تغيير حالة المتابعة');
        }
        $request->validate(
            [
                'status' => 'required|in:scheduled,done,missed,cancelled',
            ],
            [
                'status.required' => 'يرجى تحديد حالة المتابعة.',
                'status.in' => 'حالة المتابعة المحددة غير صحيحة.',
            ]
        );

        $followup->update(['status' => $request->status, 'updated_by' => Auth::id()]);

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
