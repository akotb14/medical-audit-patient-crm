<?php

namespace App\Services;

use App\Models\FollowUp;
use App\Models\Visit;
use App\Queries\VisitQuery;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VisitService
{
    /**
     * The statuses a visit reaches when its clinical work is over.
     */
    private const CLOSED_STATUSES = ['completed', 'cancelled'];

    public function __construct(
        private VisitQuery $visitQuery,
        private DoctorService $doctorService,
    ) {}


    public function paginateIndex(array $filters): array
    {

        $doctorId = $filters['doctor_id'] ?? null;

        $query = Visit::query()
            ->with([
                'patient:id,fullname,mrn,deleted_at',
                'doctor:id,fullname,clinic_id',
                'doctor.clinic:id,name',
                'creator:id,name',
                'survey',
            ])->latest();

        $stats = $this->visitQuery->getStatistics();

        $query = $this->visitQuery->getIndexQuery($query, $filters);

        return [
            'visits' => $query->paginate(20)->withQueryString(),
            'doctors' => $this->doctorService->getDoctorOptions($doctorId === null ? null : (int) $doctorId),
            'stats' => $stats,
        ];
    }


    public function create(array $data, ?int $actorId): Visit
    {
        return Visit::create([
            ...$data,
            'visit_status' => 'created',
            'created_by' => $actorId,
        ]);
    }


    public function update(Visit $visit, array $data, ?int $actorId): void
    {
        DB::transaction(function () use ($visit, $data, $actorId): void {
            $visit = Visit::query()->lockForUpdate()->findOrFail($visit->id);
            if (in_array($visit->visit_status, self::CLOSED_STATUSES, true)) {
                throw ValidationException::withMessages([
                    'visit' => 'لا يمكن تعديل زيارة مكتملة أو ملغاة.',
                ]);
            }
            $data['updated_by'] = $actorId;
            $visit->update($data);
        });
    }


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
