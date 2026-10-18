@extends('layouts.app')

@section('title', 'جدولة متابعة سريرية جديدة (New Follow-up)')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('followups.index') }}" class="hover:text-teal-700 transition">سجل المتابعات السريرية</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">جدولة متابعة جديدة</span>
            </nav>
            <h1 class="text-2xl font-bold text-[#0b1c30]">جدولة متابعة سريرية جديدة (New Follow-up)</h1>
            <p class="text-xs text-[#64748b] mt-0.5">توثيق مواعيد المتابعة لما بعد الزيارة والنتائج المخبرية الحرجة في جدول follow_ups</p>
        </div>
        <a href="{{ route('followups.index') }}" class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition shadow-xs">
            ← العودة للسجل
        </a>
    </div>

    <!-- Create Follow-up Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-[#f8fafc] flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-teal-100 text-[#00685f] flex items-center justify-center font-bold text-sm">
                    📋
                </div>
                <div>
                    <h3 class="font-bold text-sm text-[#0b1c30]">بيانات نموذج المتابعة (follow_ups Schema)</h3>
                    <p class="text-[11px] text-[#64748b]">الحقول المؤشرة بعلامة (<span class="text-red-500">*</span>) إلزامية</p>
                </div>
            </div>
            <span class="text-[11px] font-mono px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">
                schema: follow_ups
            </span>
        </div>

        <form action="{{ route('followups.store') }}" method="POST" class="p-6 space-y-6 text-xs">
            @csrf

            <!-- Patient and Visit Selection (Foreign Keys) -->
            <div class="space-y-4">
                <h4 class="font-bold text-slate-800 text-xs border-b border-slate-100 pb-2 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#00685f]"></span>
                    الأطراف المعنية (المريض والزيارة السريرية المرتبطة)
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- patient_id: nullable foreignId constrained to patients -->
                    <div>
                        <label for="patient_id" class="font-bold text-slate-700 block mb-1.5">
                            المريض ورقم الملف (patient_id):
                            <span class="text-xs text-slate-400 font-normal mr-1">(اختياري / FK)</span>
                        </label>
                        <select
                            name="patient_id"
                            id="patient_id"
                            class="w-full p-2.5 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800 text-xs"
                        >
                            <option value="">-- اختر المريض (أو اتركه فارغاً) --</option>
                            @if(isset($patients))
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" @selected(old('patient_id', $selectedPatientId ?? null) == $patient->id)>
                                        {{ $patient->name }} ({{ $patient->mrn }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('patient_id')
                            <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-[10px] text-slate-400 mt-1">مرتبط بجدول patients مع nullOnDelete</p>
                    </div>

                    <!-- visit_id: nullable foreignId constrained to visits -->
                    <div>
                        <label for="visit_id" class="font-bold text-slate-700 block mb-1.5">
                            الزيارة الطبية السابقة (visit_id):
                            <span class="text-xs text-slate-400 font-normal mr-1">(اختياري / FK)</span>
                        </label>
                        <select
                            name="visit_id"
                            id="visit_id"
                            class="w-full p-2.5 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800 text-xs"
                        >
                            <option value="">-- اختر الزيارة المرتبطة إن وجدت --</option>
                            @if(isset($visits))
                                @foreach($visits as $visit)
                                    <option value="{{ $visit->id }}" @selected(old('visit_id', $selectedVisitId ?? null) == $visit->id)>
                                        زيارة #{{ $visit->id }} - {{ $visit->patient->name ?? 'مريض' }} ({{ $visit->date ? $visit->date->format('Y-m-d') : '' }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('visit_id')
                            <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-[10px] text-slate-400 mt-1">مرتبط بجدول visits مع nullOnDelete</p>
                    </div>
                </div>
            </div>

            <!-- Due Date and Status Fields -->
            <div class="space-y-4 pt-2">
                <h4 class="font-bold text-slate-800 text-xs border-b border-slate-100 pb-2 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                    موعد الاستحقاق وحالة المتابعة
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- due_date: dateTime -->
                    <div>
                        <label for="due_date" class="font-bold text-slate-700 block mb-1.5">
                            تاريخ ووقت استحقاق المتابعة (due_date):
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="datetime-local"
                            name="due_date"
                            id="due_date"
                            required
                            value="{{ old('due_date', now()->addDay()->format('Y-m-d\TH:i')) }}"
                            class="w-full p-2.5 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800 text-xs font-mono"
                        />
                        @error('due_date')
                            <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror

                        <!-- Quick Time Presets -->
                        <div class="flex items-center gap-1.5 mt-2">
                            <span class="text-[10px] text-slate-400">تحديد سريع:</span>
                            <button type="button" onclick="setPresetDate(1)" class="px-2 py-0.5 text-[10px] bg-slate-100 hover:bg-slate-200 text-slate-700 rounded transition">
                                بعد 24 ساعة
                            </button>
                            <button type="button" onclick="setPresetDate(3)" class="px-2 py-0.5 text-[10px] bg-slate-100 hover:bg-slate-200 text-slate-700 rounded transition">
                                بعد 3 أيام
                            </button>
                            <button type="button" onclick="setPresetDate(7)" class="px-2 py-0.5 text-[10px] bg-slate-100 hover:bg-slate-200 text-slate-700 rounded transition">
                                بعد أسبوع
                            </button>
                        </div>
                    </div>

                    <!-- status: enum('scheduled', 'done', 'missed', 'cancelled') -->
                    <div>
                        <label for="status" class="font-bold text-slate-700 block mb-1.5">
                            حالة المتابعة (status):
                            <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="status"
                            id="status"
                            required
                            class="w-full p-2.5 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800 text-xs"
                            onchange="toggleOutcomeFields(this.value)"
                        >
                            <option value="scheduled" @selected(old('status') == 'scheduled' || !old('status'))>
                                ⏳ مجدولة (scheduled) - بانتظار الاتصال / الإجراء
                            </option>
                            <option value="done" @selected(old('status') == 'done')>
                                ✓ مكتملة (done) - تم التواصل وتوثيق النتيجة
                            </option>
                            <option value="missed" @selected(old('status') == 'missed')>
                                ✕ فائتة (missed) - لم يتم الرد / تعذر التواصل
                            </option>
                            <option value="cancelled" @selected(old('status') == 'cancelled')>
                                🚫 ملغاة (cancelled) - تم إلغاؤها بطلب المريض أو الطبيب
                            </option>
                        </select>
                        @error('status')
                            <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Reason Field (string, 500) -->
            <div class="space-y-2 pt-2">
                <div class="flex items-center justify-between">
                    <label for="reason" class="font-bold text-slate-700 block">
                        سبب وأهداف المتابعة السريرية (reason):
                        <span class="text-red-500">*</span>
                    </label>
                    <span id="charCounter" class="text-[11px] font-mono text-slate-400">0 / 500 حرف</span>
                </div>
                <textarea
                    name="reason"
                    id="reason"
                    rows="3"
                    maxlength="500"
                    required
                    placeholder="مثال: متابعة قياس ضغط الدم بعد تعديل جرعة العلاج والتأكد من عدم وجود دوار، أو متابعة ظهور نتائج تحليل وظائف الكلى التراكمي..."
                    class="w-full p-3 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800 text-xs leading-relaxed"
                >{{ old('reason') }}</textarea>
                @error('reason')
                    <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror

                <!-- Quick Clinical Reason Templates -->
                <div class="flex flex-wrap gap-1.5 pt-1">
                    <span class="text-[11px] text-slate-400 self-center">قوالب سريرية سريعة:</span>
                    <button type="button" onclick="setReasonTemplate('متابعة نتائج الفحوصات المخبرية الحرجة والتأكد من استقرار المؤشرات الحيوية للمريض.')" class="px-2 py-1 text-[10px] bg-slate-100 hover:bg-teal-50 hover:text-teal-800 text-slate-600 rounded border border-slate-200 transition">
                        + نتائج مخبرية
                    </button>
                    <button type="button" onclick="setReasonTemplate('متابعة استقرار ضغط الدم والسكر بعد تعديل الجرعات العلاجية وملاحظة أي أعراض جانبية.')" class="px-2 py-1 text-[10px] bg-slate-100 hover:bg-teal-50 hover:text-teal-800 text-slate-600 rounded border border-slate-200 transition">
                        + استقرار الضغط والجرعات
                    </button>
                    <button type="button" onclick="setReasonTemplate('متابعة التئام الجرح والتأكد من عدم وجود نزيف أو التهاب بعد الإجراء الجراحي المصغر.')" class="px-2 py-1 text-[10px] bg-slate-100 hover:bg-teal-50 hover:text-teal-800 text-slate-600 rounded border border-slate-200 transition">
                        + ما بعد الجراحة
                    </button>
                </div>
            </div>

            <!-- Outcome Note & Completed At (Conditional / Optional) -->
            <div id="outcomeSection" class="space-y-4 pt-2 border-t border-slate-100">
                <h4 class="font-bold text-slate-800 text-xs flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                    توثيق النتيجة السريرية (outcome_note & completed_at)
                    <span class="text-slate-400 font-normal text-[11px]">(اختياري عند الإنشاء)</span>
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label for="outcome_note" class="font-bold text-slate-700 block mb-1.5">
                            ملاحظات ونتيجة المتابعة (outcome_note):
                        </label>
                        <textarea
                            name="outcome_note"
                            id="outcome_note"
                            rows="2"
                            placeholder="إذا تم التواصل بالفعل، دوّن هنا استجابة المريض أو الإجراء الذي تم اتخاذه..."
                            class="w-full p-2.5 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800 text-xs"
                        >{{ old('outcome_note') }}</textarea>
                        @error('outcome_note')
                            <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="completed_at" class="font-bold text-slate-700 block mb-1.5">
                            تاريخ وساعة الإتمام (completed_at):
                        </label>
                        <input
                            type="datetime-local"
                            name="completed_at"
                            id="completed_at"
                            value="{{ old('completed_at') }}"
                            class="w-full p-2.5 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800 text-xs font-mono"
                        />
                        <p class="text-[10px] text-slate-400 mt-1">يُملأ تلقائياً عند تغيير الحالة إلى مكتملة (done)</p>
                    </div>

                    <!-- created_by: nullable foreignId constrained to users -->
                    <div>
                        <label for="created_by" class="font-bold text-slate-700 block mb-1.5">
                            موثق السجل / المسؤول (created_by):
                        </label>
                        <select
                            name="created_by"
                            id="created_by"
                            class="w-full p-2.5 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800 text-xs"
                        >
                            <option value="">-- اختر المستخدم / الموثق --</option>
                            @if(isset($users))
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected(old('created_by') == $user->id)>
                                        {{ $user->name }} ({{ $user->role->name ?? 'كادر طبي' }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            </div>

            <!-- Database & Foreign Keys Architecture Note -->
            <div class="bg-teal-50/40 rounded-xl border border-teal-200 p-4 text-[11px] text-slate-700 space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-teal-900">
                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>معمارية جدول follow_ups والقيود المرجعية:</span>
                </div>
                <div class="font-mono text-[10px] text-slate-600 leading-relaxed">
                    • <strong class="text-teal-900">visit_id:</strong> foreignId('visit_id')->nullable()->constrained('visits')->nullOnDelete()<br>
                    • <strong class="text-teal-900">patient_id:</strong> foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete()<br>
                    • <strong class="text-teal-900">created_by:</strong> foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()<br>
                    • <strong class="text-teal-900">status:</strong> enum('scheduled', 'done', 'missed', 'cancelled')
                </div>
            </div>

            <!-- Form Actions -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('followups.index') }}" class="px-4 py-2 font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                    إلغاء التراجع
                </a>
                <button type="submit" class="px-6 py-2.5 font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg shadow-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>حفظ وجدولة المتابعة السريرية</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function setPresetDate(days) {
    const d = new Date();
    d.setDate(d.getDate() + days);
    d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
    const val = d.toISOString().slice(0, 16);
    const input = document.getElementById('due_date');
    if (input) input.value = val;
}

function setReasonTemplate(text) {
    const input = document.getElementById('reason');
    if (input) {
        input.value = text;
        updateCharCounter();
    }
}

function updateCharCounter() {
    const input = document.getElementById('reason');
    const counter = document.getElementById('charCounter');
    if (input && counter) {
        counter.textContent = `${input.value.length} / 500 حرف`;
    }
}

function toggleOutcomeFields(status) {
    const completedInput = document.getElementById('completed_at');
    if (status === 'done' && completedInput && !completedInput.value) {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        completedInput.value = now.toISOString().slice(0, 16);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const reasonInput = document.getElementById('reason');
    if (reasonInput) {
        reasonInput.addEventListener('input', updateCharCounter);
        updateCharCounter();
    }
});
</script>
@endsection
