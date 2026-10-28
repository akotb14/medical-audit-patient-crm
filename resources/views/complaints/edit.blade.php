@extends('layouts.app')

@section('title', 'تعديل تذكرة الشكوى #' . $complaint->id  )

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between">
        <nav class="flex items-center gap-2 text-xs text-slate-500">
            <a href="{{ route('complaints.index') }}" class="hover:text-teal-700 transition">سجل الشكاوى</a>
            <span>/</span>
            <a href="{{ route('complaints.show', $complaint) }}" class="hover:text-teal-700 transition">شكوى #{{ $complaint->id }}</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">تعديل الشكوى</span>
        </nav>
        <a href="{{ route('complaints.show', $complaint) }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1">
            <span>← العودة لملف الشكوى</span>
        </a>
    </div>
    <!-- Main Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-[#f8fafc] flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-200 text-[#00685f] flex items-center justify-center font-bold text-lg">
                    ✏️
                </div>
                <div>
                    <h1 class="text-lg font-bold text-[#0b1c30]">تعديل بيانات الشكوى #{{ $complaint->id }}</h1>
                    <p class="text-xs text-[#64748b]">تحديث تفاصيل التحقيق، المسند إليه، حالة المعالجة وملاحظة الحل السريرية </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $complaint->source === 'survey' ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-sky-50 text-sky-800 border border-sky-200' }} font-mono">
                    source: {{ $complaint->source }}
                </span>
                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border font-mono {{ $complaint->status_badge_class }}">
                    {{ $complaint->status_label }}
                </span>
            </div>
        </div>

        <!-- Patient & Context Header Banner -->
        <div class="px-6 py-4 bg-slate-50/70 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4 text-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 font-bold flex items-center justify-center text-xs">
                    {{ mb_substr($complaint->patient->fullname ?? 'م', 0, 1) }}
                </div>
                <div>
                    <div class="font-bold text-slate-900">
                        {{ $complaint->patient->fullname ?? 'مريض غير محدد' }}
                    </div>
                    <div class="text-[11px] text-slate-500 font-mono">
                        الملف: <span class="text-slate-800 font-bold">{{ $complaint->patient->mrn ?? '-' }}</span> | الهاتف: {{ $complaint->patient->phone ?? '-' }}
                    </div>
                </div>
            </div>

            @if($complaint->survey)
                <div class="bg-rose-50 border border-rose-200 rounded-xl px-3 py-1.5 flex items-center gap-2 text-rose-800 text-[11px] font-semibold">
                    <span>مرتبطة باستبيان تقييم منخفض ({{ $complaint->survey->rating }}/5 نجوم)</span>
                    <a href="{{ route('surveys.show', $complaint->survey) }}" class="underline hover:text-rose-950 font-bold">عرض الاستبيان</a>
                </div>
            @endif
        </div>

        <form action="{{ route('complaints.update', $complaint) }}" method="POST" class="p-6 sm:p-8 space-y-6 text-xs" id="editComplaintForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="patient_id" value="{{ $complaint->patient->id }}">
            <!-- Title -->
            <div class="space-y-1.5">
                <label for="title" class="block font-bold text-slate-700 text-xs">
                    عنوان وموضوع الشكوى (Title): <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="title"
                    id="title"
                    value="{{ old('title', $complaint->title) }}"
                    required
                    maxlength="255"
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
                    rows="4"
                    required
                    maxlength="2000"
                    oninput="document.getElementById('descCounter').textContent = (2000 - this.value.length) + ' حرف متبقٍ';"
                    class="w-full p-3 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800 text-xs leading-relaxed @error('description') border-rose-500 @enderror"
                >{{ old('description', $complaint->description) }}</textarea>

            </div>



            <!-- Action Buttons -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('complaints.show', $complaint) }}" class="px-5 py-2.5 text-slate-600 hover:bg-slate-100 rounded-lg transition font-medium">
                    إلغاء
                </a>
                <button type="submit" class="px-6 py-2.5 text-white bg-[#00685f] hover:bg-[#005049] rounded-lg font-bold shadow-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>حفظ التعديلات</span>
                </button>
            </div>
        </form>
    </div>
</div>


@endsection
