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
                هل أنت متأكد من رغبتك في حذف عيادة
                <span class="font-bold text-slate-700">{{ $clinic->name }}</span>؟
            </p>
        </div>

        <div class="bg-amber-50 p-3 rounded-lg border border-amber-200 text-xs text-amber-900 space-y-1">
            <p class="font-bold">تنبيه قبل الحذف:</p>
            <p class="text-[11px] text-amber-800">
                إذا كانت هذه العيادة مرتبطة بأطباء مسجّلين، فلن يُسمح بحذفها قبل إعادة تعيين هؤلاء الأطباء إلى عيادة أخرى.
            </p>
        </div>

        <form action="{{route('clinics.destroy', $clinic)}}" method="POST"
            class="pt-2 flex items-center justify-center gap-3">
            @csrf
            @method('DELETE')
            {{-- <input type="hidden" id="deleteClinicIdInput" name="id" value="{{$clinic->id}}" /> --}}
            <button type="submit"
            class="px-5 py-2 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm transition">
            تأكيد حذف العيادة
             </button>
            <button type="button" onclick="document.getElementById('deleteClinicModal-{{$clinic->id}}').classList.add('hidden')"
                class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                إلغاء التراجع
            </button>
        </form>
    </div>
</div>
