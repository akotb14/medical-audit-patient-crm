<?php

use App\Models\Complaint;
use App\Models\Survey;

/*
|--------------------------------------------------------------------------
| Regression cover for REVIEW-003.
|
| The `stats` scope counted the complaints with a subquery filtered on
| `WHERE source = 'new'` — the `source` column compared against a complaint
| status that can never appear in it. `complaints_count` was therefore always 0,
| however many complaints really came from surveys.
|--------------------------------------------------------------------------
*/

it('counts only the complaints raised from surveys', function () {
    $surveys = Survey::factory()->count(2)->create();

    foreach ($surveys as $survey) {
        Complaint::factory()->fromSurvey($survey)->create();
    }

    Complaint::factory()->count(3)->create();

    $stats = Survey::query()->stats()->first();

    expect((int) $stats->complaints_count)->toBe(2);
});

it('splits the ratings on the positive boundary of three', function () {
    Survey::factory()->rating(5)->create();
    Survey::factory()->rating(4)->create();
    Survey::factory()->rating(3)->create();
    Survey::factory()->rating(2)->create();
    Survey::factory()->rating(1)->create();

    $stats = Survey::query()->stats()->first();

    expect((int) $stats->total)->toBe(5);
    expect((int) $stats->postive_ratings)->toBe(3);
    expect((int) $stats->negative_ratings)->toBe(2);
});

it('reports zeroes when no survey exists yet', function () {
    $stats = Survey::query()->stats()->first();

    expect((int) $stats->total)->toBe(0);
    expect((int) $stats->complaints_count)->toBe(0);
});
