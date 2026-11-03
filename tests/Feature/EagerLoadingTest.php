<?php

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Survey;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Regression cover for the pages that loaded a relation once per row.
|
| The visit history on the patient file and on the doctor file renders
| `$visit->patient` for every row, the surveys table renders `$survey->complaint`,
| the clinic list renders `$clinic->doctors` and the user list renders the role of
| every row, but none of those relations was eager loaded: every page came back
| with one extra query per row.
|--------------------------------------------------------------------------
*/

it('loads the patient of every visit on the patient history page', function () {
    $admin = actingAsAdmin();
    $patient = Patient::factory()->create();
    $doctor = Doctor::factory()->create();

    Visit::factory()->count(3)->recycle([$patient, $doctor, $admin])->create();

    $this->get(route('patients.show', $patient))
        ->assertOk()
        ->assertViewHas('visits', fn ($visits): bool => $visits->every(
            fn (Visit $visit): bool => $visit->relationLoaded('patient'),
        ));
});

it('loads the patient of every visit on the doctor history page', function () {
    $admin = actingAsAdmin();
    $patient = Patient::factory()->create();
    $doctor = Doctor::factory()->create();

    Visit::factory()->count(3)->recycle([$patient, $doctor, $admin])->create();

    $this->get(route('doctors.show', $doctor))
        ->assertOk()
        ->assertViewHas('visits', fn ($visits): bool => $visits->every(
            fn (Visit $visit): bool => $visit->relationLoaded('patient'),
        ));
});

it('loads the complaint of every survey on the surveys page', function () {
    $admin = actingAsAdmin();
    $patient = Patient::factory()->create();
    $doctor = Doctor::factory()->create();

    Survey::factory()->count(3)
        ->recycle([$patient, $doctor, $admin])
        ->create(['created_by' => $admin->id]);

    $this->get(route('surveys.index'))
        ->assertOk()
        ->assertViewHas('surveys', fn ($surveys): bool => $surveys->every(
            fn (Survey $survey): bool => $survey->relationLoaded('complaint'),
        ));
});

it('loads the doctors of every clinic on the clinic list', function () {
    actingAsAdmin();
    $clinic = Clinic::factory()->create();

    Doctor::factory()->count(3)->for($clinic)->create();

    $this->get(route('clinics.index'))
        ->assertOk()
        ->assertViewHas('clinics', fn ($clinics): bool => $clinics->every(
            fn (Clinic $clinic): bool => $clinic->relationLoaded('doctors'),
        ));
});

/*
| The view calls `roleNames()` on every row, which loads the roles of that row
| on its own when the list did not eager load them: one extra query per user.
| The eager load is the single query that fetches the roles of the whole page,
| so it is the only role query matching several user ids at once.
*/
it('loads the roles of the whole user list in one query', function () {
    actingAsAdmin();
    User::factory()->count(4)->create();

    $roleQueries = [];

    DB::listen(function (QueryExecuted $query) use (&$roleQueries): void {
        if (str_contains($query->sql, 'model_has_roles')) {
            $roleQueries[] = $query->sql;
        }
    });

    $this->get(route('users.index'))->assertOk();

    $batchLoads = array_filter(
        $roleQueries,
        fn (string $sql): bool => preg_match('/"model_id" in \(\d+(?:, \d+)+\)/', $sql) === 1,
    );

    expect($batchLoads)->toHaveCount(1);
});
