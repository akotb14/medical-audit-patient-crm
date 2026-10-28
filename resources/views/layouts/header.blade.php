<header class="bg-white border-b border-[#e2e8f0] sticky top-0 z-30 shadow-xs">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
        <!-- Right side (RTL): Brand title & Shift Info -->
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-lg bg-[#00685f] text-white flex items-center justify-center font-bold text-lg shadow-sm">
                ر
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="font-bold text-[#0b1c30] text-base leading-tight">
                        مركز الرعاية السريرية المتخصصة </h1>
                    <span
                        class="hidden sm:inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-[#eff4ff] text-[#00685f]">
                        الفرع الرئيسي
                    </span>
                </div>

            </div>
        </div>

        <!-- Center: Quick Search Bar for MRN or Patient Name -->
        <div class="flex-1 max-w-md hidden md:block">
            <div class="relative">
                <input type="text" id="globalSearchInput"
                    placeholder="بحث سريع برقم الملف (MRN)، الهوية، أو اسم المريض..."
                    class="w-full pl-9 pr-9 py-2 text-sm bg-[#f8fafc] border border-[#cbd5e1] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00685f] focus:bg-white text-right placeholder:text-slate-400 transition" />
                <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.3-4.3" />
                </svg>
            </div>
        </div>

        <!-- Logout Button -->
        <form action="{{ route('logout') }}" method="POST" class="inline m-0">
            @csrf
            <button type="submit"
                class="px-2.5 py-1.5 text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 hover:text-rose-900 border border-rose-200 rounded-lg shadow-2xs transition active:scale-[0.98] flex items-center gap-1.5"
                title="تسجيل الخروج من النظام (Logout)">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>خروج</span>
            </button>
        </form>
    </div>
</header>
