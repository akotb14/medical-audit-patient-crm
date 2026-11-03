<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\Complaint;
use App\Models\Doctor;
use App\Models\FollowUp;
use App\Models\Patient;
use App\Models\Survey;
use App\Models\Visit;

class DashboardService
{
    /**
     * Every figure the dashboard renders, keyed by the view variable that reads
     * it.
     *
     * The rates are computed here rather than in the view so a figure and the
     * rate derived from it can never disagree.
     *
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        return [
            ...$this->visitSummary(),
            ...$this->followUpSummary(),
            ...$this->surveySummary(),
            ...$this->complaintSummary(),
            ...$this->clinicSummary(),
            ...$this->doctorSummary(),
            ...$this->patientSummary(),
            'recentVisits' => Visit::query()
                ->with([
                    'patient',
                    'doctor.clinic',
                    'survey',
                    'creator',
                ])
                ->latest('date')
                ->latest('id')
                ->limit(7)
                ->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function visitSummary(): array
    {
        $visitStats = Visit::query()
            ->selectRaw("
                COUNT(*) AS total,
                COALESCE(SUM(visit_status = 'completed'), 0) AS completed,
                COALESCE(SUM(visit_status = 'created'), 0) AS scheduled,
                COALESCE(SUM(visit_status = 'cancelled'), 0) AS cancelled
            ")
            ->first();

        $totalVisits = (int) $visitStats->total;
        $completedVisits = (int) $visitStats->completed;

        return [
            'totalVisits' => $totalVisits,
            'completedVisits' => $completedVisits,
            'scheduledVisits' => (int) $visitStats->scheduled,
            'cancelledVisits' => (int) $visitStats->cancelled,
            'completionRate' => $totalVisits > 0
                ? round(($completedVisits / $totalVisits) * 100, 1)
                : 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function followUpSummary(): array
    {
        $followupStats = FollowUp::query()
            ->selectRaw("
                COUNT(*) AS total,
                COALESCE(SUM(status = 'scheduled'), 0) AS scheduled,
                COALESCE(SUM(status = 'done'), 0) AS done,
                COALESCE(SUM(status = 'missed'), 0) AS missed,
                COALESCE(SUM(status = 'cancelled'), 0) AS cancelled
            ")
            ->first();

        return [
            'totalFollowups' => (int) $followupStats->total,
            'scheduledFollowups' => (int) $followupStats->scheduled,
            'doneFollowups' => (int) $followupStats->done,
            'missedFollowups' => (int) $followupStats->missed,
            'cancelledFollowups' => (int) $followupStats->cancelled,
            'urgentFollowups' => FollowUp::query()
                ->with([
                    'patient',
                    'visit.doctor',
                ])
                ->where('status', 'scheduled')
                ->orderBy('due_date')
                ->limit(5)
                ->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function surveySummary(): array
    {
        $surveyStats = Survey::query()
            ->selectRaw('
                COUNT(*) AS total,
                COALESCE(AVG(rating), 0) AS average_rating,
                COALESCE(SUM(rating = 5), 0) AS rating_5,
                COALESCE(SUM(rating = 4), 0) AS rating_4,
                COALESCE(SUM(rating = 3), 0) AS rating_3,
                COALESCE(SUM(rating = 2), 0) AS rating_2,
                COALESCE(SUM(rating = 1), 0) AS rating_1
            ')
            ->first();

        $totalSurveys = (int) $surveyStats->total;
        $rating5Count = (int) $surveyStats->rating_5;
        $rating4Count = (int) $surveyStats->rating_4;
        $rating2Count = (int) $surveyStats->rating_2;
        $rating1Count = (int) $surveyStats->rating_1;

        return [
            'totalSurveys' => $totalSurveys,
            'avgRating' => round((float) $surveyStats->average_rating, 1),
            'rating5Count' => $rating5Count,
            'rating4Count' => $rating4Count,
            'rating3Count' => (int) $surveyStats->rating_3,
            'rating2Count' => $rating2Count,
            'rating1Count' => $rating1Count,
            'lowRatingSurveysCount' => $rating1Count + $rating2Count,
            'positiveRate' => $totalSurveys > 0
                ? round((($rating5Count + $rating4Count) / $totalSurveys) * 100, 1)
                : 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function complaintSummary(): array
    {
        $complaintStats = Complaint::query()
            ->selectRaw("
                COUNT(*) AS total,
                COALESCE(SUM(status = 'new'), 0) AS new_count,
                COALESCE(SUM(status = 'assigned'), 0) AS assigned,
                COALESCE(SUM(status = 'investigating'), 0) AS investigating,
                COALESCE(SUM(status = 'resolved'), 0) AS resolved,
                COALESCE(SUM(status = 'closed'), 0) AS closed,
                COALESCE(SUM(source = 'survey'), 0) AS from_surveys
            ")
            ->first();

        $newComplaints = (int) $complaintStats->new_count;
        $assignedComplaints = (int) $complaintStats->assigned;
        $investigatingComplaints = (int) $complaintStats->investigating;

        return [
            'totalComplaints' => (int) $complaintStats->total,
            'newComplaints' => $newComplaints,
            'assignedComplaints' => $assignedComplaints,
            'investigatingComplaints' => $investigatingComplaints,
            'resolvedComplaints' => (int) $complaintStats->resolved,
            'closedComplaints' => (int) $complaintStats->closed,
            'activeComplaintsCount' => $newComplaints + $assignedComplaints + $investigatingComplaints,
            'autoComplaintsFromSurveys' => (int) $complaintStats->from_surveys,
            'recentComplaints' => Complaint::query()
                ->with([
                    'patient',
                    'survey',
                    'assignee',
                ])
                ->latest('created_at')
                ->limit(4)
                ->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function clinicSummary(): array
    {
        $clinicStats = Clinic::query()
            ->selectRaw("
                COUNT(*) AS total,
                COALESCE(SUM(status = 'active'), 0) AS active
            ")
            ->first();

        return [
            'totalClinics' => (int) $clinicStats->total,
            'activeClinics' => (int) $clinicStats->active,
            'clinicsLoad' => Clinic::query()
                ->withCount([
                    'doctors',
                ])
                ->orderByDesc('doctors_count')
                ->limit(6)
                ->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function doctorSummary(): array
    {
        $doctorStats = Doctor::query()
            ->selectRaw("
                COUNT(*) AS total,
                COALESCE(SUM(status = 'active'), 0) AS active
            ")
            ->first();

        return [
            'totalDoctors' => (int) $doctorStats->total,
            'activeDoctors' => (int) $doctorStats->active,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function patientSummary(): array
    {
        $patientStats = Patient::query()
            ->selectRaw("
                COALESCE(SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END),0) AS total,
                COALESCE(
                    SUM(
                        CASE
                            WHEN allergies IS NOT NULL AND allergies != ''
                            THEN 1
                            ELSE 0
                        END
                    ),
                    0
                ) AS allergy_risk
            ")
            ->first();

        return [
            'totalPatients' => (int) $patientStats->total,
            'allergyRiskPatients' => (int) $patientStats->allergy_risk,
        ];
    }
}
