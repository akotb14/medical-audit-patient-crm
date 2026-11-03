@props(['visits'])
<div class="overflow-x-auto">
    <table class="w-full text-right text-sm">
        <thead class="bg-[#f8fafc] text-[#64748b] text-xs font-semibold uppercase border-b border-[#e2e8f0]">
            <tr>
                <th class="py-3 px-4 w-12 text-center">#</th>
                <th class="py-3 px-4">المريض ورقم الملف (MRN)</th>
                <th class="py-3 px-4">الطبيب المعالج والعيادة</th>
                <th class="py-3 px-4">تاريخ الزيارة</th>
                <th class="py-3 px-4 w-[220px]">سبب الزيارة (Reason)</th>
                <th class="py-3 px-4">حالة الزيارة (Status)</th>
                <th class="py-3 px-4">مسجل الزيارة</th>
                <th class="py-3 px-4">استبيان المريض</th>
                <th class="py-3 px-4 text-center">إجراءات</th>
            </tr>
        </thead>
        <tbody id="visitsTableBody" class="divide-y divide-[#e2e8f0]">
            @if (isset($visits))
                @foreach ($visits ?? [] as $visit)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 text-center font-mono text-xs text-slate-500">{{ $visit->id }}</td>
                        <td class="py-3 px-4">
                            @if ($visit->patient)
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-8 h-8 rounded-full bg-teal-50 border border-teal-200 text-teal-800 flex items-center justify-center font-bold text-xs">
                                        {{ mb_substr($visit->patient->fullname, 0, 1) }}
                                    </div>
                                    <div>
                                        @if ($visit->patient->trashed())
                                            <span class="font-bold text-slate-900 block">
                                                {{ $visit->patient->fullname }}
                                            </span>
                                        @else
                                            <a href="{{ route('patients.show', $visit->patient) }}"
                                                class="font-bold text-slate-900 hover:text-teal-700 block">
                                                {{ $visit->patient->fullname }}
                                            </a>
                                        @endif
                                        <span class="text-[11px] font-mono text-slate-500">
                                            {{ $visit->patient->mrn }}
                                            <x-archived-badge :patient="$visit->patient" />
                                        </span>
                                    </div>
                                </div>
                            @else
                                <span class="text-xs text-slate-400 italic">مريض غير محدد </span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if ($visit->doctor)
                                <div class="text-xs">
                                    <div class="font-bold text-slate-800">{{ $visit->doctor->fullname }}</div>
                                    <span
                                        class="text-[11px] text-teal-700">{{ $visit->doctor->clinic?->name ?: 'بدون عيادة' }}</span>
                                </div>
                            @else
                                <span class="text-xs text-slate-400 italic">طبيب غير محدد </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-mono text-xs text-slate-700">
                            {{ $visit->date ? $visit->date->format('Y-m-d') : '-' }}
                        </td>
                        <td class="py-3 px-4">
                            <p class="text-xs text-slate-800 line-clamp-2 leading-relaxed" title="{{ $visit->reason }}">
                                {{ $visit->reason }}
                            </p>
                        </td>
                        <td class="py-3 px-4">
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold {{ $visit->status_badge_classes }}">
                                {{ $visit->status_label }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-xs text-slate-500">
                            {{ $visit->creator->name }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if ($visit->visit_status == 'completed' && isset($visit->survey))
                                <a href="{{ route('surveys.show', $visit->survey) }}"
                                    class="text-[13px] text-[#00685f] hover:underline font-bold block">

                                    استبيان الزيارة <span class="font-black text-[16px]">←</span>
                                </a>
                            @elseif ($visit->visit_status == 'completed' && !isset($visit->survey))
                                <button type="button"
                                    onclick={{"openCreateSurveyModal('createSurveyModal',$visit->id,true)"}}
                                    class="text-[12px]  text-[#00685f] hover:underline font-bold block">

                                    <span>+ تسجيل استبيان جديد </span>
                                </button>
                            @else
                                <span class="text-xs text-slate-400 italic">لم يتم إنهاء الزيارة </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="inline-flex items-center gap-1.5">

                                @can('updateStatus', $visit)
                                    @if ($visit->visit_status !== 'completed' && $visit->visit_status !== 'cancelled')
                                        <button type="button"
                                            onclick="document.getElementById('completeVisitWithFollowupModal-{{ $visit->id }}').classList.remove('hidden')"
                                            class="p-1.5 text-slate-600 hover:text-teal-700 hover:bg-slate-100 rounded-md transition">
                                            ✓
                                        </button>
                                        <form action="{{ route('visits.status', $visit) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="visit_status" value="cancelled">
                                            <button type="submit" name="action" value="cancel"
                                                class="px-2 py-1 text-[11px] font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg transition">
                                                الغاء
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                                @can('view', $visit)
                                    <a href="{{ route('visits.show', $visit) }}"
                                        class="p-1.5 text-slate-600 hover:text-teal-700 hover:bg-slate-100 rounded-md transition"
                                        title="عرض التفاصيل">
                                        👁️
                                    </a>
                                @endcan
                                @can('update', $visit)
                                    <a href="{{ route('visits.edit', $visit) }}"
                                        class="p-1.5 text-slate-600 hover:text-blue-700 hover:bg-slate-100 rounded-md transition"
                                        title="تعديل">
                                        ✏️
                                    </a>
                                @endcan
                                @can('delete', $visit)
                                    <button type="button"
                                        onclick="document.getElementById('deleteVisitModal-{{ $visit->id }}').classList.remove('hidden')"
                                        class="p-1.5 text-slate-400 hover:text-red-700 hover:bg-red-50 rounded-md transition"
                                        title="حذف">
                                        🗑️
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @can('updateStatus', $visit)
                        <x-complete-visit-with-followup-modal :visit="$visit" />
                    @endcan
                    @can('delete', $visit)
                        <x-delete-visit-modal :visit="$visit" />
                    @endcan
                @endforeach
            @endif
        </tbody>
    </table>
</div>
