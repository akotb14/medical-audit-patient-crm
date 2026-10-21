@props(['visit'])
<div id="completeVisitWithFollowupModal-{{$visit->id}}" class="fixed inset-0 z-50 overflow-y-auto hidden flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs ">
      <div class="relative bg-white rounded-2xl shadow-2xl border border-emerald-200 w-full max-w-lg overflow-hidden z-10 text-right my-8 p-4">
        <div class="p-5 border-b border-emerald-100 bg-gradient-to-r from-emerald-800 to-teal-700 text-white flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center font-bold text-lg">✓</div>
            <div>
              <h3 class="font-bold text-base">إتمام الزيارة الطبية وجدولة المتابعة السريرية</h3>
              <p class="text-xs text-emerald-100 mt-0.5">
                <span id="completeVisitModalVisitSpan" class="font-mono font-bold">زيارة #{{$visit->id}}</span>
                <span>• للمريض: </span>
                <span id="completeVisitModalPatientSpan" class="font-bold">{{$visit->patient?->fullname .' - '. $visit->patient?->mrn}}</span>
              </p>
            </div>
          </div>
          <button type="button" onclick="document.getElementById('completeVisitWithFollowupModal-{{$visit->id}}').classList.add('hidden')" class="p-1 text-emerald-100 hover:text-white">✕</button>
        </div>

        <form id="completeVisitWithFollowup-{{$visit->id}}Form" action="{{route('visits.status',$visit)}}" method="POST" class="p-5 space-y-4 text-xs" >
          @csrf
            @method('PATCH')
            <input type="hidden" name="visit_status" value="completed">
          <!-- Clinical Notice -->
          <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-900 space-y-1">
            <div class="font-bold flex items-center gap-1.5 text-xs text-emerald-800">
              <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span>توثيق إتمام المراجعة واستمرارية الرعاية (Care Continuity)</span>
            </div>
            <p class="text-[11px] text-emerald-700 leading-relaxed">
              سيتم نقل حالة الزيارة إلى <span class="font-bold text-emerald-800">مكتملة (completed)</span> وتوليد سجل متابعة سريرية تلقائي في جدول <code class="font-mono bg-white px-1 py-0.2 rounded border border-emerald-200 font-bold">follow_ups</code>.
            </p>
          </div>

          <!-- Reason summary -->
          <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 space-y-1">
            <span class="text-slate-400 block text-[11px]">سبب الزيارة المسجل:</span>
            <p id="completeVisitModalReasonText" class="text-slate-800 font-medium text-xs leading-relaxed"></p>
          </div>

          <!-- Follow-up Creation Toggle -->
          <label class="flex items-start gap-3 p-3 bg-teal-50/50 border border-teal-200 rounded-xl cursor-pointer hover:bg-teal-50 transition">
            <input
              type="checkbox"
              id="completeVisitCreateFollowupCheckbox"
              checked
              onchange="document.getElementById('completeVisitFollowupSection').classList.toggle('hidden', !this.checked)"
              class="w-4 h-4 text-[#00685f] rounded border-slate-300 focus:ring-[#00685f] mt-0.5"
            />
            <div>
              <span class="font-bold text-[#0b1c30] block text-xs">إنشاء متابعة سريرية مجدولة (Create follow_ups record)</span>
              <span class="text-[11px] text-[#64748b]">ربط تلقائي مع visit_id و patient_id بحالة scheduled</span>
            </div>
          </label>

          <!-- Follow-up Fields -->
          <div id="completeVisitFollowupSection" class="space-y-3.5 pt-1">
            <div>
              <div class="flex items-center justify-between mb-1.5">
                <label class="font-bold text-slate-700">موعد استحقاق المتابعة (due_date):</label>
                <span class="text-[10px] text-teal-700 font-semibold">خيارات سريعة:</span>
              </div>
              <div class="flex items-center gap-1.5 mb-2">
                <button
                  type="button"
                  onclick="setCompleteVisitDueDate('completeVisitWithFollowupModal-{{$visit->id}}',3)"
                  class="px-2.5 py-1 text-[11px] font-semibold bg-white border border-slate-300 hover:border-teal-600 hover:text-teal-700 rounded-md transition"
                >
                  بعد 3 أيام
                </button>
                <button
                  type="button"
                  onclick="setCompleteVisitDueDate('completeVisitWithFollowupModal-{{$visit->id}}',7)"
                  class="px-2.5 py-1 text-[11px] font-semibold bg-teal-50 border border-teal-300 text-teal-800 rounded-md transition"
                >
                  بعد أسبوع (افتراضي)
                </button>
                <button
                  type="button"
                  onclick="setCompleteVisitDueDate('completeVisitWithFollowupModal-{{$visit->id}}',14)"
                  class="px-2.5 py-1 text-[11px] font-semibold bg-white border border-slate-300 hover:border-teal-600 hover:text-teal-700 rounded-md transition"
                >
                  بعد أسبوعين
                </button>
                <button
                  type="button"
                  onclick="setCompleteVisitDueDate('completeVisitWithFollowupModal-{{$visit->id}}',30)"
                  class="px-2.5 py-1 text-[11px] font-semibold bg-white border border-slate-300 hover:border-teal-600 hover:text-teal-700 rounded-md transition"
                >
                  بعد شهر
                </button>
              </div>
              <input
                type="datetime-local"
                name="due_date"
                value="{{ now()->addDays(7)->setTime(10, 0)->format('Y-m-d\TH:i')}}"
                id="completeVisitWithFollowupModal-{{$visit->id}}DueDate"
                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-mono focus:bg-white focus:ring-1 focus:ring-[#00685f]"
              />
            </div>

            <div>
              <label class="font-bold text-slate-700 block mb-1">السبب السريري للمتابعة (reason):</label>
              <textarea
                id="completeVisitFollowupReason"
                name="reason"
                rows="2"
                maxlength="500"
                class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:bg-white focus:ring-1 focus:ring-[#00685f]"
                placeholder="مراجعة الخطة العلاجية ونتائج الفحوصات..."
              ></textarea>
            </div>
          </div>

          <!-- Modal Footer Actions -->
          <div class="pt-3 border-t border-slate-200 flex items-center justify-between gap-2">
            <button
              type="submit"
              name="action"
              value="complete_only"
              class="px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition"
              title="إتمام الزيارة فقط دون إنشاء متابعة"
            >
              إتمام فقط (بدون متابعة)
            </button>
            <div class="flex items-center gap-2">
              <button
                type="button"
                onclick="document.getElementById('completeVisitWithFollowupModal-{{$visit->id}}').classList.add('hidden')"
                class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition"
              >
                إلغاء
              </button>
              <button
                type="submit"
                name="action"
                value="complete_with_followup"
                class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition flex items-center gap-1.5"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>إتمام وجدولة المتابعة</span>
              </button>
            </div>
          </div>
        </form>

    </div>
</div>
<script>
     window.setCompleteVisitDueDate = function(modalId, days) {
        const d = new Date();
        d.setDate(d.getDate() + days);
        d.setHours(10, 0, 0, 0);
        const iso = new Date(d.getTime() - (d.getTimezoneOffset() * 60000)).toISOString().slice(0, 16);
        const input = document.getElementById((modalId) + 'DueDate');
        if (input) input.value = iso;
    };
</script>
