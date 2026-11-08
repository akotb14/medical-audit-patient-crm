<?php

use App\Models\Clinic;
use App\Models\Doctor;



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
