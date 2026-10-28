@extends('layouts.app')

@section('title', 'تسجيل زيارة سريرية جديدة')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Breadcrumb & Header -->
        <div class="flex items-center justify-between">
            <div>
                <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                    <a href="{{ route('visits.index') }}" class="hover:text-teal-700 transition">سجل الزيارات</a>
                    <span>/</span>
                    <span class="text-slate-800 font-semibold">تسجيل زيارة جديدة</span>
                </nav>
                <h1 class="text-2xl font-bold text-[#0b1c30]">تسجيل زيارة طبية جديدة </h1>
                <p class="text-xs text-[#64748b] mt-0.5">توثيق مراجعة العيادة وحالة الزيارة وفق معايير الملف الطبي الموحد</p>
            </div>
            <a href="{{ route('visits.index') }}"
                class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition shadow-xs">
                ← العودة للسجل
            </a>
        </div>

        <!-- Create Visit Form Card -->
        <div class="bg-white rounded-2xl border border-[#e2e8f0] shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-[#f8fafc] flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-8 h-8 rounded-lg bg-teal-100 text-[#00685f] flex items-center justify-center font-bold text-sm">
                        🩺
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-[#0b1c30]">بيانات نموذج الزيارة </h3>
                        <p class="text-[11px] text-[#64748b]">الحقول المؤشرة بعلامة (<span class="text-red-500">*</span>)
                            إلزامية</p>
                    </div>
                </div>

            </div>

            <form action="{{ route('visits.store') }}" method="POST" class="p-6 space-y-6 text-xs">
                @csrf

                <!-- Patient and Doctor Selection (Foreign Keys with nullOnDelete) -->
                <div class="space-y-4">
                    <h4 class="font-bold text-slate-800 text-xs border-b border-slate-100 pb-2 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#00685f]"></span>
                        الأطراف المعنية (المريض والطبيب المعالج)
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- patient_id: nullable foreignId constrained to patients -->
                        <div>
                            <label for="patient_id" class="font-bold text-slate-700 block mb-1.5">
                                المريض ورقم الملف (Patient):
                            </label>
                            <select name="patient_id" id="patient_id"
                                class="w-full p-2.5 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800">
                                <option value="">-- اختر المريض --</option>
                                @foreach ($patients ?? [] as $patient)
                                    <option value="{{ $patient->id }}"
                                        {{ old('patient_id', request('patient_id')) == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->fullname }} ({{ $patient->mrn }})
                                    </option>
                                @endforeach
                            </select>

                        </div>

                        <!-- doctor_id: nullable foreignId constrained to doctors -->
                        <div>
                            <label for="doctor_id" class="font-bold text-slate-700 block mb-1.5">
                                الطبيب المعالج والعيادة (Doctor):
                            </label>
                            <select name="doctor_id" id="doctor_id"
                                class="w-full p-2.5 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800">
                                <option value="">-- اختر الطبيب المعالج --</option>
                                @if (isset($doctors))
                                    @foreach ($doctors as $doctor)
                                        <option value="{{ $doctor->id }}"
                                            {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                            {{ $doctor->fullname }} - {{ $doctor->clinic->name ?? 'عيادة عامة' }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- date: date (required) -->
                    <div>
                        <label for="date" class="font-bold text-slate-700 block mb-1.5">
                            تاريخ الزيارة (Date): <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="date" id="date" value="{{ old('date', date('Y-m-d')) }}"
                            required
                            class="w-full p-2.5 bg-white border border-slate-300 rounded-lg font-mono focus:outline-none focus:ring-1 focus:ring-[#00685f]" />
                    </div>
                </div>

                <!-- Reason: string(500) -->
                <div class="space-y-2 pt-2">
                    <div class="flex items-center justify-between">
                        <label for="reason" class="font-bold text-slate-700 block">
                            سبب الزيارة والشكوى الرئيسية (Reason): <span class="text-red-500">*</span>
                        </label>
                        <span class="text-[11px] text-slate-400 font-mono" id="reasonCharCounter">500 حرف كحد أقصى</span>
                    </div>
                    <textarea name="reason" id="reason" rows="4" maxlength="500" required
                        placeholder="سجل سبب الزيارة، الأعراض الطبية، الفحوصات المطلوبة، أو الغرض من الاستشارة السريرية (حد أقصى 500 حرف)..."
                        class="w-full p-3 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800 leading-relaxed"
                        oninput="document.getElementById('reasonCharCounter').textContent = (500 - this.value.length) + ' حرف متبقٍ';">{{ old('reason') }}</textarea>
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('visits.index') }}"
                        class="px-5 py-2.5 text-slate-600 hover:bg-slate-100 rounded-lg transition font-medium">
                        إلغاء
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 text-white bg-[#00685f] hover:bg-[#005049] rounded-lg font-bold shadow-sm transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>حفظ وتسجيل الزيارة</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
