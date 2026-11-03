<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\Survey;
use App\Models\Visit;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SurveyService
{
    /**
     * The survey register, filtered by the request.
     *
     * The tile totals are read before the filters are applied, so they describe
     * the whole survey population rather than the filtered page.
     *
     * @param  array<string, mixed>  $filters
     * @return array{surveys: LengthAwarePaginator, stats: object|null}
     */
    public function paginateIndex(array $filters): array
    {
        $search = $filters['search'] ?? '';
        $rating = (int) ($filters['rating'] ?? 0);

        $query = Survey::query()->with('patient:id,fullname,mrn,deleted_at', 'visit:id,date,doctor_id', 'visit.doctor:id,fullname', 'creator:id,name', 'complaint');

        // The tiles are one aggregate row: the relations the listing eager loads
        // have nothing to attach to and would only run discarded queries.
        $stats = (clone $query)->setEagerLoads([])->stats()->first();

        if ($search) {
            $query->search($search);
        }

        if ($rating > 0 && $rating <= 5) {
            $query->where('rating', $rating);
        }

        return [
            'surveys' => $query->latest()->paginate(20)->withQueryString(),
            'stats' => $stats,
        ];
    }

    /**
     * Record the survey of a completed visit.
     *
     * A survey is only meaningful after the visit it describes is over. A rating
     * below three opens a complaint in the same transaction as the survey, so a
     * dissatisfied patient is never recorded without the complaint that follows
     * up on it.
     *
     * @param  array<string, mixed>  $data
     * @return array{recorded: bool, complaint_opened: bool}
     */
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

    /**
     * Delete a survey that raised no complaint.
     *
     * The complaint raised from a survey is removed by the foreign key together
     * with it, so a survey that produced one is refused instead of taking the
     * complaint and its investigation with it.
     *
     * @return bool whether the survey was deleted
     */
    public function delete(Survey $survey): bool
    {
        if ($survey->complaint()->exists()) {
            return false;
        }

        $survey->delete();

        return true;
    }
}
