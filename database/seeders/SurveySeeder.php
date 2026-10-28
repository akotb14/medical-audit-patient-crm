<?php

namespace Database\Seeders;

use App\Models\Survey;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Seeder;

class SurveySeeder extends Seeder
{
    /**
     * @param  int  $count  Number of patient surveys to create.
     */
    public function __construct(public int $count = 60) {}

    /**
     * Run the database seeds.
     *
     * Surveys are attached to visits that already took place, and a visit that has
     * already been evaluated is skipped because `surveys.visit_id` is unique.
     */
    public function run(): void
    {
        $users = User::query()->get();

        $visits = Visit::query()
            ->completed()
            ->whereNotIn('id', Survey::query()->select('visit_id'))
            ->inRandomOrder()
            ->limit($this->count)
            ->get();

        foreach ($visits as $visit) {
            Survey::factory()
                ->forVisit($visit)
                ->recycle($users)
                ->create();
        }
    }
}
