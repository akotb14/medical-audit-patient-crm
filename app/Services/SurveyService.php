<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\Survey;
use App\Models\Visit;
use App\Queries\SurveyQuery;
use Illuminate\Support\Facades\DB;

class SurveyService
{
    public function __construct(private SurveyQuery $surveyQuery) {}

    public function paginateIndex(array $filters): array
    {

        $query = Survey::query()->with('patient:id,fullname,mrn,deleted_at', 'visit:id,date,doctor_id', 'visit.doctor:id,fullname', 'creator:id,name', 'complaint');

        $stats = $this->surveyQuery->getStatistics();



        return [
            'surveys' => $this->surveyQuery->getIndexQuery($query,$filters)->latest()->paginate(20)->withQueryString(),
            'stats' => $stats,
        ];
    }


    public function submit(array $data, ?int $actorId): array
    {
        $visit = Visit::query()->findOrFail($data['visit_id']);

        if ($visit->visit_status !== 'completed') {
            return ['recorded' => false, 'complaint_opened' => false];
        }

        $data['patient_id'] = $visit->patient_id;
        $data['created_by'] = $actorId;

        if ($data['rating'] >= 3) {
            Survey::create($data);

            return ['recorded' => true, 'complaint_opened' => false];
        }

        DB::transaction(function () use ($data, $actorId): void {
            $survey = Survey::create($data);

            Complaint::create([
                'patient_id' => $data['patient_id'],
                'survey_id' => $survey->id,
                'source' => 'survey',
                'title' => 'ملاحظات سلبية في استبيان رضا المرضى',
                'description' => $data['comment'] ?? 'لم يترك المريض تعليقًا نصيًا، والتقييم المنخفض هو سبب تسجيل الشكوى.',
                'status' => 'new',
                'created_by' => $actorId,
            ]);
        });

        return ['recorded' => true, 'complaint_opened' => true];
    }


    public function delete(Survey $survey): bool
    {
        if ($survey->complaint()->exists()) {
            return false;
        }

        $survey->delete();

        return true;
    }
}
