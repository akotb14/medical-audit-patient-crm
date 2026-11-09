@extends('layouts.app')

@section('title', 'تسجيل طبيب واستشاري جديد')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('doctors.index') }}" class="hover:text-teal-700">دليل الأطباء</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">تسجيل طبيب</span>
            </nav>
            <h1 class="text-2xl font-bold text-[#0b1c30]">تسجيل طبيب واستشاري جديد</h1>
        </div>
        <a href="{{ route('doctors.index') }}"
            class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition">
            ← العودة للأطباء
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-xs">
        <form action="{{ route('doctors.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- CLINIC ASSIGNMENT (ONE-TO-MANY RELATIONSHIP) -->
            <div class="bg-teal-50/70 p-4 rounded-xl border border-teal-200 space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-teal-900">
                        العيادة التابع لها الطبيب <span class="text-red-500">*</span>
                    </label>

                </div>
                <select name="clinic_id" id="newDoctorClinicSelect" required
                    class="w-full px-3 py-2 text-xs bg-white border border-teal-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-600 text-slate-800 font-medium">
                    <option value="">-- اختر العيادة الطبية التابع لها --</option>
                    @foreach($clinics ?? [] as $clinic)
                    <option value="{{ $clinic->id }}" @selected(old('clinic_id') == $clinic->id)>
                        {{ $clinic->name }}
                    </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-teal-800">
                    * يتم ربط ملف الطبيب مباشرة بالعيادة المحددة وتظهر مواعيده واستشاراته ضمن جدولها السريري.
                </p>
            </div>

            <!-- Doctor Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        الاسم الكامل للطبيب <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="fullname" required placeholder="د. الاسم الكامل"  value="{{old('fullname')}}"
                        class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-600" />
                </div>


                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        التخصص الدقيق <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="specialty" required placeholder="مثال: قسطرة وتخطيط القلب التداخلي" value="{{old('specialty')}}"
                        class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-600" />
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        رقم الهاتف المباشر <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" name="phone" required placeholder="05XXXXXXXX" value="{{old('phone')}}"
                        class="w-full px-3 py-2 text-xs font-mono border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-600" />
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        البريد الإلكتروني المؤسسي <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" required placeholder="doctor@cliniccare.med.sa" value="{{old('email')}}"
                        class="w-full px-3 py-2 text-xs font-mono border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-600" />
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        أيام وساعات الدوام <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="schedule_days" required
                        placeholder="مثال: الأحد - الخميس (08:00 ص - 04:00 م)"
                        value="{{ old('schedule_days') }}"
                        class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-600" />
                </div>

            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    نبذة مهنية ومؤهلات الطبيب
                </label>
                <textarea name="notes" rows="3"
                    placeholder="الشهادات العليا، البورد العربي/السعودي/الأمريكي، والخبرات السريرية..."
                    class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-600">{{ old('notes') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        حالة الطبيب <span class="text-red-500">*</span>
                    </label>
                    <select name="status"
                        class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-600">
                        <option value="active" @selected(old('status', 'active') === 'active')>متاح ومناوب (نشط)</option>
                        <option value="holiday" @selected(old('status') === 'holiday')>في إجازة دورية / علمية</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>غير نشط</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                <a href="{{ route('doctors.index') }}"
                    class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                    إلغاء
                </a>
                <button type="submit"
                    class="px-5 py-2 text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg shadow-sm transition">
                    تسجيل واعتماد الطبيب
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
