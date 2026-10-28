<?php

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Pagination\LengthAwarePaginator;

/*
|--------------------------------------------------------------------------
| Regression cover for REVIEW-NEW-002.
|
| The visit tiles and the doctor badge were read off the paginator, so they
| reported the size of the current page instead of the size of the collection.
| A patient with 21 visits was shown 20, and the status tiles changed value
| halfway through the history. With `status` counts the totals are now read from
| a grouped aggregate, so the same numbers survive page 2.
|--------------------------------------------------------------------------
*/

/**
 * Give one patient 21 visits spread over all three statuses: 7 created,
 * 9 completed and 5 cancelled.
 *
 * @return array{created: int, completed: int, cancelled: int}
 */
function createPatientVisitSpread(Patient $patient, Doctor $doctor, User $author): array
{
    $spread = ['created' => 7, 'completed' => 9, 'cancelled' => 5];

    Visit::factory()->count($spread['created'])->recycle([$patient, $doctor, $author])->create();
    Visit::factory()->count($spread['completed'])->completed()->recycle([$patient, $doctor, $author])->create();
    Visit::factory()->count($spread['cancelled'])->cancelled()->recycle([$patient, $doctor, $author])->create();

    return $spread;
}

/**
 * Read a KPI tile back out of the rendered page by its element id.
 */
function renderedKpiValue(string $html, string $id): ?int
{
    $matched = preg_match('/id="'.$id.'">\s*(\d+)\s*</', $html, $matches);

    return $matched === 1 ? (int) $matches[1] : null;
}

describe('the patient visit KPIs', function () {
    it('reports the total across every page of the history', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        createPatientVisitSpread($patient, $doctor, $admin);

        $response = $this->get(route('patients.show', $patient));

        $response->assertOk();

        // Page 1 holds 20 rows; the KPI has to report all 21.
        $response->assertViewHas('visits', function (LengthAwarePaginator $visits): bool {
            return $visits->count() === 20 && $visits->total() === 21;
        });
    });

    it('counts each status across the whole history', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $spread = createPatientVisitSpread($patient, $doctor, $admin);

        $this->get(route('patients.show', $patient))
            ->assertOk()
            ->assertViewHas('visitCounts', $spread);
    });

    it('reports zero for a status the patient never had', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        Visit::factory()->count(21)->recycle([$patient, $doctor, $admin])->create();

        $response = $this->get(route('patients.show', $patient));

        $response->assertOk();
        $response->assertViewHas('visitCounts', ['created' => 21, 'completed' => 0, 'cancelled' => 0]);

        expect(renderedKpiValue($response->getContent(), 'patientVisitsCompletedCount'))->toBe(0);
    });

    it('renders the values in the patient file', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        createPatientVisitSpread($patient, $doctor, $admin);

        $html = $this->get(route('patients.show', $patient))->assertOk()->getContent();

        expect(renderedKpiValue($html, 'patientVisitsTotalCount'))->toBe(21)
            ->and(renderedKpiValue($html, 'patientVisitsCreatedCount'))->toBe(7)
            ->and(renderedKpiValue($html, 'patientVisitsCompletedCount'))->toBe(9)
            ->and(renderedKpiValue($html, 'patientVisitsCancelledCount'))->toBe(5);
    });

    /*
    | The regression itself: the tiles used to be computed from the displayed
    | page, so entering page 2 rewrote all four numbers.
    */
    it('renders the same values on the second page', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        createPatientVisitSpread($patient, $doctor, $admin);

        $response = $this->get(route('patients.show', ['patient' => $patient, 'page' => 2]));

        $response->assertOk();

        // Proof the response really is page 2, not a silent fallback to page 1.
        $response->assertViewHas('visits', function (LengthAwarePaginator $visits): bool {
            return $visits->currentPage() === 2 && $visits->count() === 1;
        });

        $html = $response->getContent();

        expect(renderedKpiValue($html, 'patientVisitsTotalCount'))->toBe(21)
            ->and(renderedKpiValue($html, 'patientVisitsCreatedCount'))->toBe(7)
            ->and(renderedKpiValue($html, 'patientVisitsCompletedCount'))->toBe(9)
            ->and(renderedKpiValue($html, 'patientVisitsCancelledCount'))->toBe(5);
    });
});

describe('the clinic doctor KPI', function () {
    it('reports the total across every page of the doctor list', function () {
        actingAsAdmin();
        $clinic = Clinic::factory()->create();

        Doctor::factory()->count(21)->for($clinic)->create();

        $response = $this->get(route('clinics.show', $clinic));

        $response->assertOk();

        // Page 1 holds 20 doctors; the badge has to report all 21.
        $response->assertViewHas('doctors', function (LengthAwarePaginator $doctors): bool {
            return $doctors->count() === 20 && $doctors->total() === 21;
        });

        $response->assertSee('إجمالي الأطباء بالعيادة');

        expect(renderedKpiValue($response->getContent(), 'clinicDoctorsCountBadge'))->toBe(21);
    });
});
