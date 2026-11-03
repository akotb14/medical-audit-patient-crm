<?php

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;

/*
|--------------------------------------------------------------------------
| Regression cover for the dropdown findings of the code review.
|
| Unbounded lists — the doctor and the assignee dropdowns queried every row of
| their table on every page view. Both are now bounded, projection-limited and
| ordered by a service, which is only safe while the row a record already names
| stays in the list: an option that is stored but not rendered leaves the field
| showing whichever option happens to be first, and the form submits that one
| instead. These tests cover that silent reassignment, and the status filter
| that still keeps a doctor who is no longer active out of a new assignment.
|--------------------------------------------------------------------------
*/

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

    it('still saves a completed visit whose doctor left the active list', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create(['status' => 'inactive']);
        $visit = Visit::factory()->completed()->recycle([$patient, $doctor, $admin])->create();

        $this->put(route('visits.update', $visit), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'date' => $visit->date->toDateTimeString(),
            'reason' => $visit->reason,
        ])->assertSessionHasNoErrors()
            ->assertRedirect(route('visits.index'));

        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'doctor_id' => $doctor->id,
        ]);
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
