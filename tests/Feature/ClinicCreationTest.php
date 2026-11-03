<?php

use App\Models\Clinic;

/*
|--------------------------------------------------------------------------
| Regression cover for creating a clinic.
|
| The create form submits the name and the description only. The controller
| used to add a `user_id` key, which the model does not fill, and left
| `clinics.created_by` unset: the column is NOT NULL with no default, so MySQL
| in strict mode refused the insert instead of creating the clinic.
|--------------------------------------------------------------------------
*/

it('creates a clinic and attributes it to the signed in administrator', function () {
    $admin = actingAsAdmin();

    $this->post(route('clinics.store'), [
        'name' => 'عيادة الباطنة',
        'description' => 'متابعة أمراض الباطنة المزمنة والحالات العامة للبالغين.',
    ])->assertRedirect(route('clinics.index'))
        ->assertSessionHas('success');

    $clinic = Clinic::query()->where('name', 'عيادة الباطنة')->firstOrFail();

    expect($clinic->created_by)->toBe($admin->id)
        ->and($clinic->status)->toBe('active');
});
