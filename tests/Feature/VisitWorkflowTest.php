<?php

use App\Models\Complaint;
use App\Models\Doctor;
use App\Models\FollowUp;
use App\Models\Patient;
use App\Models\Survey;
use App\Models\Visit;
use Illuminate\Database\QueryException;

/*
|--------------------------------------------------------------------------
| Regression cover for REVIEW-002, REVIEW-007, REVIEW-010 and REVIEW-NEW-004.
|
| REVIEW-002 — the delete policy refused every visit, including the ones that
| were still open.
| REVIEW-007 — the update endpoint accepted `visit_status` straight from the
| payload and bypassed the status workflow.
| REVIEW-010 — a visit could collect more than one follow-up.
| REVIEW-NEW-004 — the edit form offered every patient for the visit, so a visit
| could be moved to another patient after it was closed. The survey, the complaint
| raised from it and the follow-up each keep their own copy of the patient, taken
| from the visit when they were created, so the move left a single clinical event
| naming two different patients and split the per-patient history.
|
| The rule that a cancelled visit may be deleted is deliberately not asserted
| here: it is REVIEW-NEW-003 and is still an open business decision.
|--------------------------------------------------------------------------
*/

/**
 * The payload the visit edit form submits.
 *
 * @return array<string, mixed>
 */
function visitUpdatePayload(Visit $visit, Patient $patient, Doctor $doctor, string $reason): array
{
    return [
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'date' => $visit->date->toDateTimeString(),
        'reason' => $reason,
    ];
}

describe('deleting a visit', function () {
    it('deletes a visit that is still awaiting the patient', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->recycle([$patient, $doctor, $admin])->create();

        $this->delete(route('visits.destroy', $visit))
            ->assertRedirect(route('visits.index'));

        $this->assertDatabaseMissing('visits', ['id' => $visit->id]);
    });

    it('refuses to delete a completed visit', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->completed()->recycle([$patient, $doctor, $admin])->create();

        $this->delete(route('visits.destroy', $visit))->assertForbidden();

        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'visit_status' => 'completed',
        ]);
    });

    it('forbids a receptionist from deleting an open visit', function () {
        $receptionist = actingAsReceptionist();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->recycle([$patient, $doctor, $receptionist])->create();

        $this->delete(route('visits.destroy', $visit))->assertForbidden();

        $this->assertDatabaseHas('visits', ['id' => $visit->id]);
    });

    it('redirects a guest from the visit delete endpoint to the login page', function () {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->recycle([$patient, $doctor])->create();

        $this->delete(route('visits.destroy', $visit))->assertRedirect(route('login'));

        $this->assertDatabaseHas('visits', ['id' => $visit->id]);
    });
});

describe('updating a visit', function () {
    it('ignores a visit status supplied through the update endpoint', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->recycle([$patient, $doctor, $admin])->create();

        $this->put(route('visits.update', $visit), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'date' => $visit->date->toDateTimeString(),
            'reason' => 'متابعة نتائج التحاليل بعد أسبوع',
            'visit_status' => 'completed',
        ])->assertRedirect(route('visits.index'));

        // The submitted fields persist; only the status is discarded.
        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'reason' => 'متابعة نتائج التحاليل بعد أسبوع',
            'visit_status' => 'created',
        ]);
    });
});

