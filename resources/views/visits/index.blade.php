@extends('layouts.app')

@section('title', 'سجل وزيارات المرضى والعيادات')

@section('content')
    <div class="space-y-6">
        <!-- Header with Operational Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-100 text-teal-800">وحدة الزيارات
                        السريرية</span>
                </div>
                <h1 class="text-2xl font-bold text-[#0b1c30] mt-1">سجل وزيارات المرضى </h1>
                <p class="text-xs text-[#64748b] mt-0.5">توثيق ومتابعة كافة زيارات المرضى، أسباب الكشف، الطبيب المعالج وحالات
                    الزيارة</p>
            </div>
            <div class="flex items-center gap-2.5">
                <button type="button" onclick="window.print()"
                    class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition shadow-xs flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>طباعة السجل اليومي</span>
                </button>
                <a href="{{ route('visits.create') }}"
                    class="px-4 py-2 text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>+ تسجيل زيارة جديدة</span>
                </a>
            </div>
        </div>

        <!-- Quick Stats Ribbon (KPIs based on visit_status) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-[#e2e8f0] p-4 shadow-xs">
                <div class="flex items-center justify-between text-slate-500 mb-1">
                    <span class="text-xs font-semibold">إجمالي الزيارات</span>
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                </div>
                <div class="text-2xl font-bold font-mono text-[#0b1c30]" id="kpiVisitsTotal">{{ $stats->total ?? 0 }}</div>
                <div class="text-[11px] text-[#64748b] mt-1 flex items-center gap-1">
                    <span class="text-teal-600 font-bold">كل الحالات</span> المسجلة بالنظام
                </div>
            </div>

            <div class="bg-white rounded-xl border border-blue-200 bg-blue-50/20 p-4 shadow-xs">
                <div class="flex items-center justify-between text-blue-700 mb-1">
                    <span class="text-xs font-semibold">تم الإنشاء / قيد الانتظار (Created)</span>
                    <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                </div>
                <div class="text-2xl font-bold font-mono text-blue-800" id="kpiVisitsCreated">{{ $stats->created ?? 0 }}
                </div>
                <div class="text-[11px] text-blue-600 mt-1">بانتظار دخول العيادة والكشف</div>
            </div>

            <div class="bg-white rounded-xl border border-emerald-200 bg-emerald-50/20 p-4 shadow-xs">
                <div class="flex items-center justify-between text-emerald-700 mb-1">
                    <span class="text-xs font-semibold">مكتملة بنجاح (Completed)</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="text-2xl font-bold font-mono text-emerald-800" id="kpiVisitsCompleted">
                    {{ $stats->completed ?? 0 }}</div>
                <div class="text-[11px] text-emerald-600 mt-1">تم توثيق الكشف والوصفة الطبية</div>
            </div>

            <div class="bg-white rounded-xl border border-rose-200 bg-rose-50/20 p-4 shadow-xs">
                <div class="flex items-center justify-between text-rose-700 mb-1">
                    <span class="text-xs font-semibold">ملغاة (Cancelled)</span>
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                </div>
                <div class="text-2xl font-bold font-mono text-rose-800" id="kpiVisitsCancelled">{{ $stats->cancelled ?? 0 }}
                </div>
                <div class="text-[11px] text-rose-600 mt-1">إلغاء بناء على طلب المريض أو الطبيب</div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="bg-white rounded-xl border border-[#e2e8f0] shadow-xs overflow-hidden">
            <!-- Filter Bar -->
            <form method="GET" action="{{ route('visits.index') }}"
                class="p-3.5 border-b border-[#e2e8f0] flex flex-wrap items-center justify-between gap-3 bg-[#f8fafc]">
                <!-- Status Tabs: Enum Filter -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0" id="visitStatusTabs">
                    <button type="submit" name="visit_status" value="all"
                        class="visit-status-tab px-3 py-1.5 text-xs font-semibold rounded-lg bg-[#00685f] text-white shadow-xs transition">
                        الكل (<span id="tabCountAll">{{ $stats->total ?? 0 }}</span>)
                    </button>
                    <button type="submit" name="visit_status" value="created"
                        class="visit-status-tab px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-600 hover:bg-slate-200 transition">
                        تم الإنشاء (<span id="tabCountCreated">{{ $stats->created ?? 0 }}</span>)
                    </button>
                    <button type="submit" name="visit_status" value="completed"
                        class="visit-status-tab px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-600 hover:bg-slate-200 transition">
                        مكتملة (<span id="tabCountCompleted">{{ $stats->completed ?? 0 }}</span>)
                    </button>
                    <button type="submit" name="visit_status" value="cancelled"
                        class="visit-status-tab px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-600 hover:bg-slate-200 transition">
                        ملغاة (<span id="tabCountCancelled">{{ $stats->cancelled ?? 0 }}</span>)
                    </button>
                </div>

                <!-- Search & Filters -->
                <div class="flex items-center gap-2 flex-wrap">
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" id="visitSearchInput"
                            placeholder="بحث في سبب الزيارة أو المريض..."
                            class="bg-white border border-[#cbd5e1] rounded-lg pr-8 pl-3 py-1.5 text-xs text-slate-700 w-48 sm:w-64 focus:outline-none focus:ring-1 focus:ring-[#00685f]" />
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute right-2.5 top-2.5" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <input type="date" name="date" value="{{ request('date') }}" id="visitDateFilter"
                        value="2026-10-08"
                        class="bg-white border border-[#cbd5e1] rounded-lg px-2.5 py-1.5 text-xs text-slate-700 font-mono focus:outline-none focus:ring-1 focus:ring-[#00685f]" />

                    <select name="doctor_id" id="visitDoctorFilter"
                        class="bg-white border border-[#cbd5e1] rounded-lg px-2.5 py-1.5 text-xs text-slate-700 focus:outline-none focus:ring-1 focus:ring-[#00685f]">
                        <option value="">كافة الأطباء والعيادات</option>
                        @if (isset($doctors))
                            @foreach ($doctors as $doc)
                                <option value="{{ $doc->id }}" @selected(request('doctor_id') == $doc->id)>{{ $doc->fullname }} -
                                    {{ $doc->clinic->name ?? '' }}</option>
                            @endforeach
                        @endif
                    </select>
                    <div class="flex items-center gap-3 text-xs">
                        <button type="submit"
                            class="px-4 py-2 text-xs font-bold text-white bg-[#00685f] rounded-lg hover:bg-[#00554e] transition">
                            بحث
                        </button>
                        <a href="{{ route('visits.index') }}"
                            class="px-3 py-2 text-xs border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                            إعادة تعيين
                        </a>
                    </div>
                </div>
            </form>

            <!-- Table Grid -->
            <x-visits.table :visits="$visits" />
        </div>
        @if (isset($visits) && method_exists($visits, 'links'))
            <div class="p-4 border-t border-slate-100 bg-[#f8fafc]">
                {{ $visits->links() }}
            </div>
        @endif

    </div>
@endsection
