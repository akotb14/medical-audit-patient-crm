<?php

use App\Models\Complaint;
use App\Models\Survey;
use App\Queries\SurveyQuery;



it('counts only the complaints raised from surveys', function () {
    $surveys = Survey::factory()->count(2)->create();

    foreach ($surveys as $survey) {
        Complaint::factory()->fromSurvey($survey)->create();
    }

    Complaint::factory()->count(3)->create();

    $stats = app(SurveyQuery::class)->getStatistics();

    expect((int) $stats->complaints_count)->toBe(2);
});

it('splits the ratings on the positive boundary of three', function () {
    Survey::factory()->rating(5)->create();
    Survey::factory()->rating(4)->create();
    Survey::factory()->rating(3)->create();
    Survey::factory()->rating(2)->create();
    Survey::factory()->rating(1)->create();

    $stats = app(SurveyQuery::class)->getStatistics();

    expect((int) $stats->total)->toBe(5);
    expect((int) $stats->postive_ratings)->toBe(3);
    expect((int) $stats->negative_ratings)->toBe(2);
});

it('reports zeroes when no survey exists yet', function () {
    $stats = app(SurveyQuery::class)->getStatistics();

    expect((int) $stats->total)->toBe(0);
    expect((int) $stats->complaints_count)->toBe(0);
});
