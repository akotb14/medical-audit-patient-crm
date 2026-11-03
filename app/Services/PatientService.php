<?php

namespace App\Services;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Collection;

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
