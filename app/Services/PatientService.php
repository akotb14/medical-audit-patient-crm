<?php

namespace App\Services;

use App\Models\Patient;
use App\Queries\PatientIndexQuery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PatientService
{
    //The number of patients a dropdown loads before the register grows past it.
    private const OPTION_LIMIT = 500;

    private const MRN_ATTEMPTS = 5;

    public function __construct(private PatientIndexQuery $patientIndexQuery) {}


    public function paginateIndex(array $filters): array
    {
        $patients = $this->patientIndexQuery
            ->build($filters)
            ->where('status', 'active')
            ->paginate(20)
            ->withQueryString();

        return [
            'patients' => $patients,
        ];
    }

    public function paginateArchived(): LengthAwarePaginator
    {
        return Patient::onlyTrashed()
            ->with('deleter')
            ->latest('deleted_at')
            ->paginate(20);
    }


    public function register(array $data, ?int $actorId): Patient
    {
        $data['created_by'] = $actorId;
        $data['status'] = 'active';

        $attempts = 0;

        while (true) {
            $attempts++;

            do {
                $mrn = 'MRN-' . strtoupper(Str::random(12));
            } while (Patient::withTrashed()->where('mrn', $mrn)->exists());

            $data['mrn'] = $mrn;

            try {
                return Patient::create($data);
            } catch (UniqueConstraintViolationException $exception) {
                if (! Patient::withTrashed()->where('mrn', $mrn)->exists()) {
                    throw $exception;
                }

                if ($attempts >= self::MRN_ATTEMPTS) {
                    throw ValidationException::withMessages([
                        'mrn' => 'تعذر توليد رقم ملف طبي فريد. يرجى إعادة المحاولة.',
                    ]);
                }
            }
        }
    }


    public function archive(Patient $patient, ?int $actorId): void
    {
        DB::transaction(function () use ($patient, $actorId): void {
            $patient->forceFill(['deleted_by' => $actorId])->save();
            $patient->delete();
        });
    }


    public function restore(Patient $patient): void
    {
        $patient->forceFill(['deleted_by' => null]);
        $patient->restore();
    }

    public function paginateVisitHistory(Patient $patient): array
    {
        $visitsQuery = $patient->visits();

        $countsByStatus = (clone $visitsQuery)
            ->toBase()
            ->selectRaw('visit_status, COUNT(*) as aggregate')
            ->groupBy('visit_status')
            ->pluck('aggregate', 'visit_status');

        $visits = $visitsQuery
            ->with([
                'patient:id,fullname,mrn,deleted_at',
                'doctor.clinic',
                'creator',
                'survey',
            ])
            ->latest()
            ->paginate(20);

        return [
            'visits' => $visits,
            'visitCounts' => [
                'created' => (int) ($countsByStatus['created'] ?? 0),
                'completed' => (int) ($countsByStatus['completed'] ?? 0),
                'cancelled' => (int) ($countsByStatus['cancelled'] ?? 0),
            ],
        ];
    }


    public function getPatientOptions(): Collection
    {
       return Patient::query()
            ->select(['id', 'fullname', 'mrn', 'phone'])
            ->latest()
            ->limit(self::OPTION_LIMIT)
            ->get();

    }


    public function getPatientOption(int $patientId):?Patient
    {
        return Patient::query()
            ->select(['id', 'fullname', 'mrn', 'phone'])
            ->whereKey($patientId)
            ->first();
    }
}
