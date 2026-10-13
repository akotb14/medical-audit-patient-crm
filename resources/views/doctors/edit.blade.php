@extends('layouts.app')

@section('title', 'تعديل بيانات الطبيب والاستشاري')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <x-alerts />
    <div class="flex items-center justify-between">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('doctors.index') }}" class="hover:text-teal-700">دليل الأطباء</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">تعديل ملف الطبيب</span>
            </nav>
            <h1 class="text-2xl font-bold text-[#0b1c30]">تعديل ملف الطبيب</h1>
        </div>
        <a
            href="{{ route('doctors.show' , $doctor) }}"
            class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition"
        >
            ← العودة للملف الطبيب
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-xs">
        <form action="{{ route('doctors.update', $doctor) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- CLINIC REASSIGNMENT (ONE-TO-MANY RELATIONSHIP) -->
            <div class="bg-teal-50/70 p-4 rounded-xl border border-teal-200 space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-teal-900">
                        العيادة التابع لها الطبيب (إعادة تعيين العيادة) <span class="text-red-500">*</span>
                    </label>

                </div>
                <select
                    name="clinic_id"
                    required
                    class="w-full px-3 py-2 text-xs bg-white border border-teal-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-600 text-slate-800 font-medium"
                >
                    @foreach($clinics ?? [] as $clinic)
                        <option value="{{ $clinic['id'] }}" {{ ($doctor->clinic_id ?? 1) == $clinic['id'] ? 'selected' : '' }}>
                            🏥 {{ $clinic['name'] }}
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-teal-800">
                    * يمكنك نقل الطبيب بين العيادات وسينعكس ذلك فوراً على جدول مواعيده ومكان استقبال مرضاه.
                </p>
            </div>

            <!-- Doctor Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        الاسم الكامل للطبيب <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="fullname"
                        value="{{ old('name', $doctor->fullname ?? '') }}"
                        required
                        class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-600"
                    />
                </div>





                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        التخصص الدقيق <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="specialty"
                        value="{{ old('specialty', $doctor->specialty ?? '') }}"
                        required
                        class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-600"
                    />
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        رقم الهاتف المباشر <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="tel"
                        name="phone"
                        value="{{ old('phone', $doctor->phone ?? '') }}"
                        required
                        class="w-full px-3 py-2 text-xs font-mono border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-600"
                    />
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        البريد الإلكتروني <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email', $doctor->email ?? '') }}"
                        required
                        class="w-full px-3 py-2 text-xs font-mono border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-600"
                    />
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        أيام وساعات الدوام <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="schedule_days"
                        value="{{ old('schedule_days', $doctor->schedule_days ?? '') }}"
                        required
                        class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-600"
                    />
                </div>


            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    نبذة مهنية ومؤهلات الطبيب
                </label>
                <textarea
                    name="notes"
                    rows="3"
                    class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-600"
                >{{ old('bio', $doctor->notes ?? '') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        حالة الطبيب <span class="text-red-500">*</span>
                    </label>
                    <select name="status" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-600">
                        <option value="active">متاح ومناوب (نشط)</option>
                        <option value="holiday">في إجازة</option>
                        <option value="inactive">غير نشط</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 pt-5">
                    <button type="button" onclick="document.getElementById('deleteDoctorModal-{{$doctor->id}}').classList.remove('hidden')" class="px-3.5 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 border border-red-200 rounded-lg transition">
                        حذف الطبيب
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg shadow-sm transition">
                        حفظ التعديلات
                    </button>
                </div>
            </div>
        </form>
    </div>
    <x-delete-doctor-modal  :doctor="$doctor"/>
</div>
@endsection
