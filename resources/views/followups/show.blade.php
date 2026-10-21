@extends('layouts.app')

@section('title', 'تفاصيل المتابعة السريرية #' . ($followup->id ?? '---'))

@section('content')
<div class="space-y-6">


    <!-- Breadcrumb & Back Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('follow_ups.index') }}" class="hover:text-teal-700 transition">سجل المتابعات السريرية</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">تفاصيل المتابعة #{{ $followup->id ?? '---' }}</span>
            </nav>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-[#0b1c30]">سجل المتابعة السريرية #{{ $followup->id ?? '---' }}</h1>

                <!-- Status Badge -->
                @if(($followup->status ?? '') === 'scheduled')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        <span>مجدولة (scheduled)</span>
                    </span>
                @elseif(($followup->status ?? '') === 'done')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        <span>✓ مكتملة وموثقة (done)</span>
                    </span>
                @elseif(($followup->status ?? '') === 'missed')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200">
                        <span>✕ فائتة / لم يرد (missed)</span>
                    </span>
                @elseif(($followup->status ?? '') === 'cancelled')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                        <span>ملغاة (cancelled)</span>
                    </span>
                @endif

                @if($followup->is_overdue)
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                        متأخرة عن الاستحقاق ⚠️
                    </span>
                @endif
            </div>
            <p class="text-xs text-[#64748b] mt-1">
                موعد الاستحقاق المقرر:
                <span class="font-mono font-bold text-slate-800">{{ $followup->due_date ? $followup->due_date->format('Y-m-d H:i') : '-' }}</span>
            </p>
        </div>

        <!-- Quick Status Change & Action Buttons -->
        <div class="flex items-center gap-2 flex-wrap">
            {{-- Complete Button --}}
            @if(($followup->status ?? '') !== 'done')
                <button
                    type="button"
                    onclick="document.getElementById('completeFollowupModal-{{$followup->id}}').classList.remove('hidden')"
                    class="px-3.5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition flex items-center gap-1.5"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>إتمام وتدوين النتيجة (Complete)</span>
                </button>
            @endif

            {{-- Mark Missed Button --}}
            @if(($followup->status ?? '') === 'scheduled')
                <form action="{{ route('follow_ups.status', $followup ?? 1) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="missed">
                    <button type="submit" class="px-3 py-2 text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-200 hover:bg-rose-100 rounded-lg transition">
                        تسجيل كتفويت (Missed)
                    </button>
                </form>
            @endif

            {{-- Cancel Button --}}
            @if(($followup->status ?? '') !== 'cancelled' && ($followup->status ?? '') !== 'done')
                <form action="{{ route('follow_ups.status', $followup ?? 1) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="px-3 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        إلغاء المتابعة
                    </button>
                </form>
            @endif

            <a href="{{ route('follow_ups.edit', $followup ?? 1) }}" class="px-3.5 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition shadow-xs flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                <span>تعديل</span>
            </a>

            <button type="button" onclick="document.getElementById('deleteFollowupModal-{{$followup->id}}').classList.remove('hidden')" class="px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 rounded-lg transition">
                حذف
            </button>

            <button type="button" onclick="window.print()" class="px-3 py-2 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition shadow-xs">
                طباعة 🖨️
            </button>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left: Reason, Outcome Note & Schema Notes (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Reason for Follow-up Card -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-teal-100 text-[#00685f] flex items-center justify-center font-bold text-sm">
                            📝
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-[#0b1c30]">سبب وأهداف المتابعة السريرية (Reason)</h3>
                            <p class="text-[11px] text-[#64748b]">التوجيهات الطبية والإجراءات المستهدفة للمريض (حتى 500 حرف)</p>
                        </div>
                    </div>
                    <span class="text-xs font-mono text-slate-400">
                        {{ mb_strlen($followup->reason ?? '') }} / 500 حرف
                    </span>
                </div>

                <div class="bg-slate-50 border-r-4 border-[#00685f] p-4 rounded-xl text-slate-800 text-sm leading-relaxed">
                    {{ $followup->reason ?? 'لم يتم تدوين سبب للمتابعة.' }}
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2 text-xs text-slate-600">
                    <div class="p-3 bg-white border border-slate-200 rounded-xl">
                        <span class="text-slate-400 block text-[11px] mb-1">تاريخ الاستحقاق المقرر:</span>
                        <span class="font-mono font-bold text-slate-800">
                            {{ $followup->due_date ? $followup->due_date->format('Y-m-d H:i') : '-' }}
                        </span>
                    </div>
                    <div class="p-3 bg-white border border-slate-200 rounded-xl">
                        <span class="text-slate-400 block text-[11px] mb-1">الحالة السريرية الحالية:</span>
                        <span class="font-bold text-teal-800">
                            {{ $followup->status_label ?? $followup->status }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Outcome Note Card (Clinical Result) -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                            ✓
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-[#0b1c30]">نتيجة وتوثيق المتابعة (Outcome Note)</h3>
                            <p class="text-[11px] text-[#64748b]">استجابة المريض السريرية، ملاحظات المكالمة، أو الإجراء المتخذ</p>
                        </div>
                    </div>
                    @if(!empty($followup->outcome_note))
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                            تم التوثيق
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                            بانتظار التدوين
                        </span>
                    @endif
                </div>

                @if(!empty($followup->outcome_note))
                    <div class="bg-emerald-50/50 border-r-4 border-emerald-600 p-4 rounded-xl text-slate-800 text-sm leading-relaxed">
                        {{ $followup->outcome_note }}
                    </div>

                    <div class="p-3 bg-white border border-emerald-200 rounded-xl flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2 text-emerald-800 font-semibold">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>تاريخ وساعة الإتمام (completed_at):</span>
                        </div>
                        <span class="font-mono font-bold text-emerald-900">
                            {{ $followup->completed_at ? $followup->completed_at->format('Y-m-d H:i') : '-' }}
                        </span>
                    </div>
                @else
                    <div class="p-4 bg-amber-50/60 border border-dashed border-amber-300 rounded-xl text-center space-y-2">
                        <p class="text-xs text-amber-800 font-medium">
                            لم يتم تدوين نتيجة أو ملاحظات المتابعة حتى الآن.
                        </p>
                        <button
                            type="button"
                            onclick="openCompleteModal({{ $followup->id ?? 1 }}, '{{ addslashes($followup->patient->name ?? 'المريض') }}')"
                            class="px-4 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition inline-flex items-center gap-1.5"
                        >
                            <span>+ تدوين النتيجة الآن</span>
                        </button>
                    </div>
                @endif
            </div>


        </div>

        <!-- Right: Linked Patient & Visit Cards (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Linked Patient Card -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-sm">👤</span>
                        <h4 class="font-bold text-xs text-slate-800 uppercase tracking-wider">بيانات المريض (Patient)</h4>
                    </div>
                    @if($followup->patient)
                        <a href="{{ route('patients.show', $followup->patient) }}" class="text-xs font-bold text-[#00685f] hover:underline">
                            عرض الملف الكامل ➜
                        </a>
                    @endif
                </div>

                @if($followup->patient)
                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-slate-400 block text-[11px]">اسم المريض:</span>
                            <span class="font-bold text-sm text-[#0b1c30]">{{ $followup->patient->fullname }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <span class="text-slate-400 block text-[11px]">رقم الملف (MRN):</span>
                                <span class="font-mono font-bold text-teal-800 bg-teal-50 px-2 py-0.5 rounded inline-block mt-0.5">
                                    {{ $followup->patient->mrn }}
                                </span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">رقم الهاتف:</span>
                                <a href="tel:{{ $followup->patient->phone }}" class="font-mono font-bold text-slate-800 hover:text-teal-700 block mt-0.5">
                                    {{ $followup->patient->phone }} 📞
                                </a>
                            </div>
                        </div>

                        @if(!empty($followup->patient->blood_group))
                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-slate-400 text-[11px]">فصيلة الدم:</span>
                                <span class="font-bold font-mono text-red-600 bg-red-50 px-2 py-0.5 rounded">{{ $followup->patient->blood_group }}</span>
                            </div>
                        @endif

                            <div class="pt-2 border-t border-slate-100">
                                <span class="text-rose-700 font-bold block text-[11px] mb-1">الحساسية الدوائية:</span>
                                <div class="flex flex-wrap gap-1">
                                        <span class="px-2 py-0.5 rounded bg-rose-50 text-rose-800 text-[10px] border border-rose-200">
                                            {{ $followup->patient->allergies }}
                                        </span>
                                </div>
                            </div>
                    </div>
                @else
                    <div class="p-4 bg-slate-50 rounded-xl text-center text-slate-400 italic text-xs">
                        لم يتم ربط هذه المتابعة بملف مريض محدد
                    </div>
                @endif
            </div>

            <!-- Linked Clinical Visit Card -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-sm">🩺</span>
                        <h4 class="font-bold text-xs text-slate-800 uppercase tracking-wider">الزيارة السريرية المرتبطة (Visit)</h4>
                    </div>
                    @if($followup->visit)
                        <a href="{{ route('visits.show', $followup->visit) }}" class="text-xs font-bold text-[#00685f] hover:underline">
                            عرض الزيارة ➜
                        </a>
                    @endif
                </div>

                @if($followup->visit)
                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-slate-400 block text-[11px]">رقم الزيارة:</span>
                                <span class="font-mono font-bold text-slate-800">زيارة سريرية #{{ $followup->visit->id }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700">
                                {{ $followup->visit->visit_status ?? 'مكتملة' }}
                            </span>
                        </div>

                        <div>
                            <span class="text-slate-400 block text-[11px]">تاريخ المراجعة:</span>
                            <span class="font-mono font-bold text-slate-800">
                                {{ $followup->visit->date ? $followup->visit->date->format('Y-m-d') : '-' }}
                            </span>
                        </div>

                        @if($followup->visit->doctor)
                            <div class="p-3 bg-teal-50/40 rounded-xl border border-teal-100">
                                <span class="text-slate-400 block text-[10px]">الطبيب المعالج والعيادة:</span>
                                <span class="font-bold text-slate-900 block mt-0.5">{{ $followup->visit->doctor->fullname }}</span>
                                <span class="text-[11px] text-teal-800">{{ $followup->visit->doctor->clinic->name ?? 'عيادة تخصصية' }}</span>
                            </div>
                        @endif

                        @if(!empty($followup->visit->reason))
                            <div>
                                <span class="text-slate-400 block text-[11px] mb-1">سبب الزيارة السابق:</span>
                                <p class="text-slate-700 bg-slate-50 p-2.5 rounded-lg leading-relaxed line-clamp-2">
                                    {{ $followup->visit->reason }}
                                </p>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="p-4 bg-slate-50 rounded-xl text-center text-slate-400 italic text-xs">
                        متابعة سريرية مستقلة غير مرتبطة بزيارة محددة (visit_id = null)
                    </div>
                @endif
            </div>

            <!-- Audit & Created By Card -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-3 text-xs">
                <h4 class="font-bold text-xs text-slate-800 border-b border-slate-100 pb-2">
                    سجل التوثيق والتدقيق السريري (Audit)
                </h4>
                <div class="space-y-2 text-slate-600">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">أنشئ بواسطة (created_by):</span>
                        <span class="font-bold text-slate-800">{{ $followup->creator->name ?? 'النظام الآلي' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">تاريخ وساعة الإنشاء:</span>
                        <span class="font-mono text-slate-700">
                            {{ $followup->created_at ? $followup->created_at->format('Y-m-d H:i:s') : '-' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">آخر تحديث للسجل:</span>
                        <span class="font-mono text-slate-700">
                            {{ $followup->updated_at ? $followup->updated_at->format('Y-m-d H:i:s') : '-' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<x-delete-followup-modal :followup="$followup" />
<x-complete-followup-modal :followup="$followup" />
@endsection
