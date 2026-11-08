<?php

use App\Models\Patient;


function patientCreatePayload(array $overrides = []): array
{
    return [
        'fullname' => 'أحمد عبد الرحمن السيد',
        'phone' => '+201000000007',
        'gender' => 'male',
        'birthdate' => '1990-05-05',
        'blood_group' => 'O+',
        ...$overrides,
    ];
}

describe('registering a patient', function () {
    it('stores the file as active and lists it', function () {
        $admin = actingAsAdmin();

        $this->post(route('patients.store'), patientCreatePayload())
            ->assertRedirect(route('patients.index'))
            ->assertSessionHasNoErrors();

        $patient = Patient::query()->sole();

        expect($patient->status)->toBe('active')
            ->and($patient->created_by)->toBe($admin->id)
            ->and($patient->mrn)->toStartWith('MRN-');

        $this->get(route('patients.index'))
            ->assertOk()
            ->assertSee('أحمد عبد الرحمن السيد');
    });

    it('refuses a second file with the phone number already registered', function () {
        actingAsAdmin();

        $this->post(route('patients.store'), patientCreatePayload())
            ->assertRedirect(route('patients.index'));

        $this->post(route('patients.store'), patientCreatePayload())
            ->assertSessionHasErrors('phone');

        expect(Patient::query()->count())->toBe(1);
    });

    it('registers a different phone number for a new file', function () {
        actingAsAdmin();

        $this->post(route('patients.store'), patientCreatePayload())
            ->assertRedirect(route('patients.index'));

        $this->post(route('patients.store'), patientCreatePayload([
            'phone' => '+201000000008',
        ]))->assertRedirect(route('patients.index'))
            ->assertSessionHasNoErrors();

        expect(Patient::query()->count())->toBe(2);
    });
});

/*
| The uniqueness rule and the unique index both cover soft-deleted rows, so the
| phone of an archived file stays reserved. The correct path is to restore the
| archived file instead of registering a second one with its phone.
*/
describe('phone numbers of archived files', function () {
    it('refuses to recycle the phone of an archived patient', function () {
        actingAsAdmin();
        Patient::factory()->create(['phone' => '+201000000031'])->delete();

        $this->post(route('patients.store'), patientCreatePayload([
            'phone' => '+201000000031',
        ]))->assertSessionHasErrors('phone');

        expect(Patient::withTrashed()->count())->toBe(1);
    });

    it('restores the archived file with its original phone', function () {
        actingAsAdmin();
        $patient = Patient::factory()->create(['phone' => '+201000000032']);
        $patient->delete();

        $this->patch(route('patients.restore', $patient->id))
            ->assertRedirect(route('patients.archived'));

        $patient->refresh();

        expect($patient->phone)->toBe('+201000000032')
            ->and($patient->deleted_at)->toBeNull();
    });

    it('keeps the archived phone reserved after the file is restored', function () {
        actingAsAdmin();
        $patient = Patient::factory()->create(['phone' => '+201000000033']);
        $patient->delete();

        $this->patch(route('patients.restore', $patient->id))
            ->assertRedirect(route('patients.archived'));

        $this->post(route('patients.store'), patientCreatePayload([
            'phone' => '+201000000033',
        ]))->assertSessionHasErrors('phone');

        expect(Patient::query()->count())->toBe(1);
    });
});
