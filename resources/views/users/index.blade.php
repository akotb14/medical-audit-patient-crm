@extends('layouts.app')

@section('title', 'إدارة المستخدمين والكادر الطبي')

@section('content')
<div class="space-y-6">
    <!-- Header with Operational Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#0b1c30]">إدارة المستخدمين والكادر الطبي</h1>
            <p class="text-xs text-[#64748b] mt-1">
                التحكم في حسابات الكادر الطبي والإداري
            </p>
        </div>
        <div class="flex items-center gap-3">
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
        <x-kpi-card title="إجمالي المستخدمين" :value="$totalUsers" subtitle="كادر العيادات المسجل" trend="نشط"
            trendType="positive" />
        <x-kpi-card title="فريق الاستقبال والفرز" :value="$receptionCountRole ?? 0" subtitle="مناوبات الصباح والمساء"
            trendType="neutral" />
    </div>

    <!-- Filament-Style Users Table Container -->
    <div class="bg-white rounded-xl border border-[#e2e8f0] shadow-xs overflow-hidden">
        <!-- Table Grid -->
        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead class="bg-[#f8fafc] text-[#64748b] text-xs font-semibold uppercase border-b border-[#e2e8f0]">
                    <tr>
                        <th class="py-3.5 px-4">المستخدم واسم الدخول</th>
                        <th class="py-3.5 px-4 text-center">الدور الوظيفي </th>
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
                                    <div class="text-xs text-[#64748b] font-mono">{{ $user->username }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span
                                class="px-2.5 py-1 rounded-full text-xs font-semibold bg-[#eff4ff] text-[#00685f] border border-teal-200">
                                {{ $user->roles->pluck('name')->implode(',') ?: 'بدون دور' }}
                            </span>
                        </td>


                        <td class="py-3.5 px-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a class="text-xs font-semibold text-[#00685f] hover:underline"
                                    href="{{ route('users.edit', $user) }}">تعديل</a>
                                <span class="text-slate-300">|</span>
                                <form method="POST" action="{{ route('users.destroy', $user) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-xs text-rose-600 hover:underline">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    @empty
                    <tr>
                        <td colspan="4" class="py-8 px-4 text-center text-xs text-slate-500">
                            لا توجد حسابات مستخدمين مسجلة بعد.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-6">
        {{$users->links()}}
    </div>
</div>
@endsection
