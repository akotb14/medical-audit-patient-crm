@extends('layouts.app')

@section('title', 'المتابعات السريرية الحرجة')

@section('content')
<div class="space-y-6">
    <!-- Banner -->
    <div
        class="bg-gradient-to-l from-teal-900 to-[#00685f] rounded-xl p-5 text-white shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-teal-200 text-xs font-bold uppercase tracking-wider mb-1">
                نظام الفرز والمتابعة السريرية لما بعد الزيارة
            </div>
            <h2 class="text-xl font-bold">المتابعات الطبية والنتائج المخبرية الحرجة</h2>
            <p class="text-xs text-teal-100 mt-1 max-w-2xl leading-relaxed">
                متابعة المرضى ذوي الخطورة العالية، المواعيد الفائتة لمرضى القلب والأمراض المزمنة، واستقرار الحالات بعد
                التدخلات الطبية.
            </p>
        </div>
        <div class="flex items-center gap-2 self-start md:self-auto">
            <div class="bg-white/10 backdrop-blur-xs px-3.5 py-2 rounded-lg border border-white/20 text-center">
                <div class="text-2xl font-bold font-mono">1</div>
                <div class="text-[10px] text-teal-100">فائقة العجلة</div>
            </div>
            <div class="bg-white/10 backdrop-blur-xs px-3.5 py-2 rounded-lg border border-white/20 text-center">
                <div class="text-2xl font-bold font-mono">2</div>
                <div class="text-[10px] text-teal-100">بانتظار الاتصال</div>
            </div>
        </div>
    </div>

    <!-- Filter Buttons -->
    <div class="flex items-center gap-2 border-b border-[#e2e8f0] pb-2" id="followUpFilterBar">
        <button type="button" data-status="all"
            class="flw-filter-btn px-3 py-1.5 text-xs font-semibold rounded-lg bg-[#00685f] text-white">كافة
            المتابعات</button>
        <button type="button" data-status="pending"
            class="flw-filter-btn px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-600 hover:bg-slate-100">بانتظار
            الاتصال</button>
        <button type="button" data-status="contacted"
            class="flw-filter-btn px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-600 hover:bg-slate-100">تم
            التواصل</button>
    </div>

    <!-- Cards Grid -->
    <div id="followUpsGrid" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Injected via Native JS or populated by Blade loop -->
    </div>
</div>
@endsection