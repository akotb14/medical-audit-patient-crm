<?php

namespace App\Http\Controllers;

use App\Http\Requests\SurveyRequest\CreateSurveyRequest;
use App\Models\Complaint;
use App\Models\Survey;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SurveyController extends Controller
{
    //
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Survey::class);
        $request->validate([
            'search' => 'nullable|string',
        ]);
        $search = $request['search'] ?? '';
        $rating = (int) $request['rating'] ?? 'all';

        $query = Survey::query()->with('patient:id,fullname,mrn,deleted_at', 'visit:id,date,doctor_id', 'visit.doctor:id,fullname', 'creator:id,name', 'complaint');

        $stats = (clone $query)->stats()->first();

        if ($search) {
            $query->search($search);
        }
        if ($rating && $rating > 0 && $rating <= 5) {
            $query->where('rating', $rating);
        }

        $surveys = $query->latest()->paginate(20)->withQueryString();

        return view('surveys.index', compact('surveys', 'stats'));
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

        $data = $request->validated();
        $visit = Visit::query()
            ->findOrFail($data['visit_id']);

        $data['patient_id'] = $visit->patient_id;
        if ($visit['visit_status'] !== 'completed') {
            return back()->with('error', 'لا يمكن تسجيل استبيان إلا بعد إنهاء الزيارة');
        }
        $data['created_by'] = Auth::id();

        if ($data['rating'] >= 3) {
            Survey::create($data);

            return back()->with('success', 'تم تسجيل الاستبيان بنجاح');
        } else {
            DB::transaction(function () use ($data) {
                $survey = Survey::create($data);
                Complaint::create([
                    'patient_id' => $data['patient_id'],
                    'survey_id' => $survey->id,
                    'source' => 'survey',
                    'title' => 'ملاحظات سلبية في استبيان رضا المرضى',
                    'description' => $data['comment'] ?? 'لم يترك المريض تعليقًا نصيًا، والتقييم المنخفض هو سبب تسجيل الشكوى.',
                    'status' => 'new',
                    'created_by' => Auth::id(),
                ]);
            });

            return back()->with('success', 'تم تسجيل الاستبيان بنجاح وفتح شكوي تلقائي بسبب استبيان المريض');
        }
    }

    public function destroy(Survey $survey)
    {
        Gate::authorize('delete', $survey);

        /*
        | The complaint raised from a survey is removed by the foreign key together
        | with it, so a survey that produced one is refused instead of taking the
        | complaint and its investigation with it.
        */
        if ($survey->complaint()->exists()) {
            return back()->with('error', 'لا يمكن حذف الاستبيان لوجود شكوى مرتبطة به.');
        }

        $survey->delete();

        return back()->with('success', 'تم حذف الاستبيان بنجاح');
    }
}
