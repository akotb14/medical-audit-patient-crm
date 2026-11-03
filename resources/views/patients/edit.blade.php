@extends('layouts.app')

@section('title', 'تعديل بيانات الملف الطبي - ' . $patient->fullname)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <x-alerts />
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-[#e2e8f0] pb-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold text-[#0b1c30]">تعديل بيانات الملف الطبي</h1>
                <span
                    class="font-mono text-xs font-bold text-[#00685f] bg-[#eff4ff] px-2 py-0.5 rounded border border-teal-200">
                    {{ $patient->mrn }}
                </span>
            </div>
            <p class="text-xs text-[#64748b] mt-1">
                تحديث المعلومات الشخصية، التنبيهات الصحية، أو بيانات التأمين والطوارئ للمريض
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('patients.show', $patient) }}"
                class="px-3.5 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                معاينة الملف الشامل
            </a>
            <a href="{{ route('patients.index') }}"
                class="px-3.5 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                ← العودة للقائمة
            </a>
        </div>
    </div>

    <!-- Edit Form -->
    <form action="{{ route('patients.update', $patient->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. Demographics -->
        <div class="bg-white rounded-xl border border-[#e2e8f0] p-6 shadow-xs space-y-4">
            <h2 class="text-sm font-bold text-[#0b1c30] flex items-center gap-2 border-b border-slate-100 pb-2">
                <svg class="w-4 h-4 text-[#00685f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>البيانات الأساسية للمريض</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <label for="fullname" class="font-bold text-slate-700 block mb-1">الاسم الرباعي: <span
                            class="text-red-500">*</span></label>
                    <input type="text" id="fullname" name="fullname" required
                        value="{{ old('fullname', $patient->fullname) }}"
                        class="w-full p-2.5 bg-white border border-slate-300 rounded-lg text-slate-800 focus:outline-none focus:ring-1 focus:ring-[#00685f]" />
                </div>

                <div>
                    <label for="address" class="font-bold text-slate-700 block mb-1">العنوان:
                        <span class="text-slate-400 font-normal">(اختياري)</span></label>
                    <input type="text" id="address" name="address"
                        value="{{ old('address', $patient->address) }}"
                        class="w-full p-2.5 bg-white border border-slate-300 rounded-lg font-mono text-left focus:outline-none focus:ring-1 focus:ring-[#00685f]" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="birthdate" class="font-bold text-slate-700 block mb-1">تاريخ الميلاد: <span
                                class="text-red-500">*</span></label>
                        <input type="date" id="birthdate" name="birthdate" min="1900-01-01"
                            max="{{now()->format('Y-m-d')}}" required
                            value="{{ old('birthdate', $patient->birthdate?->format('Y-m-d')) }}"
                            class="w-full p-2.5 bg-white border border-slate-300 rounded-lg text-slate-800 focus:outline-none focus:ring-1 focus:ring-[#00685f]" />
                    </div>
                    <div>
                        <label for="gender" class="font-bold text-slate-700 block mb-1">الجنس:</label>
                        <select id="gender" name="gender"
                            class="w-full p-2.5 bg-white border border-slate-300 rounded-lg text-slate-800 focus:outline-none focus:ring-1 focus:ring-[#00685f]">
                            <option value="male" @selected(old('gender', $patient->gender) === 'male')>ذكر
                            </option>
                            <option value="female" @selected(old('gender', $patient->gender) === 'female')>أنثى
                            </option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="phone" class="font-bold text-slate-700 block mb-1">رقم الجوال: <span
                                class="text-red-500">*</span></label>
                        <input type="tel" id="phone" name="phone" required
                            value="{{ old('phone', $patient->phone) }}"
                            class="w-full p-2.5 bg-white border border-slate-300 rounded-lg font-mono text-left focus:outline-none focus:ring-1 focus:ring-[#00685f]" />
                    </div>
                    <div>
                        <label for="blood_group" class="font-bold text-slate-700 block mb-1">فصيلة الدم:</label>
                        <select id="blood_group" name="blood_group"
                            class="w-full p-2.5 bg-white border border-slate-300 rounded-lg font-bold text-rose-700 focus:outline-none focus:ring-1 focus:ring-[#00685f]">
                            @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $group)
                                <option value="{{ $group }}"
                                    @selected(old('blood_group', $patient->blood_group) === $group)>{{ $group }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Medical History & Allergies -->
        <div class="bg-white rounded-xl border border-[#e2e8f0] p-6 shadow-xs space-y-4">
            <h2 class="text-sm font-bold text-[#0b1c30] flex items-center gap-2 border-b border-slate-100 pb-2">
                <svg class="w-4 h-4 text-[#be123c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>التنبيهات السريرية والحساسية</span>
            </h2>

            <div class="space-y-4 text-xs">
                <div>
                    <label for="allergies" class="font-bold text-rose-700 block mb-1">
                        الحساسية الدوائية والغذائية (مفصولة بفواصل):
                    </label>
                    <input type="text" id="allergies" name="allergies"
                        value="{{ old('allergies', $patient->allergies) }}"
                        class="w-full p-2.5 bg-white border border-rose-200 rounded-lg text-slate-800 focus:outline-none focus:ring-1 focus:ring-rose-500" />
                </div>

                <div>
                    <label for="chronic_diseases" class="font-bold text-slate-700 block mb-1">
                        الأمراض المزمنة (مفصولة بفواصل):
                    </label>
                    <input type="text" id="chronic_diseases" name="chronic_diseases"
                        value="{{ old('chronic_diseases', $patient->chronic_diseases) }}"
                        class="w-full p-2.5 bg-white border border-slate-300 rounded-lg text-slate-800 focus:outline-none focus:ring-1 focus:ring-[#00685f]" />
                </div>
            </div>
        </div>

        <!-- 3. Emergency Contact -->
        <div class="bg-white rounded-xl border border-[#e2e8f0] p-6 shadow-xs space-y-4">
            <h2 class="text-sm font-bold text-[#0b1c30] flex items-center gap-2 border-b border-slate-100 pb-2">
                <svg class="w-4 h-4 text-[#00685f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <span>جهة اتصال الطوارئ</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <label for="emergency_name" class="font-bold text-slate-700 block mb-1">اسم قريب للطوارئ:</label>
                    <input type="text" id="emergency_name" name="emergency_name"
                        value="{{ old('emergency_name', $patient->emergency_name) }}"
                        class="w-full p-2.5 bg-white border border-slate-300 rounded-lg text-slate-800 focus:outline-none focus:ring-1 focus:ring-[#00685f]" />
                </div>

                <div>
                    <label for="emergency_phone" class="font-bold text-slate-700 block mb-1">هاتف الطوارئ:</label>
                    <input type="tel" id="emergency_phone" name="emergency_phone"
                        value="{{ old('emergency_phone', $patient->emergency_phone) }}"
                        class="w-full p-2.5 bg-white border border-slate-300 rounded-lg font-mono text-left focus:outline-none focus:ring-1 focus:ring-[#00685f]" />
                </div>
            </div>
        </div>

        <!-- Submit & Actions -->
        <div class="flex items-center justify-between pt-2">
            <!-- Destructive Delete Trigger -->
            {{-- <button type="button"
                onclick="document.getElementById('deletePatientModal').classList.remove('hidden')"
                class="px-4 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 border border-red-200 rounded-lg transition">
                حذف الملف الطبي نهائياً...
            </button> --}}

            <div class="flex items-center gap-3">
                <a href="{{ route('patients.index') }}"
                    class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                    إلغاء التعديل
                </a>
                <button type="submit"
                    class="px-6 py-2.5 bg-[#00685f] hover:bg-[#005049] text-white text-xs font-bold rounded-lg shadow-sm transition">
                    حفظ التعديلات وتحديث الملف
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Delete Confirmation Modal Component -->
{{-- @include('patients.delete-modal', ['patient' => $patient ?? null]) --}}
@endsection
