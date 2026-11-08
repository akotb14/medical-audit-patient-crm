<?php

use App\Models\Complaint;
use App\Models\Doctor;
use App\Models\FollowUp;
use App\Models\Patient;
use App\Models\Survey;
use App\Models\Visit;
use App\Queries\PatientQuery;

function archivePatientWithHistory(): array
{
    $patient = Patient::factory()->create(['fullname' => 'منى عبد الرحمن السيد']);
    $visit = Visit::factory()->completed()->create(['patient_id' => $patient->id]);

    test()->delete(route('patients.destroy', $patient))->assertRedirect();

    return [$patient, $visit];
}

describe('archiving a patient file', function () {
    it('archives the row and records the acting administrator instead of deleting it', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();

        $this->delete(route('patients.destroy', $patient))
            ->assertRedirect()
            ->assertSessionHas('success', 'تم أرشفة المريض بنجاح');

        $this->assertSoftDeleted('patients', [
            'id' => $patient->id,
            'deleted_by' => $admin->id,
        ]);
    });

    /*
    | The audit column is written before the soft delete, so the two only survive
    | together when both stay inside the same transaction.
    */
    it('keeps neither column when the archive fails halfway', function () {
        actingAsAdmin();
        $patient = Patient::factory()->create();

        Patient::deleting(function (): void {
            throw new RuntimeException('فشل الأرشفة أثناء التنفيذ');
        });

        $this->withoutExceptionHandling();

        try {
            $this->delete(route('patients.destroy', $patient));
            $this->fail('The archive was expected to fail.');
        } catch (RuntimeException $exception) {
            expect($exception->getMessage())->toBe('فشل الأرشفة أثناء التنفيذ');
        }

        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'deleted_at' => null,
            'deleted_by' => null,
        ]);
    });

    it('forbids a receptionist from archiving a patient file', function () {
        actingAsReceptionist();
        $patient = Patient::factory()->create();

        $this->delete(route('patients.destroy', $patient))->assertForbidden();

        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'deleted_at' => null,
            'deleted_by' => null,
        ]);
    });

    it('sends a guest to the login page instead of archiving', function () {
        $patient = Patient::factory()->create();

        $this->delete(route('patients.destroy', $patient))->assertRedirect(route('login'));

        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'deleted_at' => null,
        ]);
    });
});

describe('the archived files list', function () {
    it('shows the archived file and its archiver to an administrator', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create(['fullname' => 'منى عبد الرحمن السيد']);
        $open = Patient::factory()->create(['fullname' => 'سليمان فهد المنصور']);

        $this->delete(route('patients.destroy', $patient));

        $this->get(route('patients.archived'))
            ->assertOk()
            ->assertSee('منى عبد الرحمن السيد')
            ->assertSee($admin->name)
            ->assertSee('استعادة الملف')
            ->assertDontSee($open->fullname);
    });

    it('offers the archived files to an administrator and hides them from a receptionist', function () {
        actingAsAdmin();

        $this->get(route('patients.index'))
            ->assertOk()
            ->assertSee(route('patients.archived'));

        actingAsReceptionist();

        $this->get(route('patients.index'))
            ->assertOk()
            ->assertDontSee(route('patients.archived'));
    });

    it('forbids a receptionist from browsing the archived files', function () {
        actingAsReceptionist();

        $this->get(route('patients.archived'))->assertForbidden();
    });

    it('sends a guest to the login page instead of the archived files', function () {
        $this->get(route('patients.archived'))->assertRedirect(route('login'));
    });

    it('does not expose an archived file on the profile or the edit screen', function () {
        actingAsAdmin();
        $patient = Patient::factory()->create();

        $this->delete(route('patients.destroy', $patient));

        $this->get(route('patients.show', $patient))->assertNotFound();
        $this->get(route('patients.edit', $patient))->assertNotFound();
    });
});

describe('restoring an archived file', function () {
    it('clears the archive state without reopening the patient status', function () {
        actingAsAdmin();
        $patient = Patient::factory()->inactive()->create();

        $this->delete(route('patients.destroy', $patient));

        $this->patch(route('patients.restore', $patient))
            ->assertRedirect(route('patients.archived'))
            ->assertSessionHas('success', 'تم استعادة المريض بنجاح');

        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'status' => 'inactive',
            'deleted_at' => null,
            'deleted_by' => null,
        ]);
    });

    it('forbids a receptionist from restoring an archived file', function () {
        actingAsAdmin();
        $patient = Patient::factory()->create();

        $this->delete(route('patients.destroy', $patient));

        actingAsReceptionist();

        $this->patch(route('patients.restore', $patient))->assertForbidden();

        expect($patient->refresh()->trashed())->toBeTrue();
    });
});

