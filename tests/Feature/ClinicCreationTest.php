<?php

use App\Models\Clinic;



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
