<?php

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Visit;

/*
|--------------------------------------------------------------------------
| Visit date rules (AUD-01 and AUD-10).
|
| AUD-01 — the create form submits a date without a time, so today's date was
| read as midnight and refused by a rule that compared it with the current
| moment. The custom message sat under a key the rule never reports, so the raw
| validation key was shown instead.
| AUD-10 — an open visit could be moved into the past, and a closed visit only
| had its day frozen: a different time on the same day was accepted, and the
| date-only value of the edit form reset the stored time to midnight.
|--------------------------------------------------------------------------
*/

/**
 * The payload the visit edit form submits.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function visitDatePayload(Visit $visit, array $overrides = []): array
{
    return [
        'patient_id' => $visit->patient_id,
        'doctor_id' => $visit->doctor_id,
        'date' => $visit->date->toDateString(),
        'reason' => $visit->reason,
        ...$overrides,
    ];
}

/**
 * An open visit on a future day.
 */
function openVisitForDateRules(): Visit
{
    $patient = Patient::factory()->create();
    $doctor = Doctor::factory()->create();

    return Visit::factory()->recycle([$patient, $doctor])->create([
        'visit_status' => 'created',
        'date' => now()->addWeek()->startOfMinute(),
    ]);
}

/**
 * A completed or cancelled visit stored at a time that is not midnight.
 */
function closedVisitForDateRules(string $status): Visit
{
    $patient = Patient::factory()->create();
    $doctor = Doctor::factory()->create();

    return Visit::factory()->recycle([$patient, $doctor])->create([
        'visit_status' => $status,
        'date' => now()->subDays(3)->setTime(10, 30, 45),
    ]);
}

describe('creating a visit', function () {
    it('accepts today when the form submits a date without a time', function () {
        actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $today = now()->toDateString();

        $this->post(route('visits.store'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'reason' => 'متابعة ضغط الدم بعد تعديل الجرعة',
            'date' => $today,
        ])->assertRedirect(route('visits.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('visits', [
            'patient_id' => $patient->id,
            'date' => $today.' 00:00:00',
        ]);
    });

    it('accepts a future date', function () {
        actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $future = now()->addDays(10)->toDateString();

        $this->post(route('visits.store'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'reason' => 'متابعة نتائج التحاليل بعد أسبوعين',
            'date' => $future,
        ])->assertRedirect(route('visits.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('visits', [
            'patient_id' => $patient->id,
            'date' => $future.' 00:00:00',
        ]);
    });

    it('refuses a past date submitted straight to the endpoint', function () {
        actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $this->post(route('visits.store'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'reason' => 'زيارة مسجلة بأثر رجعي',
            'date' => now()->subDay()->toDateString(),
        ])->assertSessionHasErrors('date');

        $this->assertDatabaseCount('visits', 0);
    });
});

describe('updating an open visit', function () {
    it('accepts today', function () {
        actingAsAdmin();
        $visit = openVisitForDateRules();
        $today = now()->toDateString();

        $this->put(route('visits.update', $visit), visitDatePayload($visit, [
            'date' => $today,
        ]))->assertRedirect(route('visits.index'))
            ->assertSessionHasNoErrors();

        expect($visit->refresh()->date->toDateString())->toBe($today);
    });

    it('accepts a future date', function () {
        actingAsAdmin();
        $visit = openVisitForDateRules();
        $future = now()->addDays(10)->toDateString();

        $this->put(route('visits.update', $visit), visitDatePayload($visit, [
            'date' => $future,
        ]))->assertRedirect(route('visits.index'))
            ->assertSessionHasNoErrors();

        expect($visit->refresh()->date->toDateString())->toBe($future);
    });

    it('refuses a past date submitted straight to the endpoint', function () {
        actingAsAdmin();
        $visit = openVisitForDateRules();
        $storedDate = $visit->date->copy();

        $this->put(route('visits.update', $visit), visitDatePayload($visit, [
            'date' => now()->subDay()->toDateString(),
        ]))->assertSessionHasErrors('date');

        expect($visit->refresh()->date->equalTo($storedDate))->toBeTrue();
    });
});

describe('updating a completed visit', function () {
    it('refuses another day', function () {
        actingAsAdmin();
        $visit = closedVisitForDateRules('completed');
        $storedDate = $visit->date->copy();

        $this->put(route('visits.update', $visit), visitDatePayload($visit, [
            'date' => $storedDate->copy()->addDay()->toDateString(),
        ]))->assertSessionHasErrors('date');

        expect($visit->refresh()->date->equalTo($storedDate))->toBeTrue();
    });

    it('refuses another time on the stored day', function () {
        actingAsAdmin();
        $visit = closedVisitForDateRules('completed');
        $storedDate = $visit->date->copy();

        $this->put(route('visits.update', $visit), visitDatePayload($visit, [
            'date' => $storedDate->copy()->setTime(12, 0, 0)->toDateTimeString(),
        ]))->assertSessionHasErrors('date');

        expect($visit->refresh()->date->equalTo($storedDate))->toBeTrue();
    });

    it('keeps the stored time when the date-only form is resubmitted', function () {
        actingAsAdmin();
        $visit = closedVisitForDateRules('completed');
        $storedDate = $visit->date->copy();

        $this->put(route('visits.update', $visit), visitDatePayload($visit))
            ->assertRedirect(route('visits.index'))
            ->assertSessionHasNoErrors();

        $visit->refresh();

        expect($visit->date->equalTo($storedDate))->toBeTrue()
            ->and($visit->date->format('H:i:s'))->toBe('10:30:45');
    });
});

describe('updating a cancelled visit', function () {
    it('refuses another day', function () {
        actingAsAdmin();
        $visit = closedVisitForDateRules('cancelled');
        $storedDate = $visit->date->copy();

        $this->put(route('visits.update', $visit), visitDatePayload($visit, [
            'date' => $storedDate->copy()->addDay()->toDateString(),
        ]))->assertSessionHasErrors('date');

        expect($visit->refresh()->date->equalTo($storedDate))->toBeTrue();
    });

    it('refuses another time on the stored day', function () {
        actingAsAdmin();
        $visit = closedVisitForDateRules('cancelled');
        $storedDate = $visit->date->copy();

        $this->put(route('visits.update', $visit), visitDatePayload($visit, [
            'date' => $storedDate->copy()->setTime(12, 0, 0)->toDateTimeString(),
        ]))->assertSessionHasErrors('date');

        expect($visit->refresh()->date->equalTo($storedDate))->toBeTrue();
    });

    it('accepts the exact stored timestamp', function () {
        actingAsAdmin();
        $visit = closedVisitForDateRules('cancelled');
        $storedDate = $visit->date->copy();

        $this->put(route('visits.update', $visit), visitDatePayload($visit, [
            'date' => $storedDate->toDateTimeString(),
        ]))->assertRedirect(route('visits.index'))
            ->assertSessionHasNoErrors();

        expect($visit->refresh()->date->equalTo($storedDate))->toBeTrue();
    });
});
