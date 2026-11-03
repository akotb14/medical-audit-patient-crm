<?php

use App\Models\Clinic;
use App\Models\Doctor;

/*
|--------------------------------------------------------------------------
| Regression cover for the clinic directory summary.
|
| The average doctors per clinic and the readiness percentage both divide by the
| number of clinics, so a directory with no clinic divided by zero: the page came
| back as a 500 instead of rendering the empty list.
|--------------------------------------------------------------------------
*/

it('renders the directory with no clinic at all', function () {
    actingAsAdmin();

    $this->get(route('clinics.index'))
        ->assertOk()
        ->assertViewHas('clinics', fn ($clinics): bool => $clinics->isEmpty());
});

/*
| The empty case reads 0, so the same two figures still have to be computed from
| the clinics that are listed.
*/
it('prints the averages of the clinics it lists', function () {
    actingAsAdmin();
    $clinics = Clinic::factory()->count(2)->create(['status' => 'active']);
    Doctor::factory()->for($clinics->first())->create(['status' => 'active']);

    $this->get(route('clinics.index'))
        ->assertOk()
        ->assertSee('0.5 طبيب / عيادة')
        ->assertSee('100%');
});
