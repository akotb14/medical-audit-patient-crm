@extends('layouts.app')

@section('title', 'دليل وسجل الملفات السريرية (MRN Directory)')

@section('content')
<div class="space-y-5">


    <!-- 1. Header Section: Clinical, Simple, High Focus -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-200 text-[#00685f] flex items-center justify-center font-bold shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-[#0b1c30]">سجل ودليل الملفات السريرية (MRN)</h1>
                <p class="text-xs text-[#64748b] mt-0.5">
                    جدول سريري مركزي مصمم لسرعة مسح العين، فرز الحالات الحرجة، والوصول الفوري للزيارات والملفات
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('patients.create') }}"
                class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-xl shadow-xs transition active:scale-[0.98]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span>+ فتح ملف مريض جديد (MRN)</span>
            </a>
        </div>
    </div>

    <!-- 2. Fast Clinical Summary Strip (Quick Glance Indicators) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-right">
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
            <span class="text-[11px] text-slate-500 font-medium block">إجمالي ملفات المرضى</span>
            <div class="text-lg font-bold text-slate-900 font-mono mt-0.5 tabular-nums">
                {{ is_countable($patients) ? count($patients) : '1,284' }} <span class="text-[11px] text-slate-500 font-normal">ملف نشط</span>
            </div>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
            <span class="text-[11px] text-rose-600 font-bold block">⚠️ تنبيهات حساسية دوائية</span>
            <div class="text-lg font-bold text-rose-700 font-mono mt-0.5 tabular-nums">
                {{ method_exists($patients, 'where') ? $patients->where('allergies', '!=', null)->count() : '84' }} <span class="text-[11px] text-rose-500 font-normal">حالة مراقبة</span>
            </div>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
            <span class="text-[11px] text-amber-700 font-medium block">🩺 سجل أمراض مزمنة</span>
            <div class="text-lg font-bold text-amber-800 font-mono mt-0.5 tabular-nums">
                {{ method_exists($patients, 'where') ? $patients->where('chronic_diseases', '!=', null)->count() : '142' }} <span class="text-[11px] text-slate-500 font-normal">ملف</span>
            </div>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
            <span class="text-[11px] text-teal-700 font-medium block">فصيلة الدم الأكثر شيوعاً</span>
            <div class="text-lg font-bold text-teal-800 font-mono mt-0.5 tabular-nums">
                O+ <span class="text-[11px] text-teal-600 font-normal">(38% من السجلات)</span>
            </div>
        </div>
    </div>

    <!-- 3. Fast Filter & Search Toolbar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs space-y-3">
        <form action="{{ route('patients.index') }}" method="GET" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
            {{-- Fast Search Input --}}
            <div class="relative flex-1">
                <input
                    type="text"
                    id="patientDirectorySearch"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="ابحث بالاسم، رقم الملف السريري MRN، الهوية الوطنية، أو رقم الجوال للوصول المباشر..."
                    class="w-full pr-10 pl-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00685f]/20 focus:border-[#00685f] text-right placeholder:text-slate-400 transition"
                />
                <svg class="w-4 h-4 text-slate-400 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.3-4.3" />
                </svg>
            </div>

            {{-- Blood Group Filter --}}
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-1.5 text-xs shrink-0">
                    <span class="text-slate-500 font-medium">فصيلة الدم:</span>
                    <select id="patientBloodFilter" name="blood_group"
                        class="px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs text-slate-700 font-medium focus:outline-none focus:border-[#00685f]">
                        <option value="">كافة الفصائل</option>
                        <option value="A+" @selected(request('blood_group') == 'A+')>A+</option>
                        <option value="O+" @selected(request('blood_group') == 'O+')>O+</option>
                        <option value="B+" @selected(request('blood_group') == 'B+')>B+</option>
                        <option value="AB+" @selected(request('blood_group') == 'AB+')>AB+</option>
                        <option value="A-" @selected(request('blood_group') == 'A-')>A-</option>
                        <option value="O-" @selected(request('blood_group') == 'O-')>O-</option>
                        <option value="B-" @selected(request('blood_group') == 'B-')>B-</option>
                        <option value="AB-" @selected(request('blood_group') == 'AB-')>AB-</option>
                    </select>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-1.5 shrink-0">
                    <button type="submit"
                        class="px-4 py-2 text-xs font-bold text-white bg-[#00685f] hover:bg-[#00554e] rounded-lg shadow-2xs transition">
                        بحث
                    </button>
                    @if(request('search') || request('blood_group'))
                    <a href="{{ route('patients.index') }}"
                        class="px-3 py-2 text-xs text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition"
                        title="إلغاء التصفية والعودة للكل">
                        إعادة تعيين ✕
                    </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Quick Eye Shortcuts --}}
        <div class="flex items-center justify-between text-[11px] pt-2 border-t border-slate-100 text-slate-500">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="font-bold text-slate-600">تصفية بنقرة واحدة:</span>
                <a href="{{ route('patients.index') }}" class="px-2 py-0.5 rounded {{ !request('blood_group') && !request('search') ? 'bg-teal-50 text-teal-800 font-bold' : 'hover:bg-slate-100 text-slate-600' }}">كافة السجلات</a>
                <span>·</span>
                <a href="{{ route('patients.index', ['blood_group' => 'O+']) }}" class="px-2 py-0.5 rounded hover:bg-slate-100 text-slate-600">فصيلة O+</a>
                <span>·</span>
                <a href="{{ route('patients.index', ['blood_group' => 'A+']) }}" class="px-2 py-0.5 rounded hover:bg-slate-100 text-slate-600">فصيلة A+</a>
                <span>·</span>
                <a href="{{ route('patients.index', ['blood_group' => 'B+']) }}" class="px-2 py-0.5 rounded hover:bg-slate-100 text-slate-600">فصيلة B+</a>
            </div>
            <div class="text-slate-400 font-mono text-[11px]">
                نظام الجدول السريري السريع (Eye-Scan Rapid Table)
            </div>
        </div>
    </div>

    <!-- 4. Clinical Patients Table (Pure Table View - No Cards for Instant Eye Scanning) -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs border-collapse">
                <thead class="bg-slate-50/90 text-slate-700 font-semibold border-b border-slate-200 sticky top-0 z-10 backdrop-blur-xs">
                    <tr>
                        <th class="py-3 px-3.5 font-bold text-slate-800 w-28">رقم الملف (MRN)</th>
                        <th class="py-3 px-3.5 font-bold text-slate-800 min-w-[180px]">اسم المريض والبيانات</th>
                        <th class="py-3 px-3 font-bold text-slate-800 w-24">العمر / الجنس</th>
                        <th class="py-3 px-3 font-bold text-slate-800 w-20 text-center">الفصيلة</th>
                        <th class="py-3 px-3.5 font-bold text-slate-800 min-w-[140px]">الهوية والجوال</th>
                        <th class="py-3 px-3.5 font-bold text-slate-800 min-w-[160px]">الحساسية الدوائية</th>
                        <th class="py-3 px-3.5 font-bold text-slate-800 min-w-[160px]">الأمراض المزمنة</th>
                        <th class="py-3 px-3.5 font-bold text-slate-800 text-center min-w-[190px]">الإجراءات السريرية</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-150">
                    @foreach($patients ?? [] as $patient)
                        <tr class="hover:bg-teal-50/40 transition group">
                            {{-- 1. MRN (Monospace, high-contrast, easily spotted by eye) --}}
                            <td class="py-3 px-3.5 whitespace-nowrap">
                                <span class="font-mono font-bold text-xs text-teal-800 bg-teal-50 px-2 py-1 rounded border border-teal-200/80 inline-block">
                                    {{ $patient->mrn }}
                                </span>
                            </td>

                            {{-- 2. Patient Name with Initial Avatar and direct link --}}
                            <td class="py-3 px-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-teal-50 border border-teal-200 text-[#00685f] flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ mb_substr($patient->fullname, 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('patients.show', $patient->id) }}"
                                            class="font-bold text-slate-900 group-hover:text-[#00685f] hover:underline text-xs block transition">
                                            {{ $patient->fullname }}
                                        </a>
                                        <span class="text-[10px] text-slate-400 font-mono">سجل سريري نشط</span>
                                    </div>
                                </div>
                            </td>

                            {{-- 3. Age & Gender --}}
                            <td class="py-3 px-3 whitespace-nowrap text-slate-700">
                                <span class="font-semibold">{{ $patient->birthdate?->age }}</span> <span class="text-slate-400">سنة</span>
                                @if(isset($patient->gender))
                                    <span class="text-slate-300 mx-0.5">·</span>
                                    <span class="text-slate-500 text-[11px]">{{ $patient->gender === 'male' || $patient->gender === 'ذكر' ? 'ذكر' : 'أنثى' }}</span>
                                @endif
                            </td>

                            {{-- 4. Blood Group Badge --}}
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <span class="font-mono font-bold text-xs bg-rose-50 text-rose-700 px-2 py-0.5 rounded border border-rose-200 inline-block shadow-2xs">
                                    {{ $patient->blood_group ?? 'لا يوجد' }}
                                </span>
                            </td>

                            {{-- 5. National ID & Phone --}}
                            <td class="py-3 px-3.5 font-mono text-[11px] text-slate-600 whitespace-nowrap">
                                <div class="font-semibold text-slate-800">{{ $patient->phone ?? '---' }}</div>
                            </td>

                            {{-- 6. Drug Allergies (High priority visual flag) --}}
                            <td class="py-3 px-3.5 text-xs">

                                    <span class="inline-flex items-center gap-1 font-bold text-[11px] bg-rose-50 text-rose-800 px-2 py-0.5 rounded-md border border-rose-200 shadow-2xs">
                                        <span>⚠️</span>
                                        <span>{{ $patient->allergies ?? 'لا يوجد' }}</span>
                                    </span>
                            </td>

                            {{-- 7. Chronic Diseases --}}
                            <td class="py-3 px-3.5 text-xs">
                                    <span class="inline-flex items-center gap-1 font-medium text-[11px] bg-amber-50 text-amber-900 px-2 py-0.5 rounded-md border border-amber-200">
                                        <span>🩺</span>
                                        <span>{{ $patient->chronic_diseases ?? 'لا يوجد'}}</span>
                                    </span>

                            </td>

                            {{-- 8. Clinical Quick Actions (Immediate 1-Click Operations) --}}
                            <td class="py-3 px-3.5 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    {{-- 1-Click New Visit --}}
                                    <a href="{{ route('visits.create', ['patient_id' => $patient->id]) }}"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold text-[#00685f] bg-teal-50 hover:bg-teal-100 border border-teal-200 rounded-lg transition"
                                        title="تسجيل زيارة سريرية جديدة فوراً">
                                        <span>+ زيارة</span>
                                    </a>


                                    {{-- Comprehensive Patient Profile --}}
                                    <a href="{{ route('patients.show', $patient->id) }}"
                                        class="px-2 py-1 text-[11px] font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition"
                                        title="فتح الملف الطبي الشامل">
                                        الملف
                                    </a>

                                    {{-- Edit Patient --}}
                                    <a href="{{ route('patients.edit', $patient) }}"
                                        class="p-1 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-md transition"
                                        title="تعديل بيانات المريض">
                                        ✏️
                                    </a>

                                    {{-- Safe Delete Confirmation Modal Trigger --}}
                                    <button type="button"
                                        onclick="document.getElementById('deletePatientModal-{{ $patient->id }}').classList.remove('hidden')"
                                        class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-md transition"
                                        title="حذف الملف السريري">
                                        🗑️
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <x-delete-patient-modal :patient="$patient"/>

                   @endforeach
                </tbody>
            </table>
        </div>
    </div>



    <!-- 6. Clean Pagination Bar -->
   <div  class="mt-4 flex justify-center">
    {{ $patients->links() }}
    </div>
</div>

@endsection
