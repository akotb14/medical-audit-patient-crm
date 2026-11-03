@php
    $currentUser = auth()->user();
    $roleLabels = [
        'Admin' => 'مدير النظام',
        'Receptionist' => 'استقبال',
    ];
    $currentRole = $currentUser ? $currentUser->getRoleNames()->first() : null;
@endphp

<header class="sticky top-0 z-30 border-b border-slate-200 bg-white">
    <div class="mx-auto flex h-16 max-w-[1600px] items-center justify-between gap-4 px-4 sm:px-6 lg:px-4">
        <!-- Right side (RTL): menu trigger, brand and branch -->
        <div class="flex min-w-0 items-center gap-3">
            {{-- Opens the drawer on small screens, collapses the sidebar on large screens --}}
            <button type="button" id="sidebar-open" aria-controls="app-sidebar" aria-expanded="true"
                aria-label="إخفاء القائمة الجانبية"
                class="inline-flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-lg text-slate-500 transition-colors duration-150 hover:bg-slate-100 hover:text-slate-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#00685f]">
                <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <span class="hidden h-5 w-px shrink-0 bg-slate-200 sm:block" aria-hidden="true"></span>

            <a href="{{ route('home') }}"
                class="flex min-w-0 items-center gap-2.5 rounded-lg transition-opacity duration-150 hover:opacity-80">
                <span
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-[10px] bg-[#00685f] text-white shadow-sm shadow-[#00685f]/20">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <circle cx="12" cy="12" r="9" />
                        <path stroke-linecap="round" d="M12 8v8m-4-4h8" />
                    </svg>
                </span>
                <span class="min-w-0">
                    <span class="flex items-center gap-2">
                        <span class="truncate text-[15px] font-semibold leading-tight text-slate-900">
                            مركز الرعاية السريرية المتخصصة
                        </span>
                        <span
                            class="hidden shrink-0 items-center rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600 sm:inline-flex">
                            الفرع الرئيسي
                        </span>
                    </span>
                </span>
            </a>
        </div>

        <!-- Left side (RTL): signed-in user and logout -->
        <div class="flex shrink-0 items-center gap-2">
            @if ($currentUser)
                <div class="hidden items-center gap-2.5 sm:flex">
                    <div class="text-right leading-tight">
                        <span class="block max-w-[160px] truncate text-[12.5px] font-semibold text-slate-800">
                            {{ $currentUser->name }}
                        </span>
                        <span class="block text-[11px] text-slate-500">
                            {{ $roleLabels[$currentRole] ?? 'مستخدم' }}
                        </span>
                    </div>
                    <span
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#00685f]/10 text-[11px] font-bold text-[#00685f] ring-1 ring-[#00685f]/15"
                        aria-hidden="true">
                        {{ mb_substr($currentUser->name, 0, 1) }}
                    </span>
                </div>
            @endif

            <span class="mx-0.5 hidden h-5 w-px bg-slate-200 sm:block" aria-hidden="true"></span>

            <form action="{{ route('logout') }}" method="POST" class="m-0 inline">
                @csrf
                <button type="submit"
                    class="flex h-9 cursor-pointer items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-[12px] font-semibold text-slate-500 transition-colors duration-150 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#00685f] active:scale-[0.98]"
                    title="تسجيل الخروج من النظام (Logout)">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span class="hidden sm:inline">خروج</span>
                </button>
            </form>
        </div>
    </div>
</header>