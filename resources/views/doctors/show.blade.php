@extends('layouts.app')

@section('title', 'الملف المهني للطبيب وتفاصيل العيادة')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Back Bar -->
    <x-alerts />
    <div class="flex items-center justify-between">
        <nav class="flex items-center gap-2 text-xs text-slate-500">
            <a href="{{ route('doctors.index') }}" class="hover:text-teal-700">دليل الأطباء</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold" id="doctorBreadcrumbName">{{ $doctor->fullname }}</span>
        </nav>
        <div class="flex items-center gap-2">
            <a href="{{ route('doctors.index') }}"
                class="px-3 py-1.5 text-xs font-semibold text-slate-600 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition">
                ← العودة لدليل الأطباء
            </a>
            <a href="{{ route('doctors.edit', $doctor ) }}"
                class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-300 rounded-lg transition">
                ✏️ تعديل بيانات الطبيب
            </a>
        </div>
    </div>

    <!-- Doctor Hero Card -->
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-slate-100">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-teal-50 border border-teal-200 text-[#00685f] flex items-center justify-center font-bold text-2xl shadow-xs"
                    id="doctorShowAvatar">
                    {{ mb_substr($doctor->fullname ?? 'د', 0, 1) }}
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-xl font-bold text-[#0b1c30]" id="doctorShowName">{{ $doctor->fullname  }}</h1>
                      @php
                            $status = $doctor->status ?? 'active';

                            $statusConfig = match ($status) {
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
                                    'class' => 'bg-gray-100 text-gray-700 border-gray-200',
                                ],
                                default => [
                                    'label' => 'حالة غير معروفة',
                                    'class' => 'bg-gray-100 text-gray-700 border-gray-200',
                                ],
                            };
                        @endphp

                        <span
                            class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $statusConfig['class'] }}"
                            id="doctorShowStatus"
                        >
                            {{ $statusConfig['label'] }}
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 mt-1.5">
                        <span class="text-slate-700 font-medium" id="doctorShowSpecialty">{{ $doctor->specialty}}</span>

                    </div>
                </div>
            </div>

            <!-- Fee & Quick Booking Action -->
            <div class="flex items-center gap-4">
                <button type="button" onclick="window.clinicalApp.openNewAppointmentModal()"
                    class="px-4 py-2 text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg shadow-sm transition">
                    + حجز موعد مع الطبيب
                </button>
            </div>
        </div>

        <!-- LINKED CLINIC (BELONGSTO RELATIONSHIP) CARD -->
        <div
            class="mt-6 bg-gradient-to-r from-teal-50/60 to-slate-50 p-4.5 rounded-xl border border-teal-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div
                    class="w-12 h-12 rounded-xl bg-white border border-teal-300 text-teal-700 flex items-center justify-center text-xl shadow-2xs">
                    🏥
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-bold text-teal-900 uppercase">العيادة التابع لها :</span>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800 mt-0.5" id="doctorClinicName">{{$doctor->clinic?->name ?? 'لا يوجد عياده تابع ليه الان'}}</h3>

                </div>
            </div>
            <a href="{{route('clinics.show', $doctor->clinic?->id)}}" id="doctorClinicLinkBtn"
                class="px-4 py-2 text-xs font-bold text-teal-900 bg-white hover:bg-teal-50 border border-teal-300 rounded-lg shadow-2xs transition flex items-center gap-1.5 justify-center">
                <span>معاينة العيادة وباقي أطبائها</span>
                <span>←</span>
            </a>
        </div>

        <!-- Contact & Schedule Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-5 text-xs">
            <div class="p-3 bg-[#f8fafc] rounded-lg border border-slate-100">
                <p class="text-slate-500 text-[11px]">الهاتف والتحويلة</p>
                <p class="font-mono font-bold text-slate-800 text-xs mt-0.5" id="doctorShowPhone">{{ $doctor->phone ??
                   ' لا يوجد '}}</p>
            </div>
            <div class="p-3 bg-[#f8fafc] rounded-lg border border-slate-100">
                <p class="text-slate-500 text-[11px]">البريد الإلكتروني المهني</p>
                <p class="font-mono font-semibold text-slate-700 text-xs mt-0.5" id="doctorShowEmail">{{ $doctor->email
                    ?? 'لا يوجد '}}</p>
            </div>
            <div class="p-3 bg-[#f8fafc] rounded-lg border border-slate-100">
                <p class="text-slate-500 text-[11px]">جدول الكشف الأسبوعي</p>
                <p class="font-semibold text-slate-800 text-xs mt-0.5" id="doctorShowSchedule">{{ $doctor->schedule_days
                    ?? 'لا يوجد مواعيد الان '}}</p>
            </div>
        </div>
    </div>

    <!-- UPCOMING APPOINTMENTS WITH THIS DOCTOR -->
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h2 class="text-base font-bold text-[#0b1c30]">قائمة المواعيد والزيارات السريرية المجدولة</h2>
                <p class="text-xs text-slate-500 mt-0.5">مرضى هذا الطبيب في عيادته التخصصية</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 border-b border-slate-200 font-bold">
                        <th class="py-2.5 px-3">الوقت</th>
                        <th class="py-2.5 px-3">المريض (MRN)</th>
                        <th class="py-2.5 px-3">نوع الزيارة</th>
                        <th class="py-2.5 px-3">الحالة السريرية</th>
                        <th class="py-2.5 px-3">الرسوم والدفع</th>
                    </tr>
                </thead>
                <tbody id="doctorAppointmentsTableBody" class="divide-y divide-slate-100">
                    <!-- Populated dynamically via JS prototype and Blade loop in backend -->
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
