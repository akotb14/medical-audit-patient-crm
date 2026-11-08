<?php

namespace App\Services;

use App\Models\FollowUp;
use App\Queries\FollowUpQuery;
use Illuminate\Support\Facades\DB;

class FollowUpService
{
    private const TRANSITIONS = [
        'scheduled' => ['done', 'missed', 'cancelled'],
        'missed' => ['scheduled', 'cancelled'],
        'cancelled' => ['scheduled'],
        'done' => [],
    ];
    public function canTransitionTo(string $status , string $currentStatus): bool
    {
        if ($status === $currentStatus) {
            return true;
        }

        return in_array($status, self::TRANSITIONS[$currentStatus] ?? [], true);
    }
    public function __construct(private FollowUpQuery $followUpQuery) {}
    public function paginateIndex(array $filters): array
    {
        $query = FollowUp::with(['patient', 'visit.doctor.clinic', 'creator'])
            ->latest('due_date');

        $query = $this->followUpQuery->getIndexQuery($query, $filters);

        return [
            'followups' => $query->paginate(15)->withQueryString(),
            'stats' => $this->followUpQuery->getStatistics(),
        ];
    }

    public function update(FollowUp $followup, array $data, ?int $actorId): bool
    {
        unset($data['patient_id'], $data['visit_id']);

        return DB::transaction(function () use ($followup, $data, $actorId): bool {
            $lockedFollowUp = FollowUp::query()
                ->whereKey($followup->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $this->canTransitionTo((string) $data['status'] ,(string) $lockedFollowUp->status)) {
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

            if (! $this->canTransitionTo($status , (string) $lockedFollowUp->status)) {
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
}