/*
| The archived file has to stay readable where the history refers to it, but the
| name must not link to a profile that answers 404.
*/
describe('an archived patient on the clinical screens', function () {
    it('stays readable on the visit screens', function () {
        actingAsAdmin();
        [$patient, $visit] = archivePatientWithHistory();

        $this->get(route('visits.index'))
            ->assertOk()
            ->assertSee('منى عبد الرحمن السيد')
            ->assertSee('(مؤرشف)')
            ->assertDontSee(route('patients.show', $patient));

        $this->get(route('visits.show', $visit))
            ->assertOk()
            ->assertSee('منى عبد الرحمن السيد')
            ->assertSee('(مؤرشف)')
            ->assertDontSee(route('patients.show', $patient));
    });

    it('stays readable on the follow-up screens', function () {
        actingAsAdmin();
        [$patient, $visit] = archivePatientWithHistory();
        $followup = FollowUp::factory()->forVisit($visit)->create();

        $this->get(route('follow_ups.index'))
            ->assertOk()
            ->assertSee('منى عبد الرحمن السيد')
            ->assertSee('(مؤرشف)')
            ->assertDontSee(route('patients.show', $patient));

        $this->get(route('follow_ups.show', $followup))
            ->assertOk()
            ->assertSee('منى عبد الرحمن السيد')
            ->assertSee('(مؤرشف)')
            ->assertDontSee(route('patients.show', $patient));
    });

    it('stays readable on the survey screens', function () {
        actingAsAdmin();
        [$patient, $visit] = archivePatientWithHistory();
        $survey = Survey::factory()->forVisit($visit)->create();

        $this->get(route('surveys.index'))
            ->assertOk()
            ->assertSee('منى عبد الرحمن السيد')
            ->assertSee('(مؤرشف)')
            ->assertDontSee(route('patients.show', $patient));

        $this->get(route('surveys.show', $survey))
            ->assertOk()
            ->assertSee('منى عبد الرحمن السيد')
            ->assertSee('(مؤرشف)')
            ->assertDontSee(route('patients.show', $patient));
    });

    it('stays readable on the complaint screens', function () {
        actingAsAdmin();
        [$patient] = archivePatientWithHistory();
        $complaint = Complaint::factory()->create(['patient_id' => $patient->id]);

        $this->get(route('complaints.index'))
            ->assertOk()
            ->assertSee('منى عبد الرحمن السيد')
            ->assertSee('(مؤرشف)')
            ->assertDontSee(route('patients.show', $patient));

        $this->get(route('complaints.show', $complaint))
            ->assertOk()
            ->assertSee('منى عبد الرحمن السيد')
            ->assertSee('(مؤرشف)')
            ->assertDontSee(route('patients.show', $patient));
    });

    it('stays readable on the dashboard', function () {
        actingAsAdmin();
        [$patient] = archivePatientWithHistory();

        $this->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('منى عبد الرحمن السيد')
            ->assertSee('(مؤرشف)')
            ->assertDontSee(route('patients.show', $patient));
    });



    it('is refused for a new complaint', function () {
        actingAsAdmin();
        [$patient] = archivePatientWithHistory();

        $this->post(route('complaints.store'), [
            'patient_id' => $patient->id,
            'title' => 'تأخير في موعد الكشف رغم الحجز المسبق',
            'description' => 'انتظر المريض أكثر من ساعة دون أي توضيح من الإدارة.',
        ])->assertSessionHasErrors('patient_id');

        $this->assertDatabaseCount('complaints', 0);
    });
});

describe('the archived patient file in the data', function () {
    it('is excluded from the patient statistics', function () {
        actingAsAdmin();

        Patient::factory()->count(3)->create(['blood_group' => 'A-'])
            ->each(fn (Patient $patient) => $this->delete(route('patients.destroy', $patient)));

        Patient::factory()->count(2)->create(['blood_group' => 'A+']);

        $stats = app(PatientQuery::class)->getStatistics()->first();
        expect((int) $stats->total)->toBe(2)
            ->and($stats->most_common_blood_group)->toBe('A+');
    });

    it('keeps its phone number reserved', function () {
        actingAsAdmin();
        $patient = Patient::factory()->create(['phone' => '+201000000009']);

        $this->delete(route('patients.destroy', $patient));

        $this->post(route('patients.store'), [
            'fullname' => 'أحمد عبد الرحمن السيد',
            'phone' => '+201000000009',
            'gender' => 'male',
            'birthdate' => '1990-05-05',
            'blood_group' => 'O+',
        ])->assertSessionHasErrors('phone');

        expect(Patient::query()->count())->toBe(0);
    });
});
