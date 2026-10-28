<aside class="w-64 bg-white border-l border-[#e2e8f0] flex flex-col shrink-0 min-h-[calc(100vh-4rem)]">
    <!-- System Badge -->
    <div class="p-4 border-b border-[#e2e8f0] bg-[#f8fafc]">
        <div class="flex items-center gap-2 text-xs font-bold text-[#00685f]">
            <svg class="w-4 h-4 text-[#00685f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            <span>منظومة الرعاية السريرية </span>
        </div>

    </div>

    <!-- Navigation Items -->
    <div class="p-3 space-y-1 flex-1 overflow-y-auto">
        <div class="px-3 py-1.5 text-[11px] font-semibold text-[#64748b] uppercase tracking-wider">
            الوحدات التشغيلية
        </div>
        @role('Admin')
            <a href={{ route('dashboard.index') }}
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs
        font-semibold transition text-right
        {{ request()->routeIs('dashboard.*')
            ? ' bg-[#00685f] text-white shadow-xs'
            : " text-[#3d4947]
                         hover:bg-[#f1f5f9] hover:text-[#0b1c30]" }} ">
                <div class=" flex items-center gap-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect width="7" height="9" x="3" y="3" rx="1" />
                        <rect width="7" height="5" x="14" y="3" rx="1" />
                        <rect width="7" height="9" x="14" y="12" rx="1" />
                        <rect width="7" height="5" x="3" y="16" rx="1" />
                    </svg>
                    <span>نظرة عامة والفرز السريري</span>
                </div>
            </a>
        @endrole

        <a href={{ route('visits.index') }}
            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs
            font-semibold transition text-right
             {{ request()->routeIs('visits.*')
                 ? ' bg-[#00685f] text-white shadow-xs'
                 : " text-[#3d4947]
                                                    hover:bg-[#f1f5f9] hover:text-[#0b1c30]" }} ">
            <div class=" flex items-center gap-2.5">
                <svg class="w-4 h-4 text-[#64748b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>جدول المواعيد والزيارات</span>
            </div>

        </a>

        <a href={{ route('follow_ups.index') }}
            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs
            font-semibold transition text-right
             {{ request()->routeIs('follow_ups.*')
                 ? ' bg-[#00685f] text-white shadow-xs'
                 : " text-[#3d4947]
                                                    hover:bg-[#f1f5f9] hover:text-[#0b1c30]" }} ">
            <div class=" flex items-center gap-2.5">
                <svg class="w-4 h-4 text-[#64748b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <span>المتابعات الطبية الحرجة</span>
            </div>

        </a>

        <a href="{{ route('surveys.index') }}"
            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-semibold transition text-right {{ request()->routeIs('surveys.*') ? 'bg-[#00685f] text-white shadow-xs' : 'text-[#3d4947] hover:bg-[#f1f5f9] hover:text-[#0b1c30]' }}">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 {{ request()->routeIs('surveys.*') ? 'text-white' : 'text-amber-500' }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
                <span>استبيانات الرضا </span>
            </div>

        </a>
        <a href={{ route('complaints.index') }}
            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs
            font-semibold transition text-right
             {{ request()->routeIs('complaints.*')
                 ? ' bg-[#00685f] text-white shadow-xs'
                 : " text-[#3d4947]
                                                    hover:bg-[#f1f5f9] hover:text-[#0b1c30]" }} ">
            <div class=" flex items-center gap-2.5">
                <svg class="w-4 h-4 text-[#64748b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>إدارة الشكاوى والاعتراضات</span>
            </div>

        </a>

        <a href={{ route('patients.index') }}
            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs
            font-semibold transition text-right
             {{ request()->routeIs('patients.*')
                 ? ' bg-[#00685f] text-white shadow-xs'
                 : " text-[#3d4947]
                                                    hover:bg-[#f1f5f9] hover:text-[#0b1c30]" }} ">
            <div class=" flex items-center gap-2.5">
                <svg class="w-4 h-4 text-[#64748b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>سجل المرضى والملفات (MRN)</span>
            </div>

        </a>
        <!-- Clinics & Doctors (One-to-Many Architecture) -->
        <div class="pt-3 pb-1 px-3 text-[11px] font-semibold text-[#64748b] uppercase tracking-wider">
            الهيكل الطبي والعيادات
        </div>
        <a href={{ route('clinics.index') }}
            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs
            font-semibold transition text-right
             {{ request()->routeIs('clinics.*')
                 ? ' bg-[#00685f] text-white shadow-xs'
                 : " text-[#3d4947]
                                                    hover:bg-[#f1f5f9] hover:text-[#0b1c30]" }} ">
            <div class=" flex items-center gap-2.5">
                <svg class="w-4 h-4 text-[#64748b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                    </path>
                </svg>
                <span>العيادات والأقسام</span>
            </div>

        </a>
        <a href={{ route('doctors.index') }}
            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs
            font-semibold transition text-right
             {{ request()->routeIs('doctors.*')
                 ? ' bg-[#00685f] text-white shadow-xs'
                 : " text-[#3d4947]
                                                    hover:bg-[#f1f5f9] hover:text-[#0b1c30]" }} ">
            <div class=" flex items-center gap-2.5">
                <svg class="w-4 h-4 text-[#64748b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span>الأطباء والاستشاريون</span>
            </div>

        </a>
        @can('viewAny', App\Models\User::class)
            <!-- RBAC Users & Roles Modules -->
            <div class="pt-3 pb-1 px-3 text-[11px] font-semibold text-[#64748b] uppercase tracking-wider">
                الأمان وإدارة الكادر
            </div>

            <a href={{ route('users.index') }}
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs
    font-semibold transition text-right
    {{ request()->routeIs('users.*')
        ? ' bg-[#00685f] text-white shadow-xs'
        : " text-[#3d4947]
                         hover:bg-[#f1f5f9] hover:text-[#0b1c30]" }} ">
                <div class=" flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-[#64748b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>المستخدمون والكادر الطبي</span>
                </div>

            </a>
        @endcan

    </div>


</aside>