describe('completing a visit', function () {
    it('completes the visit and schedules exactly one follow-up', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->recycle([$patient, $doctor, $admin])->create();

        $this->from(route('visits.index'))
            ->patch(route('visits.status', $visit), [
                'action' => 'complete_with_followup',
                'due_date' => now()->addDays(7)->toDateTimeString(),
                'reason' => 'مراجعة نتائج التحاليل',
            ])
            ->assertRedirect(route('visits.index'));

        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'visit_status' => 'completed',
        ]);

        $this->assertDatabaseHas('follow_ups', [
            'visit_id' => $visit->id,
            'patient_id' => $patient->id,
            'status' => 'scheduled',
            'created_by' => $admin->id,
        ]);

        expect(FollowUp::query()->where('visit_id', $visit->id)->count())->toBe(1);
    });

    /*
    | Sequential duplicate prevention. The second submission is rejected by the
    | status guard inside the transaction, so this proves the workflow cannot be
    | replayed. It says nothing about row locking under real concurrency.
    */
    it('refuses a second completion and keeps the single follow-up', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->recycle([$patient, $doctor, $admin])->create();

        $this->from(route('visits.index'))
            ->patch(route('visits.status', $visit), [
                'action' => 'complete_with_followup',
                'due_date' => now()->addDays(7)->toDateTimeString(),
                'reason' => 'مراجعة نتائج التحاليل',
            ])
            ->assertRedirect(route('visits.index'));

        $this->from(route('visits.index'))
            ->patch(route('visits.status', $visit), [
                'action' => 'complete_with_followup',
                'due_date' => now()->addDays(14)->toDateTimeString(),
                'reason' => 'محاولة ثانية',
            ])
            ->assertSessionHasErrors('visit');

        expect(FollowUp::query()->where('visit_id', $visit->id)->count())->toBe(1);

        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'visit_status' => 'completed',
        ]);
    });

    /*
    | The unique index on follow_ups.visit_id is the backstop the workflow relies
    | on, so it is asserted directly rather than only through the controller.
    */
    it('rejects a second follow-up for the same visit at the database level', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->completed()->recycle([$patient, $doctor, $admin])->create();

        FollowUp::factory()->forVisit($visit)->create();

        expect(fn () => FollowUp::factory()->forVisit($visit)->create())
            ->toThrow(QueryException::class);

        expect(FollowUp::query()->where('visit_id', $visit->id)->count())->toBe(1);
    });

    it('cannot be replayed once the visit was cancelled', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->cancelled()->recycle([$patient, $doctor, $admin])->create();

        $this->from(route('visits.index'))
            ->patch(route('visits.status', $visit), [
                'action' => 'complete_only',
            ])
            ->assertSessionHasErrors('visit');

        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'visit_status' => 'cancelled',
        ]);

        expect(FollowUp::query()->where('visit_id', $visit->id)->exists())->toBeFalse();
    });
});

/*
| The patient of a visit is frozen once a record was derived from it. Nothing is
| rewritten when the change is refused: the visit keeps the patient its survey,
| its complaint and its follow-up were recorded against, so the file the history
| belongs to never changes under them.
*/
describe('reassigning the patient on a visit', function () {
    it('refuses to move a closed visit that already carries a follow-up', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $otherPatient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->completed()->recycle([$patient, $doctor, $admin])->create();

        FollowUp::factory()->forVisit($visit)->create();

        $this->from(route('visits.edit', $visit))
            ->put(
                route('visits.update', $visit),
                visitUpdatePayload($visit, $otherPatient, $doctor, $visit->reason),
            )
            ->assertSessionHasErrors('patient_id');

        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'patient_id' => $patient->id,
        ]);

        $this->assertDatabaseHas('follow_ups', [
            'visit_id' => $visit->id,
            'patient_id' => $patient->id,
        ]);
    });

    it('refuses to move a visit that carries a survey and the complaint raised from it', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $otherPatient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->completed()->recycle([$patient, $doctor, $admin])->create();

        $survey = Survey::factory()->forVisit($visit)->create();
        $complaint = Complaint::factory()->fromSurvey($survey)->unassigned()->create();

        $this->from(route('visits.edit', $visit))
            ->put(
                route('visits.update', $visit),
                visitUpdatePayload($visit, $otherPatient, $doctor, $visit->reason),
            )
            ->assertSessionHasErrors('patient_id');

        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'patient_id' => $patient->id,
        ]);

        $this->assertDatabaseHas('surveys', [
            'id' => $survey->id,
            'visit_id' => $visit->id,
            'patient_id' => $patient->id,
        ]);

        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'patient_id' => $patient->id,
        ]);
    });

    /*
    | The guard is about the derived records, not about the status: a visit no
    | survey or follow-up was built from stays correctable, so a booking made
    | against the wrong patient can still be repaired.
    */
    it('still moves a visit that nothing was derived from', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $otherPatient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->recycle([$patient, $doctor, $admin])->create();

        $this->put(
            route('visits.update', $visit),
            visitUpdatePayload($visit, $otherPatient, $doctor, $visit->reason),
        )->assertRedirect(route('visits.index'));

        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'patient_id' => $otherPatient->id,
            'updated_by' => $admin->id,
        ]);
    });

    it('keeps the rest of a closed visit editable while the patient stays the same', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->completed()->recycle([$patient, $doctor, $admin])->create();

        FollowUp::factory()->forVisit($visit)->create();

        $this->put(
            route('visits.update', $visit),
            visitUpdatePayload($visit, $patient, $doctor, 'تصحيح سبب الزيارة بعد مراجعة الملف'),
        )->assertRedirect(route('visits.index'));

        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'patient_id' => $patient->id,
            'reason' => 'تصحيح سبب الزيارة بعد مراجعة الملف',
            'visit_status' => 'completed',
            'updated_by' => $admin->id,
        ]);
    });
});
