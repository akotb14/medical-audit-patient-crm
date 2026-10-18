@extends('layouts.app')

@section('title', 'الملف المهني للطبيب وتفاصيل العيادة')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Back Bar -->
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

 <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 border-b border-slate-200 font-bold">
                        <th class="py-2.5 px-3"># المعرف</th>
                        <th class="py-2.5 px-3">تاريخ الزيارة (Date)</th>
                        <th class="py-2.5 px-3">المريض ورقم الملف (Patient MRN)</th>
                        <th class="py-2.5 px-3 min-w-[200px]">سبب الزيارة (Reason - 500 حرف)</th>
                        <th class="py-2.5 px-3">حالة الزيارة (visit_status)</th>
                        <th class="py-2.5 px-3">المسجل</th>
                        <th class="py-2.5 px-3 text-center">تعديل وإجراءات الزيارة</th>
                    </tr>
                </thead>
                <tbody id="doctorVisitsTableBody" class="divide-y divide-slate-100">
                    @foreach($doctor->visits ?? [] as $visit)
                        <tr class="hover:bg-slate-50/80 transition" id="doctorVisitRow-{{ $visit->id }}">
                            <td class="py-3 px-3 font-mono font-bold text-slate-500">
                                #{{ $visit->id }}
                            </td>
                            <td class="py-3 px-3 font-mono font-bold text-slate-800">
                                {{ is_string($visit->date) ? $visit->date : ($visit->date ? $visit->date->format('Y-m-d') : '') }}
                            </td>
                            <td class="py-3 px-3">
                                @if($visit->patient)
                                    <a href="{{ route('patients.show', $visit->patient_id) }}" class="font-bold text-[#0b1c30] hover:text-teal-700 block">
                                        {{ $visit->patient->fullname }}
                                    </a>
                                    <span class="font-mono text-[10px] text-teal-800 font-bold bg-teal-50 px-1.5 py-0.2 rounded border border-teal-200">
                                        {{ $visit->patient->mrn }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">غير محدد </span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                <p class="text-slate-700 font-medium line-clamp-2 max-w-sm" title="{{ $visit->reason }}">
                                    {{ $visit->reason }}
                                </p>
                            </td>
                            <td class="py-3 px-3">
                                @if($visit->visit_status === 'completed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        <span>مكتملة</span>
                                    </span>
                                @elseif($visit->visit_status === 'cancelled')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                        <span>ملغاة</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-800 border border-blue-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                        <span>تم الإنشاء</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-slate-500 text-[11px]">
                                {{ $visit->creator->name }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Edit Button (Direct Modal in Blade) -->
                                    <a 
                                        type="button" 
                                        href="{{ route('visits.edit', $visit) }}"
                                        class="px-2.5 py-1 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-lg transition shadow-2xs flex items-center gap-1"
                                        title="تعديل بيانات الزيارة"
                                    >
                                        <span>✏️ تعديل</span>
                                    </a>

                                    <!-- Quick Status Update Form in Blade -->
                                    @if($visit->visit_status !== 'completed')
                                        <form method="POST" action="{{ route('visits.status', $visit->id) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="visit_status" value="completed">
                                            <button 
                                                type="submit" 
                                                class="px-2 py-1 text-[11px] font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg transition"
                                                title="إتمام الزيارة الطبية"
                                            >
                                                ✓ إتمام
                                            </button>
                                        </form>
                                    @endif

                                    @if($visit->visit_status !== 'cancelled')
                                        <form method="POST" action="{{ route('visits.status', $visit) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="visit_status" value="cancelled">
                                            <button 
                                                type="submit" 
                                                class="px-2 py-1 text-[11px] font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg transition"
                                                title="إلغاء الزيارة"
                                            >
                                                ✕ إلغاء
                                            </button>
                                        </form>
                                    @endif

                                    <a 
                                        href="{{ route('visits.show', $visit->id) }}" 
                                        class="px-2 py-1 text-xs text-slate-500 hover:text-slate-800 rounded transition"
                                        title="عرض التفاصيل الكاملة"
                                    >
                                        👁️
                                    </a>
                                </div>
                            </td>
                        </tr>
                   
                    @endforeach
                </tbody>
            </table>
        </div>
</div>
@endsection
