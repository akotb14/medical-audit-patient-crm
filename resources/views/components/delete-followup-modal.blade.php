@props(['followup'])
<div id="deleteFollowupModal-{{ $followup->id}}" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs hidden">
    <div class="bg-white w-full p-5 max-w-md rounded-2xl shadow-xl border border-slate-200 overflow-hidden transform transition-all text-right animate-in fade-in zoom-in-95 duration-150">
        <div class="p-5 border-b border-slate-100 bg-red-50/50 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center font-bold text-sm">
                    ⚠️
                </div>
                <div>
                    <h3 class="font-bold text-sm text-[#0b1c30]">تأكيد حذف سجل المتابعة السريرية</h3>
                    <p class="text-[11px] text-[#64748b]">إجراء لا يمكن التراجع عنه في سجل المتابعات</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('deleteFollowupModal-{{$followup->id}}').classList.add('hidden')" class="w-7 h-7 flex items-center justify-center text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-200/50 text-base">
                ✕
            </button>
        </div>

        <form action="{{route('follow_ups.destroy', $followup)}}" method="POST" class="p-6 space-y-4 text-xs">
            @csrf
            @method('DELETE')

            <p class="text-slate-700 leading-relaxed">
                هل أنت متأكد من رغبتك في حذف سجل المتابعة السريرية رقم
                <span class="font-mono font-bold text-red-600">#{{$followup->id}}</span> للمريض
                <strong class="text-slate-900">{{$followup->patient?->fullname ?? 'غير معروف'}}</strong>؟
            </p>

            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 text-[11px] leading-relaxed">
                💡 ملاحظة: إذا كان الهدف هو عدم الحضور أو عدم الرد، يُفضل تغيير حالة المتابعة إلى <strong>فائتة</strong> أو <strong>ملغاة</strong> بدلاً من الحذف للحفاظ على السجل الطبي والتدقيق السريري.
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-start gap-2.5">
                <button type="submit" class="px-5 py-2 font-bold text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>تأكيد الحذف نهائياً</span>
                </button>
                <button type="button" onclick="document.getElementById('deleteFollowupModal-{{$followup->id}}').classList.add('hidden')" class="px-4 py-2 font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                    إلغاء التراجع
                </button>
            </div>
        </form>
    </div>
</div>
