<?php

use App\Models\Doctor;
use App\Models\FollowUp;
use App\Models\Patient;
use App\Models\Visit;

/*
|--------------------------------------------------------------------------
| Regression cover for the follow-up findings of the code review.
|
| Completion — the status endpoint wrote the status alone, so a follow-up marked
| done from the dropdown kept a null `completed_at`, while the complete and the
| update actions both write it. The same endpoint also kept a completion moment
| that no longer belonged to the new status.
| Overdue edit — `due_date` had to be in the future on update, and the edit form
| always resubmits the stored date, so an overdue follow-up could never be
| corrected again.
| Lengths — the rules allowed 2000 characters into columns that hold 500, which
| MySQL refuses with a "Data too long" error instead of a validation message.
|--------------------------------------------------------------------------
*/

/**
 * A scheduled follow-up on a completed visit of its own.
 *
 * @param  array<string, mixed>  $overrides
 */
function scheduledFollowUp(array $overrides = []): FollowUp
{
    $patient = Patient::factory()->create();
    $doctor = Doctor::factory()->create();
    $visit = Visit::factory()->completed()->recycle([$patient, $doctor])->create();

    return FollowUp::factory()->forVisit($visit)->create([
        'status' => 'scheduled',
        'due_date' => now()->addWeek(),
        'completed_at' => null,
        'outcome_note' => null,
        ...$overrides,
    ]);
}

