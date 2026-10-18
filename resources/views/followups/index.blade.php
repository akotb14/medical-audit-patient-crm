@extends('layouts.app')

@section('title', 'المتابعات السريرية والنتائج الحرجة (Follow-ups)')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-bold text-[#0b1c30]">المتابعات السريرية والنتائج الحرجة (Follow-ups)</h1>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                    جدول follow_ups
                </span>
            </div>
            <p class="text-xs text-[#64748b] mt-1">
                نظام فرز ومتابعة المرضى بعد الزيارات السريرية، توثيق النتائج المخبرية، والاتصال بالحالات ذات الخطورة
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <a
                href="{{ route('followups.create') }}"
                class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg shadow-sm transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ جدولة متابعة جديدة</span>
            </a>
            <a
                href="{{ route('visits.index') }}"
                class="flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 rounded-lg shadow-xs transition"
            >
                <span>سجل الزيارات ➜</span>
            </a>
        </div>
    </div>

    <!-- Quick Clinical KPI Stats Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-500">إجمالي المتابعات</p>
                <p class="text-xl font-bold text-[#0b1c30] mt-0.5">{{ $stats->total ?? (isset($followups) ? $followups->total() : 0) }}</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-sm">
                📋
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-amber-200/80 shadow-xs flex items-center justify-between bg-amber-50/10">
            <div>
                <p class="text-[11px] font-semibold text-amber-700">مجدولة (قيد الانتظار)</p>
                <p class="text-xl font-bold text-amber-800 mt-0.5">{{ $stats->scheduled ?? 0 }}</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-sm">
                ⏳
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-emerald-200/80 shadow-xs flex items-center justify-between bg-emerald-50/10">
            <div>
                <p class="text-[11px] font-semibold text-emerald-700">مكتملة وموثقة (Done)</p>
                <p class="text-xl font-bold text-emerald-800 mt-0.5">{{ $stats->done ?? 0 }}</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                ✓
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-rose-200/80 shadow-xs flex items-center justify-between bg-rose-50/10">
            <div>
                <p class="text-[11px] font-semibold text-rose-700">فائتة / غير مجابة</p>
                <p class="text-xl font-bold text-rose-800 mt-0.5">{{ $stats->missed ?? 0 }}</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-sm">
                ⚠️
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-500">ملغاة (Cancelled)</p>
                <p class="text-xl font-bold text-slate-700 mt-0.5">{{ $stats->cancelled ?? 0 }}</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-sm">
                ✕
            </div>
        </div>
    </div>

    <!-- Filter and Search Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs space-y-3">
        <form action="{{ route('followups.index') }}" method="GET" class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex-1 flex flex-wrap items-center gap-2.5">
                <!-- Search Input -->
                <div class="relative flex-1 min-w-[240px]">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="بحث باسم المريض، رقم الملف MRN، أو سبب المتابعة..."
                        class="w-full pl-3 pr-9 py-2 text-xs bg-[#f8fafc] border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800 text-right"
                    />
                    <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <!-- Status Filter -->
                <div class="flex items-center gap-1.5 text-xs">
                    <span class="text-slate-400 text-[11px]">الحالة:</span>
                    <select name="status" class="px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-slate-700 text-xs focus:ring-1 focus:ring-[#00685f]">
                        <option value="all" @selected(request('status') == 'all' || !request('status'))>كافة الحالات</option>
                        <option value="scheduled" @selected(request('status') == 'scheduled')>مجدولة (scheduled)</option>
                        <option value="done" @selected(request('status') == 'done')>مكتملة (done)</option>
                        <option value="missed" @selected(request('status') == 'missed')>فائتة (missed)</option>
                        <option value="cancelled" @selected(request('status') == 'cancelled')>ملغاة (cancelled)</option>
                    </select>
                </div>

                <!-- Date Filter -->
                <div class="flex items-center gap-1.5 text-xs">
                    <span class="text-slate-400 text-[11px]">الاستحقاق:</span>
                    <select name="date_filter" class="px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-slate-700 text-xs focus:ring-1 focus:ring-[#00685f]">
                        <option value="all" @selected(request('date_filter') == 'all' || !request('date_filter'))>كافة التواريخ</option>
                        <option value="today" @selected(request('date_filter') == 'today')>مستحقة اليوم</option>
                        <option value="overdue" @selected(request('date_filter') == 'overdue')>متأخرة عن موعدها</option>
                        <option value="upcoming" @selected(request('date_filter') == 'upcoming')>قادمة</option>
                    </select>
                </div>

                <button type="submit" class="px-3.5 py-2 text-xs font-semibold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg transition">
                    تصفية
                </button>

                @if(request()->hasAny(['search', 'status', 'date_filter', 'patient_id']))
                    <a href="{{ route('followups.index') }}" class="px-3 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        إعادة ضبط
                    </a>
                @endif
            </div>

            <!-- Layout Switcher (Table vs Cards) -->
            <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-lg border border-slate-200 self-end md:self-auto">
                <button
                    type="button"
                    id="followupsTableViewBtn"
                    onclick="switchFollowupsView('table')"
                    class="px-3 py-1.5 text-xs font-bold rounded-md transition shadow-xs bg-white text-[#00685f]"
                >
                    جدول سريري
                </button>
                <button
                    type="button"
                    id="followupsCardsViewBtn"
                    onclick="switchFollowupsView('cards')"
                    class="px-3 py-1.5 text-xs font-semibold rounded-md transition text-slate-600 hover:text-slate-900"
                >
                    بطاقات التواصل
                </button>
            </div>
        </form>
    </div>

    <!-- VIEW 1: High-Density Clinical Table (Fast Eye-Scanning) -->
    <div id="followupsTableContainer" class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse text-xs">
                <thead class="bg-[#f8fafc] text-[#64748b] text-[11px] font-bold border-b border-[#e2e8f0]">
                    <tr>
                        <th class="py-3 px-3.5 w-12 text-center">#</th>
                        <th class="py-3 px-3.5">المريض ورقم الملف (Patient)</th>
                        <th class="py-3 px-3.5">الزيارة المرتبطة (Visit FK)</th>
                        <th class="py-3 px-3.5">موعد الاستحقاق (due_date)</th>
                        <th class="py-3 px-3.5 min-w-[220px]">سبب وأهداف المتابعة (Reason)</th>
                        <th class="py-3 px-3.5">الحالة (Status)</th>
                        <th class="py-3 px-3.5 min-w-[200px]">النتيجة والتوثيق (Outcome Note)</th>
                        <th class="py-3 px-3.5">مسجل السجل</th>
                        <th class="py-3 px-3.5 text-center w-28">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e2e8f0]">
                    @if(isset($followups) && count($followups) > 0)
                        @foreach($followups as $flw)
                            @php
                                $isOverdue = $flw->status === 'scheduled' && $flw->due_date && $flw->due_date->isPast();
                            @endphp
                            <tr class="hover:bg-teal-50/30 transition {{ $isOverdue ? 'bg-rose-50/20' : '' }}">
                                <!-- ID -->
                                <td class="py-3.5 px-3.5 text-center font-mono font-bold text-slate-500">
                                    #{{ $flw->id }}
                                </td>

                                <!-- Patient -->
                                <td class="py-3.5 px-3.5">
                                    @if($flw->patient)
                                        <div>
                                            <a href="{{ route('patients.show', $flw->patient) }}" class="font-bold text-slate-900 hover:text-[#00685f] transition block">
                                                {{ $flw->patient->name }}
                                            </a>
                                            <div class="flex items-center gap-1.5 text-[11px] text-slate-500 font-mono mt-0.5">
                                                <span class="bg-slate-100 text-slate-700 px-1.5 py-0.2 rounded">{{ $flw->patient->mrn }}</span>
                                                <a href="tel:{{ $flw->patient->phone }}" class="text-teal-700 hover:underline">
                                                    {{ $flw->patient->phone }}
                                                </a>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic">غير مقترن بمريض</span>
                                    @endif
                                </td>

                                <!-- Linked Visit -->
                                <td class="py-3.5 px-3.5">
                                    @if($flw->visit)
                                        <div>
                                            <a href="{{ route('visits.show', $flw->visit) }}" class="font-mono font-bold text-[#00685f] hover:underline flex items-center gap-1">
                                                <span>زيارة #{{ $flw->visit->id }}</span>
                                            </a>
                                            <span class="text-[11px] text-slate-500 block font-mono">
                                                {{ $flw->visit->date ? $flw->visit->date->format('Y-m-d') : '-' }}
                                            </span>
                                            @if($flw->visit->doctor)
                                                <span class="text-[10px] text-slate-600 block line-clamp-1">
                                                    {{ $flw->visit->doctor->name }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">متابعة مستقلة (null)</span>
                                    @endif
                                </td>

                                <!-- Due Date -->
                                <td class="py-3.5 px-3.5 font-mono">
                                    <div class="font-bold text-slate-800">
                                        {{ $flw->due_date ? $flw->due_date->format('Y-m-d') : '-' }}
                                    </div>
                                    <div class="text-[11px] text-slate-500">
                                        {{ $flw->due_date ? $flw->due_date->format('h:i A') : '' }}
                                    </div>
                                    @if($isOverdue)
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200 mt-1">
                                            <span>متأخرة ⚠️</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- Reason -->
                                <td class="py-3.5 px-3.5">
                                    <p class="text-slate-800 leading-relaxed line-clamp-2" title="{{ $flw->reason }}">
                                        {{ $flw->reason }}
                                    </p>
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3.5 px-3.5">
                                    @if($flw->status === 'scheduled')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            <span>مجدولة (scheduled)</span>
                                        </span>
                                    @elseif($flw->status === 'done')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            <span>✓ مكتملة (done)</span>
                                        </span>
                                    @elseif($flw->status === 'missed')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200">
                                            <span>✕ فائتة (missed)</span>
                                        </span>
                                    @elseif($flw->status === 'cancelled')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                            <span>ملغاة (cancelled)</span>
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-700">
                                            {{ $flw->status }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Outcome Note -->
                                <td class="py-3.5 px-3.5">
                                    @if(!empty($flw->outcome_note))
                                        <div class="bg-emerald-50/40 p-2 rounded-lg border border-emerald-100 text-[11px] text-slate-700 leading-relaxed">
                                            <p class="line-clamp-2" title="{{ $flw->outcome_note }}">{{ $flw->outcome_note }}</p>
                                            @if($flw->completed_at)
                                                <span class="text-[10px] text-emerald-700 font-mono block mt-1">
                                                    تاريخ الإتمام: {{ $flw->completed_at->format('Y-m-d H:i') }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">بانتظار توثيق النتيجة</span>
                                    @endif
                                </td>

                                <!-- Creator -->
                                <td class="py-3.5 px-3.5 text-slate-600 text-[11px]">
                                    {{ $flw->creator->name ?? 'النظام الآلي' }}
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        {{-- View Details --}}
                                        <a
                                            href="{{ route('followups.show', $flw) }}"
                                            class="p-1.5 text-slate-600 hover:text-[#00685f] hover:bg-slate-100 rounded-lg transition"
                                            title="عرض التفاصيل الكاملة"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>

                                        {{-- Quick Complete with Outcome Note --}}
                                        @if($flw->status !== 'done')
                                            <button
                                                type="button"
                                                onclick="openCompleteModal({{ $flw->id }}, '{{ addslashes($flw->patient->name ?? 'المريض') }}')"
                                                class="p-1.5 text-emerald-700 hover:bg-emerald-50 rounded-lg border border-emerald-200 transition"
                                                title="إتمام وتوثيق النتيجة (Complete)"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </button>
                                        @endif

                                        {{-- Edit --}}
                                        <a
                                            href="{{ route('followups.edit', $flw) }}"
                                            class="p-1.5 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                            title="تعديل المتابعة"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </a>

                                        {{-- Delete --}}
                                        <button
                                            type="button"
                                            onclick="openDeleteFollowupModal({{ $flw->id }}, '{{ addslashes($flw->patient->name ?? 'المريض') }}')"
                                            class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                            title="حذف"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="9" class="py-12 text-center">
                                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-lg">
                                    📋
                                </div>
                                <h3 class="font-bold text-slate-700 text-sm">لا توجد سجلات متابعة مطابقة لخيارات البحث</h3>
                                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                                    يمكنك جدولة متابعة جديدة للمرضى بعد الزيارات السريرية أو الفحوصات الحرجة
                                </p>
                                <div class="mt-4">
                                    <a href="{{ route('followups.create') }}" class="px-4 py-2 text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg transition inline-flex items-center gap-1.5">
                                        <span>+ جدولة أول متابعة سريرية</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(isset($followups) && method_exists($followups, 'links'))
            <div class="p-4 border-t border-slate-100 bg-[#f8fafc]">
                {{ $followups->links() }}
            </div>
        @endif
    </div>

    <!-- VIEW 2: Clinical Cards View (Triage & Calling Mode) -->
    <div id="followupsCardsContainer" class="grid grid-cols-1 md:grid-cols-2 gap-4 hidden">
        @if(isset($followups) && count($followups) > 0)
            @foreach($followups as $flw)
                @php
                    $isOverdue = $flw->status === 'scheduled' && $flw->due_date && $flw->due_date->isPast();
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between hover:border-teal-300 transition {{ $isOverdue ? 'ring-1 ring-rose-300' : '' }}">
                    <div>
                        <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2.5">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded">
                                    #{{ $flw->id }}
                                </span>
                                @if($isOverdue)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">
                                        متأخرة عن الاستحقاق ⚠️
                                    </span>
                                @endif
                            </div>

                            @if($flw->status === 'scheduled')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    مجدولة (scheduled)
                                </span>
                            @elseif($flw->status === 'done')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    ✓ مكتملة (done)
                                </span>
                            @elseif($flw->status === 'missed')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200">
                                    ✕ فائتة (missed)
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-600">
                                    {{ $flw->status }}
                                </span>
                            @endif
                        </div>

                        <!-- Patient & Visit -->
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div>
                                @if($flw->patient)
                                    <a href="{{ route('patients.show', $flw->patient) }}" class="font-bold text-slate-900 hover:text-[#00685f] text-sm block">
                                        {{ $flw->patient->name }}
                                    </a>
                                    <div class="flex items-center gap-2 text-xs text-slate-500 font-mono mt-0.5">
                                        <span class="bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded">{{ $flw->patient->mrn }}</span>
                                        <a href="tel:{{ $flw->patient->phone }}" class="text-teal-700 font-bold hover:underline">
                                            {{ $flw->patient->phone }} 📞
                                        </a>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">مريض غير مسجل</span>
                                @endif
                            </div>

                            <div class="text-left text-xs">
                                <span class="text-slate-400 block text-[10px]">موعد الاستحقاق:</span>
                                <span class="font-mono font-bold text-slate-800">
                                    {{ $flw->due_date ? $flw->due_date->format('Y-m-d') : '-' }}
                                </span>
                            </div>
                        </div>

                        <!-- Reason -->
                        <div class="bg-[#f8fafc] border border-slate-200 rounded-xl p-3 text-xs text-slate-800 leading-relaxed mb-3">
                            <span class="font-bold text-[#00685f] block mb-1">سبب المتابعة السريرية:</span>
                            {{ $flw->reason }}
                        </div>

                        <!-- Outcome Note (if any) -->
                        @if(!empty($flw->outcome_note))
                            <div class="bg-emerald-50/50 border border-emerald-100 rounded-xl p-3 text-xs text-emerald-950 leading-relaxed mb-3">
                                <span class="font-bold text-emerald-800 block mb-1">نتيجة المتابعة الموثقة:</span>
                                {{ $flw->outcome_note }}
                            </div>
                        @endif
                    </div>

                    <!-- Footer Actions -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('followups.show', $flw) }}" class="font-bold text-[#00685f] hover:underline">
                                عرض السجل ➜
                            </a>
                            @if($flw->visit)
                                <a href="{{ route('visits.show', $flw->visit) }}" class="text-slate-500 hover:text-slate-800">
                                    (الزيارة #{{ $flw->visit->id }})
                                </a>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            @if($flw->status !== 'done')
                                <button
                                    type="button"
                                    onclick="openCompleteModal({{ $flw->id }}, '{{ addslashes($flw->patient->name ?? 'المريض') }}')"
                                    class="px-3 py-1.5 font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition"
                                >
                                    ✓ تدوين النتيجة
                                </button>
                            @endif
                            <a href="{{ route('followups.edit', $flw) }}" class="px-2.5 py-1.5 text-slate-600 hover:bg-slate-100 rounded-lg border border-slate-200">
                                تعديل
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

{{-- Modals --}}
@include('followups.complete-modal')
@include('followups.delete-modal')

<script>
function switchFollowupsView(view) {
    const tableContainer = document.getElementById('followupsTableContainer');
    const cardsContainer = document.getElementById('followupsCardsContainer');
    const tableBtn = document.getElementById('followupsTableViewBtn');
    const cardsBtn = document.getElementById('followupsCardsViewBtn');

    if (view === 'table') {
        if (tableContainer) tableContainer.classList.remove('hidden');
        if (cardsContainer) cardsContainer.classList.add('hidden');
        if (tableBtn) {
            tableBtn.className = 'px-3 py-1.5 text-xs font-bold rounded-md transition shadow-xs bg-white text-[#00685f]';
        }
        if (cardsBtn) {
            cardsBtn.className = 'px-3 py-1.5 text-xs font-semibold rounded-md transition text-slate-600 hover:text-slate-900';
        }
    } else {
        if (tableContainer) tableContainer.classList.add('hidden');
        if (cardsContainer) cardsContainer.classList.remove('hidden');
        if (cardsBtn) {
            cardsBtn.className = 'px-3 py-1.5 text-xs font-bold rounded-md transition shadow-xs bg-white text-[#00685f]';
        }
        if (tableBtn) {
            tableBtn.className = 'px-3 py-1.5 text-xs font-semibold rounded-md transition text-slate-600 hover:text-slate-900';
        }
    }
}
</script>
@endsection
