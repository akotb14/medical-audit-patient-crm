@extends('layouts.app')

@section('title', 'المتابعات السريرية والنتائج الحرجة (Follow-ups)')

@section('content')
    <div class="space-y-6">

        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-2xl font-bold text-[#0b1c30]">المتابعات السريرية والنتائج الحرجة (Follow-ups)</h1>
                    <span
                        class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                        جدول follow_ups
                    </span>
                </div>
                <p class="text-xs text-[#64748b] mt-1">
                    نظام فرز ومتابعة المرضى بعد الزيارات السريرية، توثيق النتائج المخبرية، والاتصال بالحالات ذات الخطورة
                </p>
            </div>
            <div class="flex items-center gap-2.5">
                @can('viewAny', App\Models\Visit::class)
                    <a href="{{ route('visits.index') }}"
                        class="flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 rounded-lg shadow-xs transition">
                        <span>سجل الزيارات ➜</span>
                    </a>
                @endcan
            </div>
        </div>

        <!-- Quick Clinical KPI Stats Strip (Direct Blade - No Component) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
            <!-- Total Follow-ups -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold text-slate-500">إجمالي المتابعات (Total)</p>
                    <p class="text-xl font-bold text-[#0b1c30] mt-0.5">{{ $stats->total ?? 0 }}</p>
                </div>
                <div
                    class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-sm">
                    📋
                </div>
            </div>

            <!-- Scheduled -->
            <div
                class="bg-white p-4 rounded-xl border border-amber-200/80 shadow-xs flex items-center justify-between bg-amber-50/10">
                <div>
                    <p class="text-[11px] font-semibold text-amber-700">مجدولة (قيد الانتظار)</p>
                    <p class="text-xl font-bold text-amber-800 mt-0.5">{{ $stats->scheduled ?? 0 }}</p>
                </div>
                <div
                    class="w-9 h-9 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-sm">
                    ⏳
                </div>
            </div>

            <!-- Done -->
            <div
                class="bg-white p-4 rounded-xl border border-emerald-200/80 shadow-xs flex items-center justify-between bg-emerald-50/10">
                <div>
                    <p class="text-[11px] font-semibold text-emerald-700">مكتملة وموثقة (Done)</p>
                    <p class="text-xl font-bold text-emerald-800 mt-0.5">{{ $stats->done ?? 0 }}</p>
                </div>
                <div
                    class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                    ✓
                </div>
            </div>

            <!-- Missed -->
            <div
                class="bg-white p-4 rounded-xl border border-rose-200/80 shadow-xs flex items-center justify-between bg-rose-50/10">
                <div>
                    <p class="text-[11px] font-semibold text-rose-700">فائتة / غير مجابة</p>
                    <p class="text-xl font-bold text-rose-800 mt-0.5">{{ $stats->missed ?? 0 }}</p>
                </div>
                <div
                    class="w-9 h-9 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-sm">
                    ⚠️
                </div>
            </div>

            <!-- Cancelled -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold text-slate-500">ملغاة (Cancelled)</p>
                    <p class="text-xl font-bold text-slate-700 mt-0.5">{{ $stats->cancelled ?? 0 }}</p>
                </div>
                <div
                    class="w-9 h-9 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-sm">
                    ✕
                </div>
            </div>
        </div>

        <!-- Filter and Search Bar -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs space-y-3">
            <form action="{{ route('follow_ups.index') }}" method="GET"
                class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="flex-1 flex flex-wrap items-center gap-2.5">
                    <!-- Search Input -->
                    <div class="relative flex-1 min-w-[240px]">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="بحث باسم المريض، رقم الملف MRN، أو سبب المتابعة..."
                            class="w-full pl-3 pr-9 py-2 text-xs bg-[#f8fafc] border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800 text-right" />
                        <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <!-- Status Filter -->
                    <div class="flex items-center gap-1.5 text-xs">
                        <span class="text-slate-400 text-[11px]">الحالة:</span>
                        <select name="status"
                            class="px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-slate-700 text-xs focus:ring-1 focus:ring-[#00685f]">
                            <option value="all" @selected(request('status') == 'all' || !request('status'))>كافة الحالات</option>
                            <option value="scheduled" @selected(request('status') == 'scheduled')>مجدولة (scheduled)</option>
                            <option value="done" @selected(request('status') == 'done')>مكتملة (done)</option>
                            <option value="missed" @selected(request('status') == 'missed')>فائتة (missed)</option>
                            <option value="cancelled" @selected(request('status') == 'cancelled')>ملغاة (cancelled)</option>
                        </select>
                    </div>

                    <!-- Date Filter -->


                    <button type="submit"
                        class="px-3.5 py-2 text-xs font-semibold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg transition">
                        تصفية
                    </button>

                    @if (request()->hasAny(['search', 'status', 'date_filter', 'patient_id']))
                        <a href="{{ route('follow_ups.index') }}"
                            class="px-3 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                            إعادة ضبط
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- VIEW 1: High-Density Clinical Table (Direct Blade - No Component) -->
        <div id="followupsTableContainer" class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right border-collapse text-xs">
                    <thead class="bg-[#f8fafc] text-[#64748b] text-[11px] font-bold border-b border-[#e2e8f0]">
                        <tr>
                            <th class="py-3 px-3.5 w-12 text-center font-mono">#</th>
                            <th class="py-3 px-3.5 min-w-[170px]">المريض ورقم الملف (Patient)</th>
                            <th class="py-3 px-3.5 min-w-[130px]">الزيارة المرتبطة (Visit FK)</th>
                            <th class="py-3 px-3.5 min-w-[130px]">موعد الاستحقاق (due_date)</th>
                            <th class="py-3 px-3.5 min-w-[220px]">سبب وأهداف المتابعة (Reason)</th>
                            <th class="py-3 px-3.5 min-w-[120px]">الحالة (Status)</th>
                            <th class="py-3 px-3.5 min-w-[180px]">النتيجة والتوثيق (Outcome Note)</th>
                            <th class="py-3 px-3.5 min-w-[100px]">مسجل السجل</th>
                            <th class="py-3 px-3.5 text-center w-28">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e2e8f0]">
                        @if (isset($followups))
                            @foreach ($followups ?? [] as $flw)
                                @php
                                    $isOverdue =
                                        $flw->status === 'scheduled' && $flw->due_date && $flw->due_date->isPast();
                                @endphp
                                <tr class="hover:bg-teal-50/30 transition {{ $isOverdue ? 'bg-rose-50/25' : '' }}">
                                    <!-- ID -->
                                    <td class="py-3.5 px-3.5 text-center font-mono font-bold text-slate-500">
                                        #{{ $flw->id }}
                                    </td>

                                    <!-- Patient -->
                                    <td class="py-3.5 px-3.5">
                                        @if ($flw->patient)
                                            <div>
                                                <a href="{{ route('patients.show', $flw->patient) }}"
                                                    class="font-bold text-slate-900 hover:text-[#00685f] transition block">
                                                    {{ $flw->patient->fullname }}
                                                </a>
                                                <div
                                                    class="flex items-center gap-1.5 text-[11px] text-slate-500 font-mono mt-0.5">
                                                    <span
                                                        class="bg-slate-100 text-slate-700 px-1.5 py-0.2 rounded">{{ $flw->patient->mrn }}</span>
                                                    <a href="tel:{{ $flw->patient->phone }}"
                                                        class="text-teal-700 hover:underline">
                                                        {{ $flw->patient->phone }}
                                                    </a>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-slate-400 italic">غير مقترن بمريض </span>
                                        @endif
                                    </td>

                                    <!-- Linked Visit -->
                                    <td class="py-3.5 px-3.5">
                                        @if ($flw->visit)
                                            <div>
                                                <a href="{{ route('visits.show', $flw->visit) }}"
                                                    class="font-mono font-bold text-[#00685f] hover:underline flex items-center gap-1">
                                                    <span>زيارة #{{ $flw->visit->id }}</span>
                                                </a>
                                                <span class="text-[11px] text-slate-500 block font-mono">
                                                    {{ $flw->visit->date ? $flw->visit->date->format('Y-m-d') : '-' }}
                                                </span>
                                                @if ($flw->visit->doctor)
                                                    <span class="text-[10px] text-slate-600 block line-clamp-1">
                                                        {{ $flw->visit->doctor->fullname }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-slate-400 italic text-[11px]">متابعة مستقلة </span>
                                        @endif
                                    </td>

                                    <!-- Due Date -->
                                    <td class="py-3.5 px-3.5 font-mono">
                                        <div class="font-bold text-slate-800 flex items-center gap-1">
                                            <span>{{ $flw->due_date ? $flw->due_date->format('Y-m-d') : '-' }}</span>
                                            @if ($isOverdue)
                                                <span
                                                    class="text-rose-600 text-[10px] font-bold bg-rose-100 px-1 py-0.2 rounded"
                                                    title="تاريخ الاستحقاق قد انقضى">متأخر</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-500">
                                            {{ $flw->due_date ? $flw->due_date->format('h:i A') : '' }}
                                        </div>
                                    </td>

                                    <!-- Reason -->
                                    <td class="py-3.5 px-3.5">
                                        <p class="text-xs text-slate-800 leading-relaxed line-clamp-2 max-w-sm"
                                            title="{{ $flw->reason }}">
                                            {{ $flw->reason }}
                                        </p>
                                    </td>

                                    <!-- Status Badge (Direct Blade) -->
                                    <td class="py-3.5 px-3.5 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full font-bold border px-2 py-0.5 text-[10px] {{ $flw->status_badge_classes }}">
                                            @if ($flw->status === 'scheduled')
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            @elseif($flw->status === 'done')
                                                <span class="font-bold text-[11px] leading-none">✓</span>
                                            @elseif($flw->status === 'missed')
                                                <span class="font-bold text-[11px] leading-none">✕</span>
                                            @else
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            @endif
                                            <span>{{ $flw->status_Label }}</span>
                                        </span>
                                    </td>

                                    <!-- Outcome Note & Completed At -->
                                    <td class="py-3.5 px-3.5">
                                        @if ($flw->outcome_note)
                                            <div>
                                                <p class="text-xs text-emerald-900 leading-relaxed line-clamp-2 max-w-xs font-medium"
                                                    title="{{ $flw->outcome_note }}">
                                                    ✓ {{ $flw->outcome_note }}
                                                </p>
                                                @if ($flw->completed_at)
                                                    <span class="text-[10px] text-slate-400 font-mono block mt-0.5">
                                                        أُتمت: {{ $flw->completed_at->format('Y-m-d h:i A') }}
                                                    </span>
                                                @endif
                                            </div>
                                        @elseif($flw->status === 'scheduled')
                                            <button type="button"
                                                onclick="openCompleteFollowupModal({{ $flw->id }}, '{{ addslashes($flw->patient->name ?? 'المريض') }}', '{{ addslashes($flw->outcome_note ?? '') }}')"
                                                class="text-[11px] text-[#00685f] hover:underline font-bold flex items-center gap-1">
                                                <span>+ توثيق النتيجة</span>
                                            </button>
                                        @else
                                            <span class="text-slate-400 italic text-[11px]">—</span>
                                        @endif
                                    </td>

                                    <!-- Creator / Audit -->
                                    <td class="py-3.5 px-3.5 text-xs text-slate-500">
                                        {{ $flw->creator ? $flw->creator->name : 'المشرف' }}
                                    </td>

                                    <!-- Actions -->
                                    <td class="py-3.5 px-3.5 text-center">
                                        <div class="inline-flex items-center gap-1 justify-center">
                                            @can('complete', $flw)
                                                @if ($flw->status === 'scheduled')
                                                    <button type="button"
                                                        onclick="document.getElementById('completeFollowupModal-{{ $flw->id }}').classList.remove('hidden')"
                                                        title="إتمام وتوثيق المتابعة (Complete)"
                                                        class="p-1.5 text-emerald-700 hover:text-emerald-900 hover:bg-emerald-50 rounded-lg border border-emerald-200 transition">
                                                        ✓
                                                    </button>
                                                @endif
                                            @endcan
                                            @can('view', $flw)
                                                <a href="{{ route('follow_ups.show', $flw) }}" title="عرض التفاصيل (Show)"
                                                    class="p-1.5 text-slate-600 hover:text-teal-700 hover:bg-teal-50 rounded-lg border border-slate-200 transition">
                                                    👁️
                                                </a>
                                            @endcan
                                            @can('update', $flw)
                                                <a href="{{ route('follow_ups.edit', $flw) }}" title="تعديل (Edit)"
                                                    class="p-1.5 text-slate-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg border border-slate-200 transition">
                                                    ✏️
                                                </a>
                                            @endcan
                                            @can('delete', $flw)
                                                <button type="button"
                                                    onclick="document.getElementById('deleteFollowupModal-{{ $flw->id }}').classList.remove('hidden')"
                                                    title="حذف (Delete)"
                                                    class="p-1.5 text-slate-400 hover:text-red-700 hover:bg-red-50 rounded-lg border border-slate-200 transition">
                                                    🗑️
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @can('complete', $flw)
                                    <x-complete-followup-modal :followup="$flw" />
                                @endcan
                                @can('delete', $flw)
                                    <x-delete-followup-modal :followup="$flw" />
                                @endcan
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>


            @if (isset($followups) && method_exists($followups, 'links'))
                <div class="p-4 border-t border-slate-100 bg-[#f8fafc]">
                    {{ $followups->links() }}
                </div>
            @endif
        </div>

    </div>



@endsection
