<?php

namespace App\Services;

use App\Models\FollowUp;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class FollowUpService
{
    /**
     * The follow-up register, filtered by the request.
     *
     * @param  array<string, mixed>  $filters
     * @return array{followups: LengthAwarePaginator, stats: object}
     */
    public function paginateIndex(array $filters): array
    {
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

        return [
            'followups' => $query->paginate(15)->withQueryString(),
            'stats' => $this->stats(),
        ];
    }

    /**
     * Update the editable fields of a follow-up.
     *
     * `patient_id` and `visit_id` identify the association the follow-up was
     * created with. They are permanent, so any submitted value is dropped before
     * the row is written. The transition is re-checked after the row is locked:
     * a concurrent completion must not slip between validation and the write.
     *
     * @param  array<string, mixed>  $data
     * @return bool whether the transition was accepted
     */
    public function update(FollowUp $followup, array $data, ?int $actorId): bool
    {
        unset($data['patient_id'], $data['visit_id']);

        return DB::transaction(function () use ($followup, $data, $actorId): bool {
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
            $data['updated_by'] = $actorId;
            $lockedFollowUp->update($data);

            return true;
        });
    }

    /**
     * Mark a scheduled follow-up as done, with the outcome that was reached.
     *
     * The status is checked under the row lock, so two concurrent completions
     * cannot both pass and overwrite the outcome and completion moment.
     *
     * @return bool whether the follow-up was completed
     */
    public function complete(FollowUp $followup, ?string $outcomeNote, ?int $actorId): bool
    {
        return DB::transaction(function () use ($followup, $outcomeNote, $actorId): bool {
            $lockedFollowUp = FollowUp::query()
                ->whereKey($followup->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedFollowUp->status !== 'scheduled') {
                return false;
            }

            $lockedFollowUp->update([
                'outcome_note' => $outcomeNote,
                'status' => 'done',
                'completed_at' => now(),
                'updated_by' => $actorId,
            ]);

            return true;
        });
    }

    /**
     * Move a follow-up to another status.
     *
     * The current status is read after the row is locked, so two concurrent
     * status changes cannot both pass the transition check. The completion
     * moment is written with the status, exactly as update() and complete() do.
     *
     * @return array{outcome: string, current_label: string}
     */
    public function changeStatus(FollowUp $followup, string $status, ?int $actorId): array
    {
        return DB::transaction(function () use ($followup, $status, $actorId): array {
            $lockedFollowUp = FollowUp::query()
                ->whereKey($followup->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $currentLabel = $lockedFollowUp->status_label;

            if ($lockedFollowUp->status === 'done') {
                return ['outcome' => 'done', 'current_label' => $currentLabel];
            }

            if (! $lockedFollowUp->canTransitionTo($status)) {
                return ['outcome' => 'invalid', 'current_label' => $currentLabel];
            }

            $lockedFollowUp->update([
                'status' => $status,
                'completed_at' => $status === 'done' ? ($lockedFollowUp->completed_at ?? now()) : null,
                'updated_by' => $actorId,
            ]);

            return ['outcome' => 'updated', 'current_label' => $currentLabel];
        });
    }

    public function delete(FollowUp $followup): void
    {
        $followup->delete();
    }

    /**
     * The tiles at the top of the register.
     */
    private function stats(): object
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
