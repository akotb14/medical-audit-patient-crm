{{-- Mobile overlay: closes the navigation drawer --}}
<div id="sidebar-overlay" class="fixed inset-0 z-40 hidden bg-slate-900/50 backdrop-blur-xs lg:hidden"
    aria-hidden="true"></div>

<aside id="app-sidebar" aria-label="التنقل الرئيسي"
    class="isolate hidden lg:flex fixed inset-y-0 right-0 z-50 w-64 max-w-[85vw] flex-col overflow-hidden bg-gradient-to-b from-[#012d29] via-[#00453f] to-[#005b52] shadow-2xl lg:relative lg:z-auto lg:shadow-none">

    {{-- Decorative layer: dotted texture + colour glows --}}
    <div class="pointer-events-none absolute inset-0 -z-10 opacity-15"
        style="background-image: radial-gradient(circle at 1px 1px, rgba(255, 255, 255, 0.55) 1px, transparent 0); background-size: 22px 22px;"
        aria-hidden="true"></div>
    <div class="pointer-events-none absolute -right-16 -top-24 -z-10 h-56 w-56 rounded-full bg-teal-300/25 blur-3xl"
        aria-hidden="true"></div>
    <div class="pointer-events-none absolute -bottom-24 -left-16 -z-10 h-56 w-56 rounded-full bg-emerald-300/20 blur-3xl"
        aria-hidden="true"></div>

    <!-- System Badge & Mobile Close -->
    {{-- The header already carries the brand on desktop, so this block belongs to the mobile drawer only. --}}
    <div class="flex h-16 items-center justify-between gap-2 border-b border-white/10 px-4 lg:hidden">
        <div class="flex min-w-0 items-center gap-2.5">
            <span
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/15 text-white ring-1 ring-white/25 backdrop-blur-xs">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" stroke-width="1.75" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v8m-4-4h8" />
                </svg>
            </span>
            <span class="min-w-0 leading-tight">
                <span class="block truncate text-xs font-extrabold text-white">منظومة الرعاية السريرية</span>
                <span class="block truncate text-[11px] text-teal-50/75">لوحة التحكم السريرية</span>
            </span>
        </div>
        <button type="button" id="sidebar-close"
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-teal-50/80 transition hover:bg-white/10 hover:text-white lg:hidden"
            aria-label="إغلاق قائمة التنقل">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Navigation Items -->
    <nav class="flex-1 space-y-1 overflow-y-auto p-2">
        <x-sidebar-section label="الوحدات التشغيلية" />

        @role('Admin')
            <x-sidebar-link :href="route('dashboard.index')" :active="request()->routeIs('dashboard.*')"
                label="نظرة عامة والفرز السريري" tone="teal">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <rect width="7" height="9" x="3" y="3" rx="1" />
                    <rect width="7" height="5" x="14" y="3" rx="1" />
                    <rect width="7" height="9" x="14" y="12" rx="1" />
                    <rect width="7" height="5" x="3" y="16" rx="1" />
                </svg>
            </x-sidebar-link>
        @endrole
        <x-sidebar-link :href="route('patients.index')" :active="request()->routeIs('patients.*')"
            label="سجل المرضى والملفات (MRN)" tone="cyan">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </x-sidebar-link>

        <x-sidebar-link :href="route('visits.index')" :active="request()->routeIs('visits.*')"
            label="جدول المواعيد والزيارات" tone="sky">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </x-sidebar-link>

        <x-sidebar-link :href="route('follow_ups.index')" :active="request()->routeIs('follow_ups.*')"
            label="المتابعات الطبية الحرجة" tone="rose">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
        </x-sidebar-link>

        <x-sidebar-link :href="route('surveys.index')" :active="request()->routeIs('surveys.*')" label="استبيانات الرضا"
            tone="amber">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
            </svg>
        </x-sidebar-link>

        <x-sidebar-link :href="route('complaints.index')" :active="request()->routeIs('complaints.*')"
            label="إدارة الشكاوى والاعتراضات" tone="orange">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </x-sidebar-link>

        

        <!-- Clinics & Doctors (One-to-Many Architecture) -->
        <x-sidebar-section label="الهيكل الطبي والعيادات" />

        <x-sidebar-link :href="route('clinics.index')" :active="request()->routeIs('clinics.*')"
            label="العيادات والأقسام" tone="indigo">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
        </x-sidebar-link>

        <x-sidebar-link :href="route('doctors.index')" :active="request()->routeIs('doctors.*')"
            label="الأطباء والاستشاريون" tone="emerald">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
        </x-sidebar-link>

        @can('viewAny', App\Models\User::class)
            <!-- RBAC Users & Roles Modules -->
            <x-sidebar-section label="الأمان وإدارة الكادر" />

            <x-sidebar-link :href="route('users.index')" :active="request()->routeIs('users.*')"
                label="المستخدمون والكادر الطبي" tone="violet">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </x-sidebar-link>
        @endcan
    </nav>

    <!-- Sidebar Footer -->
    <div class="border-t border-white/10 px-4 py-3">
        <p class="text-center text-[11px] font-medium text-teal-50/75">
            مركز الرعاية السريرية المتخصصة · © {{ date('Y') }}
        </p>
    </div>
</aside>
