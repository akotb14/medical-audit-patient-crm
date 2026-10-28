<?php

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

/*
|--------------------------------------------------------------------------
| Regression cover for REVIEW-NEW-001.
|
| `patients.show` and `doctors.show` both page the visit history at 20 records,
| but neither view rendered `links()`, so the history past the first page was
| unreachable for a patient or a doctor with more than 20 visits.
|
| Page 1 and page 2 differ only in the visit rows they show, so the fixtures pin
| `created_at` by hand. Both controllers order the history with `latest()`, and
| generated timestamps would leave the paging expectation non-deterministic.
|--------------------------------------------------------------------------
*/

/**
 * Build a visit history for one patient and one doctor, ordered oldest first.
 *
 * @return list<Visit>
 */
function createDatedVisitHistory(Patient $patient, Doctor $doctor, int $count, User $author): array
{
    $visits = [];

    for ($position = 1; $position <= $count; $position++) {
        $visit = Visit::factory()
            ->recycle([$patient, $doctor, $author])
            ->create(['reason' => "سبب الزيارة المحفوظة رقم {$position}"]);

        // `created_at` is not fillable, so it is written after the insert.
        $visit->forceFill(['created_at' => Carbon::parse('2026-01-01 09:00:00')->addDays($position)])
            ->saveQuietly();

        $visits[] = $visit->refresh();
    }

    return $visits;
}

/**
 * Count the pagination bars in a rendered page. The default Tailwind paginator
 * emits one `<nav role="navigation">` per call to `links()`, and the application
 * layout emits none of its own, so a count above one means a duplicated pager.
 */
function paginationBarCount(string $html): int
{
    return substr_count($html, 'role="navigation"');
}

describe('the patient visit history', function () {
    it('links to the second page', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        createDatedVisitHistory($patient, $doctor, 21, $admin);

        $response = $this->get(route('patients.show', $patient));

        $response->assertOk();
        $response->assertSee('page=2', false);

        expect(paginationBarCount($response->getContent()))->toBe(1);
    });

    it('renders the second page with the oldest visit', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $visits = createDatedVisitHistory($patient, $doctor, 21, $admin);
        $oldest = $visits[0];
        $newest = $visits[20];

        $response = $this->get(route('patients.show', ['patient' => $patient, 'page' => 2]));

        $response->assertOk();

        $response->assertViewHas('visits', function (LengthAwarePaginator $paginator) use ($oldest): bool {
            return $paginator->pluck('id')->all() === [$oldest->id];
        });

        $response->assertSee($oldest->reason);
        $response->assertDontSee($newest->reason);
    });

    it('stops paging at twenty visits', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        createDatedVisitHistory($patient, $doctor, 20, $admin);

        $response = $this->get(route('patients.show', $patient));

        $response->assertOk();
        $response->assertDontSee('page=2', false);

        $response->assertViewHas('visits', function (LengthAwarePaginator $paginator): bool {
            return $paginator->count() === 20 && $paginator->total() === 20;
        });

        expect(paginationBarCount($response->getContent()))->toBe(0);
    });
});

describe('the doctor visit history', function () {
    it('links to the second page', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        createDatedVisitHistory($patient, $doctor, 21, $admin);

        $response = $this->get(route('doctors.show', $doctor));

        $response->assertOk();
        $response->assertSee('page=2', false);

        expect(paginationBarCount($response->getContent()))->toBe(1);
    });
});

describe('the visits index pager', function () {
    /*
    | The only view that already paged correctly. It has to keep exactly one
    | pager, so the new blocks were not copied on top of existing links.
    */
    it('renders a single pagination bar', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        createDatedVisitHistory($patient, $doctor, 21, $admin);

        $response = $this->get(route('visits.index'));

        $response->assertOk();

        expect(paginationBarCount($response->getContent()))->toBe(1);
    });
});

describe('access to the visit history pages', function () {
    it('redirects a guest to the login page', function () {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $this->get(route('patients.show', $patient))->assertRedirect(route('login'));
        $this->get(route('doctors.show', $doctor))->assertRedirect(route('login'));
    });

    /*
    | A receptionist may read visits but cannot manage them, so the pager has to
    | sit outside the `@can('viewAny', Visit::class)` block that wraps the table.
    */
    it('renders the pager for a receptionist', function () {
        $receptionist = actingAsReceptionist();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        createDatedVisitHistory($patient, $doctor, 21, $receptionist);

        $response = $this->get(route('patients.show', $patient));

        $response->assertOk();
        $response->assertSee($patient->fullname);

        expect(paginationBarCount($response->getContent()))->toBe(1);
    });
});
