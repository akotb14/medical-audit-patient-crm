<!-- Modal: حذف العيادة التخصصية مع فحص علاقة الأطباء -->
@props(['clinic'])
<div id="deleteClinicModal-{{$clinic->id}}"
    class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4 text-right">
        <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center mx-auto text-xl">
            ⚠️
        </div>
        <div class="text-center">
            <h3 class="text-base font-bold text-slate-800">تأكيد حذف العيادة</h3>
            <p class="text-xs text-slate-500 mt-1">
                هل أنت متأكد من رغبتك في حذف عيادة <span id="deleteClinicNameTarget"
                    class="font-bold text-slate-700"></span> (<span id="deleteClinicCodeTarget"
                    class="font-mono text-slate-600"></span>)؟
            </p>
        </div>

        <div class="bg-amber-50 p-3 rounded-lg border border-amber-200 text-xs text-amber-900 space-y-1">
            <p class="font-bold">⚠️ تنبيه قيود العلاقة (Integrity Constraint):</p>
            <p class="text-[11px] text-amber-800">
                في حال وجود أطباء تابعين لهذه العيادة (<span id="deleteClinicDoctorsCountTarget"
                    class="font-bold">0</span> طبيب)، سيتم منع الحذف أو يلزم إعادة تعيينهم لعيادة أخرى أولاً.
            </p>
        </div>

        <form id="deleteClinicForm" action="{{route('clinics.destroy', $clinic)}}" method="POST"
            class="pt-2 flex items-center justify-center gap-3">
            @csrf
            @method('DELETE')
            <input type="hidden" id="deleteClinicIdInput" name="clinic_id" value="" />
            <button type="button" onclick="window.clinicalApp.closeModal('deleteClinicModal-{{$clinic->id}}')"
                class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                إلغاء التراجع
            </button>
            <button type="submit"
                class="px-5 py-2 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm transition">
                تأكيد حذف العيادة
            </button>
        </form>
    </div>
</div>