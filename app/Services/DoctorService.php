<?php

namespace App\Services;

use App\Models\Doctor;
use App\Queries\DoctorIndexQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DoctorService
{
    /**
     * The number of doctors a dropdown loads before the directory grows past it.
     * The currently selected doctor is always kept in the list.
     */
    private const OPTION_LIMIT = 500;

    /**
     * The columns a doctor dropdown renders.
     *
     * @var list<string>
     */
    private const OPTION_COLUMNS = ['id', 'fullname', 'clinic_id'];

    public function __construct(private DoctorIndexQuery $doctorIndexQuery) {}


    public function paginateIndex(array $filters): Paginator
    {
        return $this->doctorIndexQuery
            ->build($filters)
            ->simplePaginate(15)
            ->withQueryString();
    }



    /**
     * The doctor options offered by a dropdown: the active directory, bounded and
     * ordered by name.
     *
     * A doctor who is no longer active is not offered for a new assignment, but
     * the one a record already names stays in the list: dropping it would leave
     * the field pointing at an option that is not there, and the browser would
     * submit whichever doctor happened to be first instead.
     *
     * @return Collection<int, Doctor>
     */
    public function getDoctorOptions(?int $selectedDoctorId = null): Collection
    {
        $doctors = $this->optionQuery()
            ->where('status', 'active')
            ->limit(self::OPTION_LIMIT)
            ->get();

        if ($selectedDoctorId === null || $doctors->contains('id', $selectedDoctorId)) {
            return $doctors;
        }

        $selectedDoctor = $this->optionQuery()->find($selectedDoctorId);

        if ($selectedDoctor === null) {
            return $doctors;
        }

        return $doctors
            ->push($selectedDoctor)
            ->sortBy('fullname')
            ->values();
    }

    /**
     * @return Builder<Doctor>
     */
    private function optionQuery(): Builder
    {
        return Doctor::query()
            ->select(self::OPTION_COLUMNS)
            ->with('clinic:id,name')
            ->orderBy('fullname');
    }

    public function getDoctorsCounts()
    {
        return Cache::remember(
            'doctors_counts',
            3600,
            fn() => Doctor::where('status', 'active')->count()
        );
    }

    public function getDoctorsCountsByClinic()
    {
        return Cache::remember(
            'doctors_counts_by_clinic',
            3600,
            fn() => Doctor::query()
                ->where('status', 'active')
                ->groupBy('clinic_id')
                ->selectRaw('clinic_id, COUNT(*) as count')
                ->get()
                ->mapWithKeys(fn($item) => [
                    $item->clinic_id => $item->count,
                ])
                ->all()
        );
    }


    public function create(array $data, ?int $actorId): Doctor
    {
        $doctor = Doctor::create([
            ...$data,
            'created_by' => $actorId,
        ]);

        $this->flushCountCaches();

        return $doctor;
    }

   
    public function update(Doctor $doctor, array $data, ?int $actorId): void
    {
        $doctor->update([
            ...$data,
            'updated_by' => $actorId,
        ]);

        $this->flushCountCaches();
    }


    public function delete(Doctor $doctor)
    {
        DB::transaction(function () use ($doctor) {
            $doctor->update(['status' => 'inactive']);
            $doctor->delete();
        });

        $this->flushCountCaches();
    }

    private function flushCountCaches(): void
    {
        Cache::forget('doctors_counts');
        Cache::forget('doctors_counts_by_clinic');
    }
}
