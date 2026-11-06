<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\FollowUp;
use App\Models\Visit;
use App\Queries\VisitIndexQuery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VisitService
{
    /**
     * The statuses a visit reaches when its clinical work is over.
     */
    private const CLOSED_STATUSES = ['completed', 'cancelled'];

    public function __construct(
        private VisitIndexQuery $visitIndexQuery,
        private DoctorService $doctorService,
    ) {}

    /**
     * The visit register, filtered by the request.
     *
     * The tiles describe the width of the register before the status filter, so
     * they are read from a clone of the query and the filters are then applied to
     * the listing.
     *
     * @param  array<string, mixed>  $filters
     * @return array{visits: LengthAwarePaginator, doctors: Collection<int, Doctor>, stats: object|null}
     */
    public function paginateIndex(array $filters): array
    {
        $status = $filters['visit_status'] ?? 'all';
        $date = $filters['date'] ?? null;
        $doctorId = $filters['doctor_id'] ?? null;
        $patientId = $filters['patient_id'] ?? null;
        $search = $filters['search'] ?? null;

        $query = Visit::query()
            ->with([
                // `deleted_at` has to travel with the partial select: the row is
                // marked archived (and unlinked) from the model, not from a query.
                'patient:id,fullname,mrn,deleted_at',
                'doctor:id,fullname,clinic_id',
                'doctor.clinic:id,name',
                'creator:id,name',
                'survey',
            ])
            ->latest();

        // The tiles are one aggregate row: the relations the listing eager loads
        // have nothing to attach to and would only run discarded queries.
        $stats = $this->visitIndexQuery->stats((clone $query)->setEagerLoads([]));

        if ($status !== 'all') {
            $query->status($status);
        }

        if ($date) {
            $query->forDate($date);
        }

        if ($doctorId) {
            $query->forDoctor($doctorId);
        }

        if ($patientId) {
            $query->forPatient($patientId);
        }

        if ($search) {
            $query->search($search);
        }

        return [
            'visits' => $query->paginate(20)->withQueryString(),
            'doctors' => $this->doctorService->getDoctorOptions($doctorId === null ? null : (int) $doctorId),
            'stats' => $stats,
        ];
    }

    /**
     * Schedule a visit.
     *
     * The visit form does not submit a status, so a new row always starts
     * `created`.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?int $actorId): Visit
    {
        return Visit::create([
            ...$data,
            'visit_status' => 'created',
            'created_by' => $actorId,
        ]);
    }

    /**
     * Update the editable fields of a visit.
     *
     * The status is re-read under the row lock. A visit can be completed or
     * cancelled between binding the model and writing the update, and the
     * request was validated against the status the row had at that moment.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Visit $visit, array $data, ?int $actorId): void
    {
        DB::transaction(function () use ($visit, $data, $actorId): void {
            $lockedVisit = Visit::query()
                ->whereKey($visit->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $wasClosed = in_array($visit->visit_status, self::CLOSED_STATUSES, true);
            $isClosed = in_array($lockedVisit->visit_status, self::CLOSED_STATUSES, true);

            /*
            | The submitted date, doctor and reason were validated against an open
            | visit. Writing them now would bypass the freeze that applies to a
            | closed visit, so the request is rejected and the user retries on the
            | fresh state.
            */
            if ($isClosed && ! $wasClosed) {
                throw ValidationException::withMessages([
                    'visit' => 'تم إغلاق الزيارة أثناء التعديل. يرجى إعادة المحاولة.',
                ]);
            }

            /*
            | The edit form submits the date without a time. Resubmitting it for a
            | closed visit is allowed, but the stored time is part of the medical
            | history: leaving the key out keeps the original timestamp instead of
            | writing midnight over it.
            */
            if ($isClosed) {
                unset($data['date']);
            }

            $data['updated_by'] = $actorId;
            $lockedVisit->update($data);
        });
    }

    /**
     * Complete or cancel a visit.
     *
     * The row is locked before the status is checked, so two operators acting on
     * the same visit cannot both transition it. Completing with a follow-up
     * writes the follow-up inside the same transaction as the status change, so
     * a visit is never completed without the follow-up it was completed with.
     *
     * @param  array{action: string, due_date?: string|null, reason?: string|null}  $data
     */
    public function changeStatus(Visit $visit, array $data, ?int $actorId): void
    {
        DB::transaction(function () use ($visit, $data, $actorId): void {
            $lockedVisit = Visit::query()
                ->whereKey($visit->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedVisit->visit_status !== 'created') {
                throw ValidationException::withMessages([
                    'visit' => 'لا يمكن تنفيذ الإجراء؛ الزيارة ليست في حالة مجدولة.',
                ]);
            }

            if ($data['action'] === 'cancel') {
                $lockedVisit->update([
                    'visit_status' => 'cancelled',
                    'updated_by' => $actorId,
                ]);

                return;
            }

            if ($data['action'] === 'complete_with_followup') {
                if (FollowUp::query()->where('visit_id', $lockedVisit->id)->exists()) {
                    throw ValidationException::withMessages([
                        'followup' => 'توجد متابعة مسجلة لهذه الزيارة بالفعل.',
                    ]);
                }

                FollowUp::create([
                    'visit_id' => $lockedVisit->id,
                    'patient_id' => $lockedVisit->patient_id,
                    'status' => 'scheduled',
                    'due_date' => $data['due_date'],
                    'reason' => $data['reason'],
                    'created_by' => $actorId,
                ]);
            }

            $lockedVisit->update([
                'visit_status' => 'completed',
                'updated_by' => $actorId,
            ]);
        });
    }

    /**
     * Delete a visit that is not part of the medical history.
     *
     * The status is re-checked under the row lock. A completed visit must not be
     * removed even if it was open when the request started.
     *
     * @return bool whether the visit was deleted
     */
    public function delete(Visit $visit): bool
    {
        return DB::transaction(function () use ($visit): bool {
            $lockedVisit = Visit::query()
                ->whereKey($visit->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedVisit->visit_status === 'completed') {
                return false;
            }

            $lockedVisit->delete();

            return true;
        });
    }
}
