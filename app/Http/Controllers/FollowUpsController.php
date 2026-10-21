<?php

namespace App\Http\Controllers;

use App\Http\Requests\FollowUpsRequests\FollowUpsIndexRequest;
use App\Http\Requests\FollowUpsRequests\UpdateFollowUpRequest;
use App\Models\FollowUp;
use Illuminate\Http\Request;

class FollowUpsController extends Controller
{
    //
    public function index(FollowUpsIndexRequest $request)
    {
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
        $followup->load(['patient', 'visit.doctor.clinic', 'creator']);

        return view('followups.show', compact('followup'));
    }

    public function edit(FollowUp $followup)
    {
        $followup->load(['patient', 'visit.doctor.clinic', 'creator']);
        return view('followups.edit', compact('followup'));
    }

    public function update(
        UpdateFollowUpRequest $request,
        FollowUp $followup
    ) {
        $data = $request->validated();

        if (($data['status'] ?? null) === 'done') {
            $data['completed_at'] = $followup->completed_at ?? now();
        } else {
            $data['completed_at'] = null;
        }

        $followup->update($data);

        return redirect()
            ->route('follow_ups.index')
            ->with('success', 'تم تحديث المتابعة بنجاح');
    }
    public function complete(Request $request, FollowUp $followup)
    {
        $request->validate([
            'outcome_note' => 'nullable|max:2000',
        ], [
            'outcome_note.max' => 'يجب أن لا يتجاوز ملاحظة النتيجة 2000 حرف.',
        ]);
        $followup->update([
            'outcome_note' => $request->outcome_note,
            'status' => 'done',
            'completed_at' => now(),
        ]);
        return redirect()->route('follow_ups.index')->with('success', 'تم إنجاز المتابعة بنجاح');
    }

    public function status(Request $request, FollowUp $followup)
    {
        $request->validate(
            [
                'status' => 'required|in:scheduled,done,missed,cancelled',
            ],
            [
                'status.required' => 'يرجى تحديد حالة المتابعة.',
                'status.in' => 'حالة المتابعة المحددة غير صحيحة.',
            ]
        );
        $followup->update(['status' => $request->status]);
        return redirect()->route('follow_ups.index')->with('success', 'تم تغيير حالة المتابعة بنجاح');
    }

    public function destroy(FollowUp $followup)
    {
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
