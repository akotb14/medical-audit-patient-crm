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
    /**
     * The number of patients a dropdown loads before the register grows past it.
     * The currently selected patient is always kept in the list.
     */
    private const OPTION_LIMIT = 500;

    /**
     * The columns a patient dropdown renders.
     *
     * @var list<string>
     */
    private const OPTION_COLUMNS = ['id', 'fullname', 'mrn', 'phone'];

    /**
     * The number of times an MRN collision is retried before the registration is
     * refused.
     */
    private const MRN_ATTEMPTS = 5;

    public function __construct(private PatientIndexQuery $patientIndexQuery) {}

    /**
     * The patient register, filtered by the request and limited to active files.
     *
     * @param  array<string, mixed>  $filters
     * @return array{patients: LengthAwarePaginator, stats: object|null}
     */
    public function paginateIndex(array $filters): array
    {
        $patients = $this->patientIndexQuery
            ->build($filters)
            ->where('status', 'active')
            ->paginate(20)
            ->withQueryString();

        return [
            'patients' => $patients,
            'stats' => Patient::stats()->first(),
        ];
    }

    /**
     * The archived (soft deleted) patient files, most recently archived first.
     */
    public function paginateArchived(): LengthAwarePaginator
    {
        return Patient::onlyTrashed()
            ->with('deleter')
            ->latest('deleted_at')
            ->paginate(20);
    }

    /**
     * Register a patient file under a generated MRN.
     *
     * `patients.status` has no database default and the create form does not
     * submit one, so a newly registered file always starts active. Another
     * registration can take the generated MRN between the uniqueness check and
     * the insert, so only that collision is retried; a violation on any other
     * unique column is rethrown unchanged.
     *
     * @param  array<string, mixed>  $data
     */
    public function register(array $data, ?int $actorId): Patient
    {
        $data['created_by'] = $actorId;
        $data['status'] = 'active';

        $attempts = 0;

        while (true) {
            $attempts++;

            do {
                $mrn = 'MRN-'.strtoupper(Str::random(8));
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

    /**
     * Archive a patient file, attributing the archive in the audit column.
     *
     * Archiving is two writes: `runSoftDelete()` only persists `deleted_at` and
     * `updated_at`, so the audit column is stored on its own first. Both inside
     * one transaction, or a patient ends up archived with nobody attributed.
     */
    public function archive(Patient $patient, ?int $actorId): void
    {
        DB::transaction(function () use ($patient, $actorId): void {
            $patient->forceFill(['deleted_by' => $actorId])->save();
            $patient->delete();
        });
    }

    /**
     * Restore an archived patient file and clear the archive attribution.
     */
    public function restore(Patient $patient): void
    {
        // `restore()` runs a single UPDATE, so both columns clear together.
        $patient->forceFill(['deleted_by' => null]);
        $patient->restore();
    }

    /**
     * The patient's visit history, with the status counts its tiles describe.
     *
     * The tiles describe the whole history, so they cannot be read off the
     * paginator: one grouped aggregate keeps them independent of the page.
     *
     * @return array{visits: LengthAwarePaginator, visitCounts: array{created: int, completed: int, cancelled: int}}
     */
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

    /**
     * The patient options offered by a dropdown, bounded and ordered by name.
     *
     * @return Collection<int, Patient>
     */
    public function getPatientOptions(?int $selectedPatientId = null): Collection
    {
        $patients = Patient::query()
            ->select(self::OPTION_COLUMNS)
            ->orderBy('fullname')
            ->limit(self::OPTION_LIMIT)
            ->get();

        if ($selectedPatientId === null || $patients->contains('id', $selectedPatientId)) {
            return $patients;
        }

        $selectedPatient = Patient::query()
            ->select(self::OPTION_COLUMNS)
            ->find($selectedPatientId);

        if ($selectedPatient === null) {
            return $patients;
        }

        return $patients
            ->push($selectedPatient)
            ->sortBy('fullname')
            ->values();
    }

    /**
     * A single patient option, used when the create form is opened for one file.
     *
     * @return Collection<int, Patient>
     */
    public function getPatientOption(int $patientId): Collection
    {
        return Patient::query()
            ->select(self::OPTION_COLUMNS)
            ->whereKey($patientId)
            ->get();
    }
}
