<?php

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Visit;



it('refuses to delete a doctor who has visits', function () {
    $admin = actingAsAdmin();
    $patient = Patient::factory()->create();
    $doctor = Doctor::factory()->create();

    Visit::factory()->recycle([$patient, $doctor, $admin])->create();

    $this->delete(route('doctors.destroy', $doctor))
        ->assertRedirect(route('doctors.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('doctors', ['id' => $doctor->id ]);
});

it('deletes a doctor who has no visits', function () {
    actingAsAdmin();
    $doctor = Doctor::factory()->create();

    $this->delete(route('doctors.destroy', $doctor))
        ->assertRedirect(route('doctors.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('doctors', ['id' => $doctor->id ,'deleted_at' => null]);
});

it('refuses to delete a clinic that still has doctors', function () {
    actingAsAdmin();
    $clinic = Clinic::factory()->create();

    Doctor::factory()->for($clinic)->create();

    $this->delete(route('clinics.destroy', $clinic))
        ->assertRedirect(route('clinics.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('clinics', ['id' => $clinic->id]);
});

it('deletes a clinic with no doctors', function () {
    actingAsAdmin();
    $clinic = Clinic::factory()->create();

    $this->delete(route('clinics.destroy', $clinic))
        ->assertRedirect(route('clinics.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('clinics', ['id' => $clinic->id]);
});

/*
| Deactivating a doctor is the supported alternative to deleting one, so the
| status must not weaken the history guard in either direction.
*/
it('refuses to delete an inactive doctor who has visits', function () {
    $admin = actingAsAdmin();
    $patient = Patient::factory()->create();
    $doctor = Doctor::factory()->create(['status' => 'inactive']);

    Visit::factory()->recycle([$patient, $doctor, $admin])->create();

    $this->delete(route('doctors.destroy', $doctor))
        ->assertRedirect(route('doctors.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('doctors', [
        'id' => $doctor->id,
        'status' => 'inactive',
    ]);
});

it('deletes an inactive doctor who has no visits', function () {
    actingAsAdmin();
    $doctor = Doctor::factory()->create(['status' => 'inactive']);

    $this->delete(route('doctors.destroy', $doctor))
        ->assertRedirect(route('doctors.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('doctors', ['id' => $doctor->id , 'deleted_at' => null]);
});

it('refuses to delete a clinic whose doctors are all inactive', function () {
    actingAsAdmin();
    $clinic = Clinic::factory()->create();

    Doctor::factory()->for($clinic)->create(['status' => 'inactive']);

    $this->delete(route('clinics.destroy', $clinic))
        ->assertRedirect(route('clinics.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('clinics', ['id' => $clinic->id]);
});
