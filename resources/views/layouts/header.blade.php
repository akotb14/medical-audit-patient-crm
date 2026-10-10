<header class="bg-white border-b border-[#e2e8f0] sticky top-0 z-30 shadow-xs">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
        <!-- Right side (RTL): Brand title & Shift Info -->
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-[#00685f] text-white flex items-center justify-center font-bold text-lg shadow-sm">
                ر
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="font-bold text-[#0b1c30] text-base leading-tight">
                        {{ config('app.name', 'مركز الرعاية السريرية المتخصصة') }}
                    </h1>
                    <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-[#eff4ff] text-[#00685f]">
                        الفرع الرئيسي
                    </span>
                </div>
                <div class="flex items-center gap-2 text-xs text-[#64748b] mt-0.5">
                    <span class="inline-flex items-center gap-1 font-mono">
                        <svg class="w-3 h-3 text-[#00685f]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        الوردية الصباحية: 08:00 - 16:00
                    </span>
                    <span class="text-slate-300">|</span>
                    <span class="font-mono">{{ now()->locale('ar')->translatedFormat('l، d F Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Center: Quick Search Bar for MRN or Patient Name -->
        <div class="flex-1 max-w-md hidden md:block">
            <div class="relative">
                <input
                    type="text"
                    id="globalSearchInput"
                    placeholder="بحث سريع برقم الملف (MRN)، الهوية، أو اسم المريض..."
                    class="w-full pl-9 pr-9 py-2 text-sm bg-[#f8fafc] border border-[#cbd5e1] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00685f] focus:bg-white text-right placeholder:text-slate-400 transition"
                />
                <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            </div>
        </div>

        <!-- Left side: Quick Action Buttons & Profile -->
        <div class="flex items-center gap-2 sm:gap-3">
            <button
                type="button"
                onclick="window.clinicalApp.openModal('newAppointmentModal')"
                class="flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg shadow-sm transition active:scale-[0.98]"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span class="hidden sm:inline">تسجيل موعد</span>
                <span class="sm:hidden">موعد</span>
            </button>

            <!-- Alerts Badge -->
            <button
                type="button"
                onclick="window.clinicalApp.openModal('newComplaintModal')"
                class="p-2 text-slate-600 hover:bg-slate-100 rounded-lg relative transition"
                title="تنبيهات المتابعة والشكاوى النشطة"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span class="absolute top-1 right-1 w-4 h-4 bg-red-600 text-white rounded-full text-[10px] font-bold flex items-center justify-center animate-pulse">
                    3
                </span>
            </button>

            <div class="h-6 w-px bg-slate-200 mx-1 hidden sm:block"></div>

            <!-- Profile Info -->
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-teal-100 border border-teal-300 text-[#00685f] flex items-center justify-center font-bold text-xs">
                    س.د
                </div>
                <div class="hidden lg:block text-right">
                    <div class="text-xs font-bold text-[#0b1c30]">أ. سارة الدوسري</div>
                    <div class="text-[10px] text-[#64748b]">مشرفة استقبال العيادات</div>
                </div>
            </div>
        </div>
    </div>
</header>
