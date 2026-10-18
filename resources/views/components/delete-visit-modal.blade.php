<!-- Modal: حذف أو أرشفة سجل الزيارة السريرية -->
@props(['visit'])
<div id="deleteVisitModal-{{ $visit->id }}" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-rose-100 text-right space-y-4">
        <div class="flex items-center gap-3 text-rose-700">
            <div class="w-10 h-10 rounded-xl bg-rose-100 flex items-center justify-center text-lg font-bold">
                ⚠️
            </div>
            <div>
                <h3 class="font-bold text-base text-slate-900">تأكيد حذف سجل الزيارة السريرية</h3>
                <p class="text-xs text-rose-600">إجراء يؤثر على سجل التاريخ الطبي للمريض</p>
            </div>
        </div>

        <div class="text-xs text-slate-600 leading-relaxed space-y-2">
            <p>
                هل أنت متأكد من رغبتك في حذف سجل الزيارة رقم: 
                <strong id="deleteVisitIdSpan" class="font-mono text-slate-900">#---</strong>؟
            </p>
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-[11px] text-amber-900 leading-relaxed">
                <strong>تنبيه السلامة السريرية:</strong> يتم حفظ وتفريغ قيود المفاتيح الأجنبية مع الجداول المرتبطة (<code class="font-mono">patients</code> و <code class="font-mono">doctors</code> و <code class="font-mono">users</code>) وفق قاعدة <code class="font-mono">nullOnDelete</code> دون كسر سلامة البيانات المرجعية.
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
            <button 
                type="button" 
                onclick="window.clinicalApp ? window.clinicalApp.closeModal('deleteVisitModal') : document.getElementById('deleteVisitModal-{{ $visit->id }}').classList.add('hidden')" 
                class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition"
            >
                إلغاء
            </button>
            <form id="deleteVisitForm" method="POST" action="{{route('visits.destroy' ,$visit)}}" class="inline">
                @csrf
                @method('DELETE')
                <button 
                    type="submit" 
                    id="confirmDeleteVisitBtn"
                    class="px-5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-lg shadow-sm transition"
                >
                    نعم، حذف الزيارة
                </button>
            </form>
        </div>
    </div>
</div>
