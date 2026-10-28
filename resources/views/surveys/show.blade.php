@extends('layouts.app')

@section('title', 'تفاصيل استبيان الرضا #' . $survey->id)

@section('content')
    <div class="space-y-6">
        <!-- Breadcrumb & Back -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                    <a href="{{ route('surveys.index') }}" class="hover:text-teal-700 transition">استبيانات الرضا</a>
                    <span>/</span>
                    <span class="text-slate-800 font-semibold">استبيان رقم #{{ $survey->id }}</span>
                </nav>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-[#0b1c30]">استبيان رضا المريض #{{ $survey->id }}</h1>
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $survey->rating_badge_classes }}">
                        {{ $survey->rating_label }}
                    </span>
                </div>
                <p class="text-xs text-[#64748b] mt-1 font-mono">
                    تاريخ التسجيل: {{ $survey->created_at ? $survey->created_at->format('Y-m-d H:i') : '-' }} بواسطة:
                    {{ $survey->recorder->name ?? 'موظف النظام' }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('surveys.index') }}"
                    class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition shadow-xs">
                    ← العودة للاستبيانات
                </a>
                <button type="button" onclick="window.print()"
                    class="px-3.5 py-2 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition shadow-xs">
                    طباعة الملخص
                </button>
            </div>
        </div>

        <!-- Rating Highlight Banner -->
        <div
            class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div
                    class="w-16 h-16 rounded-2xl {{ $survey->isLowRating() ? 'bg-rose-100 text-rose-700 border border-rose-200' : 'bg-teal-50 text-[#00685f] border border-teal-200' }} flex flex-col items-center justify-center font-bold">
                    <span class="text-2xl font-mono leading-none">{{ $survey->rating }}</span>
                    <span class="text-[10px] text-slate-500 font-mono">/ 5</span>
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-1 text-amber-500 text-xl">
                        @for ($i = 1; $i <= 5; $i++)
                            <span>{{ $i <= $survey->rating ? '★' : '☆' }}</span>
                        @endfor
                    </div>
                    <h3 class="font-bold text-sm text-slate-800">{{ $survey->rating_label }}</h3>
                    <p class="text-xs text-slate-500">تم التقييم وفق المعيار السريري BR-5 (عدد صحيح من 1 إلى 5 نجوم)</p>
                </div>
            </div>
            <!-- BR-6 Status Indicator -->
            <div
                class="p-4 rounded-xl {{ $survey->rating < 3 ? 'bg-rose-50 border border-rose-200 text-rose-900' : 'bg-emerald-50 border border-emerald-200 text-emerald-900' }} md:max-w-md text-xs space-y-1.5">
                @if ($survey->rating < 3)
                    <div class="flex items-center gap-2 font-bold text-rose-800">
                        <span>⚠️</span>
                        <span> تم إنشاء تذكرة شكوى رسمية تلقائياً</span>
                    </div>
                    <p class="text-[11px] text-rose-700 leading-relaxed">
                        نظراً لأن التقييم أقل من 3 نجوم، تم توليد شكوى غير مكررة ومجدولة للتحقيق الفوري لضمان معايير تجربة
                        المريض.
                    </p>
                    @can('view', $survey->complaint)
                        @if ($survey->complaint)
                            <div class="pt-1">
                                <a href="{{ route('complaints.show', $survey->complaint) }}"
                                    class="inline-flex items-center gap-1 px-3 py-1 bg-rose-700 text-white rounded-lg text-xs font-bold hover:bg-rose-800 transition">
                                    <span>عرض تذكرة الشكوى #{{ $survey->complaint->id }}</span>
                                    <span>➜</span>
                                </a>
                            </div>
                        @endif
                    @endcan
                @else
                    <div class="flex items-center gap-2 font-bold text-emerald-800">
                        <span>✓</span>
                        <span>تقييم إيجابي - لا يستدعي فتح شكوى</span>
                    </div>
                    <p class="text-[11px] text-emerald-700 leading-relaxed">
                        يعكس هذا التقييم رضا المراجع عن الزيارة السريرية والخدمات الطبية المقدمة.
                    </p>
                @endif
            </div>
        </div>

        <!-- Main Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left: Patient Comment & Survey Info (7 Cols) -->
            <div class="lg:col-span-7 space-y-6">
                <!-- Patient Comment Card -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="flex items-center gap-2.5 border-b border-slate-100 pb-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-teal-50 border border-teal-200 text-[#00685f] flex items-center justify-center font-bold text-sm">
                            💬
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-[#0b1c30]">ملاحظات وتجربة المريض (Patient Comment)</h3>
                            <p class="text-[11px] text-[#64748b]">التوثيق النصي لآراء المراجع وملاحظاته حول الخدمة</p>
                        </div>
                    </div>

                    <div
                        class="p-4 bg-slate-50 border-r-4 border-[#00685f] rounded-lg text-slate-800 text-sm leading-relaxed">
                        @if ($survey->comment)
                            "{{ $survey->comment }}"
                        @else
                            <span class="text-slate-400 italic text-xs">لم يقم المريض بتدوين أي تعليقات نصية إضافية، واكتفى
                                بالتقييم النجمي ({{ $survey->rating }}/5).</span>
                        @endif
                    </div>
                </div>


            </div>

            <!-- Right: Linked Visit & Patient (5 Cols) -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Linked Patient Card -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-3 text-xs">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                        <span class="font-bold text-slate-800">بيانات المريض (Patient)</span>
                        @can('view', $survey->patient)

                            @if ($survey->patient && ! $survey->patient->trashed())
                                <a href="{{ route('patients.show', $survey->patient) }}"
                                    class="text-[#00685f] hover:underline font-bold text-[11px]">
                                    عرض الملف الطبي ➜
                                </a>
                            @endif
                        @endcan
                    </div>

                    @if ($survey->patient)
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-full bg-teal-100 text-[#00685f] flex items-center justify-center font-bold text-sm">
                                {{ mb_substr($survey->patient->fullname, 0, 1) }}
                            </div>
                            <div>
                                <div class="font-bold text-sm text-slate-900">{{ $survey->patient->fullname }}
                                    <x-archived-badge :patient="$survey->patient" /></div>
                                <div class="font-mono text-slate-500 text-[11px]">رقم الملف: {{ $survey->patient->mrn }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-slate-400 italic">غير متوفر</div>
                    @endif
                </div>

                <!-- Linked Visit Card -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-3 text-xs">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                        <span class="font-bold text-slate-800">الزيارة المقترنة (Visit Details)</span>
                        @can('view', $survey->visit)

                            @if ($survey->visit)
                                <a href="{{ route('visits.show', $survey->visit) }}"
                                    class="text-[#00685f] hover:underline font-bold text-[11px]">
                                    زيارة #{{ $survey->visit->id }} ➜
                                </a>
                            @endif
                        @endcan
                    </div>

                    @if ($survey->visit)
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">تاريخ الزيارة:</span>
                                <span
                                    class="font-mono font-bold text-slate-800">{{ $survey->visit->date ? $survey->visit->date->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">حالة الزيارة:</span>
                                <span class="font-bold text-teal-800">{{ $survey->visit->status_label }}</span>
                            </div>
                            @if ($survey->visit->doctor)
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">الطبيب المعالج:</span>
                                    <span class="font-bold text-slate-800">د. {{ $survey->visit->doctor->name }}</span>
                                </div>
                            @endif
                            @if ($survey->visit->reason)
                                <div class="pt-1 border-t border-slate-100">
                                    <span class="text-slate-400 block text-[11px] mb-0.5">سبب الزيارة:</span>
                                    <p class="text-slate-700 text-[11px] line-clamp-2">{{ $survey->visit->reason }}</p>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-slate-400 italic">غير مقترن بزيارة</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
