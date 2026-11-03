<?php

use App\Models\Patient;

/*
|--------------------------------------------------------------------------
| Regression cover for the order of the patient listing.
|
| The list ordered by `created_at` alone. Registration timestamps have second
| precision, so patients registered in the same second had no defined order
| and could be repeated or skipped between pages. The id tiebreaker keeps the
| newest-first order deterministic.
|--------------------------------------------------------------------------
*/

it('orders patients registered in the same second by id across pages', function () {
    actingAsAdmin();

    $createdAt = now()->startOfSecond();

    $patients = Patient::factory()->count(21)->create([
        'status' => 'active',
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
    ]);

    $firstPage = $this->get(route('patients.index'))->assertOk()->viewData('patients');
    $secondPage = $this->get(route('patients.index', ['page' => 2]))->assertOk()->viewData('patients');

    $expectedFirstPage = $patients->pluck('id')->sortDesc()->take(20)->values()->all();

    expect($firstPage->pluck('id')->all())->toBe($expectedFirstPage)
        ->and($secondPage->pluck('id')->all())->toBe([$patients->pluck('id')->min()]);
});
