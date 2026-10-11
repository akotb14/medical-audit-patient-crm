@extends('layouts.app')

@section('title', 'إدارة الشكاوى والاعتراضات')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#0b1c30]">إدارة الشكاوى واعتراضات المرضى</h1>
            <p class="text-xs text-[#64748b] mt-1">دورة حياة الاستجابة السريعة لملاحظات المرضى وفق معايير الجودة
                السريرية وتجربة العميل (PX)</p>
        </div>
        <button type="button" onclick="window.clinicalApp.openModal('newComplaintModal')"
            class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-[#00685f] hover:bg-[#00524a] rounded-lg shadow-sm transition">
            + تسجيل اعتراض / شكوى جديدة
        </button>
    </div>

    <!-- Stepper Stage Quick Filter -->
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1" id="complaintStageFilters">
        <button type="button" data-stage="all"
            class="cmp-stage-btn px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#00685f] text-white">الكل</button>
        <button type="button" data-stage="new"
            class="cmp-stage-btn px-3 py-1.5 rounded-lg text-xs font-semibold bg-white text-slate-600 border border-slate-200">جديدة</button>
        <button type="button" data-stage="assigned"
            class="cmp-stage-btn px-3 py-1.5 rounded-lg text-xs font-semibold bg-white text-slate-600 border border-slate-200">مسندة</button>
        <button type="button" data-stage="investigating"
            class="cmp-stage-btn px-3 py-1.5 rounded-lg text-xs font-semibold bg-white text-slate-600 border border-slate-200">قيد
            التحقيق</button>
        <button type="button" data-stage="resolved"
            class="cmp-stage-btn px-3 py-1.5 rounded-lg text-xs font-semibold bg-white text-slate-600 border border-slate-200">تم
            الحل</button>
        <button type="button" data-stage="closed"
            class="cmp-stage-btn px-3 py-1.5 rounded-lg text-xs font-semibold bg-white text-slate-600 border border-slate-200">مغلقة</button>
    </div>

    <!-- Dual Layout: Ticket List & Stepper Details -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Ticket List (Right in RTL) -->
        <div id="complaintsListContainer" class="lg:col-span-5 space-y-3">
            <!-- Injected via Native JS or Blade loop -->
        </div>

        <!-- Stepper Details Inspector (Left in RTL) -->
        <div id="complaintInspectorContainer"
            class="lg:col-span-7 bg-white rounded-xl border border-[#e2e8f0] p-6 shadow-xs space-y-6">
            <!-- Injected dynamically based on active ticket -->
        </div>
    </div>
</div>
@endsection