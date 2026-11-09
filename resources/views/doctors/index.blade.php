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
                @can('create', App\Models\Doctor::class)
                    <a href="{{ route('doctors.create') }}"
                        class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        <span>+ تسجيل طبيب جديد</span>
                    </a>
                @endcan
                @can('viewAny', App\Models\Clinic::class)
                    <a href="{{ route('clinics.index') }}"
                        class="flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 rounded-lg transition">
                        عرض العيادات
                    </a>
                @endcan
            </div>
        </div>

        <!-- Filter & Search Bar with One-to-Many Clinic Filter -->
        <form action="{{ route('doctors.index') }}" method="GET"
            class="bg-white p-4 rounded-xl border border-slate-200 flex flex-wrap items-center justify-between gap-3 shadow-xs">
            <div class="relative flex-1 max-w-md">
                <input type="text" id="doctorDirectorySearch" name="search" value="{{ request('search') }}"
                    placeholder="بحث باسم الطبيب، رقم ترخيص الهيئة، أو التخصص..."
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
                    @foreach ($clinics ?? [] as $clinic)
                        <option value="{{ $clinic->id }}" @selected((string) request('clinic_id') === (string) $clinic->id)>{{ $clinic->name }}</option>
                    @endforeach

                </select>
                <select name="status"
                    class="px-3 py-2 text-xs bg-white border border-slate-300 rounded-lg focus:ring-1 focus:ring-[#00685f]">
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
                <button type="submit"
                    class="px-4 py-2 text-xs font-bold text-white bg-[#00685f] rounded-lg hover:bg-[#00554e] transition">
                    بحث
                </button>
                <a href="{{ route('doctors.index') }}"
                    class="px-3 py-2 text-xs border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                    إعادة تعيين
                </a>
            </div>
        </form>

        <!-- Doctors Table (High-Density Clinical Table - Fast Eye-Scanning) -->
        <div id="doctorsTableContainer" class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs border-collapse">
                    <thead class="bg-slate-50/90 text-slate-700 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4 font-bold text-slate-800 min-w-[190px]">الطبيب</th>
                            <th class="py-3 px-3 font-bold text-slate-800 min-w-[130px]">التخصص الدقيق</th>
                            <th class="py-3 px-3 font-bold text-slate-800 min-w-[160px]">العيادة التابعة</th>
                            <th class="py-3 px-3 font-bold text-slate-800 min-w-[150px]">أيام الكشف</th>
                            <th class="py-3 px-3 font-bold text-slate-800 min-w-[110px]">الجوال</th>
                            <th class="py-3 px-3 font-bold text-slate-800 text-center">الحالة</th>
                            <th class="sticky left-0 z-20 bg-slate-50/90 py-3 px-4 font-bold text-slate-800 text-center min-w-[150px]">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($doctors ?? [] as $doctor)
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
                            <tr class="hover:bg-teal-50/40 transition group">
                                <!-- Doctor Name and Contact -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div
                                            class="w-9 h-9 rounded-lg bg-teal-50 border border-teal-200 text-[#00685f] flex items-center justify-center font-bold text-sm shrink-0">
                                            {{ mb_substr($doctor->fullname, 0, 1) }}
                                        </div>
                                        <div class="min-w-0">
                                            @can('view', $doctor)
                                                <a href="{{ route('doctors.show', $doctor) }}"
                                                    class="font-bold text-slate-900 group-hover:text-[#00685f] hover:underline text-[13px] block transition">
                                                    {{ $doctor->fullname }}
                                                </a>
                                            @else
                                                <span class="font-bold text-slate-900 text-[13px] block">
                                                    {{ $doctor->fullname }}
                                                </span>
                                            @endcan
                                            @if ($doctor->email)
                                                <span class="text-[10px] text-slate-400 block truncate">{{ $doctor->email }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Specialty -->
                                <td class="py-3.5 px-3">
                                    <span class="font-semibold text-slate-800">{{ $doctor->specialty ?: '—' }}</span>
                                </td>

                                <!-- Linked Clinic Badge (BelongsTo Clinic) -->
                                <td class="py-3.5 px-3">
                                    @if ($doctor->clinic)
                                        <a href="{{ route('clinics.show', $doctor->clinic) }}"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200/80 transition">
                                            <span class="text-[#00685f] font-bold">{{ $doctor->clinic->name }}</span>
                                        </a>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 text-slate-500 border border-slate-200/80">
                                            <span class="font-bold">لا يوجد</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- Consultation Days -->
                                <td class="py-3.5 px-3 text-slate-700">
                                    {{ $doctor->schedule_days ?: '—' }}
                                </td>

                                <!-- Phone -->
                                <td class="py-3.5 px-3 font-mono text-[11px] text-slate-600 whitespace-nowrap">
                                    {{ $doctor->phone ?: '—' }}
                                </td>

                                <!-- Status -->
                                <td class="py-3.5 px-3 text-center whitespace-nowrap">
                                    <span
                                        class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold border {{ $status['class'] }}">
                                        {{ $status['text'] }}
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td
                                        class="sticky left-0 z-10 bg-white py-3.5 px-4 text-center whitespace-nowrap shadow-[6px_0_8px_-6px_rgba(15,23,42,0.18)] group-hover:bg-teal-50/40">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @can('view', $doctor)
                                            <a href="{{ route('doctors.show', $doctor) }}"
                                                class="px-2.5 py-1 text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg transition shadow-2xs"
                                                title="عرض الملف المهني">
                                                الملف
                                            </a>
                                        @endcan
                                        @can('update', $doctor)
                                            <a href="{{ route('doctors.edit', $doctor) }}"
                                                class="px-2.5 py-1 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-lg transition"
                                                title="تعديل بيانات الطبيب">
                                                تعديل
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    لا يوجد أطباء مسجلين
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-6">
            {{ $doctors->links() }}
        </div>
    </div>
@endsection
