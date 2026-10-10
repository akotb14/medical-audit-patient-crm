<!-- Delete Patient Confirmation Modal Component -->
<div id="deletePatientModal" class="fixed inset-0 z-50 overflow-hidden hidden flex items-center justify-center p-4">
    <!-- Backdrop -->
    <div onclick="document.getElementById('deletePatientModal').classList.add('hidden')"
        class="absolute inset-0 bg-[#0f172a]/50 backdrop-blur-xs"></div>

    <div
        class="relative bg-white rounded-2xl shadow-2xl border border-red-200 w-full max-w-md overflow-hidden z-10 text-right">
        <!-- Header -->
        <div class="p-5 border-b border-red-100 bg-red-50 flex items-center justify-between">
            <div class="flex items-center gap-2.5 text-red-700">
                <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center font-bold">
                    ⚠️
                </div>
                <div>
                    <h3 class="font-bold text-base text-red-900">تأكيد حذف الملف السريري</h3>
                    <p class="text-xs text-red-700">إجراء غير قابل للتراجع</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('deletePatientModal').classList.add('hidden')"
                class="p-1 text-slate-400 hover:text-slate-700">✕</button>
        </div>

        <!-- Body -->
        <div class="p-5 space-y-3 text-xs text-slate-700 leading-relaxed">
            <p>
                هل أنت متأكد من رغبتك في حذف ملف المريض <strong class="text-slate-900 font-bold">{{ $patient->name ??
                    'المريض' }}</strong> (رقم الملف: <span class="font-mono text-teal-800 font-bold">{{ $patient->mrn ??
                    'MRN' }}</span>)؟
            </p>
            <div class="bg-amber-50 border border-amber-200 p-3 rounded-lg text-amber-900 text-[11px]">
                <strong>تنبيه طبي وأمني:</strong> حذف الملف سيؤدي إلى أرشفة سجل المواعيد السابقة، ونتائج التحاليل،
                وتنبيهات الحساسية وفق متطلبات التوثيق الصحي.
            </div>
        </div>

        <!-- Footer Actions with DELETE form -->
        <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
            <button type="button" onclick="document.getElementById('deletePatientModal').classList.add('hidden')"
                class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-200 rounded-lg transition">
                إلغاء
            </button>
            <form action="{{ route('patients.destroy', $patient->id ?? 1) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="px-5 py-2 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm transition">
                    نعم، حذف الملف نهائياً
                </button>
            </form>
        </div>
    </div>
</div>