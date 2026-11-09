@extends('layouts.app')

@section('title', 'استبيانات رضا المرضى (Surveys)')

@section('content')
    <div class="space-y-6">


        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-2xl font-bold text-[#0b1c30]">استبيانات رضا المرضى وتجربة المراجع</h1>
                    <span
                        class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-teal-50 text-teal-800 border border-teal-200">
                        سجل رضا المرضى
                    </span>
                </div>
                <p class="text-xs text-[#64748b] mt-1">
                    قياس جودة الخدمة الطبية، استطلاع رأي المريض (1-5 نجوم)، وتفعيل الشكاوى التلقائية في حال تدني التقييم
                </p>
            </div>
            @can('create', App\Models\Complaint::class)
                <div class="flex items-center gap-2.5">

                    <a href="{{ route('complaints.index') }}"
                        class="flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 rounded-lg shadow-xs transition">
                        <span>سجل الشكاوى ➜</span>
                    </a>
                </div>
            @endcan
        </div>

        <!-- Quick Satisfaction KPI Stats Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
            <!-- Total Surveys -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold text-slate-500">إجمالي الاستبيانات</p>
                    <p class="text-xl font-bold text-[#0b1c30] mt-0.5">{{ $stats->total }}</p>
                </div>
                <div
                    class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-sm">
                    📋
                </div>
            </div>

            <!-- Average Rating -->
            <div
                class="bg-white p-4 rounded-xl border border-teal-200 shadow-xs flex items-center justify-between bg-teal-50/10">
                <div>
                    <p class="text-[11px] font-semibold text-teal-800">معدل الرضا العام</p>
                    <div class="flex items-center gap-1 mt-0.5">
                        <span class="text-xl font-bold text-teal-900 font-mono">{{ $stats->average_rating }}</span>
                        <span class="text-amber-500 text-base">★</span>
                        <span class="text-[11px] text-slate-400 font-mono">/ 5</span>
                    </div>
                </div>
                <div
                    class="w-9 h-9 rounded-lg bg-teal-100 text-[#00685f] flex items-center justify-center font-bold text-sm">
                    ⭐
                </div>
            </div>

            <!-- High Ratings (4 & 5 stars) -->
            <div
                class="bg-white p-4 rounded-xl border border-emerald-200 shadow-xs flex items-center justify-between bg-emerald-50/10">
                <div>
                    <p class="text-[11px] font-semibold text-emerald-700">تقييم إيجابي (4-5 نجوم)</p>
                    <p class="text-xl font-bold text-emerald-800 mt-0.5">{{ $stats->postive_ratings }}</p>
                </div>
                <div
                    class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                    👍
                </div>
            </div>

            <!-- Low Ratings (< 3 stars) -->
            <div
                class="bg-white p-4 rounded-xl border border-rose-200 shadow-xs flex items-center justify-between bg-rose-50/10">
                <div>
                    <p class="text-[11px] font-semibold text-rose-700">تقييم سلبي (&lt; 3 نجوم)</p>
                    <p class="text-xl font-bold text-rose-800 mt-0.5">{{ $stats->negative_ratings }}</p>
                </div>
                <div
                    class="w-9 h-9 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-sm">
                    ⚠️
                </div>
            </div>

            <!-- Automated Complaints Triggered -->
            <div
                class="bg-white p-4 rounded-xl border border-amber-200 shadow-xs flex items-center justify-between bg-amber-50/10">
                <div>
                    <p class="text-[11px] font-semibold text-amber-700">شكاوى مفعلة تلقائياً </p>
                    <p class="text-xl font-bold text-amber-800 mt-0.5">{{ $stats->complaints_count }}</p>
                </div>
                <div
                    class="w-9 h-9 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-sm">
                    🔔
                </div>
            </div>
        </div>



        <!-- Filter and Search Bar -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <form action="{{ route('surveys.index') }}" method="GET"
                class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="flex-1 flex flex-wrap items-center gap-2.5">
                    <!-- Search Input -->
                    <div class="relative flex-1 min-w-[240px]">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="بحث بملاحظات المريض، اسم المريض، أو رقم الملف MRN..."
                            class="w-full pl-3 pr-9 py-2 text-xs bg-[#f8fafc] border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800 text-right" />
                        <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <!-- Rating Filter -->
                    <div class="flex items-center gap-1.5 text-xs">
                        <span class="text-slate-400 text-[11px]">التقييم:</span>
                        <select name="rating"
                            class="px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-slate-700 text-xs focus:ring-1 focus:ring-[#00685f]">
                            <option value="all" @selected(request('rating') == 'all' || !request('rating'))>كافة التقييمات</option>
                            <option value="5" @selected(request('rating') == '5')>⭐⭐⭐⭐⭐ (5 نجوم - ممتاز)</option>
                            <option value="4" @selected(request('rating') == '4')>⭐⭐⭐⭐ (4 نجوم - جيد جداً)</option>
                            <option value="3" @selected(request('rating') == '3')>⭐⭐⭐ (3 نجوم - مقبول)</option>
                            <option value="2" @selected(request('rating') == '2')>⭐⭐ (نجمتان - غير راضٍ)</option>
                            <option value="1" @selected(request('rating') == '1')>⭐ (نجمة واحدة - سيء جداً)</option>
                        </select>
                    </div>

                    <button type="submit"
                        class="px-3.5 py-2 text-xs font-semibold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg transition">
                        تصفية
                    </button>

                    @if (request()->hasAny(['search', 'rating', 'patient_id']))
                        <a href="{{ route('surveys.index') }}"
                            class="px-3 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                            إعادة ضبط
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Surveys Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right border-collapse text-xs">
                    <thead class="bg-[#f8fafc] text-[#64748b] text-[11px] font-bold border-b border-[#e2e8f0]">
                        <tr>
                            <th class="py-3 px-3.5 w-12 text-center font-mono">#</th>
                            <th class="py-3 px-3.5 min-w-[170px]">المريض ورقم الملف (Patient)</th>
                            <th class="py-3 px-3.5 min-w-[150px]">الزيارة السريرية (Visit)</th>
                            <th class="py-3 px-3.5 min-w-[140px]">درجة التقييم (Rating 1-5)</th>
                            <th class="py-3 px-3.5 min-w-[220px]">ملاحظات وتجربة المريض (Comment)</th>
                            <th class="py-3 px-3.5 min-w-[180px]">الشكوى الناتجة آلياً (Complaint)</th>
                            <th class="py-3 px-3.5 min-w-[120px]">المسجل والتاريخ</th>
                            <th class="sticky left-0 z-20 bg-[#f8fafc] py-3 px-3.5 text-center w-24">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e2e8f0]">
                        @if (isset($surveys) && count($surveys) > 0)
                            @foreach ($surveys as $survey)
                                @php
                                    $isLow = $survey->is_low_rating;
                                @endphp
                                <tr class="group hover:bg-teal-50/20 transition {{ $isLow ? 'bg-rose-50/20' : '' }}">
                                    <!-- ID -->
                                    <td class="py-3.5 px-3.5 text-center font-mono font-bold text-slate-500">
                                        #{{ $survey->id }}
                                    </td>

                                    <!-- Patient -->
                                    <td class="py-3.5 px-3.5">
                                        @if ($survey->patient)
                                            <div>
                                                @if ($survey->patient->trashed())
                                                    <span class="font-bold text-slate-900 block">
                                                        {{ $survey->patient->fullname }}
                                                    </span>
                                                @else
                                                    <a href="{{ route('patients.show', $survey->patient) }}"
                                                        class="font-bold text-slate-900 hover:text-[#00685f] transition block">
                                                        {{ $survey->patient->fullname }}
                                                    </a>
                                                @endif
                                                <span class="text-[11px] text-slate-500 font-mono">MRN:
                                                    {{ $survey->patient->mrn }}
                                                    <x-archived-badge :patient="$survey->patient" /></span>
                                            </div>
                                        @else
                                            <span class="text-slate-400 italic">غير محدد</span>
                                        @endif
                                    </td>

                                    <!-- Visit -->
                                    <td class="py-3.5 px-3.5">
                                        @if ($survey->visit)
                                            <div>
                                                <a href="{{ route('visits.show', $survey->visit) }}"
                                                    class="font-mono font-bold text-[#00685f] hover:underline flex items-center gap-1">
                                                    <span>زيارة #{{ $survey->visit->id }}</span>
                                                </a>
                                                <span class="text-[11px] text-slate-500 font-mono block">
                                                    {{ $survey->visit->date ? $survey->visit->date->format('Y-m-d') : '-' }}
                                                </span>
                                                @if ($survey->visit->doctor)
                                                    <span class="text-[10px] text-slate-600 block line-clamp-1">
                                                        د. {{ $survey->visit->doctor->fullname }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-slate-400 italic">غير مرتبط بزيارة</span>
                                        @endif
                                    </td>

                                    <!-- Rating (1-5) -->
                                    <td class="py-3.5 px-3.5 whitespace-nowrap">
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-0.5 text-amber-500 text-sm">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <span>{{ $i <= $survey->rating ? '★' : '☆' }}</span>
                                                @endfor
                                                <span
                                                    class="font-mono font-bold text-slate-800 text-xs mr-1">({{ $survey->rating }}/5)</span>
                                            </div>
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $survey->rating_badge_classes }}">
                                                {{ $survey->rating_label }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Comment -->
                                    <td class="py-3.5 px-3.5">
                                        @if ($survey->comment)
                                            <p class="text-xs text-slate-800 leading-relaxed line-clamp-2 max-w-sm"
                                                title="{{ $survey->comment }}">
                                                "{{ $survey->comment }}"
                                            </p>
                                        @else
                                            <span class="text-slate-400 italic text-[11px]">بدون ملاحظات إضافية</span>
                                        @endif
                                    </td>

                                    <!-- Auto-created Complaint -->
                                    <td class="py-3.5 px-3.5">
                                        @if ($survey->complaint)
                                            <div
                                                class="p-2 rounded-lg bg-rose-50 border border-rose-200 text-rose-900 space-y-1">
                                                <div class="flex items-center justify-between">
                                                    <span class="font-bold text-[11px] flex items-center gap-1">
                                                        <span>🚨</span>
                                                        <span>شكوى #{{ $survey->complaint->id }}</span>
                                                    </span>
                                                    <span
                                                        class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-white border border-rose-300">
                                                        {{ $survey->complaint->status_label }}
                                                    </span>
                                                </div>
                                                @can('view', $survey->complaint)
                                                    <a href="{{ route('complaints.show', $survey->complaint) }}"
                                                        class="text-[11px] text-[#00685f] hover:underline font-bold block">
                                                        متابعة التحقيق والتسوية ➜
                                                    </a>
                                                @endcan
                                            </div>
                                        @elseif($isLow)
                                            <span class="text-rose-700 font-bold text-[11px] flex items-center gap-1">
                                                <span>⚠️</span>
                                                <span>مستحق شكوى آلياً</span>
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold text-emerald-800 bg-emerald-50 border border-emerald-200">
                                                <span>✓</span>
                                                <span>تجربة إيجابية</span>
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Recorded By & Date -->
                                    <td class="py-3.5 px-3.5 text-slate-500 font-mono text-[11px]">
                                        <div>{{ $survey->created_at ? $survey->created_at->format('Y-m-d') : '-' }}</div>
                                        <div class="text-[10px] text-slate-400">
                                            {{ $survey->creator?->name ?? 'غير معروف' }}</div>
                                    </td>

                                    <!-- Actions -->
                                    <td
                                        class="sticky left-0 z-10 bg-white py-3.5 px-3.5 text-center shadow-[6px_0_8px_-6px_rgba(15,23,42,0.18)] group-hover:bg-teal-50/20 {{ $isLow ? 'bg-rose-50/20' : '' }}">
                                        <div class="inline-flex items-center gap-1 justify-center">
                                            @can('view', $survey)
                                                <a href="{{ route('surveys.show', $survey) }}"
                                                    class="p-1.5 text-slate-600 hover:text-teal-700 hover:bg-teal-50 rounded-lg border border-slate-200 transition"
                                                    title="عرض تفاصيل الاستبيان">
                                                    <x-icon name="eye" />
                                                </a>
                                            @endcan
                                            @can('delete', $survey)
                                                <form action="{{ route('surveys.destroy', $survey) }}" method="POST"
                                                    onsubmit="return confirm('هل أنت متأكد من حذف هذا الاستبيان؟')"
                                                    class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="p-1.5 text-slate-400 hover:text-red-700 hover:bg-red-50 rounded-lg border border-slate-200 transition"
                                                        title="حذف">
                                                        <x-icon name="trash" />
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center mx-auto text-xl mb-2">
                                        ⭐</div>
                                    <p class="font-bold text-slate-700 text-sm">لا توجد استبيانات رضا مسجلة حتى الآن</p>
                                    <p class="text-xs text-slate-400 mt-1">ابدأ بقياس تجربة المراجعين بعد الزيارات السريرية
                                        لرفع جودة الرعاية الطبية</p>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if (isset($surveys) && method_exists($surveys, 'links') && $surveys->hasPages())
                <div class="p-4 border-t border-slate-100 bg-[#f8fafc]">
                    {{ $surveys->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
