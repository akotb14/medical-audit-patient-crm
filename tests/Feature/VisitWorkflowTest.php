<?php

use App\Models\Complaint;
use App\Models\Doctor;
use App\Models\FollowUp;
use App\Models\Patient;
use App\Models\Survey;
use App\Models\Visit;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Route;

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
| naming two different patients and split the per-patient history. The patient is
| now frozen as soon as the visit leaves `created`.
|
| The code review added the follow-up reason length on the completion endpoint:
| the rule allowed 1000 characters into the 500 the column holds.
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

    /*
    | The scheduled reason is stored in `follow_ups.reason`, which holds 500
    | characters. The rule allowed 1000, so the upper half of that range reached
    | MySQL and came back as a "Data too long" error instead of a validation
    | message.
    */
    it('accepts a follow-up reason as long as the stored column', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->recycle([$patient, $doctor, $admin])->create();

        $this->from(route('visits.index'))
            ->patch(route('visits.status', $visit), [
                'action' => 'complete_with_followup',
                'due_date' => now()->addDays(7)->toDateTimeString(),
                'reason' => str_repeat('م', 500),
            ])
            ->assertRedirect(route('visits.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('follow_ups', [
            'visit_id' => $visit->id,
            'reason' => str_repeat('م', 500),
        ]);
    });

    it('refuses a follow-up reason longer than the stored column', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->recycle([$patient, $doctor, $admin])->create();

        $this->from(route('visits.index'))
            ->patch(route('visits.status', $visit), [
                'action' => 'complete_with_followup',
                'due_date' => now()->addDays(7)->toDateTimeString(),
                'reason' => str_repeat('م', 501),
            ])
            ->assertSessionHasErrors('reason');

        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'visit_status' => 'created',
        ]);

        expect(FollowUp::query()->where('visit_id', $visit->id)->exists())->toBeFalse();
    });
});

/*
| The patient of a visit is frozen the moment the visit leaves `created`. The
| survey, the complaint raised from it and the follow-up that may hang off it
| each keep their own copy of the patient, taken from the visit when they were
| created, so moving a closed visit would leave one clinical event naming two
| patients. Nothing is rewritten when the change is refused: the visit keeps the
| patient its derived records were recorded against. A visit that is still open
| stays correctable, so a booking filed under the wrong patient can be repaired.
*/
describe('reassigning the patient on a visit', function () {
    it('refuses to move a completed visit that nothing was derived from', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $otherPatient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->completed()->recycle([$patient, $doctor, $admin])->create();

        $this->from(route('visits.edit', $visit))
            ->put(
                route('visits.update', $visit),
                visitUpdatePayload($visit, $otherPatient, $doctor, $visit->reason),
            )
            ->assertSessionHasErrors('patient_id');

        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'visit_status' => 'completed',
            'patient_id' => $patient->id,
        ]);
    });

    it('refuses to move a cancelled visit that nothing was derived from', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $otherPatient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->cancelled()->recycle([$patient, $doctor, $admin])->create();

        $this->from(route('visits.edit', $visit))
            ->put(
                route('visits.update', $visit),
                visitUpdatePayload($visit, $otherPatient, $doctor, $visit->reason),
            )
            ->assertSessionHasErrors('patient_id');

        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'visit_status' => 'cancelled',
            'patient_id' => $patient->id,
        ]);
    });

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
    | A visit that never left `created` has no derived record to split, so the
    | booking can still be repaired by moving it to the patient it belongs to.
    */
    it('still moves a visit that is still open', function () {
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

    /*
    | The update endpoint ignores `visit_status`: the status travels through the
    | completion and cancellation endpoints only. Sending `created` back with the
    | payload therefore cannot reopen the visit, and the freeze still applies.
    */
    it('refuses the change when an open status arrives with the payload', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $otherPatient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->completed()->recycle([$patient, $doctor, $admin])->create();

        $this->from(route('visits.edit', $visit))
            ->put(route('visits.update', $visit), [
                ...visitUpdatePayload($visit, $otherPatient, $doctor, $visit->reason),
                'visit_status' => 'created',
            ])
            ->assertSessionHasErrors('patient_id');

        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'visit_status' => 'completed',
            'patient_id' => $patient->id,
        ]);
    });

    /*
    | The visit is closed while the edit form is in flight. The request was
    | validated against the open visit the form was built from, so the patient
    | change has to be refused once the row is re-read under the lock.
    */
    it('refuses the change when the visit closes between validation and save', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $otherPatient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->recycle([$patient, $doctor, $admin])->create();

        // The model the request validates against still reads `created`.
        $staleVisit = Visit::query()->findOrFail($visit->id);

        Visit::query()->whereKey($visit->id)->update(['visit_status' => 'completed']);

        Route::bind('visit', fn () => $staleVisit);

        $this->from(route('visits.edit', $visit))
            ->put(
                route('visits.update', $visit),
                visitUpdatePayload($staleVisit, $otherPatient, $doctor, $staleVisit->reason),
            )
            ->assertSessionHasErrors('visit');

        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'visit_status' => 'completed',
            'patient_id' => $patient->id,
        ]);
    });

});

/*
| A completed or cancelled visit is part of the medical history. The doctor who
| saw the patient, the day of the visit and the reason for it are frozen from
| then on, so reopening the edit form cannot rewrite the closed event.
| Resubmitting the stored values stays allowed, and an open visit stays fully
| editable.
*/
describe('rewriting a closed visit', function () {
    it('refuses to change the doctor of a completed visit', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $otherDoctor = Doctor::factory()->create();
        $visit = Visit::factory()->completed()->recycle([$patient, $doctor, $admin])->create();

        $this->put(route('visits.update', $visit), [
            'patient_id' => $patient->id,
            'doctor_id' => $otherDoctor->id,
            'date' => $visit->date->toDateTimeString(),
            'reason' => $visit->reason,
        ])->assertSessionHasErrors('doctor_id');

        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'doctor_id' => $doctor->id,
        ]);
    });

    it('refuses to change the date or the reason of a cancelled visit', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->cancelled()->recycle([$patient, $doctor, $admin])->create();

        $this->put(route('visits.update', $visit), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'date' => $visit->date->copy()->addWeek()->toDateString(),
            'reason' => 'سبب مختلف تمامًا عن السبب المسجل',
        ])->assertSessionHasErrors(['date', 'reason']);

        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'date' => $visit->date->toDateTimeString(),
            'reason' => $visit->reason,
        ]);
    });

    it('still saves a closed visit that is resubmitted unchanged', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->completed()->recycle([$patient, $doctor, $admin])->create();

        $this->put(
            route('visits.update', $visit),
            visitUpdatePayload($visit, $patient, $doctor, $visit->reason),
        )->assertRedirect(route('visits.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'doctor_id' => $doctor->id,
            'reason' => $visit->reason,
            'updated_by' => $admin->id,
        ]);
    });

    it('keeps an open visit fully editable', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $otherDoctor = Doctor::factory()->create();
        $visit = Visit::factory()->recycle([$patient, $doctor, $admin])->create();

        $this->put(route('visits.update', $visit), [
            'patient_id' => $patient->id,
            'doctor_id' => $otherDoctor->id,
            'date' => $visit->date->toDateString(),
            'reason' => 'سبب معدل قبل الزيارة',
        ])->assertRedirect(route('visits.index'));

        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'doctor_id' => $otherDoctor->id,
            'reason' => 'سبب معدل قبل الزيارة',
        ]);
    });
});
