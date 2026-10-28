<?php

namespace Database\Seeders;

use App\Models\Complaint;
use App\Models\Patient;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Database\Seeder;

class ComplaintSeeder extends Seeder
{
    /**
     * @param  int  $manualCount  Complaints reported directly by the staff.
     * @param  int  $fromSurveyCount  Complaints raised from poorly rated surveys.
     */
    public function __construct(
        public int $manualCount = 15,
        public int $fromSurveyCount = 12,
    ) {}

    /**
     * Run the database seeds.
     *
     * Manual complaints have no survey, while survey complaints are derived from
     * dissatisfied ratings (two stars or less) and reuse the survey patient. A
     * survey already behind a complaint is skipped because `complaints.survey_id`
     * is unique.
     */
    public function run(): void
    {
        $users = User::query()->get();

        Complaint::factory()
            ->count($this->manualCount)
            ->recycle($users)
            ->recycle(Patient::query()->get(['id']))
            ->create();

        $surveys = Survey::query()
            ->where('rating', '<=', 2)
            ->whereNotIn('id', Complaint::query()->whereNotNull('survey_id')->select('survey_id'))
            ->inRandomOrder()
            ->limit($this->fromSurveyCount)
            ->get();

        foreach ($surveys as $survey) {
            Complaint::factory()
                ->fromSurvey($survey)
                ->recycle($users)
                ->create();
        }
    }
}
