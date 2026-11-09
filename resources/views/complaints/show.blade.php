@extends('layouts.app')

@section('title', 'تذكرة الشكوى #' . $complaint->id)

@section('content')
    <div class="space-y-6">




        <!-- Breadcrumb & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                    <a href="{{ route('complaints.index') }}" class="hover:text-teal-700 transition">سجل الشكاوى</a>
                    <span>/</span>
                    <span class="text-slate-800 font-semibold">تذكرة رقم #{{ $complaint->id }}</span>
                </nav>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl font-bold text-[#0b1c30]">تذكرة الشكوى #{{ $complaint->id }}</h1>
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $complaint->status_badge_classes }}">
                        {{ $complaint->status_label }}
                    </span>
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $complaint->source_badge_classes }}">
                        {{ $complaint->source_label }}
                    </span>
                </div>
                <p class="text-xs text-[#64748b] mt-1 font-mono">
                    تاريخ الفتح: {{ $complaint->created_at ? $complaint->created_at->format('Y-m-d H:i') : '-' }} بواسطة:
                    {{ $complaint->creator->name }}
                </p>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                @can('update', $complaint)
                    <a href="{{ route('complaints.edit', $complaint) }}"
                        class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition shadow-xs flex items-center gap-1.5">
                        <x-icon name="pencil" /> <span>تعديل</span>
                    </a>
                @endcan
                @can('viewAny', App\Models\Complaint::class)
                    <a href="{{ route('complaints.index') }}"
                        class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition shadow-xs">
                        ← العودة للقائمة
                    </a>
                @endcan

                <button type="button" onclick="window.print()"
                    class="px-3.5 py-2 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition shadow-xs">
                    طباعة التقرير
                </button>
            </div>
        </div>

        <!-- Stepper Lifecycle Component -->
        @php
            $stages = [
                'new' => ['name' => '1. جديدة', 'icon' => '🆕', 'desc' => 'تسجيل البلاغ'],
                'assigned' => ['name' => '2. مسندة', 'icon' => '👤', 'desc' => 'تعيين المحقق'],
                'investigating' => [
                    'name' => '3. قيد التحقيق',
                    'icon' => '🔍',
                    'desc' => 'التدقيق والتواصل',
                ],
                'resolved' => [
                    'name' => '4. تم الحل',
                    'icon' => '✓',
                    'desc' => 'توثيق الإجراء التصحيحي',
                ],
                'closed' => ['name' => '5. مغلقة', 'icon' => '🔒', 'desc' => 'إغلاق رسمي نهائي'],
            ];

            $stageKeys = array_keys($stages);
            $currentIndex = array_search($complaint->status, $stageKeys);
            if ($currentIndex === false) {
                $currentIndex = 0;
            }
        @endphp

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-sm">🔄</span>
                    <h3 class="font-bold text-sm text-[#0b1c30]">مسار دورة حياة الشكوى</h3>
                </div>
                <span class="text-xs font-bold text-teal-800">
                    المرحلة الحالية: {{ $complaint->status_label }}
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
                @foreach ($stages as $key => $stage)
                    @php
                        $stepIndex = array_search($key, $stageKeys);
                        $isPassed = $stepIndex < $currentIndex;
                        $isCurrent = $stepIndex === $currentIndex;
                    @endphp
                    <div
                        class="p-3.5 rounded-xl border text-center space-y-1 transition {{ $isCurrent ? 'bg-teal-50 border-teal-500 ring-2 ring-teal-200' : ($isPassed ? 'bg-emerald-50/40 border-emerald-300' : 'bg-slate-50 border-slate-200 opacity-60') }}">
                        <div class="text-base">{{ $isPassed ? '✓' : $stage['icon'] }}</div>
                        <div class="font-bold text-xs {{ $isCurrent ? 'text-teal-900' : 'text-slate-800' }}">
                            {{ $stage['name'] }}</div>
                        <div class="text-[10px] text-slate-500">{{ $stage['desc'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Main Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left: Details & Actions (7 Cols) -->
            <div class="lg:col-span-7 space-y-6">
                <!-- Complaint Details Card -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <span class="text-xs font-bold text-slate-400 block mb-1">موضوع وعنوان الشكوى:</span>
                        <h2 class="text-base font-bold text-slate-900">{{ $complaint->title }}</h2>
                    </div>

                    <div class="space-y-2">
                        <span class="text-xs font-bold text-slate-700 block">تفاصيل ووقائع البلاغ:</span>
                        <div
                            class="p-4 bg-slate-50 border-r-4 border-rose-500 rounded-lg text-slate-800 text-xs leading-relaxed whitespace-pre-line">
                            {{ $complaint->description }}
                        </div>
                    </div>

                    <!-- Timestamps Details -->
                    <div class="grid grid-cols-3 gap-2.5 pt-2 text-xs border-t border-slate-100 font-mono text-[11px]">
                        <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-100">
                            <span class="text-slate-400 block mb-0.5">تاريخ الفتح:</span>
                            <span
                                class="font-bold text-slate-800">{{ $complaint->created_at ? $complaint->created_at->format('Y-m-d H:i') : '-' }}</span>
                        </div>
                        <div class="p-2.5 bg-teal-50/50 rounded-lg border border-teal-100">
                            <span class="text-teal-700 block mb-0.5">تاريخ الحل:</span>
                            <span
                                class="font-bold text-teal-900">{{ $complaint->resolved_at ? $complaint->resolved_at->format('Y-m-d H:i') : 'لم تُحل بعد' }}</span>
                        </div>
                        <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-100">
                            <span class="text-slate-400 block mb-0.5">تاريخ الإغلاق:</span>
                            <span
                                class="font-bold text-slate-800">{{ $complaint->closed_at ? $complaint->closed_at->format('Y-m-d H:i') : 'مفتوحة' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Resolution Note Card -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-sm">📝</span>
                            <h3 class="font-bold text-sm text-[#0b1c30]">توثيق الإجراء التصحيحي وحل الشكوى </h3>
                        </div>
                        @if ($complaint->resolution_note)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                موثق رسمياً
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                مطلوب للحل والإغلاق
                            </span>
                        @endif
                    </div>

                    @if ($complaint->resolution_note)
                        <div class="p-4 bg-emerald-50/80 border border-emerald-200 rounded-xl space-y-2">
                            <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-900">
                                <span>✓</span>
                                <span>الإجراء المتخذ والتسوية السريرية:</span>
                            </div>
                            <p class="text-xs text-emerald-950 leading-relaxed whitespace-pre-line">
                                {{ $complaint->resolution_note }}
                            </p>
                        </div>
                    @else
                        <p
                            class="text-xs text-slate-500 leading-relaxed bg-slate-50 p-4 rounded-xl border border-slate-200">
                            لم يتم تسجيل إجراء تصحيحي بعد. لا يمكن نقل الشكوى إلى حالة «تم الحل» أو «مغلقة» إلا بعد
                            تعبئة هذا الحقل.
                        </p>
                    @endif
                </div>

                <!-- Stepper Transition & Action Form -->
                @can('advance', $complaint)
                    <div class="bg-white rounded-2xl border border-[#00685f]/30 p-6 shadow-xs space-y-4">
                        <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                            <span class="text-base">⚡</span>
                            <h3 class="font-bold text-sm text-[#0b1c30]">تحديث المرحلة والإجراءات </h3>
                        </div>

                        <form action="{{ route('complaints.advance', $complaint) }}" method="POST" class="space-y-4">
                            @csrf
                            @method('PATCH')

                            {{-- Current Status --}}
                            <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">

                                <p class="text-xs font-semibold text-slate-500 mb-1">
                                    المرحلة الحالية
                                </p>

                                <p class="font-bold text-slate-800">
                                    {{ $complaint->status_label }}
                                </p>

                            </div>

                            {{-- NEW → ASSIGNED --}}
                            @if ($complaint->status === 'new')
                                <div>
                                    <label for="assigned_to" class="block text-sm font-bold text-slate-700 mb-2">
                                        اختر الموظف المسؤول
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <select name="assigned_to" id="assigned_to" required
                                        class="w-full rounded-xl border border-slate-300 p-3">
                                        <option value="">اختر الموظف</option>

                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}" @selected(old('assigned_to') == $user->id)>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            {{-- INVESTIGATING → RESOLVED --}}
                            @if ($complaint->status === 'investigating')
                                <div>
                                    <label for="resolution_note" class="block text-sm font-bold text-slate-700 mb-2">
                                        ملاحظة الحل
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <textarea name="resolution_note" id="resolution_note" rows="4" required
                                        class="w-full rounded-xl border border-slate-300 p-3" placeholder="اكتب الإجراءات التي تم اتخاذها لحل الشكوى...">{{ old('resolution_note') }}</textarea>
                                </div>
                            @endif

                            {{-- Submit --}}
                            @if ($complaint->nextStatus())
                                <button type="submit"
                                    class="w-full rounded-xl bg-teal-700 px-5 py-3 font-bold text-white hover:bg-teal-800 transition">

                                    @switch($complaint->status)
                                        @case('new')
                                            إسناد الشكوى
                                        @break

                                        @case('assigned')
                                            بدء التحقيق
                                        @break

                                        @case('investigating')
                                            إنهاء التحقيق وتسجيل الحل
                                        @break

                                        @case('resolved')
                                            إغلاق الشكوى
                                        @break
                                    @endswitch

                                </button>
                            @else
                                <div
                                    class="rounded-xl bg-green-50 border border-green-200 p-4 text-green-800 text-sm font-semibold">
                                    تم إغلاق الشكوى نهائيًا.
                                </div>
                            @endif

                        </form>

                        @if ($complaint->canReopen())
                            @can('reopen', $complaint)
                                <form action="{{ route('complaints.reopen', $complaint) }}" method="POST"
                                    class="mt-4 space-y-4 rounded-xl border border-amber-200 bg-amber-50 p-4">
                                    @csrf
                                    @method('PATCH')

                                    <div>
                                        <h3 class="font-bold text-amber-900">
                                            إعادة فتح الشكوى
                                        </h3>

                                        <p class="mt-1 text-xs text-amber-700">
                                            سيتم إعادة الشكوى إلى مرحلة التحقيق.
                                        </p>
                                    </div>



                                    <button type="submit"
                                        class="rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-amber-700">
                                        إعادة فتح الشكوى
                                    </button>
                                </form>
                            @endcan
                        @endif
                    </div>
                @endcan

            </div>

            <!-- Right: Linked Patient & Survey & Assignee (5 Cols) -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Linked Patient Card -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-3 text-xs">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                        <span class="font-bold text-slate-800">بيانات المريض الشاكي (Patient)</span>
                        @can('view', $complaint->patient)
                            @if ($complaint->patient && ! $complaint->patient->trashed())
                                <a href="{{ route('patients.show', $complaint->patient) }}"
                                    class="text-[#00685f] hover:underline font-bold text-[11px]">
                                    عرض الملف الطبي ➜
                                </a>
                            @endif
                        @endcan
                    </div>

                    @if ($complaint->patient)
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-teal-100 text-[#00685f] flex items-center justify-center font-bold text-sm">
                                    {{ mb_substr($complaint->patient->fullname, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-sm text-slate-900">{{ $complaint->patient->fullname }}
                                        <x-archived-badge :patient="$complaint->patient" />
                                    </div>
                                    <div class="font-mono text-slate-500 text-[11px]">MRN: {{ $complaint->patient->mrn }}
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 text-[11px]">
                                <div>
                                    <span class="text-slate-400 block">رقم الهاتف:</span>
                                    @if ($complaint->patient->phone)
                                        <a href="tel:{{ $complaint->patient->phone }}"
                                            class="font-mono font-bold text-teal-800 hover:underline">
                                            {{ $complaint->patient->phone }}
                                        </a>
                                    @else
                                        <span class="text-slate-400 italic">غير مسجل</span>
                                    @endif
                                </div>

                            </div>
                        </div>
                    @else
                        <div class="text-slate-400 italic">غير محدد</div>
                    @endif
                </div>

                <!-- Linked Survey Card -->
                @if ($complaint->survey)
                    <div class="rounded-2xl border border-rose-200 p-5 shadow-xs space-y-3 text-xs bg-rose-50/20">
                        <div class="flex items-center justify-between border-b border-rose-100 pb-2.5">
                            <span class="font-bold text-rose-900 flex items-center gap-1.5">
                                <span>⭐</span>
                                <span>الاستبيان المرتبط</span>
                            </span>
                            @can('view', $complaint->survey)
                                <a href="{{ route('surveys.show', $complaint->survey) }}"
                                    class="text-rose-700 hover:underline font-bold text-[11px]">
                                    عرض الاستبيان ➜
                                </a>
                            @endcan
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">درجة التقييم:</span>
                                <span class="font-bold text-rose-700 text-sm">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <span>{{ $i <= $complaint->survey->rating ? '★' : '☆' }}</span>
                                    @endfor
                                    ({{ $complaint->survey->rating }}/5)
                                </span>
                            </div>

                            @if ($complaint->survey->visit)
                                <div
                                    class="flex items-center justify-between pt-1 border-t border-rose-100/60 font-mono text-[11px]">
                                    <span class="text-slate-500">الزيارة المقترنة:</span>
                                    <a href="{{ route('visits.show', $complaint->survey->visit) }}"
                                        class="text-[#00685f] hover:underline font-bold">
                                        زيارة #{{ $complaint->survey->visit->id }}
                                    </a>
                                </div>
                            @endif

                            @if ($complaint->survey->comment)
                                <div class="pt-1 border-t border-rose-100/60">
                                    <span class="text-slate-400 block text-[10px] mb-0.5">ملاحظة المريض في
                                        الاستبيان:</span>
                                    <p class="text-slate-700 italic text-[11px]">"{{ $complaint->survey->comment }}"</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Assignee Card -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-3 text-xs">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                        <span class="font-bold text-slate-800">المحقق المسؤول (Assignee)</span>
                    </div>

                    @if ($complaint->assignee)
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-full bg-purple-100 text-purple-800 flex items-center justify-center font-bold text-sm">
                                {{ mb_substr($complaint->assignee->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="font-bold text-sm text-slate-900">{{ $complaint->assignee->name }}</div>
                                <div class="text-slate-500 text-[11px]">
                                    {{ $complaint->assignee->roleLabels('مدقق جودة وخدمة عملاء') }}</div>
                            </div>
                        </div>
                    @else
                        <div class="p-4 bg-slate-50 rounded-xl text-center space-y-2">
                            <p class="text-slate-500 text-xs">لم يتم إسناد هذه الشكوى إلى محقق بعد</p>
                            <button type="button" onclick="document.getElementById('advance_assigned_to').focus()"
                                class="px-3 py-1 bg-purple-700 text-white rounded-lg text-xs font-bold hover:bg-purple-800 transition">
                                + تعيين محقق الآن
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function onStatusTransitionChange(status) {
            const resContainer = document.getElementById('resolutionNoteContainer');
            const asterisk = document.getElementById('resNoteRequiredAsterisk');
            const resInput = document.getElementById('resolution_note');

            if (resContainer) {
                if (status === 'resolved' || status === 'closed') {
                    resContainer.classList.remove('hidden');
                    if (asterisk) asterisk.classList.remove('hidden');
                    if (resInput) resInput.setAttribute('required', 'required');
                } else {
                    if (asterisk) asterisk.classList.add('hidden');
                    if (resInput) resInput.removeAttribute('required');
                }
            }
        }
    </script>
@endsection
