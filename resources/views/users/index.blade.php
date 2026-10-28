@extends('layouts.app')

@section('title', 'إدارة المستخدمين والكادر الطبي')

@section('content')
<div class="space-y-6">
    <!-- Header with Operational Actions -->
    <x-alerts />

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#0b1c30]">إدارة المستخدمين والكادر الطبي</h1>
            <p class="text-xs text-[#64748b] mt-1">
                التحكم في حسابات الكادر الطبي والإداري وتعيين الأدوار والصلاحيات السريرية (RBAC)
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" onclick="window.clinicalApp.navigate('roles_matrix')"
                class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition flex items-center gap-1.5">
                <svg class="w-4 h-4 text-[#00685f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span>مصفوفة الأدوار والصلاحيات</span>
            </button>
            <a href="{{route('users.create')}}"
                class="px-4 py-2 text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg shadow-sm transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span> إضافة مستخدم جديد</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats for Users & Security -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-kpi-card title="إجمالي المستخدمين" :value="$users->count() ?? 0" subtitle="كادر العيادات المسجل" trend="نشط"
            trendType="positive" />
        <x-kpi-card title="فريق الاستقبال والفرز" :value="$receptionCountRole ?? 0" subtitle="مناوبات الصباح والمساء"
            trendType="neutral" />
    </div>

    <!-- Filament-Style Users Table Container -->
    <div class="bg-white rounded-xl border border-[#e2e8f0] shadow-xs overflow-hidden">
        <!-- Filter Bar -->
        <div class="p-3.5 border-b border-[#e2e8f0] flex flex-wrap items-center justify-between gap-3 bg-[#f8fafc]">
            <!-- Role Tabs -->
            <div class="flex items-center gap-1 overflow-x-auto pb-1 sm:pb-0" id="userRoleFilterTabs">
                <button type="button" data-role="all"
                    class="user-role-tab px-3 py-1.5 text-xs font-semibold rounded-lg bg-[#00685f] text-white shadow-xs">كافة
                    الأدوار</button>
                <button type="button" data-role="مدير النظام"
                    class="user-role-tab px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-600 hover:bg-slate-200">مدير
                    النظام</button>

                <button type="button" data-role="موظف استقبال"
                    class="user-role-tab px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-600 hover:bg-slate-200">استقبال
                    وفرز</button>

            </div>

            <!-- Search by Name/Email -->
            <div class="flex items-center gap-2">
                <div class="relative">
                    <input type="text" id="userSearchInput" placeholder="بحث بالاسم أو البريد..."
                        class="w-64 pl-8 pr-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#00685f]" />
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Table Grid -->
        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead class="bg-[#f8fafc] text-[#64748b] text-xs font-semibold uppercase border-b border-[#e2e8f0]">
                    <tr>
                        <th class="py-3.5 px-4">المستخدم والبريد الإلكتروني</th>
                        <th class="py-3.5 px-4">الدور الوظيفي (Role)</th>
                        <th class="py-3.5 px-4">القسم / العيادة</th>
                        <th class="py-3.5 px-4">الصلاحيات الممنوحة</th>
                        <th class="py-3.5 px-4">حالة الحساب</th>
                        <th class="py-3.5 px-4">آخر دخول</th>
                        <th class="py-3.5 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody" class="divide-y divide-[#e2e8f0]">
                    @forelse($users ?? [] as $user)
                    <tr class="hover:bg-[#f8fafc] transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-full bg-teal-100 text-[#00685f] font-bold text-xs flex items-center justify-center">
                                    {{ mb_substr($user->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-[#0b1c30]">{{ $user->name }}</div>
                                    <div class="text-xs text-[#64748b] font-mono">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span
                                class="px-2.5 py-1 rounded-full text-xs font-semibold bg-[#eff4ff] text-[#00685f] border border-teal-200">
                                {{ $user->roleNames() ?? 'موظف' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-700 text-xs">
                            {{ $user->department ?? 'الإدارة العامة' }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="text-xs text-slate-600 bg-slate-100 px-2 py-0.5 rounded font-mono">
                                {{ $user->permissions_count ?? 8 }} صلاحيات
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span
                                class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                <span>نشط</span>
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-xs font-mono text-slate-500">
                            {{ $user->last_login_at ?? 'منذ 15 دقيقة' }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a class="text-xs text-[#00685f] hover:underline font-semibold"
                                    href={{route('users.edit', $user->id)}}>تعديل</a>
                                <span class="text-slate-300">|</span>
                                <form method="POST" action={{route('users.destroy', $user)}}>
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-xs text-rose-600 hover:underline">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    @empty
                    <!-- Dynamic JS fallback for prototype view -->
                </tbody>
                @endforelse
            </table>
        </div>
    </div>
    <div class="mt-6">
        {{$users->links()}}
    </div>
</div>
@endsection
