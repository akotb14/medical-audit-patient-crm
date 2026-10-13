@extends('layouts.app')

@section('title', 'فتح ملف مريض جديد (MRN Intake)')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header with Back Button -->
    <div class="flex items-center justify-between border-b border-[#e2e8f0] pb-4">
        <div>
            <h1 class="text-2xl font-bold text-[#0b1c30]">فتح ملف مريض سريري جديد</h1>
            <p class="text-xs text-[#64748b] mt-1">
                تسجيل بيانات المراجع وإصدار الرقم الطبي الموحد (MRN) وتوثيق التنبيهات الصحية
            </p>
        </div>
        <a href="{{ route('patients.index') }}"
            class="px-3.5 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition flex items-center gap-1">
            <span>← العودة لدليل المرضى</span>
        </a>
    </div>
    <x-alerts />
    <!-- Create Form -->
    <form action="{{ route('patients.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- 1. Personal & Contact Demographics -->
        <div class="bg-white rounded-xl border border-[#e2e8f0] p-6 shadow-xs space-y-4">
            <h2 class="text-sm font-bold text-[#0b1c30] flex items-center gap-2 border-b border-slate-100 pb-2">
                <svg class="w-4 h-4 text-[#00685f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>البيانات الشخصية والرقم الطبي</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <label for="name" class="font-bold text-slate-700 block mb-1">الاسم الرباعي للمريض: <span
                            class="text-red-500">*</span></label>
                    <input type="text" id="name" name="fullname" required placeholder="مثال: عبد العزيز بن فيصل الدوسري" value="{{old('fullname')}}"
                        class="w-full p-2.5 bg-white border border-slate-300 rounded-lg text-slate-800 focus:outline-none focus:ring-1 focus:ring-[#00685f]" />
                </div>

                <div>
                    <label for="address" class="font-bold text-slate-700 block mb-1"> العنوان: <span
                            class="text-red-500">*</span></label>
                    <input type="text" id="address" name="address" required placeholder="مثال: شارع الملك فيصل" value="{{old('address')}}"
                        class="w-full p-2.5 bg-white border border-slate-300 rounded-lg font-mono text-left focus:outline-none focus:ring-1 focus:ring-[#00685f]" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="birth_date" class="font-bold text-slate-700 block mb-1">تاريخ الميلاد: <span
                                class="text-red-500">*</span></label>
                        <input type="date" id="birth_date" name="birthdate" min="1900-01-01"
                            max="{{now()->format('Y-m-d')}}" required placeholder="2001-01-01" value="{{old('birthdate')}}"
                            class="w-full p-2.5 bg-white border border-slate-300 rounded-lg text-slate-800 focus:outline-none focus:ring-1 focus:ring-[#00685f]" />
                    </div>
                    <div>
                        <label for="gender" class="font-bold text-slate-700 block mb-1">الجنس: <span
                                class="text-red-500">*</span></label>
                        <select id="gender" name="gender"
                            class="w-full p-2.5 bg-white border border-slate-300 rounded-lg text-slate-800 focus:outline-none focus:ring-1 focus:ring-[#00685f]">
                            <option value="male">ذكر</option>
                            <option value="female">أنثى</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="phone" class="font-bold text-slate-700 block mb-1">رقم الجوال: <span
                                class="text-red-500">*</span></label>
                        <input type="tel" id="phone" name="phone" required placeholder="05XXXXXXXX" value="{{old('phone')}}"
                            class="w-full p-2.5 bg-white border border-slate-300 rounded-lg font-mono text-left focus:outline-none focus:ring-1 focus:ring-[#00685f]" />
                    </div>
                    <div>
                        <label for="blood_group" class="font-bold text-slate-700 block mb-1">فصيلة الدم:</label>
                        <select id="blood_group" name="blood_group"
                            class="w-full p-2.5 bg-white border border-slate-300 rounded-lg font-bold text-rose-700 focus:outline-none focus:ring-1 focus:ring-[#00685f]">
                            <option value="A+">A+</option>
                            <option value="O+">O+</option>
                            <option value="B+">B+</option>
                            <option value="AB+">AB+</option>
                            <option value="A-">A-</option>
                            <option value="O-">O-</option>
                            <option value="B-">B-</option>
                            <option value="AB-">AB-</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Clinical History & Allergy Alerts -->
        <div class="bg-white rounded-xl border border-[#e2e8f0] p-6 shadow-xs space-y-4">
            <h2 class="text-sm font-bold text-[#0b1c30] flex items-center gap-2 border-b border-slate-100 pb-2">
                <svg class="w-4 h-4 text-[#be123c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>التنبيهات السريرية والحساسية الدوائية (Clinical Alerts)</span>
            </h2>

            <div class="space-y-4 text-xs">
                <div>
                    <label for="allergies" class="font-bold text-rose-700 block mb-1">
                        الحساسية الدوائية والغذائية المعروفة (مفصولة بفواصل):
                    </label>
                    <input type="text" id="allergies" name="allergies" value="{{old('allergies')}}"
                        placeholder="مثال: البنسلين (Penicillin)، السلفا، مركبات الأسبرين"
                        class="w-full p-2.5 bg-white border border-rose-200 rounded-lg text-slate-800 focus:outline-none focus:ring-1 focus:ring-rose-500" />
                    <p class="text-[11px] text-slate-500 mt-1">تظهر هذه التنبيهات بشارة تحذير حمراء عالية التباين في
                        كافة شاشات الكشف والمواعيد.</p>
                </div>

                <div>
                    <label for="chronic_diseases" class="font-bold text-slate-700 block mb-1">
                        الأمراض المزمنة والتاريخ الطبي (مفصولة بفواصل):
                    </label>
                    <input type="text" id="chronic_diseases" name="chronic_diseases" value="{{old('chronic_diseases')}}"
                        placeholder="مثال: ارتفاع ضغط الدم، السكري النوع الثاني، الربو التحسسي"
                        class="w-full p-2.5 bg-white border border-slate-300 rounded-lg text-slate-800 focus:outline-none focus:ring-1 focus:ring-[#00685f]" />
                </div>
            </div>
        </div>

        <!-- 3. Emergency Contact & Insurance -->
        <div class="bg-white rounded-xl border border-[#e2e8f0] p-6 shadow-xs space-y-4">
            <h2 class="text-sm font-bold text-[#0b1c30] flex items-center gap-2 border-b border-slate-100 pb-2">
                <svg class="w-4 h-4 text-[#00685f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <span>جهة اتصال الطوارئ </span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <label for="emergency_name" class="font-bold text-slate-700 block mb-1">اسم قريب للطوارئ:</label>
                    <input type="text" id="emergency_name" name="emergency_name" value="{{old('emergency_name')}}"
                        placeholder="مثال: فهد عبد العزيز الدوسري (الأخ)"
                        class="w-full p-2.5 bg-white border border-slate-300 rounded-lg text-slate-800 focus:outline-none focus:ring-1 focus:ring-[#00685f]" />
                </div>

                <div>
                    <label for="emergency_phone" class="font-bold text-slate-700 block mb-1">هاتف الطوارئ:</label>
                    <input type="tel" id="emergency_phone" name="emergency_phone" placeholder="05XXXXXXXX" value="{{old('emergency_phone')}}"
                        class="w-full p-2.5 bg-white border border-slate-300 rounded-lg font-mono text-left focus:outline-none focus:ring-1 focus:ring-[#00685f]" />
                </div>


            </div>
        </div>

        <!-- Submit & Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('patients.index') }}"
                class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                إلغاء
            </a>
            <button type="submit"
                class="px-6 py-2.5 bg-[#00685f] hover:bg-[#005049] text-white text-xs font-bold rounded-lg shadow-sm transition">
                فتح الملف الطبي وحفظ البيانات
            </button>
        </div>
    </form>
</div>
@endsection
