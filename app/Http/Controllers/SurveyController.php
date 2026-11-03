<?php

namespace App\Http\Controllers;

use App\Http\Requests\SurveyRequest\CreateSurveyRequest;
use App\Http\Requests\SurveyRequest\SurveyIndexRequest;
use App\Models\Survey;
use App\Services\SurveyService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class SurveyController extends Controller
{
    public function __construct(private SurveyService $surveyService) {}

    public function index(SurveyIndexRequest $request)
    {
        Gate::authorize('viewAny', Survey::class);

        return view('surveys.index', $this->surveyService->paginateIndex($request->validated()));
    }

    public function show(Survey $survey)
    {
        Gate::authorize('view', $survey);
        $survey->load('patient:id,fullname,mrn,deleted_at', 'visit:id,date,visit_status,doctor_id', 'visit.doctor:id,fullname', 'creator:id,name', 'complaint');

        return view('surveys.show', compact('survey'));
    }

    public function store(CreateSurveyRequest $request)
    {
        Gate::authorize('create', Survey::class);

        $result = $this->surveyService->submit($request->validated(), Auth::id());

        if (! $result['recorded']) {
            return back()->with('error', 'لا يمكن تسجيل استبيان إلا بعد إنهاء الزيارة');
        }

        if ($result['complaint_opened']) {
            return back()->with('success', 'تم تسجيل الاستبيان بنجاح وفتح شكوي تلقائي بسبب استبيان المريض');
        }

        return back()->with('success', 'تم تسجيل الاستبيان بنجاح');
    }

    public function destroy(Survey $survey)
    {
        Gate::authorize('delete', $survey);

        if (! $this->surveyService->delete($survey)) {
            return back()->with('error', 'لا يمكن حذف الاستبيان لوجود شكوى مرتبطة به.');
        }

        return back()->with('success', 'تم حذف الاستبيان بنجاح');
    }
}
