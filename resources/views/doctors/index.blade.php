@extends('layouts.app')

@section('title', 'دليل الأطباء والاستشاريين')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold text-[#0b1c30]">الكادر الطبي والاستشاريون</h1>
                <span class="text-xs text-[#64748b]">عدد الأطباء: {{ $doctors_counts }}</span>
            </div>
            <p class="text-xs text-[#64748b] mt-1">
                سجل الأطباء المرخصين، التخصصات الدقيقة، العيادة التابعين لها، رسوم الكشف، ومواعيد المناوبات
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('doctors.create') }}"
                class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span>+ تسجيل طبيب جديد</span>
            </a>
            <a href="{{ route('clinics.index') }}"
                class="flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 rounded-lg transition">
                🏥 عرض العيادات
            </a>
        </div>
    </div>
    
    <!-- Filter & Search Bar with One-to-Many Clinic Filter -->
    <form action={{route(('doctors.index'))}} method="GET"
        class="bg-white p-4 rounded-xl border border-slate-200 flex flex-wrap items-center justify-between gap-3 shadow-xs">
        <div class="relative flex-1 max-w-md">
            <input type="text" id="doctorDirectorySearch"  name="search"
            value="{{ request('search') }}" placeholder="بحث باسم الطبيب، رقم ترخيص الهيئة، أو التخصص..."
                class="w-full pl-3 pr-9 py-2 text-xs bg-[#f8fafc] border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#00685f] text-right" />
            <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8" />
                <path d="m21 21-4.3-4.3" />
            </svg>
        </div>
        <!-- Clinic One-to-Many Filter -->
        <div class="flex items-center gap-3 text-xs">
            <span class="text-slate-500 font-medium">العيادة التابع لها :</span>
            <select id="doctorClinicFilter" name="clinic_id" 
                class="px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-1 focus:ring-[#00685f]">
                <option value="">كافة العيادات</option>
                <!-- Populated with Clinics for One-to-Many selection -->
                @foreach ($clinics ??[] as $clinic)

                <option value="{{$clinic['id']}}" 
                 @selected((string) request('clinic_id') === (string) $clinic['id'])
                >{{ $clinic['name'] }}</option>
                @endforeach

            </select>
             <select
        name="status"
        class="px-3 py-2 text-xs bg-white border border-slate-300 rounded-lg focus:ring-1 focus:ring-[#00685f]"
    >
        <option value="">كل الحالات</option>
        <option value="active" @selected(request('status') === 'active')>
            متاح ومناوب
        </option>
        <option value="holiday" @selected(request('status') === 'holiday')>
            في إجازة
        </option>
        <option value="inactive" @selected(request('status') === 'inactive')>
            غير نشط
        </option>
    </select>
        </div>
        <div class="flex items-center gap-3 text-xs">
             <button
                type="submit"
                class="px-4 py-2 text-xs font-bold text-white bg-[#00685f] rounded-lg hover:bg-[#00554e] transition"
            >
                بحث
            </button>
            <a
                href="{{ route('doctors.index') }}"
                class="px-3 py-2 text-xs border border-slate-300 rounded-lg hover:bg-slate-50 transition"
            >
                إعادة تعيين
            </a>
        </div>
    </form>

    <!-- Doctors Grid Container -->
    <div id="doctorsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <!-- Rendered by Native JS prototype & Blade loop in backend -->
        @forelse($doctors ?? [] as $doctor)
        <div
            class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between hover:shadow-md transition">
            <div>
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 rounded-full bg-teal-50 border border-teal-200 text-[#00685f] flex items-center justify-center font-bold text-base">
                            {{ mb_substr($doctor->fullname, 0, 1) }}
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-[#0b1c30]">{{ $doctor->fullname }}</h3>
                        </div>
                    </div>
                    @php
                        $status = match ($doctor->status) {
                            'active' => [
                                'text' => 'متاح ومناوب',
                                'class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                            ],
                            'holiday' => [
                                'text' => 'إجازة',
                                'class' => 'bg-amber-50 text-amber-800 border-amber-200',
                            ],
                            'inactive' => [
                                'text' => 'غير نشط',
                                'class' => 'bg-gray-100 text-gray-700 border-gray-200',
                            ],
                            default => [
                                'text' => 'غير محدد',
                                'class' => 'bg-gray-100 text-gray-700 border-gray-200',
                            ],
                        };
                    @endphp

                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ $status['class'] }}">
                        {{ $status['text'] }}
                    </span>
                </div>

                <!-- Linked Clinic Badge (BelongsTo Clinic) -->
                <div class="mb-3">
                    <a href="{{ route('clinics.show', $doctor) }}"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200/80 transition">
                        <span>🏥 العيادة:</span>
                        <span class="text-[#00685f] font-bold">{{ $doctor->clinic->name ??'لا يوجد' }}</span>
                    </a>
                </div>

                <div class="bg-[#f8fafc] rounded-lg p-3 border border-slate-100 space-y-2 mb-4 text-xs">
                    <div class="flex items-center justify-between text-slate-600">
                        <span>التخصص الدقيق:</span>
                        <span class="font-semibold text-slate-800">{{ $doctor->specialty }}</span>
                    </div>

                    <div class="flex items-center justify-between text-slate-600">
                        <span>أيام الكشف:</span>
                        <span class="text-slate-700">{{ $doctor->schedule_days }}</span>
                    </div>

                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                <a href="{{ route('doctors.show', $doctor) }}"
                    class="flex-1 py-1.5 text-center text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg transition">
                    الملف المهني ←
                </a>
                <a href="{{ route('doctors.edit', $doctor) }}"
                    class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                    تعديل
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 text-center text-slate-400">
            لا يوجد أطباء مسجلين
        </div>
        @endforelse
    </div>
    <div class="mt-6">
        {{$doctors->links()}}
    </div>
</div>
@endsection
