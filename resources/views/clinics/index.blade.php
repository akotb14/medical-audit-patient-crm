@extends('layouts.app')

@section('title', 'دليل العيادات والأقسام الطبية')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-bold text-[#0b1c30]">دليل العيادات والأقسام التخصصية</h1>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-teal-50 text-teal-800 border border-teal-200">
                    {{ $clinics->count() ??  0 }} عيادة معتمدة
                </span>
            </div>
            <p class="text-xs text-[#64748b] mt-1">
                إدارة أجنحة وعيادات المجمع الطبي، توزيع الأطباء والاستشاريين، أرقام الغرف، والتحويلات الهاتفية الداخلية
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <a
                href="{{ route('clinics.create') }}"
                class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg shadow-sm transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ إضافة عيادة جديدة</span>
            </a>
            <a
                href="{{ route('doctors.create') }}"
                class="flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-teal-800 bg-teal-50 hover:bg-teal-100 border border-teal-200 rounded-lg transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>+ تعيين طبيب</span>
            </a>
        </div>
    </div>



    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-500">إجمالي العيادات المسجلة</p>
                <p class="text-xl font-bold text-[#0b1c30] mt-0.5">{{ $clinics->count() ?? 0 }} عيادات</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-teal-50 text-[#00685f] flex items-center justify-center font-bold text-lg">
                🏥
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-500">الأطباء الموزعون على العيادات</p>
                <p class="text-xl font-bold text-teal-700 mt-0.5">{{ $doctors_counts ?? 0}} أطباء متاحين</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-lg">
                👨‍⚕️
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-500">متوسط الأطباء لكل عيادة</p>
                <p class="text-xl font-bold text-blue-700 mt-0.5">{{ number_format($doctors_counts / $clinics->count(), 1) ?? 0}} طبيب / عيادة</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center font-bold text-lg">
                📊
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-500">جاهزية التشغيل السريري</p>
                <p class="text-xl font-bold text-emerald-700 mt-0.5">{{ number_format($clinics->where('status', 'active')->count() /  $clinics->count() *100 ) ??0}}% جاهز للعمل</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-slate-50 text-slate-700 flex items-center justify-center font-bold text-lg">
                ✅
            </div>
        </div>
    </div>

    <!-- VIEW 1: HIGH-DENSITY CLINICAL TABLE (Fast Eye-Scanning) -->
    <div id="clinicsTableContainer" class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50/90 text-slate-600 border-b border-slate-200">
                        <th class="py-3 px-4 font-bold">الرمز والعيادة</th>
                        <th class="py-3 px-3 font-bold w-[190px]">وصف</th>
                        <th class="py-3 px-3 font-bold">الأطباء التابعون </th>
                        <th class="py-3 px-3 font-bold text-center">الحالة</th>
                        <th class="py-3 px-4 font-bold text-center">الإجراءات السريعة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($clinics ?? [] as $clinic)
                        <tr class="hover:bg-slate-50/80 transition group">
                            <!-- Code & Clinic Name -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-teal-50 border border-teal-200 text-[#00685f] flex items-center justify-center font-bold text-sm shrink-0">
                                        🏥
                                    </div>
                                    <div>
                                        <a href="{{ route('clinics.show', $clinic->id) }}" class="font-bold text-slate-900 group-hover:text-[#00685f] transition text-[13px] block">
                                            {{ $clinic->name }}
                                        </a>
                                    </div>
                                </div>
                            </td>

                            <!-- Specialty -->
                            <td class="py-3.5 px-3">
                                <span class="font-semibold text-slate-800">{{ $clinic->description }}</span>
                            </td>

                            <!-- Assigned Doctors -->
                            <td class="py-3.5 px-3">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ ($doctors_counts_by_clinic[$clinic->id] ?? 0) > 0 ? 'bg-teal-50 text-teal-800 border border-teal-200' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $doctors_counts_by_clinic[$clinic->id] ?? 0 }} أطباء
                                    </span>
                                    @if(isset($clinic->doctors) && count($clinic->doctors) > 0)
                                        <span class="text-[11px] text-slate-500 hidden xl:inline">
                                            ({{ Str::limit($clinic->doctors->pluck('fullname')->join('، '), 25) }})
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-3 text-center">
                                @if($clinic->status  === 'active')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                        <span>نشطة ومجهزة</span>
                                    </span>
                                @elseif($clinic->status === 'maintenance')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                        <span>تحت الصيانة</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-800 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                        <span>غير مفعلة</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a
                                        href="{{ route('clinics.show', $clinic) }}"
                                        class="px-2.5 py-1 text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg transition shadow-2xs flex items-center gap-1"
                                        title="عرض ملف العيادة والأطباء التابعين"
                                    >
                                        <span>الملف</span>
                                        <span class="text-[10px]">➜</span>
                                    </a>
                                    <a
                                        href="{{ route('clinics.edit', $clinic) }}"
                                        class="p-1.5 text-xs text-slate-600 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg transition"
                                        title="تعديل بيانات العيادة"
                                    >
                                        ✏️
                                    </a>
                                    <button
                                        type="button"
                                        onclick="document.getElementById('deleteClinicModal-{{ $clinic->id }}').classList.remove('hidden')"
                                        class="p-1.5 text-xs text-rose-600 hover:bg-rose-50 border border-rose-200 rounded-lg transition"
                                        title="حذف العيادة"
                                    >
                                        🗑️
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <x-delete-clinic-modal :clinic="$clinic" />
                      
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>



    <!-- Pagination -->
    @if(method_exists($clinics, 'links'))
        <div class="mt-4">
            {{ $clinics->links() }}
        </div>
    @endif
</div>



@endsection
