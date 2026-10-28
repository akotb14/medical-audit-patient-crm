@extends('layouts.app')

@section('title', 'الملف السريري الشامل - ' . ($patient->name ?? 'سليمان فهد المنصور'))

@section('content')
    <div class="space-y-6">
        <!-- Top Patient Hero Header -->
        <div
            class="bg-white rounded-xl border border-[#e2e8f0] p-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div
                    class="w-16 h-16 rounded-2xl bg-[#00685f] text-white flex items-center justify-center font-bold text-2xl shadow-sm">
                    {{ mb_substr($patient->fullname ?? 'س', 0, 1) }}
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-2xl font-bold text-[#0b1c30]">{{ $patient->fullname ?? 'سليمان فهد المنصور' }}</h1>
                        <span
                            class="font-mono text-xs font-bold text-[#00685f] bg-[#eff4ff] px-2.5 py-1 rounded border border-teal-200">
                            {{ $patient->mrn ?? 'MRN-84920' }}
                        </span>
                        <span
                            class="font-mono font-bold text-xs bg-rose-50 text-rose-700 px-2.5 py-1 rounded border border-rose-200">
                            فصيلة الدم: {{ $patient->blood_group ?? 'A+' }}
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-xs text-[#64748b] mt-2 font-mono">
                        <span>عنوان: {{ $patient->address ?? '1098234710' }}</span>
                        <span>·</span>
                        <span>العمر: {{ $patient->birthdate?->age ?? 58 }} سنة ({{ $patient->gender ?? 'ذكر' }})</span>
                        <span>·</span>
                        <span class="text-teal-800 font-bold">هاتف: {{ $patient->phone ?? '0505123984' }}</span>
                    </div>
                </div>
            </div>

            <!-- Action Controls -->
            <div class="flex flex-wrap items-center gap-2 self-start md:self-auto">
                @can('update', $patient)
                    <a href="{{ route('patients.edit', $patient->id ?? 1) }}"
                        class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>تعديل الملف</span>
                    </a>
                @endcan
                <button type="button" onclick="window.print()"
                    class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 border border-slate-200 rounded-lg transition"
                    title="طباعة التقرير الطبي الكامل">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Clinical Safety Strip: Drug Allergies & Chronic Conditions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Allergy Alert Banner (WCAG AAA contrast) -->
            <div class="bg-[#fff1f2] border border-[#fecdd3] rounded-xl p-5 space-y-3">
                <div class="flex items-center gap-2 text-xs font-bold text-[#be123c]">
                    <svg class="w-4 h-4 text-[#be123c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>تنبيه الحساسية الدوائية والغذائية المؤكدة:</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span
                        class="px-3 py-1 bg-white text-[#be123c] font-bold text-xs rounded-lg border border-[#fecdd3] shadow-2xs">
                        ⚠️ {{ $patient->allergies ?: 'لا يوجد' }}
                    </span>

                </div>
                <p class="text-[11px] text-[#9f1239] leading-relaxed">
                    يمنع صرف أو حقن أي مضادات حيوية أو مستحضرات تحوي هذه المواد دون استشارة أخصائي الحساسية.
                </p>
            </div>

            <!-- Chronic Diseases & Emergency Contacts -->
            <div class="bg-white border border-[#e2e8f0] rounded-xl p-5 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <span class="font-bold text-xs text-[#0b1c30]">الأمراض والمتابعات المزمنة:</span>
                    <span class="text-[11px] text-slate-500 font-mono">طوارئ:
                        {{ $patient->emergency_phone ?? 'لا يوجد' }}</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="px-3 py-1 bg-slate-100 text-slate-800 text-xs font-semibold rounded-lg">
                        {{ $patient->chronic_diseases ?? 'لا يوجد' }}
                    </span>
                </div>
                <div class="pt-2 text-xs text-[#64748b]">
                    قريب الطوارئ: <span
                        class="font-semibold text-slate-800">{{ $patient->emergency_name ?? 'لا يوجد' }}</span>
                </div>
            </div>
        </div>

        <!-- Clinical History & Care Tabs -->
        <div class="bg-white rounded-xl border border-[#e2e8f0] shadow-xs overflow-hidden">
            <!-- Tabs Nav -->
            <div class="border-b border-[#e2e8f0] bg-[#f8fafc] px-6 flex gap-6 text-xs font-semibold">
                <button class="py-4 border-b-2 border-[#00685f] text-[#00685f]">
                    سجل المواعيد والزيارات السريرية
                </button>
                <button class="py-4 border-b-2 border-transparent text-[#64748b] hover:text-slate-900">
                    المتابعات الحرجة وما بعد الزيارة
                </button>
                <button class="py-4 border-b-2 border-transparent text-[#64748b] hover:text-slate-900">
                    سجل الملاحظات والشكاوى
                </button>
            </div>

            <!-- Appointments History Table -->
            <div class="bg-white rounded-xl border border-[#e2e8f0] shadow-xs overflow-hidden">
                <!-- Tabs Nav -->
                <div class="border-b border-[#e2e8f0] bg-[#f8fafc] px-6 flex items-center justify-between">
                    <div class="flex gap-6 text-xs font-semibold">
                        <button type="button"
                            class="py-4 border-b-2 border-[#00685f] text-[#00685f] flex items-center gap-1.5">
                            <span>🏥 سجل الزيارات السريرية (Visits)</span>
                            <span class="px-2 py-0.2 rounded-full text-[10px] font-bold bg-teal-100 text-teal-800">
                                {{ $visits->total() }}
                            </span>
                        </button>
                    </div>
                    @can('create', App\Models\Visit::class)
                        <!-- Quick Action in Tab -->
                        <a href="{{ route('visits.create', ['patient_id' => $patient->id]) }}"
                            class="my-2 px-3.5 py-1.5 text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg">
                            + تسجيل زيارة جديدة
                        </a>
                    @endcan
                </div>

                <!-- Visits History & Edit Table Content -->
                <div class="p-6 space-y-5">
                    <!-- Patient Visits KPI Bar -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200/80">
                            <span class="text-[11px] font-medium text-slate-500">إجمالي الزيارات</span>
                            <p class="text-lg font-black text-slate-800 mt-0.5" id="patientVisitsTotalCount">
                                {{ $visits->total() }}</p>
                        </div>
                        <div class="p-3 bg-blue-50/60 rounded-lg border border-blue-200/80">
                            <span class="text-[11px] font-medium text-blue-800">قيد الانتظار (created)</span>
                            <p class="text-lg font-black text-blue-700 mt-0.5" id="patientVisitsCreatedCount">
                                {{ $visitCounts['created'] }}
                            </p>
                        </div>
                        <div class="p-3 bg-emerald-50/60 rounded-lg border border-emerald-200/80">
                            <span class="text-[11px] font-medium text-emerald-800">مكتملة (completed)</span>
                            <p class="text-lg font-black text-emerald-700 mt-0.5" id="patientVisitsCompletedCount">
                                {{ $visitCounts['completed'] }}
                            </p>
                        </div>
                        <div class="p-3 bg-rose-50/60 rounded-lg border border-rose-200/80">
                            <span class="text-[11px] font-medium text-rose-800">ملغاة (cancelled)</span>
                            <p class="text-lg font-black text-rose-700 mt-0.5" id="patientVisitsCancelledCount">
                                {{ $visitCounts['cancelled'] }}
                            </p>
                        </div>
                    </div>
                    @can('viewAny', App\Models\visit::class)
                        <!-- Visits CRUD Table -->
                        <x-visits.table :visits="$visits" />
                    @endcan

                    @if (isset($visits) && method_exists($visits, 'links'))
                        <div class="p-4 border-t border-slate-100 bg-[#f8fafc]">
                            {{ $visits->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection
