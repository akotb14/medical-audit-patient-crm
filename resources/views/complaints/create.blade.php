@extends('layouts.app')

@section('title', 'تسجيل شكوى يدوية جديدة (FR-6)')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between">
        <nav class="flex items-center gap-2 text-xs text-slate-500">
            <a href="{{ route('complaints.index') }}" class="hover:text-teal-700 transition">سجل الشكاوى</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">تسجيل شكوى يدوية</span>
        </nav>
        <a href="{{ route('complaints.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1">
            <span>← العودة للقائمة</span>
        </a>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-[#f8fafc] flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 flex items-center justify-center font-bold text-lg">
                    ✍️
                </div>
                <div>
                    <h1 class="text-lg font-bold text-[#0b1c30]">تسجيل تذكرة شكوى واعتراض مريض </h1>
                    <p class="text-xs text-[#64748b]">فتح ملف شكوى مباشر وتعيين المحقق المسؤول لمتابعة التحقيق والإجراء التصحيحي</p>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-sky-50 text-sky-800 border border-sky-200 font-mono">
                source: manual
            </span>
        </div>

        <form action="{{ route('complaints.store') }}" method="POST" class="p-6 sm:p-8 space-y-6 text-xs">
            @csrf

            <!-- Patient Selection -->
            <div class="space-y-1.5">
                <label for="patient_id" class="block font-bold text-slate-700 text-xs">
                    المريض صاحب الشكوى (Patient): <span class="text-red-500">*</span>
                </label>
                <select
                    name="patient_id"
                    id="patient_id"
                    required
                    class="w-full p-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800 text-xs @error('patient_id') border-rose-500 @enderror"
                >
                    <option value="">-- اختر المريض من القائمة --</option>
                    @foreach($patients as $patient)
                        <option value="{{ $patient->id }}" @selected(old('patient_id') == $patient->id )>
                            {{ $patient->fullname }} - رقم الملف: {{ $patient->mrn }} (هاتف: {{ $patient->phone ?? 'غير مسجل' }})
                        </option>
                    @endforeach
                </select>

            </div>

            <!-- Title -->
            <div class="space-y-1.5">
                <label for="title" class="block font-bold text-slate-700 text-xs">
                    عنوان وموضوع الشكوى (Title): <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="title"
                    id="title"
                    value="{{ old('title') }}"
                    required
                    maxlength="255"
                    placeholder="مثال: تأخر في موعد الفحص، سوء تواصل عند الاستقبال، ملاحظة على خطة العلاج..."
                    class="w-full p-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800 text-xs @error('title') border-rose-500 @enderror"
                />
            </div>

            <!-- Description -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label for="description" class="block font-bold text-slate-700 text-xs">
                        تفاصيل ووقائع الشكوى (Description): <span class="text-red-500">*</span>
                    </label>
                    <span class="text-[11px] text-slate-400 font-mono" id="descCounter">2000 حرف كحد أقصى</span>
                </div>
                <textarea
                    name="description"
                    id="description"
                    rows="5"
                    required
                    maxlength="2000"
                    placeholder="اكتب تفاصيل الواقعة بدقة، أسماء الكادر الطبي المعني، الوقت والتاريخ، ومطالب المريض..."
                    oninput="document.getElementById('descCounter').textContent = (2000 - this.value.length) + ' حرف متبقٍ';"
                    class="w-full p-3 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800 text-xs leading-relaxed @error('description') border-rose-500 @enderror"
                >{{ old('description') }}</textarea>

            </div>

            <!-- Assigned To (FR-6) -->
            <div class="space-y-1.5">
                <label for="assigned_to" class="block font-bold text-slate-700 text-xs">
                    إسناد التحقيق إلى موظف / محقق جودة (Assign To):
                    <span class="text-slate-400 font-normal mr-1">(اختياري / يمكن إسناده لاحقاً)</span>
                </label>
                <select
                    name="assigned_to"
                    id="assigned_to"
                    class="w-full p-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800 text-xs @error('assigned_to') border-rose-500 @enderror"
                >
                    <option value="">-- بدون إسناد فوري (ستكون الحالة: جديدة new) --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected(old('assigned_to') == $user->id)>
                            {{ $user->name }} - {{ $user->roleNames() ?: 'بدون دور' }}
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400">عند اختيار موظف مسؤول، ستتحول الحالة تلقائياً إلى مسندة (<strong class="font-mono">assigned</strong>).</p>

            </div>

            <!-- Action Buttons -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('complaints.index') }}" class="px-5 py-2.5 text-slate-600 hover:bg-slate-100 rounded-lg transition font-medium">
                    إلغاء
                </a>
                <button type="submit" class="px-6 py-2.5 text-white bg-[#00685f] hover:bg-[#005049] rounded-lg font-bold shadow-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>تسجيل تذكرة الشكوى</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
