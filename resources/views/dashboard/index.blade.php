@extends('layouts.app')

@section('title', 'لوحة البيانات والتحليل السريري المتقدم')

@section('content')
    <div class="space-y-6">
        <!-- 1. Executive Operations Header Bar -->
        <div
            class="bg-gradient-to-l from-white via-white to-teal-50/40 rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-xs relative overflow-hidden">
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span
                            class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-teal-50 text-[#00685f] border border-teal-200">
                            منظومة إدارة الزيارات والمتابعات السريرية
                        </span>
                        <span class="text-xs text-slate-500 font-medium">
                            {{ now()->locale('ar')->translatedFormat('l، d F Y') }}
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0b1c30] tracking-tight">
                        لوحة البيانات والعمليات السريرية
                    </h1>
                    <p class="text-xs sm:text-sm text-[#64748b] max-w-3xl leading-relaxed">
                        منظومة المراقبة اللحظية لتدفق الزيارات السريرية، المتابعات الحرجة، مؤشرات رضا المراجعين والجودة،
                        وتوزيع أحمال العيادات التخصصية.
                    </p>
                </div>

                <!-- Quick Action Command Strip -->
                <div class="flex items-center gap-2.5 flex-wrap shrink-0">
                    <a href="{{ route('visits.create') }}"
                        class="flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-xl shadow-sm hover:shadow transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>+ تسجيل زيارة جديدة</span>
                    </a>

                    {{-- <button type="button" onclick="openCreateSurveyModal('createSurveyModal')"
                        class="flex items-center gap-2 px-3.5 py-2.5 text-xs font-bold text-teal-800 bg-teal-50 hover:bg-teal-100 border border-teal-200 rounded-xl transition cursor-pointer">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                        <span>+ استبيان رضا </span>
                    </button> --}}

                    <button type="button" onclick="window.print()"
                        class="p-2.5 text-slate-500 hover:text-slate-800 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl shadow-2xs transition"
                        title="طباعة تقرير العمليات السريرية">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Decorative background glow -->
            <div class="absolute -left-20 -bottom-20 w-64 h-64 rounded-full bg-teal-100/40 blur-3xl pointer-events-none">
            </div>
        </div>

        <!-- 2. Main Executive Clinical KPI Ribbon (6 Rich Cards) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            <!-- KPI 1: Visits Flow -->
            <x-dashboard.kpi-card title="إجمالي الزيارات السريرية" :value="$totalVisits" subvalue="زيارة مسجلة" icon="🏥"
                color="teal" :progress="$completionRate" :footer="$completedVisits . ' مكتمل · ' . $scheduledVisits . ' قادم'" :link="route('visits.index')" />

            <!-- KPI 2: Critical Follow-ups (follow_ups schema) -->
            <x-dashboard.kpi-card title="المتابعات السريرية الحرجة" :value="$scheduledFollowups" subvalue="حالة قيد المتابعة"
                icon="🩺" color="amber" badge="أولوية سريرية" :footer="$doneFollowups . ' موثق · ' . $missedFollowups . ' فائت'" :link="route('follow_ups.index')" />

            <!-- KPI 3: Satisfaction Index (FR-5, BR-5) -->
            <x-dashboard.kpi-card title="مؤشر تجربة ورضا المرضى" :value="$avgRating . ' ★'" subvalue="من 5.0 نجوم" icon="⭐"
                color="emerald" :trend="$positiveRate . '% إيجابي'" :trend-positive="true" :progress="$positiveRate" :footer="$rating5Count . ' ممتاز (5★) · ' . $lowRatingSurveysCount . ' تنبيه جودة'"
                :link="route('surveys.index')" />

            <!-- KPI 4: Complaints & Quality SLA (FR-6, BR-7) -->
            <x-dashboard.kpi-card title="تذاكر الشكاوى والاعتراض" :value="$activeComplaintsCount" subvalue="تذكرة نشطة" icon="🚨"
                color="rose" :footer="$autoComplaintsFromSurveys . ' ناتجة آلياً · ' . $resolvedComplaints . ' تم حلها'" :link="route('complaints.index')" />

            <!-- KPI 5: Specialty Clinics & Capacity -->
            <x-dashboard.kpi-card title="العيادات والأقسام التخصصية" :value="$activeClinics . '/' . $totalClinics" subvalue="أجنحة مشغلة"
                icon="🏢" color="blue" :footer="$activeDoctors . ' طبيب مسجل'" :link="route('clinics.index')" />

            <!-- KPI 6: Patients MRN & Clinical Safety -->
            <x-dashboard.kpi-card title="دليل وسجل المرضى (MRN)" :value="$totalPatients" subvalue="ملف طبي نشط" icon="👤"
                color="purple" badge="أمان المريض" :footer="$allergyRiskPatients . ' حالات حساسية دوائية حرجة'" :link="route('patients.index')" />
        </div>



        <!-- 4. Quality Lifecycle Stepper & Specialty Clinics Load (2 Panels) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Visual Panel 3: Complaints 5-Stage Lifecycle Stepper (FR-6, BR-7) (6 Cols) -->
            <div class="lg:col-span-6 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span>
                            <h3 class="font-bold text-base text-[#0b1c30]">دورة معالجة الشكاوى</h3>
                        </div>
                        <p class="text-xs text-[#64748b] mt-0.5">مراحل التدقيق الخماسية وإلزامية توثيق الحل للإغلاق </p>
                    </div>
                    <a href="{{ route('complaints.index') }}" class="text-xs font-bold text-[#00685f] hover:underline">
                        فتح سجل الشكاوى ➜
                    </a>
                </div>

                <!-- 5-Stage Horizontal Stepper Component -->
                <div class="grid grid-cols-5 gap-2 text-center pt-2">
                    <!-- Step 1: New -->
                    <div class="p-3 bg-red-50 border border-red-200 rounded-2xl space-y-1">
                        <div
                            class="w-6 h-6 rounded-full bg-red-600 text-white font-bold text-xs flex items-center justify-center mx-auto">
                            1</div>
                        <div class="text-[11px] font-bold text-red-900">جديدة</div>
                        <div class="text-base font-extrabold font-mono text-red-800">{{ $newComplaints }}</div>
                    </div>

                    <!-- Step 2: Assigned -->
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-2xl space-y-1">
                        <div
                            class="w-6 h-6 rounded-full bg-amber-600 text-white font-bold text-xs flex items-center justify-center mx-auto">
                            2</div>
                        <div class="text-[11px] font-bold text-amber-900">مسندة</div>
                        <div class="text-base font-extrabold font-mono text-amber-800">{{ $assignedComplaints }}</div>
                    </div>

                    <!-- Step 3: Investigating -->
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-2xl space-y-1">
                        <div
                            class="w-6 h-6 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center mx-auto">
                            3</div>
                        <div class="text-[11px] font-bold text-blue-900">تحقيق</div>
                        <div class="text-base font-extrabold font-mono text-blue-800">{{ $investigatingComplaints }}</div>
                    </div>

                    <!-- Step 4: Resolved (BR-7 Note Required) -->
                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-2xl space-y-1">
                        <div
                            class="w-6 h-6 rounded-full bg-emerald-600 text-white font-bold text-xs flex items-center justify-center mx-auto">
                            4</div>
                        <div class="text-[11px] font-bold text-emerald-900">تم الحل</div>
                        <div class="text-base font-extrabold font-mono text-emerald-800">{{ $resolvedComplaints }}</div>
                    </div>

                    <!-- Step 5: Closed -->
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl space-y-1">
                        <div
                            class="w-6 h-6 rounded-full bg-slate-600 text-white font-bold text-xs flex items-center justify-center mx-auto">
                            5</div>
                        <div class="text-[11px] font-bold text-slate-800">مغلقة</div>
                        <div class="text-base font-extrabold font-mono text-slate-700">{{ $closedComplaints }}</div>
                    </div>
                </div>

                <!-- BR-7 Requirement Notice -->
                <div
                    class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-between text-xs">
                    <span class="text-slate-600">
                         لا يمكن إغلاق أي شكوى بدون تدوين ملاحظة الحل.
                    </span>
                    <span
                        class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-800 border border-teal-200 font-mono">
                        @if ($totalComplaints > 0)
                            {{ round((($resolvedComplaints + $closedComplaints) / $totalComplaints) * 100, 0) }}% نسبة
                            الحل
                        @else
                            لا توجد شكاوى مسجلة
                        @endif
                    </span>
                </div>
            </div>

            <!-- Visual Panel 4: Specialty Clinics Caseload & Doctor Ratio (6 Cols) -->
            <div class="lg:col-span-6 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#00685f]"></span>
                            <h3 class="font-bold text-base text-[#0b1c30]">أحمال العيادات التخصصية والكوادر
                            </h3>
                        </div>
                        <p class="text-xs text-[#64748b] mt-0.5">توزيع الأطباء الاستشاريين وحصص الكشف السريري</p>
                    </div>
                    <a href="{{ route('clinics.index') }}" class="text-xs font-bold text-[#00685f] hover:underline">
                        دليل العيادات ➜
                    </a>
                </div>

                <!-- Clinics Workload List -->
                <div class="space-y-3 pt-1">
                    @forelse($clinicsLoad as $clinic)
                        <div
                            class="p-3 bg-slate-50/70 hover:bg-slate-100/70 rounded-2xl transition border border-slate-200/60 space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900">{{ $clinic->name }}</span>
                                    
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] text-teal-800 font-bold">{{ $clinic->doctors_count }}
                                        أطباء</span>

                                </div>
                            </div>

                        </div>
                    @empty
                        <div class="p-4 text-center text-xs text-slate-400">
                            لا توجد بيانات عيادات كافية لعرض الأحمال.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- 5. Urgent Follow-ups & Live Clinical Activity Audit (2 Columns) -->
        <div class="grid grid-cols-1 gap-6">
            <!-- Urgent Follow-ups Feed -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <h3 class="font-bold text-base text-[#0b1c30]">المتابعات السريرية العاجلة
                            </h3>
                        </div>
                        <p class="text-xs text-[#64748b] mt-0.5">الحالات التي تستدعي التواصل المباشر وتوثيق النتائج الحرجة
                        </p>
                    </div>
                    <a href="{{ route('follow_ups.index') }}" class="text-xs font-bold text-amber-700 hover:underline">
                        كافة المتابعات ➜
                    </a>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($urgentFollowups as $flw)
                        <div class="py-3.5 first:pt-0 last:pb-0 flex items-center justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="font-bold text-slate-900 text-xs">{{ $flw->patient->fullname }}</span>
                                    <span
                                        class="font-mono text-[11px] text-teal-800 bg-teal-50 px-2 py-0.5 rounded border border-teal-200">
                                        {{ $flw->patient->mrn }}
                                    </span>
                                    <x-archived-badge :patient="$flw->patient" />
                                    @if ($flw->visit)
                                        <span class="text-[10px] text-slate-400 font-mono">زيارة
                                            #{{ $flw->visit->id }}</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-600 line-clamp-1 max-w-md">
                                    {{ $flw->reason }}
                                </p>
                                <div class="flex items-center gap-3 text-[11px] text-slate-400 font-mono">
                                    <span>موعد الاستحقاق: <strong
                                            class="text-amber-800">{{ $flw->due_date ? $flw->due_date->format('Y-m-d H:i') : '-' }}</strong></span>
                                    @if ($flw->visit && $flw->visit->doctor)
                                        <span>الطبيب: د. {{ $flw->visit->doctor->fullname }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="shrink-0 flex items-center gap-2">
                                <a href="{{ route('follow_ups.show', $flw) }}"
                                    class="px-3 py-1.5 text-xs font-bold text-teal-800 bg-teal-50 hover:bg-teal-100 border border-teal-200 rounded-xl transition">
                                    فحص وتوثيق
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400 text-xs">
                            لا توجد حالات متابعة حرجة متأخرة اليوم.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- 6. Fast Triage & Recent Visits Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden space-y-4 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <h3 class="font-bold text-base text-[#0b1c30]">جدول فرز ومتابعة الزيارات السريرية الحديثة</h3>
                    <p class="text-xs text-[#64748b] mt-0.5">متابعة فورية لحالات الزيارات، الطبيب المعالج، واستبيانات الرضا
                        المرتبطة</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('visits.index') }}"
                        class="px-4 py-2 text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-xl transition">
                        عرض كافة الزيارات ➜
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right border-collapse text-xs">
                    <thead class="bg-[#f8fafc] text-[#64748b] text-[11px] font-bold border-b border-[#e2e8f0]">
                        <tr>
                            <th class="py-3 px-3.5 w-12 text-center font-mono">#</th>
                            <th class="py-3 px-3.5 min-w-[170px]">المريض ورقم الملف (MRN)</th>
                            <th class="py-3 px-3.5 min-w-[170px]">الطبيب المعالج والعيادة</th>
                            <th class="py-3 px-3.5 min-w-[120px]">تاريخ الكشف</th>
                            <th class="py-3 px-3.5 min-w-[200px]">سبب الزيارة السريرية</th>
                            <th class="py-3 px-3.5 min-w-[120px]">حالة الزيارة</th>
                            <th class="py-3 px-3.5 min-w-[130px]">استبيان الرضا </th>
                            <th class="sticky left-0 z-20 bg-[#f8fafc] py-3 px-3.5 text-center w-24">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e2e8f0]">
                        @forelse($recentVisits as $v)
                            <tr class="group hover:bg-teal-50/20 transition">
                                <td class="py-3.5 px-3.5 text-center font-mono font-bold text-slate-500">
                                    #{{ $v->id }}
                                </td>
                                <td class="py-3.5 px-3.5">
                                    @if ($v->patient)
                                        @if ($v->patient->trashed())
                                            <span class="font-bold text-slate-900 block">
                                                {{ $v->patient->fullname }}
                                            </span>
                                        @else
                                            <a href="{{ route('patients.show', $v->patient) }}"
                                                class="font-bold text-slate-900 hover:text-[#00685f] transition block">
                                                {{ $v->patient->fullname }}
                                            </a>
                                        @endif
                                        <span class="text-[11px] font-mono text-slate-500">{{ $v->patient->mrn }}
                                            <x-archived-badge :patient="$v->patient" /></span>
                                    @else
                                        <span class="text-slate-400 italic">غير محدد</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3.5">
                                    @if ($v->doctor)
                                        <div class="font-bold text-slate-800">{{ $v->doctor->fullname }}</div>
                                        <span
                                            class="text-[10px] text-slate-500 block">{{ $v->doctor->clinic?->name ?: ($v->doctor->specialty ?: 'غير مسجل') }}</span>
                                    @else
                                        <span class="text-slate-400 italic">لم يتم تعيين طبيب</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3.5 font-mono text-slate-600">
                                    {{ $v->date ? $v->date->format('Y-m-d') : '-' }}
                                </td>
                                <td class="py-3.5 px-3.5 text-slate-700 max-w-xs truncate" title="{{ $v->reason }}">
                                    {{ $v->reason ?: 'غير مسجل' }}
                                </td>
                                <td class="py-3.5 px-3.5">
                                    <span
                                        class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $v->status_badge_classes }}">
                                        {{ $v->status_label }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-3.5">
                                    @if ($v->survey)
                                        <span
                                            class="inline-flex items-center gap-1 font-bold text-xs {{ $v->survey->rating >= 4 ? 'text-emerald-700' : ($v->survey->rating == 3 ? 'text-amber-700' : 'text-rose-700') }}">
                                            <span>{{ $v->survey->rating }} ★</span>
                                            <span
                                                class="text-[10px] text-slate-400 font-normal">({{ $v->survey->rating_label }})</span>
                                        </span>
                                    @else
                                        <button type="button"
                                            onclick="openCreateSurveyModal('createSurveyModal', {{ $v->id }})"
                                            class="text-[11px] font-bold text-teal-700 hover:underline cursor-pointer">
                                            + تقييم الزيارة
                                        </button>
                                    @endif
                                </td>
                                <td
                                    class="sticky left-0 z-10 bg-white py-3.5 px-3.5 text-center shadow-[6px_0_8px_-6px_rgba(15,23,42,0.18)] group-hover:bg-teal-50/20">
                                    <a href="{{ route('visits.show', $v) }}"
                                        class="p-1.5 text-slate-500 hover:text-teal-700 hover:bg-teal-50 rounded-lg border border-slate-200 transition inline-block"
                                        title="فتح ملف الزيارة">
                                        <x-icon name="eye" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400">
                                    لا توجد زيارات حديثة مسجلة.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Reusable Component Modals -->
        <x-create-survey-modal id="createSurveyModal" />
    </div>
@endsection
