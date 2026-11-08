<?php

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;



describe('the doctor list of a visit form', function () {
    it('keeps the doctor a visit is filed under after that doctor leaves the active list', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create([
            'fullname' => 'د. سامي عبد الرحمن الشاذلي',
            'status' => 'inactive',
        ]);
        $visit = Visit::factory()->recycle([$patient, $doctor, $admin])->create();

        $this->get(route('visits.edit', $visit))
            ->assertOk()
            ->assertSee('د. سامي عبد الرحمن الشاذلي');
    });

  
    it('does not offer a doctor who left the active list for a new visit', function () {
        actingAsAdmin();
        Doctor::factory()->create([
            'fullname' => 'د. نادر فتحي المرسي',
            'status' => 'inactive',
        ]);
        Doctor::factory()->create([
            'fullname' => 'د. هالة سمير الجندي',
            'status' => 'active',
        ]);

        $this->get(route('visits.create'))
            ->assertOk()
            ->assertSee('د. هالة سمير الجندي')
            ->assertDontSee('د. نادر فتحي المرسي');
    });
});

describe('the assignee list of a complaint filter', function () {
    /*
    | The filler names sort before the assignee, so the bounded list stops short
    | of the user the filter selected. The count assertion pins the bound itself,
    | and the id assertion pins the row that has to survive it.
    */
    it('keeps the assignee the filter selected after the staff list grew past the dropdown', function () {
        actingAsAdmin();

        User::factory()->count(501)->create(['name' => 'أحمد التجريبي']);

        $assignee = User::factory()->create(['name' => 'يوسف كمال الدين']);

        $response = $this->get(route('complaints.index', ['assigned_to' => (string) $assignee->id]))
            ->assertOk();

        $users = $response->viewData('users');

        expect($users->count())->toBe(501)
            ->and($users->pluck('id'))->toContain($assignee->id);
    });
});
