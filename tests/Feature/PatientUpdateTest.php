<?php

use App\Models\Patient;



it('records the acting user and persists every submitted field', function () {
    $admin = actingAsAdmin();
    $patient = Patient::factory()->create();

    $this->put(route('patients.update', $patient), [
        'fullname' => 'منى عبد الرحمن السيد',
        'phone' => '+201000000001',
        'address' => 'شارع النصر، مدينة نصر، القاهرة',
        'gender' => 'female',
        'birthdate' => '1990-05-05',
        'blood_group' => 'O+',
        'allergies' => null,
        'chronic_diseases' => null,
        'emergency_name' => 'أحمد عبد الرحمن السيد',
        'emergency_phone' => '+201000000002',
    ])->assertRedirect(route('patients.index'));

    $this->assertDatabaseHas('patients', [
        'id' => $patient->id,
        'fullname' => 'منى عبد الرحمن السيد',
        'phone' => '+201000000001',
        'gender' => 'female',
        'blood_group' => 'O+',
        'allergies' => null,
        'emergency_name' => 'أحمد عبد الرحمن السيد',
        'updated_by' => $admin->id,
    ]);

    expect($patient->refresh()->birthdate->format('Y-m-d'))->toBe('1990-05-05');
});

/*
| The unique rule on `phone` has to ignore the record being edited, otherwise a
| patient can never be saved without changing their number.
*/
it('accepts the current phone number unchanged', function () {
    $admin = actingAsAdmin();
    $patient = Patient::factory()->create();

    $this->put(route('patients.update', $patient), [
        'fullname' => $patient->fullname,
        'phone' => $patient->phone,
        'address' => $patient->address,
        'gender' => $patient->gender,
        'birthdate' => $patient->birthdate->format('Y-m-d'),
        'blood_group' => $patient->blood_group,
        'allergies' => $patient->allergies,
        'chronic_diseases' => $patient->chronic_diseases,
        'emergency_name' => $patient->emergency_name,
        'emergency_phone' => $patient->emergency_phone,
    ])->assertRedirect(route('patients.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('patients', [
        'id' => $patient->id,
        'phone' => $patient->phone,
        'updated_by' => $admin->id,
    ]);
});

it('redirects a guest from the patient update endpoint to the login page', function () {
    $patient = Patient::factory()->create();

    $this->put(route('patients.update', $patient), ['fullname' => 'اسم غير محفوظ'])
        ->assertRedirect(route('login'));

    $this->assertDatabaseHas('patients', [
        'id' => $patient->id,
        'fullname' => $patient->fullname,
    ]);
});
