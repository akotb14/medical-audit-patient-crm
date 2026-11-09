@extends('layouts.app')

@section('title', 'تفاصيل الزيارة السريرية #' . $visit->id)

@section('content')
    <div class="space-y-6">
        <!-- Breadcrumb & Back Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                    <a href="{{ route('visits.index') }}" class="hover:text-teal-700 transition">سجل الزيارات</a>
                    <span>/</span>
                    <span class="text-slate-800 font-semibold">تفاصيل الزيارة السريرية </span>
                </nav>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-[#0b1c30]">سجل الزيارة الطبية </h1>
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $visit->status_badge_classes }}">
                        {{ $visit->status_label }}
                    </span>
                </div>
                <p class="text-xs text-[#64748b] mt-1">تاريخ المراجعة: <span
                        class="font-mono font-bold text-slate-800">{{ $visit->date ? $visit->date->format('Y-m-d') : '-' }}</span>
                </p>
            </div>

            <!-- Quick Status Change & Navigation Buttons -->
            <div class="flex items-center gap-2 flex-wrap">

                @can('update', $visit)
                    <a href="{{ route('visits.edit', $visit) }}"
                        class="px-3.5 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition shadow-xs flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        <span>تعديل</span>
                    </a>
                @endcan

                <button type="button" onclick="window.print()"
                    class="px-3.5 py-2 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition shadow-xs">
                    طباعة الملخص
                </button>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left: Reason for Visit & Clinical Records (7 Cols) -->
            <div class="lg:col-span-7 space-y-6">
                <!-- Reason Card -->
                <div class="bg-white rounded-2xl border border-[#e2e8f0] p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div
                                class="w-8 h-8 rounded-lg bg-teal-100 text-[#00685f] flex items-center justify-center font-bold text-sm">
                                📝
                            </div>
                            <div>
                                <h3 class="font-bold text-base text-[#0b1c30]">سبب الزيارة والشكوى الطبية (Reason)</h3>
                                <p class="text-[11px] text-[#64748b]">التوثيق السريري للمراجع (حتى 500 حرف)</p>
                            </div>
                        </div>
                        <span class="text-xs font-mono text-slate-400">
                            {{ mb_strlen($visit->reason ?? '') }} / 500 حرف
                        </span>
                    </div>

                    <div
                        class="bg-slate-50 border-r-4 border-[#00685f] p-4 rounded-lg text-slate-800 text-sm leading-relaxed">
                        {{ $visit->reason ?: 'لم يتم تدوين سبب الزيارة.' }}
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2 text-xs text-slate-600">
                        <div class="p-3 bg-white border border-slate-200 rounded-xl">
                            <span class="text-slate-400 block text-[11px] mb-1">تاريخ توثيق الزيارة:</span>
                            <span
                                class="font-mono font-bold text-slate-800">{{ $visit->date ? $visit->date->format('Y-m-d') : '-' }}</span>
                        </div>
                        <div class="p-3 bg-white border border-slate-200 rounded-xl">
                            <span class="text-slate-400 block text-[11px] mb-1">حالة السجل الحالية:</span>
                            <span class="font-bold text-teal-800">{{ $visit->status_label }}</span>
                        </div>
                    </div>
                </div>


            </div>

            <!-- Right: Linked Patient & Doctor Cards (5 Cols) -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Linked Patient Card -->
                <div class="bg-white rounded-2xl border border-[#e2e8f0] p-5 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-sm">👤</span>
                            <h4 class="font-bold text-xs text-slate-800 uppercase tracking-wider">بيانات المريض (Patient)
                            </h4>
                        </div>
                        @if ($visit->patient && ! $visit->patient->trashed())
                            <a href="{{ route('patients.show', $visit->patient) }}"
                                class="text-xs font-bold text-[#00685f] hover:underline">
                                عرض الملف الطبي ➜
                            </a>
                        @endif
                    </div>

                    @if ($visit->patient)
                        <div class="space-y-3 text-xs">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-teal-100 text-teal-800 flex items-center justify-center font-bold text-sm">
                                    {{ mb_substr($visit->patient->fullname, 0, 1) }}
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm text-slate-900">{{ $visit->patient->fullname }}
                                        <x-archived-badge :patient="$visit->patient" /></h4>
                                    <span
                                        class="font-mono text-teal-700 font-bold text-xs">{{ $visit->patient->mrn }}</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">

                                <div>
                                    <span class="text-slate-400 block text-[11px]">رقم الهاتف:</span>
                                    <span
                                        class="font-mono text-teal-800 font-bold">{{ $visit->patient->phone ?: 'غير مسجل' }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4 text-xs text-slate-400 italic">
                            لم يتم تعيين مريض لهذه الزيارة
                        </div>
                    @endif
                </div>

                <!-- Linked Doctor & Clinic Card -->
                <div class="bg-white rounded-2xl border border-[#e2e8f0] p-5 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-sm">👨‍⚕️</span>
                            <h4 class="font-bold text-xs text-slate-800 uppercase tracking-wider">الطبيب المعالج والعيادة
                                (Doctor)</h4>
                        </div>
                        @if ($visit->doctor)
                            <a href="{{ route('doctors.show', $visit->doctor) }}"
                                class="text-xs font-bold text-blue-600 hover:underline">
                                الملف المهني ➜
                            </a>
                        @endif
                    </div>

                    @if ($visit->doctor)
                        <div class="space-y-3 text-xs">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-blue-100 text-blue-800 flex items-center justify-center font-bold text-sm">
                                    {{ mb_substr($visit->doctor->fullname, 0, 1) }}
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm text-slate-900">{{ $visit->doctor->fullname }}</h4>
                                    <span
                                        class="text-slate-500 text-xs">{{ $visit->doctor->specialty ?: 'التخصص غير مسجل' }}</span>
                                </div>
                            </div>

                            @if ($visit->doctor->clinic)
                                <div class="p-3 bg-blue-50/50 rounded-xl border border-blue-100 space-y-1">
                                    <div class="text-[11px] text-blue-700 font-semibold">العيادة التابعة:</div>
                                    <div class="font-bold text-slate-800">{{ $visit->doctor->clinic->name }}</div>
                                </div>
                            @else
                                <div class="text-center py-3 text-xs text-slate-400 italic">
                                    لا توجد عيادة مرتبطة بهذا الطبيب.
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-4 text-xs text-slate-400 italic">
                            لم يتم تعيين طبيب لهذه الزيارة
                        </div>
                    @endif
                </div>

                <!-- Creator & Metadata Card -->
                @can('viewAny', App\Models\User::class)
                    <div class="bg-white rounded-2xl border border-[#e2e8f0] p-5 shadow-xs space-y-3 text-xs">
                        <h4 class="font-bold text-xs text-slate-800 border-b border-slate-100 pb-2">سجل التوثيق والإنشاء (Audit)
                        </h4>
                        <div class="space-y-2 text-slate-600">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">أنشئ بواسطة:</span>
                                <span
                                    class="font-bold text-slate-800">{{ $visit->creator?->name ?? 'غير معروف' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">تاريخ الإنشاء:</span>
                                <span
                                    class="font-mono text-slate-700">{{ $visit->created_at ? $visit->created_at->format('Y-m-d H:i') : '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">آخر تحديث:</span>
                                <span
                                    class="font-mono text-slate-700">{{ $visit->updated_at ? $visit->updated_at->format('Y-m-d H:i') : '-' }}</span>
                            </div>
                        </div>
                    </div>
                @endcan
            </div>
        </div>
    </div>
@endsection
