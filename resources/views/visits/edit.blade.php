@extends('layouts.app')

@section('title', 'تعديل بيانات الزيارة السريرية')

@section('content')
@php
    // The patient a visit is filed under is frozen once the visit leaves `created`.
    $patientFrozen = in_array($visit->visit_status, ['completed', 'cancelled'], true);
@endphp
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('visits.index') }}" class="hover:text-teal-700 transition">سجل الزيارات</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">تعديل الزيارة #{{ $visit->id }}</span>
            </nav>
            <h1 class="text-2xl font-bold text-[#0b1c30]">تعديل بيانات الزيارة السريرية </h1>
            <p class="text-xs text-[#64748b] mt-0.5">تحديث سبب الكشف، حالة الزيارة، أو إعادة تعيين الطبيب المعالج</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('visits.show', $visit ) }}" class="px-3.5 py-2 text-xs font-semibold text-teal-800 bg-teal-50 border border-teal-200 rounded-lg hover:bg-teal-100 transition">
                ملف الزيارة
            </a>
            <a href="{{ route('visits.index') }}" class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition shadow-xs">
                ← العودة للسجل
            </a>
        </div>
    </div>

    <!-- Edit Visit Form Card -->
    <div class="bg-white rounded-2xl border border-[#e2e8f0] shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-[#f8fafc] flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm">
                    ✏️
                </div>
                <div>
                    <h3 class="font-bold text-sm text-[#0b1c30]">تعديل حقول الزيارة </h3>
                    <p class="text-[11px] text-[#64748b]">معرف السجل: #{{ $visit->id }}</p>
                </div>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $visit->status_badge_classes }}">
                {{ $visit->status_label }}
            </span>
        </div>

        <form action="{{ route('visits.update', $visit ) }}" method="POST" class="p-6 space-y-6 text-xs">
            @csrf
            @method('PUT')

            <!-- Patient and Doctor Selection -->
            <div class="space-y-4">
                <h4 class="font-bold text-slate-800 text-xs border-b border-slate-100 pb-2 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#00685f]"></span>
                    الأطراف المعنية (المريض والطبيب)
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- patient_id -->
                    <div class="flex flex-col gap-2">
                        <label for="patient_id" class="font-bold text-slate-700 block mb-1.5">
                            المريض ورقم الملف (Patient):
                        </label>
                        <span name="patient_id" class="w-full p-2.5 bg-white border font-semibold border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800" >{{$patient?->fullname}}</span>

                    </div>

                    <!-- doctor_id -->
                    <div class="flex flex-col gap-2">
                        <label for="doctor_id" class="font-bold text-slate-700 block mb-1.5">
                            الطبيب المعالج والعيادة (Doctor):
                        </label>
                        <select name="doctor_id" id="doctor_id" class="w-full p-2.5 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800">
                            <option value="">-- بدون تحديد طبيب  --</option>
                            @if(isset($doctors))
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ (old('doctor_id', $visit->doctor_id ?? '') == $doctor->id) ? 'selected' : '' }}>
                                        {{ $doctor->fullname }} - {{ $doctor->clinic?->name ?: 'بدون عيادة' }}
                                    </option>
                                @endforeach
                            @endif
                        </select>

                    </div>
                </div>
            </div>

            <!-- Date, Status and Created By -->
            <div class="space-y-4 pt-2">
                <h4 class="font-bold text-slate-800 text-xs border-b border-slate-100 pb-2 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                    التاريخ وحالة الزيارة
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- date -->
                    <div>
                        <label for="date" class="font-bold text-slate-700 block mb-1.5">
                            تاريخ الزيارة (Date): <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="date"
                            name="date"
                            id="date"
                            value="{{ old('date', $visit->date->format('Y-m-d')) }}"
                            required
                            class="w-full p-2.5 bg-white border border-slate-300 rounded-lg font-mono focus:outline-none focus:ring-1 focus:ring-[#00685f]"
                        />

                    </div>


                </div>

                <!-- created_by -->

            </div>

            <!-- Reason: max 500 characters -->
            <div class="space-y-2 pt-2">
                <div class="flex items-center justify-between">
                    <label for="reason" class="font-bold text-slate-700 block">
                        سبب الزيارة والشكوى الطبية (Reason): <span class="text-red-500">*</span>
                    </label>
                    <span class="text-[11px] text-slate-400 font-mono" id="editReasonCounter">500 حرف كحد أقصى</span>
                </div>
                <textarea
                    name="reason"
                    id="reason"
                    rows="4"
                    maxlength="500"
                    required
                    class="w-full p-3 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800 leading-relaxed"
                    oninput="document.getElementById('editReasonCounter').textContent = (500 - this.value.length) + ' حرف متبقٍ';"
                >{{ old('reason', $visit->reason ?? '') }}</textarea>

            </div>

            <!-- Action Buttons -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <button type="button" onclick="document.getElementById('deleteVisitModal-{{$visit->id}}').classList.remove('hidden')" class="px-4 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 rounded-lg transition">
                        حذف الزيارة...
                    </button>

                <div class="flex items-center gap-3">
                    <a href="{{ route('visits.index') }}" class="px-5 py-2.5 text-slate-600 hover:bg-slate-100 rounded-lg transition font-medium">
                        إلغاء
                    </a>
                    <button type="submit" class="px-6 py-2.5 text-white bg-[#00685f] hover:bg-[#005049] rounded-lg font-bold shadow-sm transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>حفظ التعديلات</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
    <x-delete-visit-modal :visit="$visit" />
</div>
@endsection
