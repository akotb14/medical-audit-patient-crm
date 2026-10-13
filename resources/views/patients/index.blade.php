@extends('layouts.app')

@section('title', 'دليل المرضى والملفات السريرية (MRN Directory)')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    {{-- Success Message --}}
    <x-alerts />

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#0b1c30]">دليل وسجل الملفات السريرية (MRN)</h1>
            <p class="text-xs text-[#64748b] mt-1">
                إدارة سجلات المرضى المركزية، الحساسية الدوائية، الأمراض المزمنة، وتاريخ الزيارات السريرية
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('patients.create')}}"
                class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span>+ فتح ملف مريض جديد (MRN)</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div
        class="bg-white p-4 rounded-xl border border-[#e2e8f0] flex flex-wrap items-center justify-between gap-3 shadow-xs">
        <div class="relative flex-1 max-w-md">
            <input type="text" id="patientDirectorySearch" placeholder="بحث بالاسم، رقم الملف MRN، أو الهوية الوطنية..."
                class="w-full pl-3 pr-9 py-2 text-xs bg-[#f8fafc] border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#00685f] text-right" />
            <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8" />
                <path d="m21 21-4.3-4.3" />
            </svg>
        </div>
        <div class="flex items-center gap-2 text-xs">
            <span class="text-slate-500">فصيلة الدم:</span>
            <select id="patientBloodFilter"
                class="px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-slate-700">
                <option value="all">كافة الفصائل</option>
                <option value="A+">A+</option>
                <option value="O+">O+</option>
                <option value="B+">B+</option>
                <option value="AB+">AB+</option>
                <option value="O-">O-</option>
            </select>
        </div>
    </div>

    <!-- Patients Grid -->
    <div id="patientsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Rendered by Native JS in frontend prototype, and populated by Blade loop in backend -->
        @forelse($patients ?? [] as $patient)
        <div class="bg-white rounded-xl border border-[#e2e8f0] p-4.5 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-200 text-[#00685f] flex items-center justify-center font-bold text-sm">
                            {{ mb_substr($patient->fullname, 0, 1) }}
                        </div>
                        <div>
                            <a href="{{ route('patients.show', $patient->id) }}"
                                class="font-bold text-sm text-[#0b1c30] hover:text-[#00685f]">
                                {{ $patient->fullname }}
                            </a>
                            <div class="flex items-center gap-1.5 text-xs text-[#64748b] mt-0.5 font-mono">
                                <span class="font-bold text-teal-800">{{ $patient->mrn }}</span>
                                <span>·</span>
                                <span>{{ $patient->birthdate?->age }} سنة</span>
                            </div>
                        </div>
                    </div>
                    <span
                        class="font-mono font-bold text-xs bg-rose-50 text-rose-700 px-2 py-0.5 rounded border border-rose-200">
                        {{ $patient->blood_group }}
                    </span>
                </div>

                <div class="space-y-1.5 py-2 border-y border-slate-100 text-xs">
                    <div class="flex items-center gap-1">
                        <span class="text-[11px] font-bold text-rose-700">الحساسية:</span>
                        <span class="text-[11px] text-slate-700 truncate">{{$patient->allergies ? $patient->allergies :
                            'لا يوجد'
                            }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="text-[11px] font-bold text-slate-500">المزمن:</span>
                        <span class="text-[11px] text-slate-700 truncate">{{ $patient->chronic_diseases ?
                            $patient->chronic_diseases : 'لا يوجد'
                            }}</span>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 mt-3 flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                    <a href="{{ route('patients.show', $patient->id) }}"
                        class="text-[#00685f] hover:underline font-bold">
                        الملف الشامل
                    </a>
                    <span class="text-slate-300">|</span>
                    <a href="{{ route('patients.edit', $patient) }}"
                        class="text-slate-600 hover:text-slate-900 font-semibold">
                        تعديل
                    </a>
                </div>
                <button type="button"
                    onclick="document.getElementById('deletePatientModal-{{ $patient->id }}').classList.remove('hidden')"
                    class="text-red-600">
                    حذف
                </button>
            </div>
        </div>

        <x-delete-patient-modal :patient="$patient" />
        @empty
        <!-- Populated via native JS in SPA mode -->

        @endforelse
    </div>
    <div class="mt-6">
        {{$patients->links()}}
    </div>
</div>
@endsection
