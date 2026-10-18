@extends('layouts.app')

@section('title', 'تعديل المتابعة السريرية #' . ($followup->id ?? '---'))

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('followups.index') }}" class="hover:text-teal-700 transition">سجل المتابعات السريرية</a>
                <span>/</span>
                <a href="{{ route('followups.show', $followup) }}" class="hover:text-teal-700 transition">سجل المتابعة #{{ $followup->id }}</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">تعديل</span>
            </nav>
            <h1 class="text-2xl font-bold text-[#0b1c30]">تعديل سجل المتابعة السريرية #{{ $followup->id }}</h1>
            <p class="text-xs text-[#64748b] mt-0.5">تحديث موعد الاستحقاق، الحالة السريرية، وتوثيق نتيجة المتابعة والملاحظات</p>
        </div>
        <a href="{{ route('followups.show', $followup) }}" class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition shadow-xs">
            ← العودة للتفاصيل
        </a>
    </div>

    <!-- Edit Follow-up Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-[#f8fafc] flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-teal-100 text-[#00685f] flex items-center justify-center font-bold text-sm">
                    ✏️
                </div>
                <div>
                    <h3 class="font-bold text-sm text-[#0b1c30]">تعديل بيانات سجل المتابعة (follow_ups)</h3>
                    <p class="text-[11px] text-[#64748b]">تعديل الحقول المرتبطة بالمريض، الموعد، والنتيجة السريرية</p>
                </div>
            </div>
            <span class="text-[11px] font-mono px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">
                id: {{ $followup->id }}
            </span>
        </div>

        <form action="{{ route('followups.update', $followup) }}" method="POST" class="p-6 space-y-6 text-xs">
            @csrf
            @method('PUT')

            <!-- Patient and Visit Selection -->
            <div class="space-y-4">
                <h4 class="font-bold text-slate-800 text-xs border-b border-slate-100 pb-2 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#00685f]"></span>
                    الأطراف المعنية (المريض والزيارة السريرية المرتبطة)
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- patient_id -->
                    <div>
                        <label for="patient_id" class="font-bold text-slate-700 block mb-1.5">
                            المريض ورقم الملف (patient_id):
                        </label>
                        <select
                            name="patient_id"
                            id="patient_id"
                            class="w-full p-2.5 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800 text-xs"
                        >
                            <option value="">-- بدون ارتباط بمريض (null) --</option>
                            @if(isset($patients))
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" @selected(old('patient_id', $followup->patient_id) == $patient->id)>
                                        {{ $patient->name }} ({{ $patient->mrn }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('patient_id')
                            <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- visit_id -->
                    <div>
                        <label for="visit_id" class="font-bold text-slate-700 block mb-1.5">
                            الزيارة السريرية المرتبطة (visit_id):
                        </label>
                        <select
                            name="visit_id"
                            id="visit_id"
                            class="w-full p-2.5 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800 text-xs"
                        >
                            <option value="">-- بدون ارتباط بزيارة (null) --</option>
                            @if(isset($visits))
                                @foreach($visits as $visit)
                                    <option value="{{ $visit->id }}" @selected(old('visit_id', $followup->visit_id) == $visit->id)>
                                        زيارة #{{ $visit->id }} - {{ $visit->patient->name ?? 'مريض' }} ({{ $visit->date ? $visit->date->format('Y-m-d') : '' }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('visit_id')
                            <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Due Date and Status Fields -->
            <div class="space-y-4 pt-2">
                <h4 class="font-bold text-slate-800 text-xs border-b border-slate-100 pb-2 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                    موعد الاستحقاق والحالة السريرية
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- due_date -->
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
                            value="{{ old('due_date', $followup->due_date ? $followup->due_date->format('Y-m-d\TH:i') : '') }}"
                            class="w-full p-2.5 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800 text-xs font-mono"
                        />
                        @error('due_date')
                            <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- status -->
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
                            onchange="handleStatusChange(this.value)"
                        >
                            <option value="scheduled" @selected(old('status', $followup->status) == 'scheduled')>
                                ⏳ مجدولة (scheduled) - بانتظار الاتصال / الإجراء
                            </option>
                            <option value="done" @selected(old('status', $followup->status) == 'done')>
                                ✓ مكتملة (done) - تم التواصل وتوثيق النتيجة
                            </option>
                            <option value="missed" @selected(old('status', $followup->status) == 'missed')>
                                ✕ فائتة (missed) - لم يتم الرد / تعذر التواصل
                            </option>
                            <option value="cancelled" @selected(old('status', $followup->status) == 'cancelled')>
                                🚫 ملغاة (cancelled) - تم إلغاؤها
                            </option>
                        </select>
                        @error('status')
                            <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Reason Field -->
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
                    class="w-full p-3 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800 text-xs leading-relaxed"
                >{{ old('reason', $followup->reason) }}</textarea>
                @error('reason')
                    <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Outcome Note & Completed At -->
            <div class="space-y-4 pt-2 border-t border-slate-100">
                <h4 class="font-bold text-slate-800 text-xs flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                    توثيق النتيجة السريرية (outcome_note & completed_at)
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label for="outcome_note" class="font-bold text-slate-700 block mb-1.5">
                            ملاحظات ونتيجة المتابعة (outcome_note):
                        </label>
                        <textarea
                            name="outcome_note"
                            id="outcome_note"
                            rows="3"
                            placeholder="تدوين استجابة المريض أو الإجراء المتخذ عند التواصل..."
                            class="w-full p-2.5 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800 text-xs leading-relaxed"
                        >{{ old('outcome_note', $followup->outcome_note) }}</textarea>
                        @error('outcome_note')
                            <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="completed_at" class="font-bold text-slate-700 block">
                                تاريخ وساعة الإتمام (completed_at):
                            </label>
                            <button type="button" onclick="setCompletedAtNow()" class="text-[10px] text-emerald-700 font-bold hover:underline">
                                تعيين للآن
                            </button>
                        </div>
                        <input
                            type="datetime-local"
                            name="completed_at"
                            id="completed_at"
                            value="{{ old('completed_at', $followup->completed_at ? $followup->completed_at->format('Y-m-d\TH:i') : '') }}"
                            class="w-full p-2.5 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800 text-xs font-mono"
                        />
                        @error('completed_at')
                            <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- created_by -->
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
                                    <option value="{{ $user->id }}" @selected(old('created_by', $followup->created_by) == $user->id)>
                                        {{ $user->name }} ({{ $user->role->name ?? 'كادر طبي' }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('followups.show', $followup) }}" class="px-4 py-2 font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                    إلغاء
                </a>
                <button type="submit" class="px-6 py-2.5 font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg shadow-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>حفظ التعديلات على سجل المتابعة</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateCharCounter() {
    const input = document.getElementById('reason');
    const counter = document.getElementById('charCounter');
    if (input && counter) {
        counter.textContent = `${input.value.length} / 500 حرف`;
    }
}

function handleStatusChange(status) {
    const completedInput = document.getElementById('completed_at');
    if (status === 'done' && completedInput && !completedInput.value) {
        setCompletedAtNow();
    } else if (status !== 'done' && completedInput) {
        completedInput.value = '';
    }
}

function setCompletedAtNow() {
    const completedInput = document.getElementById('completed_at');
    if (completedInput) {
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
