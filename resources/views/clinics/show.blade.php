@extends('layouts.app')

@section('title', 'ملف العيادة التخصصية والأطباء التابعين')

@section('content')
    <div class="space-y-6">

        <!-- Breadcrumb & Back Bar -->
        <div class="flex items-center justify-between">
            <nav class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('clinics.index') }}" class="hover:text-teal-700">دليل العيادات</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold" id="clinicBreadcrumbName">{{ $clinic->name }}</span>
            </nav>
            <div class="flex items-center gap-2">
                <a href="{{ route('clinics.index') }}"
                    class="px-3 py-1.5 text-xs font-semibold text-slate-600 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition">
                    ← العودة لدليل العيادات
                </a>
                @can('update', $clinic)
                    <a href="{{ route('clinics.edit', $clinic->id) }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-300 rounded-lg transition">
                        <x-icon name="pencil" /> <span>تعديل بيانات العيادة</span>
                    </a>
                @endcan
            </div>
        </div>

        <!-- Clinic Hero Card -->
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-xs">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-slate-100">
                <div class="flex items-center gap-4">
                    <div
                        class="w-16 h-16 rounded-2xl bg-teal-50 border border-teal-200 text-[#00685f] flex items-center justify-center shadow-xs">
                        <x-icon name="building" class="w-8 h-8" />
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-xl font-bold text-[#0b1c30]" id="clinicShowName">{{ $clinic->name }}</h1>
                            @php
                                $statusConfig = match ($clinic->status) {
                                    'active' => ['label' => 'نشطة', 'class' => 'bg-green-100 text-green-700'],
                                    'maintenance' => [
                                        'label' => 'تحت الصيانة',
                                        'class' => 'bg-amber-100 text-amber-700',
                                    ],
                                    'inactive' => ['label' => 'غير نشطة', 'class' => 'bg-red-100 text-red-700'],
                                    default => ['label' => 'غير محددة', 'class' => 'bg-gray-100 text-gray-700'],
                                };
                            @endphp

                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusConfig['class'] }}">
                                {{ $statusConfig['label'] }}
                            </span>
                        </div>

                    </div>
                </div>

                <!-- One-to-Many Summary Counter Badge -->
                <div class="bg-teal-50/70 p-4 rounded-xl border border-teal-200/80 flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-[11px] font-bold text-teal-900 uppercase">إجمالي الأطباء بالعيادة</p>
                        <p class="text-2xl font-black text-teal-800 mt-0.5" id="clinicDoctorsCountBadge">
                            {{ $doctors->total() }} <span
                                class="text-xs font-medium text-teal-700">أطباء</span>
                        </p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center">
                        <x-icon name="users" class="w-5 h-5" />
                    </div>
                </div>
            </div>
            @if ($clinic->description)
                <div class="p-3 bg-[#f8fafc] rounded-lg border border-slate-100">
                    <p class="font-semibold text-emerald-700 text-xs mt-0.5">{{ $clinic->description }}</p>
                </div>
            @endif
        </div>

        <!-- ONE-TO-MANY RELATIONSHIP SECTION: CLINIC DOCTORS -->
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-bold text-[#0b1c30]">الأطباء والاستشاريون التابعون لهذه العيادة</h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        كافة الأطباء المسجلين والمرتبطين وظيفياً بهذه العيادة وجداول كشوفاتهم
                    </p>
                </div>

            </div>

            <!-- Doctors List Table / Cards -->
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 border-b border-slate-200 font-bold">
                            <th class="py-3 px-3">الطبيب المعالج</th>
                            <th class="py-3 px-3"> التخصص</th>
                            <th class="py-3 px-3">أيام الدوام</th>
                            <th class="py-3 px-3">الحالة</th>
                            <th class="py-3 px-3 text-left">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="clinicDoctorsTableBody" class="divide-y divide-slate-100">
                        @forelse($doctors ?? [] as $doctor)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-3">
                                    <div class="flex items-center gap-2.5">
                                        <div
                                            class="w-8 h-8 rounded-full bg-teal-100 text-teal-800 flex items-center justify-center font-bold text-xs">
                                            {{ mb_substr($doctor->fullname, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800">{{ $doctor->fullname }}</p>
                                            <p class="text-[11px] text-slate-400 font-mono">{{ $doctor->phone }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-3">
                                    <p class="text-[11px] text-slate-500">{{ $doctor->specialty }}</p>
                                </td>
                                <td class="py-3 px-3 text-slate-600">{{ $doctor->schedule_days }}</td>
                                <td class="py-3 px-3">

                                    @php
                                        $statusStyles = match ($doctor->status) {
                                            'active' => [
                                                'label' => 'متاح ومناوب',
                                                'class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                            ],
                                            'holiday' => [
                                                'label' => 'في إجازة',
                                                'class' => 'bg-amber-50 text-amber-800 border-amber-200',
                                            ],
                                            'inactive' => [
                                                'label' => 'غير نشط',
                                                'class' => 'bg-slate-100 text-slate-700 border-slate-200',
                                            ],
                                            default => [
                                                'label' => 'غير معروف',
                                                'class' => 'bg-gray-100 text-gray-600 border-gray-200',
                                            ],
                                        };
                                    @endphp

                                    <span
                                        class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ $statusStyles['class'] }}">
                                        {{ $statusStyles['label'] }}
                                    </span>

                                </td>
                                <td class="py-3 px-3 text-left">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @can('view', $doctor)
                                            <a href="{{ route('doctors.show', $doctor->id) }}"
                                                class="px-2.5 py-1 text-[11px] font-semibold text-teal-800 bg-teal-50 hover:bg-teal-100 rounded transition">
                                                الملف المهني
                                            </a>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="clinicDoctorsEmptyRow">
                                <td colspan="5" class="py-8 text-center text-slate-400">
                                    لا يوجد أطباء معينون لهذه العيادة حالياً.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div>
            {{ $doctors->links() }}
        </div>
    </div>
@endsection
