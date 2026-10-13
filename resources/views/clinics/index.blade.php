@extends('layouts.app')

@section('title', 'دليل العيادات والأقسام الطبية')

@section('content')
<div class="space-y-6">
    <x-alerts />
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold text-[#0b1c30]">العيادات والأقسام التخصصية</h1>

            </div>
            <p class="text-xs text-[#64748b] mt-1">
                إدارة أجنحة وعيادات المجمع الطبي، توزيع الأطباء والاستشاريين، وأرقام الغرف والتحويلات الهاتفية
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('clinics.create') }}"
                class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>+ إضافة عيادة جديدة</span>
            </a>
            {{-- <a href="{{ route('doctors.create') }}"
                class="flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-teal-800 bg-teal-50 hover:bg-teal-100 border border-teal-200 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span>+ تعيين طبيب لعيادة</span>
            </a> --}}
        </div>
    </div>

    <!-- Quick Stats Strip -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-500">إجمالي العيادات النشطة</p>
                <p class="text-xl font-bold text-[#0b1c30] mt-0.5" id="totalClinicsStat">{{$clinics->count()??0}} عيادات
                </p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-teal-50 text-[#00685f] flex items-center justify-center font-bold">
                🏥
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-500">الأطباء الموزعون على العيادات</p>
                <p class="text-xl font-bold text-teal-700 mt-0.5" id="totalDoctorsAssignedStat">{{$doctors_counts}} أطباء متاحين</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold">
                👨‍⚕️
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-500">متوسط الأطباء لكل عيادة</p>
                <p class="text-xl font-bold text-blue-700 mt-0.5">{{number_format($doctors_counts/$clinics->count())}} طبيب / عيادة</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
                📊
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-500">جاهزية العيادات</p>
                <p class="text-xl font-bold text-emerald-700 mt-0.5">{{number_format($clinics->where("status","active")->count()/$clinics->count()*100)}}% جاهز</p>تشغيل</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-slate-50 text-slate-700 flex items-center justify-center font-bold">
                ✅
            </div>
        </div>
    </div>

    

    <!-- Clinics Grid Container -->
    <div id="clinicsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <!-- Rendered by Native JS prototype & Blade loop in backend -->
        @forelse($clinics ?? [] as $clinic)
        <div
            class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between hover:shadow-md transition">
            <div>
                <div class="flex items-start justify-between gap-2 mb-3">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 rounded-xl bg-teal-50 border border-teal-200 text-[#00685f] flex items-center justify-center font-bold text-lg">
                            🏥
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-[#0b1c30]">{{ $clinic->name }}</h3>

                        </div>
                    </div>
                   
                   @php
                    $statusConfig = match ($clinic->status) {
                    'active' => ['label' => 'نشطة', 'class' => 'bg-green-100 text-green-700'],
                    'maintenance' => ['label' => 'تحت الصيانة', 'class' => 'bg-amber-100 text-amber-700'],
                    'inactive' => ['label' => 'غير نشطة', 'class' => 'bg-red-100 text-red-700'],
                    default => ['label' => 'غير محددة', 'class' => 'bg-gray-100 text-gray-700'],
                    };
                    @endphp
                    <div  class="flex flex-col gap-1 items-end ">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-teal-100 text-teal-800">
                            {{ ($doctors_counts_by_clinic[$clinic->id]) ?? 0 }} أطباء
                        </span>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusConfig['class'] }}">
                            {{ $statusConfig['label'] }}
                        </span>
                    </div>
                </div>

              
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                <a href="{{ route('clinics.show', $clinic->id ) }}"
                    class="flex-1 py-1.5 text-center text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg transition">
                    عرض العيادة والأطباء ←
                </a>
                <a href="{{ route('clinics.edit', $clinic->id ) }}"
                    class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                    تعديل
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 text-center text-slate-400">
            لا توجد عيادات مسجلة حالياً
        </div>
        @endforelse
    </div>
    <div class="mt-6">
        {{$clinics->links()}}
    </div>
</div>
@endsection