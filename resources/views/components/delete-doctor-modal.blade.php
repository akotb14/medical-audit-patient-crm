@props(['doctor'])
<!-- Modal: حذف أو أرشفة ملف الطبيب -->
<div id="deleteDoctorModal-{{$doctor->id}}" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4 text-right">
        <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center mx-auto text-xl">
            👨‍⚕️
        </div>
        <div class="text-center">
            <h3 class="text-base font-bold text-slate-800">تأكيد حذف ملف الطبيب</h3>
            <p class="text-xs text-slate-500 mt-1">
                هل أنت متأكد من حذف الطبيب <span id="deleteDoctorNameTarget" class="font-bold text-slate-700"></span> التابع لعيادة <span id="deleteDoctorClinicTarget" class="font-bold text-teal-800"></span>؟
            </p>
        </div>

        <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 text-xs text-slate-600 space-y-1">
            <p class="font-bold">ملاحظة تنظيمية:</p>
            <p class="text-[11px]">
                سيتم فك ارتباط الطبيب بالعيادة ونقل المواعيد القادمة إلى طبيب مناوب آخر في نفس العيادة.
            </p>
        </div>

        <form id="deleteDoctorForm" action="{{route('doctors.destroy', $doctor)}}" method="POST" class="pt-2 flex items-center justify-center gap-3">
            @csrf
            @method('DELETE')
            {{-- <input type="hidden" id="deleteDoctorIdInput" name="id" value="{{$doctor->id}}" /> --}}
            <button
                type="button"
                onclick="document.getElementById('deleteDoctorModal-{{$doctor->id}}').classList.add('hidden')"
                class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition"
            >
                إلغاء
            </button>
            <button
                type="submit"
                class="px-5 py-2 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm transition"
            >
                تأكيد حذف الطبيب
            </button>
        </form>
    </div>
</div>
