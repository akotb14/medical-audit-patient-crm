@extends('layouts.app')

@section('title', 'الملفات المؤرشفة')

@section('content')
    <div class="space-y-5">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 flex items-center justify-center shrink-0">
                    🗄️
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-[#0b1c30]">الملفات المؤرشفة</h1>
                    <p class="text-xs text-[#64748b] mt-0.5">
                        ملفات أُرشفت ولم تُحذف: السجل السريري محفوظ كاملاً، ويمكن استعادة الملف في أي وقت.
                    </p>
                </div>
            </div>

            <a href="{{ route('patients.index') }}"
                class="px-3 py-1.5 text-xs font-semibold text-slate-600 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition shrink-0">
                ← العودة لدليل المرضى
            </a>
        </div>

        <!-- Archived Files Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-right">
                    <thead class="bg-[#f8fafc] text-slate-500">
                        <tr>
                            <th class="py-3 px-4 font-semibold">رقم الملف (MRN)</th>
                            <th class="py-3 px-4 font-semibold">المريض</th>
                            <th class="py-3 px-4 font-semibold">الهاتف</th>
                            <th class="py-3 px-4 font-semibold">تاريخ الأرشفة</th>
                            <th class="py-3 px-4 font-semibold">أرشفة بواسطة</th>
                            <th class="py-3 px-4 font-semibold">إجراء</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($patients as $patient)
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-3 px-4 font-mono text-slate-600">{{ $patient->mrn }}</td>
                                <td class="py-3 px-4 font-bold text-slate-800">{{ $patient->fullname }}</td>
                                <td class="py-3 px-4 font-mono text-slate-600">{{ $patient->phone }}</td>
                                <td class="py-3 px-4 text-slate-600">
                                    {{ $patient->deleted_at?->format('Y-m-d H:i') ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-slate-600">{{ $patient->deleter?->name ?? '-' }}</td>
                                <td class="py-3 px-4">
                                    <form method="POST" action="{{ route('patients.restore', $patient) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="px-3 py-1.5 text-[11px] font-bold text-teal-800 bg-teal-50 border border-teal-200 hover:bg-teal-100 rounded-lg transition">
                                            استعادة الملف
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-10 px-4 text-center text-slate-400">
                                    لا توجد ملفات مؤرشفة حالياً.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if (isset($patients) && method_exists($patients, 'links'))
                <div class="p-4 border-t border-slate-100 bg-[#f8fafc]">
                    {{ $patients->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection