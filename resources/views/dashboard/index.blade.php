@extends('layouts.app')

@section('title', 'جدول المواعيد السريرية')

@section('content')
<div class="space-y-6">
    <!-- Header with Operational Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#0b1c30]">جدول المواعيد والزيارات السريرية</h1>
            <p class="text-xs text-[#64748b] mt-1">تنسيق وتتبع تدفق المرضى اليومي لكافة التخصصات الطبية</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" onclick="window.print()"
                class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                تصدير السجل اليومي
            </button>
            <button type="button" onclick="window.clinicalApp.openModal('newAppointmentModal')"
                class="px-4 py-2 text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg shadow-sm transition">
                + تسجيل موعد جديد
            </button>
        </div>
    </div>

    <!-- Quick Stats Ribbon -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-kpi-card title="مواعيد اليوم الكلية" :value="$stats->todayTotalAppointments ?? 148" subtitle="مقارنة بالأمس"
            trend="+12%" trendType="positive" />
        <x-kpi-card title="حالات مكتملة" :value="$stats->todayCompleted ?? 82" subtitle="نسبة الحضور 84%" trend="مكتمل"
            trendType="positive" />
        <x-kpi-card title="مواعيد قادمة مجدولة" :value="$stats->todayScheduled ?? 42" subtitle="بالانتظار في الصالة"
            trendType="neutral" />
        <x-kpi-card title="حالات فائتة / تغيّب" :value="$stats->todayMissed ?? 16" subtitle="تحتاج متابعة هاتفية"
            trend="مطلوب تدقيق" trendType="danger" />
    </div>

    <!-- Filament-Style Table Container -->
    <div class="bg-white rounded-xl border border-[#e2e8f0] shadow-xs overflow-hidden">
        <!-- Filter Bar -->
        <div class="p-3.5 border-b border-[#e2e8f0] flex flex-wrap items-center justify-between gap-3 bg-[#f8fafc]">
            <!-- Status Tabs -->
            <div class="flex items-center gap-1 overflow-x-auto pb-1 sm:pb-0" id="appointmentStatusTabs">
                <button type="button" data-status="all"
                    class="apt-status-tab px-3 py-1.5 text-xs font-semibold rounded-lg bg-[#00685f] text-white shadow-xs">الكل
                    (7)</button>
                <button type="button" data-status="scheduled"
                    class="apt-status-tab px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-600 hover:bg-slate-200">مجدولة
                    (3)</button>
                <button type="button" data-status="done"
                    class="apt-status-tab px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-600 hover:bg-slate-200">مكتملة
                    (2)</button>
                <button type="button" data-status="missed"
                    class="apt-status-tab px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-600 hover:bg-slate-200">فائتة
                    (1)</button>
                <button type="button" data-status="cancelled"
                    class="apt-status-tab px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-600 hover:bg-slate-200">ملغاة
                    (1)</button>
            </div>

            <!-- Department & Date Select -->
            <div class="flex items-center gap-2">
                <input type="date" value="2026-10-08"
                    class="bg-white border border-[#cbd5e1] rounded-lg px-2.5 py-1 text-xs text-slate-700 font-mono" />
                <select id="aptDeptSelect"
                    class="bg-white border border-[#cbd5e1] rounded-lg px-2.5 py-1 text-xs text-slate-700">
                    <option value="جميع العيادات">جميع العيادات</option>
                    <option value="عيادة الباطنية والقلب">عيادة الباطنية والقلب</option>
                    <option value="عيادة طب وجراحة الأسنان">عيادة طب وجراحة الأسنان</option>
                    <option value="عيادة العظام والمفاصل">عيادة العظام والمفاصل</option>
                    <option value="عيادة الأطفال وحديثي الولادة">عيادة الأطفال وحديثي الولادة</option>
                </select>
            </div>
        </div>

        <!-- Batch Bar -->
        <div id="aptBatchBar"
            class="hidden bg-[#eff4ff] border-b border-teal-200 px-4 py-2 flex items-center justify-between text-xs">
            <span class="text-[#00685f] font-semibold">تم تحديد المواعيد</span>
            <button type="button" onclick="window.clinicalApp.batchMarkDone()"
                class="px-3 py-1 bg-[#047857] text-white rounded font-medium hover:bg-[#036045]">
                تأكيد حضور المحددين
            </button>
        </div>

        <!-- Table Grid -->
        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead class="bg-[#f8fafc] text-[#64748b] text-xs font-semibold uppercase border-b border-[#e2e8f0]">
                    <tr>
                        <th class="py-3 px-4 w-10 text-center"><input type="checkbox" id="selectAllApt"
                                class="rounded text-[#00685f]" /></th>
                        <th class="py-3 px-4">المريض ورقم الملف (MRN)</th>
                        <th class="py-3 px-4">الطبيب المعالج</th>
                        <th class="py-3 px-4">العيادة</th>
                        <th class="py-3 px-4">الوقت</th>
                        <th class="py-3 px-4">نوع الزيارة</th>
                        <th class="py-3 px-4">الحالة</th>
                        <th class="py-3 px-4">الرسوم والفوترة</th>
                        <th class="py-3 px-4 text-center">إجراءات سريعة</th>
                    </tr>
                </thead>
                <tbody id="appointmentsTableBody" class="divide-y divide-[#e2e8f0]">
                    <!-- Rows populated dynamically by native JS or Blade loop -->
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection