<?php

use App\Models\Complaint;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Survey;
use App\Models\Visit;

/*
|--------------------------------------------------------------------------
| Regression cover for the survey findings of the code review.
|
| Low rating — the views asked the `isLowRating` scope on a survey instance. A
| scope called on an instance returns a query builder, which is always truthy, so
| every survey was rendered as a quality alert and every satisfied patient was
| labelled as owing a complaint.
| Relations — the eager loaded visit projection omitted `doctor_id`, so the
| doctor of the visit never resolved, and the views read a `recorder` relation
| that does not exist, so the fallback name was always shown instead of the
| staff member who recorded the survey.
| Deletion — `complaints.survey_id` cascades, so deleting a survey deleted the
| complaint raised from it silently.
|--------------------------------------------------------------------------
*/

/**
 * A completed visit with a patient and a doctor of its own.
 *
 * @return array{0: Visit, 1: Patient, 2: Doctor}
 */
function completedVisitWithDoctor(): array
{
    $patient = Patient::factory()->create();
    $doctor = Doctor::factory()->create();

    $visit = Visit::factory()->completed()->recycle([$patient, $doctor])->create();

    return [$visit, $patient, $doctor];
}

describe('the rating of a survey', function () {
    it('reads a five star survey as a positive experience', function () {
        $admin = actingAsAdmin();
        [$visit] = completedVisitWithDoctor();

        Survey::factory()->forVisit($visit)->rating(5)->create(['created_by' => $admin->id]);

        $this->get(route('surveys.index'))
            ->assertOk()
            ->assertSee('تجربة إيجابية')
            ->assertDontSee('مستحق شكوى آلياً');
    });

    it('reads a one star survey as a complaint alert', function () {
        $admin = actingAsAdmin();
        [$visit] = completedVisitWithDoctor();

        Survey::factory()->forVisit($visit)->rating(1)->create(['created_by' => $admin->id]);

        $this->get(route('surveys.index'))
            ->assertOk()
            ->assertSee('مستحق شكوى آلياً')
            ->assertDontSee('تجربة إيجابية');
    });
});

describe('the relations of a survey', function () {
    it('names the doctor and the staff member who recorded it on the list', function () {
        $admin = actingAsAdmin();
        $admin->update(['name' => 'سمير عبد الحميد رشاد']);

        [$visit, , $doctor] = completedVisitWithDoctor();

        Survey::factory()->forVisit($visit)->rating(5)->create([
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $this->get(route('surveys.index'))
            ->assertOk()
            ->assertSee($doctor->fullname)
            ->assertSee('سمير عبد الحميد رشاد')
            ->assertDontSee('موظف الاستقبال');
    });

    it('names the doctor and the staff member who recorded it on the survey page', function () {
        $admin = actingAsAdmin();
        $admin->update(['name' => 'سمير عبد الحميد رشاد']);

        [$visit, , $doctor] = completedVisitWithDoctor();

        $survey = Survey::factory()->forVisit($visit)->rating(5)->create([
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $this->get(route('surveys.show', $survey))
            ->assertOk()
            ->assertSee($doctor->fullname)
            ->assertSee('سمير عبد الحميد رشاد')
            ->assertDontSee('موظف النظام');
    });
});

describe('deleting a survey', function () {
    it('refuses to delete a survey that raised a complaint', function () {
        $admin = actingAsAdmin();
        [$visit] = completedVisitWithDoctor();

        $survey = Survey::factory()->forVisit($visit)->rating(1)->create(['created_by' => $admin->id]);
        $complaint = Complaint::factory()->fromSurvey($survey)->unassigned()->create();

        $this->from(route('surveys.index'))
            ->delete(route('surveys.destroy', $survey))
            ->assertRedirect(route('surveys.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('surveys', ['id' => $survey->id]);
        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'survey_id' => $survey->id,
            'status' => 'new',
        ]);
    });

    it('deletes a survey that raised no complaint', function () {
        $admin = actingAsAdmin();
        [$visit] = completedVisitWithDoctor();

        $survey = Survey::factory()->forVisit($visit)->rating(5)->create(['created_by' => $admin->id]);

        $this->from(route('surveys.index'))
            ->delete(route('surveys.destroy', $survey))
            ->assertRedirect(route('surveys.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('surveys', ['id' => $survey->id]);
    });

    /*
    | The guard reads the relation, not the state of the investigation: a
    | complaint that was already resolved must not disappear with the survey it
    | was raised from.
    */
    it('refuses to delete a survey whose complaint has already been resolved', function () {
        $admin = actingAsAdmin();
        [$visit] = completedVisitWithDoctor();

        $survey = Survey::factory()->forVisit($visit)->rating(1)->create(['created_by' => $admin->id]);
        $complaint = Complaint::factory()->fromSurvey($survey)->resolved()->create();

        $this->from(route('surveys.index'))
            ->delete(route('surveys.destroy', $survey))
            ->assertRedirect(route('surveys.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('surveys', ['id' => $survey->id]);
        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'survey_id' => $survey->id,
            'status' => 'resolved',
        ]);
    });

    /*
    | The delete check runs outside a lock, which is safe only while a complaint
    | reaches a survey inside the transaction that creates that survey. The
    | manual form cannot name a survey, so no request can file a fresh complaint
    | against a survey that already exists — the window an unlocked check would
    | otherwise have to close. If this ever stops holding, the check needs a lock.
    */
    it('never files a manually written complaint against an existing survey', function () {
        $admin = actingAsAdmin();
        [$visit, $patient] = completedVisitWithDoctor();
        $survey = Survey::factory()->forVisit($visit)->rating(5)->create(['created_by' => $admin->id]);

        $this->post(route('complaints.store'), [
            'patient_id' => $patient->id,
            'survey_id' => $survey->id,
            'title' => 'شكوى مسجلة يدويًا',
            'description' => 'وصف الشكوى كما سجله موظف خدمة المرضى.',
        ])->assertRedirect(route('complaints.index'));

        $complaint = Complaint::query()->latest('id')->firstOrFail();

        expect($complaint->survey_id)->toBeNull()
            ->and($complaint->source)->toBe('manual');

        // Nothing was filed against the survey, so it stays deletable.
        $this->from(route('surveys.index'))
            ->delete(route('surveys.destroy', $survey))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('surveys', ['id' => $survey->id]);
    });
});

/*
| The list filters by rating, but the paginator was not told to carry the query
| string: the second page dropped the filter and showed every survey instead of
| the ones the user asked for.
*/
describe('paging the survey list', function () {
    it('keeps the rating filter on the next page', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $visits = Visit::factory()->completed()->count(21)
            ->recycle([$patient, $doctor, $admin])
            ->create();

        $visits->each(fn (Visit $visit) => Survey::factory()
            ->forVisit($visit)
            ->rating(5)
            ->create(['created_by' => $admin->id]));

        $response = $this->get(route('surveys.index', ['rating' => 5]))->assertOk();

        $surveys = $response->viewData('surveys');

        expect($surveys->total())->toBe(21)
            ->and($surveys->nextPageUrl())->toContain('rating=5');
    });
});
