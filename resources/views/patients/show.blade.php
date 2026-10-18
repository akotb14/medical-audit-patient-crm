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
            <a href="{{ route('patients.edit', $patient->id ?? 1) }}"
                class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>تعديل الملف</span>
            </a>
            <button type="button" onclick="window.clinicalApp.openBookModal('{{ $patient->id ?? 'pat-1' }}')"
                class="px-4 py-2 text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg shadow-sm transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>+ حجز موعد</span>
            </button>
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
                    ⚠️ {{ $patient->allergies ?: 'لا يوجد'}}
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
                <span class="text-[11px] text-slate-500 font-mono">طوارئ: {{ $patient->emergency_phone ?? 'لا يوجد' }}</span>
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="px-3 py-1 bg-slate-100 text-slate-800 text-xs font-semibold rounded-lg">
                    {{ $patient->chronic_diseases ?? 'لا يوجد'}}
                </span>
            </div>
            <div class="pt-2 text-xs text-[#64748b]">
                قريب الطوارئ: <span class="font-semibold text-slate-800">{{ $patient->emergency_name ?? 'لا يوجد' }}</span>
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
                <button type="button" class="py-4 border-b-2 border-[#00685f] text-[#00685f] flex items-center gap-1.5">
                    <span>🏥 سجل الزيارات السريرية (Visits)</span>
                    <span class="px-2 py-0.2 rounded-full text-[10px] font-bold bg-teal-100 text-teal-800">
                        {{ count($patient->visits ?? []) > 0 ? count($patient->visits) : 0 }}
                    </span>
                </button>
            </div>

            <!-- Quick Action in Tab -->
            <button 
                type="button" 
                onclick="openPatientNewVisitModal()" 
                class="my-2 px-3.5 py-1.5 text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg shadow-2xs transition flex items-center gap-1"
            >
                <span>+ تسجيل زيارة جديدة</span>
            </button>
        </div>

        <!-- Visits History & Edit Table Content -->
        <div class="p-6 space-y-5">
            <!-- Patient Visits KPI Bar -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="p-3 bg-slate-50 rounded-lg border border-slate-200/80">
                    <span class="text-[11px] font-medium text-slate-500">إجمالي الزيارات</span>
                    <p class="text-lg font-black text-slate-800 mt-0.5" id="patientVisitsTotalCount">{{ count($patient->visits ?? []) > 0 ? count($patient->visits) : 0 }}</p>
                </div>
                <div class="p-3 bg-blue-50/60 rounded-lg border border-blue-200/80">
                    <span class="text-[11px] font-medium text-blue-800">قيد الانتظار (created)</span>
                    <p class="text-lg font-black text-blue-700 mt-0.5" id="patientVisitsCreatedCount">
                        {{ isset($patient->visits) ? $patient->visits->where('visit_status', 'created')->count() : 0 }}
                    </p>
                </div>
                <div class="p-3 bg-emerald-50/60 rounded-lg border border-emerald-200/80">
                    <span class="text-[11px] font-medium text-emerald-800">مكتملة (completed)</span>
                    <p class="text-lg font-black text-emerald-700 mt-0.5" id="patientVisitsCompletedCount">
                        {{ isset($patient->visits) ? $patient->visits->where('visit_status', 'completed')->count() : 0 }}
                    </p>
                </div>
                <div class="p-3 bg-rose-50/60 rounded-lg border border-rose-200/80">
                    <span class="text-[11px] font-medium text-rose-800">ملغاة (cancelled)</span>
                    <p class="text-lg font-black text-rose-700 mt-0.5" id="patientVisitsCancelledCount">
                        {{ isset($patient->visits) ? $patient->visits->where('visit_status', 'cancelled')->count() : 0 }}
                    </p>
                </div>
            </div>

            <!-- Visits CRUD Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                  <thead class="bg-[#f8fafc] text-[#64748b] text-xs font-semibold uppercase border-b border-[#e2e8f0]">
                    <tr>
                      <th class="py-3 px-4"># المعرف</th>
                      <th class="py-3 px-4">تاريخ الزيارة (Date)</th>
                      <th class="py-3 px-4">الطبيب المعالج والعيادة (Doctor)</th>
                      <th class="py-3 px-4 w-[220px]">سبب الزيارة والتشخيص (Reason - 500 حرف)</th>
                      <th class="py-3 px-4">حالة الزيارة (visit_status)</th>
                      <th class="py-3 px-4">المسجل</th>
                      <th class="py-3 px-4 text-center">تعديل وإجراءات الزيارة</th>
                    </tr>
                  </thead>
                  <tbody id="patientVisitsTableBody" class="divide-y divide-[#e2e8f0] text-xs">
                    @foreach($patient->visits ?? [] as $visit)
                        <tr class="hover:bg-[#f8fafc] transition" id="patientVisitRow-{{ $visit->id }}">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-500">
                                #{{ $visit->id }}
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-800">
                                {{ is_string($visit->date) ? $visit->date : ($visit->date ? $visit->date->format('Y-m-d') : '') }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($visit->doctor)
                                    <span class="font-bold text-slate-900 block">{{ $visit->doctor->fullname }}</span>
                                    <span class="text-[11px] text-teal-800 font-semibold">{{ $visit->doctor->clinic->name ?? 'العيادة التخصصية' }}</span>
                                @else
                                    <span class="text-slate-400 italic">غير محدد (nullOnDelete)</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <p class="text-slate-700 font-medium line-clamp-2 max-w-sm" title="{{ $visit->reason }}">
                                    {{ $visit->reason }}
                                </p>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($visit->visit_status === 'completed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        <span>مكتملة</span>
                                    </span>
                                @elseif($visit->visit_status === 'cancelled')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                        <span>ملغاة</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-800 border border-blue-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                        <span>تم الإنشاء</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 text-[11px]">
                                {{ $visit->creator->name ?? 'موظف الاستقبال' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Edit Button (Direct Modal in Blade) -->
                                    <a 
                                        type="button" 
                                        href="{{route('visits.edit' , $visit)}}"
                                        class="px-2.5 py-1 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-lg transition shadow-2xs flex items-center gap-1"
                                        title="تعديل بيانات الزيارة"
                                    >
                                        <span>✏️ تعديل</span>
                                </a>

                                    <!-- Quick Status Toggle Forms in Blade -->
                                    @if($visit->visit_status !== 'completed')
                                        <form method="POST" action="{{ route('visits.status', $visit->id) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="visit_status" value="completed">
                                            <button 
                                                type="submit" 
                                                class="px-2 py-1 text-[11px] font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg transition"
                                                title="إتمام الزيارة"
                                            >
                                                ✓ إتمام
                                            </button>
                                        </form>
                                    @endif

                                    @if($visit->visit_status !== 'cancelled')
                                        <form method="POST" action="{{ route('visits.status', $visit->id) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="visit_status" value="cancelled">
                                            <button 
                                                type="submit" 
                                                class="px-2 py-1 text-[11px] font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg transition"
                                                title="إلغاء الزيارة"
                                            >
                                                ✕ إلغاء
                                            </button>
                                        </form>
                                    @endif

                                    <a 
                                        href="{{ route('visits.show', $visit->id) }}" 
                                        class="px-2 py-1 text-xs text-slate-500 hover:text-slate-800 rounded transition"
                                        title="عرض التفاصيل"
                                    >
                                        👁️
                                    </a>
                                </div>
                            </td>
                        </tr>
                  
                    @endforeach
                  </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
</div>
@endsection