/**
 * The payload the follow-up edit form submits.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function followUpUpdatePayload(FollowUp $followUp, array $overrides = []): array
{
    return [
        'patient_id' => $followUp->patient_id,
        'visit_id' => $followUp->visit_id,
        'due_date' => $followUp->due_date->format('Y-m-d H:i'),
        'status' => $followUp->status,
        'outcome_note' => $followUp->outcome_note,
        'reason' => $followUp->reason,
        ...$overrides,
    ];
}

describe('changing the status of a follow-up', function () {
    it('records the completion moment when it is marked done', function () {
        actingAsAdmin();
        $followUp = scheduledFollowUp();

        $this->from(route('follow_ups.index'))
            ->patch(route('follow_ups.status', $followUp), ['status' => 'done'])
            ->assertRedirect(route('follow_ups.index'));

        $followUp->refresh();

        expect($followUp->status)->toBe('done')
            ->and($followUp->completed_at)->not->toBeNull();
    });

    it('clears a completion moment that does not belong to the new status', function () {
        actingAsAdmin();
        $followUp = scheduledFollowUp(['completed_at' => now()->subDay()]);

        $this->from(route('follow_ups.index'))
            ->patch(route('follow_ups.status', $followUp), ['status' => 'cancelled'])
            ->assertRedirect(route('follow_ups.index'));

        expect($followUp->refresh()->completed_at)->toBeNull();
    });
});

describe('editing a follow-up', function () {
    it('saves an overdue follow-up without moving its date', function () {
        actingAsAdmin();
        $followUp = scheduledFollowUp([
            'status' => 'missed',
            'due_date' => now()->subDays(3)->startOfMinute(),
        ]);
        $dueDate = $followUp->due_date;

        $this->put(route('follow_ups.update', $followUp), followUpUpdatePayload($followUp, [
            'outcome_note' => 'تمت المتابعة هاتفيًا مع المريض، الحالة مستقرة.',
        ]))->assertRedirect(route('follow_ups.index'))
            ->assertSessionHasNoErrors();

        $followUp->refresh();

        expect($followUp->outcome_note)->toBe('تمت المتابعة هاتفيًا مع المريض، الحالة مستقرة.')
            ->and($followUp->due_date->equalTo($dueDate))->toBeTrue();
    });

    it('accepts a future date when the follow-up is rescheduled', function () {
        actingAsAdmin();
        $followUp = scheduledFollowUp([
            'status' => 'missed',
            'due_date' => now()->subDays(3)->startOfMinute(),
        ]);
        $newDate = now()->addDays(10)->startOfMinute();

        $this->put(route('follow_ups.update', $followUp), followUpUpdatePayload($followUp, [
            'due_date' => $newDate->format('Y-m-d H:i'),
        ]))->assertRedirect(route('follow_ups.index'))
            ->assertSessionHasNoErrors();

        expect($followUp->refresh()->due_date->equalTo($newDate))->toBeTrue();
    });

    it('refuses to move an overdue follow-up to another past date', function () {
        actingAsAdmin();
        $followUp = scheduledFollowUp([
            'status' => 'missed',
            'due_date' => now()->subDays(3)->startOfMinute(),
        ]);
        $dueDate = $followUp->due_date;

        $this->put(route('follow_ups.update', $followUp), followUpUpdatePayload($followUp, [
            'due_date' => now()->subDays(30)->format('Y-m-d H:i'),
        ]))->assertSessionHasErrors('due_date');

        expect($followUp->refresh()->due_date->equalTo($dueDate))->toBeTrue();
    });
});

describe('the stored length of the follow-up text fields', function () {
    it('accepts a reason and an outcome note as long as the stored columns', function () {
        actingAsAdmin();
        $followUp = scheduledFollowUp(['status' => 'done']);

        $this->put(route('follow_ups.update', $followUp), followUpUpdatePayload($followUp, [
            'reason' => str_repeat('م', 500),
            'outcome_note' => str_repeat('ن', 500),
        ]))->assertRedirect(route('follow_ups.index'))
            ->assertSessionHasNoErrors();
    });

    it('refuses an outcome note longer than the stored column', function () {
        actingAsAdmin();
        $followUp = scheduledFollowUp(['status' => 'done']);

        $this->put(route('follow_ups.update', $followUp), followUpUpdatePayload($followUp, [
            'outcome_note' => str_repeat('ن', 501),
        ]))->assertSessionHasErrors('outcome_note');
    });

    it('refuses a reason longer than the stored column', function () {
        actingAsAdmin();
        $followUp = scheduledFollowUp(['status' => 'done']);

        $this->put(route('follow_ups.update', $followUp), followUpUpdatePayload($followUp, [
            'reason' => str_repeat('م', 501),
        ]))->assertSessionHasErrors('reason');
    });
});

/*
| A follow-up moves through a fixed set of states. Both the edit form and the
| status dropdown accepted any status, so a cancelled or missed follow-up could
| be recorded as completed and a completed one reopened.
*/
describe('the allowed status transitions of a follow-up', function () {
    it('refuses to mark a cancelled follow-up as done from the status endpoint', function () {
        actingAsAdmin();
        $followUp = scheduledFollowUp(['status' => 'cancelled']);

        $this->from(route('follow_ups.index'))
            ->patch(route('follow_ups.status', $followUp), ['status' => 'done'])
            ->assertRedirect(route('follow_ups.index'))
            ->assertSessionHas('error');

        $followUp->refresh();

        expect($followUp->status)->toBe('cancelled')
            ->and($followUp->completed_at)->toBeNull();
    });

    it('refuses to mark a missed follow-up as done through the edit form', function () {
        actingAsAdmin();
        $followUp = scheduledFollowUp([
            'status' => 'missed',
            'due_date' => now()->subDays(2)->startOfMinute(),
        ]);

        $this->put(route('follow_ups.update', $followUp), followUpUpdatePayload($followUp, [
            'status' => 'done',
        ]))->assertSessionHasErrors('status');

        expect($followUp->refresh()->status)->toBe('missed');
    });

    it('refuses to reopen a completed follow-up', function () {
        actingAsAdmin();
        $followUp = scheduledFollowUp(['status' => 'done']);

        $this->put(route('follow_ups.update', $followUp), followUpUpdatePayload($followUp, [
            'status' => 'scheduled',
        ]))->assertSessionHasErrors('status');

        expect($followUp->refresh()->status)->toBe('done');
    });

    it('lets a cancelled follow-up be rescheduled', function () {
        actingAsAdmin();
        $followUp = scheduledFollowUp(['status' => 'cancelled']);

        $this->put(route('follow_ups.update', $followUp), followUpUpdatePayload($followUp, [
            'status' => 'scheduled',
            'due_date' => now()->addDays(3)->startOfMinute()->format('Y-m-d H:i'),
        ]))->assertRedirect(route('follow_ups.index'))
            ->assertSessionHasNoErrors();

        expect($followUp->refresh()->status)->toBe('scheduled');
    });
});

/*
| The patient and the visit name the association the follow-up was created
| with. They stay editable in the payload of the edit form, so a crafted
| request must not be able to move the follow-up to another patient or to a
| visit that already has a follow-up of its own.
*/
describe('the patient and visit of a follow-up', function () {
    it('keeps the original patient when another one is submitted', function () {
        actingAsAdmin();
        $followUp = scheduledFollowUp();
        $originalPatient = $followUp->patient_id;
        $otherPatient = Patient::factory()->create();

        $this->put(route('follow_ups.update', $followUp), followUpUpdatePayload($followUp, [
            'patient_id' => $otherPatient->id,
        ]))->assertRedirect(route('follow_ups.index'))
            ->assertSessionHasNoErrors();

        expect($followUp->refresh()->patient_id)->toBe($originalPatient);
    });

    it('keeps the original visit when one carrying a follow-up is submitted', function () {
        actingAsAdmin();
        $followUp = scheduledFollowUp();
        $originalVisit = $followUp->visit_id;
        $otherFollowUp = scheduledFollowUp();

        $this->put(route('follow_ups.update', $followUp), followUpUpdatePayload($followUp, [
            'visit_id' => $otherFollowUp->visit_id,
        ]))->assertRedirect(route('follow_ups.index'))
            ->assertSessionHasNoErrors();

        expect($followUp->refresh()->visit_id)->toBe($originalVisit);
    });
});

/*
| The complete endpoint had no coverage, so a follow-up could be marked done
| from any status and the outcome note was stored without the completion
| moment. The stored column also accepts 500 characters.
*/
describe('completing a follow-up from its action endpoint', function () {
    it('stores the outcome note and the completion moment', function () {
        actingAsAdmin();
        $followUp = scheduledFollowUp();

        $this->from(route('follow_ups.index'))
            ->patch(route('follow_ups.complete', $followUp), [
                'outcome_note' => 'تحسن المريض بعد تعديل الخطة العلاجية.',
            ])
            ->assertRedirect(route('follow_ups.index'))
            ->assertSessionHas('success');

        $followUp->refresh();

        expect($followUp->status)->toBe('done')
            ->and($followUp->outcome_note)->toBe('تحسن المريض بعد تعديل الخطة العلاجية.')
            ->and($followUp->completed_at)->not->toBeNull();
    });

    it('refuses to complete a follow-up that is not scheduled', function () {
        actingAsAdmin();
        $followUp = scheduledFollowUp(['status' => 'cancelled']);

        $this->from(route('follow_ups.index'))
            ->patch(route('follow_ups.complete', $followUp))
            ->assertRedirect(route('follow_ups.index'))
            ->assertSessionHas('error');

        expect($followUp->refresh()->status)->toBe('cancelled')
            ->and($followUp->completed_at)->toBeNull();
    });

    it('refuses an outcome note longer than the stored column', function () {
        actingAsAdmin();
        $followUp = scheduledFollowUp();

        $this->from(route('follow_ups.index'))
            ->patch(route('follow_ups.complete', $followUp), [
                'outcome_note' => str_repeat('ن', 501),
            ])
            ->assertSessionHasErrors('outcome_note');

        expect($followUp->refresh()->status)->toBe('scheduled');
    });
});
