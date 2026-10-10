<aside class="w-64 bg-white border-l border-[#e2e8f0] flex flex-col shrink-0 min-h-[calc(100vh-4rem)]">
    <!-- System Badge -->
    <div class="p-4 border-b border-[#e2e8f0] bg-[#f8fafc]">
        <div class="flex items-center gap-2 text-xs font-bold text-[#00685f]">
            <svg class="w-4 h-4 text-[#00685f]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            <span>منظومة الرعاية السريرية RTL</span>
        </div>
        <div class="text-[11px] text-[#64748b] mt-1">
            إصدار الإدارة الطبية 2.6.4 (Laravel Blade / Native JS)
        </div>
    </div>

    <!-- Navigation Items -->
    <div class="p-3 space-y-1 flex-1 overflow-y-auto">
        <div class="px-3 py-1.5 text-[11px] font-semibold text-[#64748b] uppercase tracking-wider">
            الوحدات التشغيلية
        </div>

        <button
            type="button"
            data-screen="dashboard"
            class="nav-tab-btn w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-semibold transition text-right bg-[#00685f] text-white shadow-xs"
        >
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                <span>نظرة عامة والفرز السريري</span>
            </div>
        </button>

        <button
            type="button"
            data-screen="appointments"
            class="nav-tab-btn w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-semibold transition text-right text-[#3d4947] hover:bg-[#f1f5f9] hover:text-[#0b1c30]"
        >
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-[#64748b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>جدول المواعيد والزيارات</span>
            </div>
            <span id="navAptCount" class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold border bg-teal-50 text-teal-800 border-teal-200">
                7
            </span>
        </button>

        <button
            type="button"
            data-screen="followups"
            class="nav-tab-btn w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-semibold transition text-right text-[#3d4947] hover:bg-[#f1f5f9] hover:text-[#0b1c30]"
        >
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-[#64748b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                <span>المتابعات الطبية الحرجة</span>
            </div>
            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold border bg-amber-50 text-amber-800 border-amber-200">
                4
            </span>
        </button>

        <button
            type="button"
            data-screen="complaints"
            class="nav-tab-btn w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-semibold transition text-right text-[#3d4947] hover:bg-[#f1f5f9] hover:text-[#0b1c30]"
        >
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-[#64748b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>إدارة الشكاوى والاعتراضات</span>
            </div>
            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold border bg-red-50 text-red-800 border-red-200">
                3
            </span>
        </button>

        <a
          href={{route('patients.index')}}
            data-screen="patients"
            class="nav-tab-btn w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-semibold transition text-right text-[#3d4947] hover:bg-[#f1f5f9] hover:text-[#0b1c30]"
        >
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-[#64748b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span>سجل المرضى والملفات (MRN)</span>
            </div>
            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold border bg-slate-100 text-slate-700 border-slate-200">
                6
            </span>
        </a>

        <!-- RBAC Users & Roles Modules -->
        <div class="pt-3 pb-1 px-3 text-[11px] font-semibold text-[#64748b] uppercase tracking-wider">
            الأمان وإدارة الكادر
        </div>

        <button
            type="button"
            data-screen="users"
            class="nav-tab-btn w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-semibold transition text-right text-[#3d4947] hover:bg-[#f1f5f9] hover:text-[#0b1c30]"
        >
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-[#64748b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <a href="{{route('users.index')}}"><span>المستخدمون والكادر الطبي</span></a>
            </div>
            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold border bg-purple-50 text-purple-700 border-purple-200">
                RBAC
            </span>
        </button>

        <button
            type="button"
            data-screen="roles_matrix"
            class="nav-tab-btn w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-semibold transition text-right text-[#3d4947] hover:bg-[#f1f5f9] hover:text-[#0b1c30]"
        >
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-[#64748b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <a href="{{route('users.index')}}"><span>مصفوفة الأدوار والصلاحيات</span></a>
            </div>
        </button>


        <!-- Department Filter -->
        <div class="pt-5 pb-1 px-3 text-[11px] font-semibold text-[#64748b] uppercase tracking-wider flex items-center justify-between">
            <span>تصفية العيادات</span>
        </div>
        <div class="space-y-0.5 pt-1">
            <button type="button" onclick="window.clinicalApp.filterDept('جميع العيادات')" class="dept-filter-btn w-full text-right px-3 py-1.5 rounded-md text-[11px] font-bold text-[#00685f] bg-[#eff4ff]">جميع العيادات</button>
            <button type="button" onclick="window.clinicalApp.filterDept('عيادة الباطنية والقلب')" class="dept-filter-btn w-full text-right px-3 py-1.5 rounded-md text-[11px] text-[#64748b] hover:text-[#0b1c30]">عيادة الباطنية والقلب</button>
            <button type="button" onclick="window.clinicalApp.filterDept('عيادة العظام والمفاصل')" class="dept-filter-btn w-full text-right px-3 py-1.5 rounded-md text-[11px] text-[#64748b] hover:text-[#0b1c30]">عيادة العظام والمفاصل</button>
            <button type="button" onclick="window.clinicalApp.filterDept('عيادة الأطفال وحديثي الولادة')" class="dept-filter-btn w-full text-right px-3 py-1.5 rounded-md text-[11px] text-[#64748b] hover:text-[#0b1c30]">عيادة الأطفال وحديثي الولادة</button>
            <button type="button" onclick="window.clinicalApp.filterDept('عيادة طب وجراحة الأسنان')" class="dept-filter-btn w-full text-right px-3 py-1.5 rounded-md text-[11px] text-[#64748b] hover:text-[#0b1c30]">عيادة طب وجراحة الأسنان</button>
        </div>
    </div>

    <!-- Footer Info -->
    <div class="p-3 border-t border-[#e2e8f0] bg-[#f8fafc] text-xs">
        <div class="flex items-center gap-1.5 text-slate-700 font-medium">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>Laravel Blade + Native JS</span>
        </div>
        <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
            قوالب نقية بدون أي مكتبات React. جاهزة للنسخ المباشر في مجلد Views.
        </p>
    </div>
</aside>
