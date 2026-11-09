@props(['followup'])
<div id="completeFollowupModal-{{ $followup->id }}"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs hidden">

    <div
        class="bg-white p-4 w-full max-w-lg rounded-2xl shadow-xl border border-slate-200 overflow-hidden transform transition-all text-right animate-in fade-in zoom-in-95 duration-150">
        <div class="p-5 border-b border-slate-100 bg-[#f8fafc] flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div
                    class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                    ✓
                </div>
                <div>
                    <h3 class="font-bold text-sm text-[#0b1c30]">توثيق نتيجة المتابعة السريرية</h3>
                    <p class="text-[11px] text-[#64748b]">تسجيل استجابة المريض وتحديث الحالة إلى «مكتملة»</p>
                </div>
            </div>
            <button type="button"
                onclick="document.getElementById('completeFollowupModal-{{ $followup->id }}').classList.add('hidden')"
                class="w-7 h-7 flex items-center justify-center text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-200/50 text-base">
                ✕
            </button>
        </div>

        <form action="{{ route('follow_ups.complete', $followup) }}" method="POST"
            class="p-6 space-y-4 text-xs">
            @csrf
            @method('PATCH')

            <div class="p-3 bg-emerald-50/50 border border-emerald-100 rounded-xl space-y-1">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 text-[11px]">رقم قيد المتابعة:</span>
                    <span class="font-mono font-bold text-emerald-800">#{{ $followup->id }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 text-[11px]">المريض:</span>
                    <span class="font-bold text-slate-800">{{ $followup->patient?->fullname ?? 'غير معروف' }}</span>
                </div>
            </div>

            <div>
                <label for="complete_outcome_note_{{ $followup->id }}" class="font-bold text-slate-700 block mb-1.5">
                    ملاحظات ونتيجة المتابعة (Outcome Note):
                    <span class="text-red-500">*</span>
                </label>
                <textarea name="outcome_note" id="complete_outcome_note_{{ $followup->id }}" rows="4" required
                    placeholder="مثال: تم الاتصال بالمريض هاتفياً، وأفاد بانخفاض الحرارة إلى 37.2 م وزوال الصداع. أكد استمراره في تناول الدواء في المواعيد المقررة دون أي أعراض جانبية..."
                    class="w-full p-3 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-600 text-slate-800 text-xs leading-relaxed"></textarea>
                <div class="flex items-center justify-between text-[11px] text-slate-400 mt-1">
                    <span>نماذج سريعة:</span>
                    <span data-outcome-counter>0 / 2000 حرف</span>
                </div>
            </div>

            <!-- Quick Template Chips -->
            <div class="flex flex-wrap gap-1.5 pt-1">
                <button type="button"
                    onclick="applyOutcomeTemplate('تم التواصل هاتفياً مع المريض وأفاد باستقرار الحالة وزوال الأعراض تماماً.' , 'completeFollowupModal-{{ $followup->id }}')"
                    class="px-2 py-1 text-[11px] bg-slate-100 hover:bg-emerald-50 hover:text-emerald-800 text-slate-600 rounded-md border border-slate-200 transition">
                    + استقرار الأعراض
                </button>
                <button type="button"
                    onclick="applyOutcomeTemplate('تم التحقق من نتائج الفحوصات المخبرية وإبلاغ المريض بسلامتها ومطابقتها للمعايير.' , 'completeFollowupModal-{{ $followup->id }}')"
                    class="px-2 py-1 text-[11px] bg-slate-100 hover:bg-emerald-50 hover:text-emerald-800 text-slate-600 rounded-md border border-slate-200 transition">
                    + فحص مخبري سليم
                </button>
                <button type="button"
                    onclick="applyOutcomeTemplate('أفاد المريض بوجود تحسن جزئي، وتم توجيهه لمراجعة العيادة لمتابعة الفحص السريري.' , 'completeFollowupModal-{{ $followup->id }}')"
                    class="px-2 py-1 text-[11px] bg-slate-100 hover:bg-emerald-50 hover:text-emerald-800 text-slate-600 rounded-md border border-slate-200 transition">
                    + توجيه للمراجعة
                </button>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button"
                    onclick="document.getElementById('completeFollowupModal-{{ $followup->id }}').classList.add('hidden')"
                    class="px-4 py-2 font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                    إلغاء
                </button>
                <button type="submit"
                    class="px-5 py-2 font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>حفظ النتيجة وإتمام المتابعة</span>
                </button>
            </div>
        </form>
    </div>
</div>
<script>
    if (typeof window.applyOutcomeTemplate === 'undefined') {
        // Apply Outcome Template
        window.applyOutcomeTemplate = function(template, modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) {
                return;
            }
            const textarea = modal.querySelector('textarea[name="outcome_note"]');
            if (!textarea) {
                return;
            }
            textarea.value = template;
            textarea.focus();

            textarea.dispatchEvent(new Event('input', {
                bubbles: true
            }));
        };

        // Count Outcome Note Characters (delegated: one modal per follow-up row)
        document.addEventListener('input', function(event) {
            const textarea = event.target;
            if (!textarea.matches || !textarea.matches('textarea[name="outcome_note"]')) {
                return;
            }
            const counter = textarea.closest('form')?.querySelector('[data-outcome-counter]');
            if (counter) {
                counter.textContent = `${textarea.value.length} / 2000 حرف`;
            }
        });
    }
</script>
