<?php

namespace Database\Seeders;

use App\Models\FollowUp;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Seeder;

class FollowUpSeeder extends Seeder
{
    /**
     * @param  int  $count  Number of follow-ups to create.
     */
    public function __construct(public int $count = 100) {}

    /**
     * Run the database seeds.
     *
     * A follow-up is only created for a visit that already took place, and visits
     * that already have a follow-up are skipped so that a visit never gets two.
     */
    public function run(): void
    {
        $users = User::query()->get();

        $visits = Visit::query()
            ->completed()
            ->whereNotIn('id', FollowUp::query()->select('visit_id'))
            ->inRandomOrder()
            ->limit($this->count)
            ->get();

        foreach ($visits as $visit) {
            FollowUp::factory()
                ->forVisit($visit)
                ->recycle($users)
                ->create();
        }
    }
}
