<?php

use App\Models\Clinic;
use App\Models\Doctor;

/*
|--------------------------------------------------------------------------
| Regression cover for the clinic badge of the doctor list.
|
| The badge passed the doctor model to `clinics.show`, so the doctor id was
| used as the clinic id: the link opened a different clinic's page, or failed
| when no clinic carried that id. A doctor with no clinic also had no id to
| link with.
|--------------------------------------------------------------------------
*/

it('links the clinic badge of every doctor to that doctor\'s clinic', function () {
    actingAsAdmin();

    // Two clinics, so the doctor id (1) cannot identify the clinic it belongs to.
    $wrongClinic = Clinic::factory()->create();
    $clinic = Clinic::factory()->create();
    Doctor::factory()->for($clinic)->create();

    $this->get(route('doctors.index'))
        ->assertOk()
        ->assertSee(route('clinics.show', $clinic))
        ->assertDontSee(route('clinics.show', $wrongClinic));
});

it('renders a doctor with no clinic without a link', function () {
    actingAsAdmin();

    Doctor::factory()->withoutClinic()->create();

    $this->get(route('doctors.index'))
        ->assertOk()
        ->assertSee('لا يوجد');
});
