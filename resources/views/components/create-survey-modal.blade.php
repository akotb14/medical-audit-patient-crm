@props([
    'id' => 'createSurveyModal',
    'availableVisits' => [],
    'selectedVisit' => null,
    'isPage' => false,
])
@php
    $modalId = $id;
    $selectedVisitId = $selectedVisit->id ?? null;
    $selectedPatient = $selectedVisit->patient ?? null;
    $selectedDoctor = $selectedVisit->doctor ?? null;
@endphp

<div id="{{ $modalId }}"
    @if (!$isPage) class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs overflow-y-auto hidden"
        aria-labelledby="{{ $modalId }}Title"
        role="dialog"
        aria-modal="true"
    @else
        class="w-full max-w-2xl mx-auto" @endif>
    <!-- Backdrop dismiss for modal -->
    @if (!$isPage)
        <div class="fixed inset-0" onclick="closeCreateSurveyModal('{{ $modalId }}')"></div>
    @endif

    <div
        class="relative bg-white w-full max-w-lg rounded-2xl shadow-xl border border-slate-200 overflow-hidden transform transition-all text-right my-8 z-10 animate-in fade-in zoom-in-95 duration-150">
        <!-- Modal Header -->
        <div class="p-5 border-b border-slate-100 bg-[#f8fafc] flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-200 text-[#00685f] flex items-center justify-center font-bold text-lg shadow-2xs">
                    ⭐
                </div>
                <div>
                    <h3 id="{{ $modalId }}Title" class="font-bold text-base text-[#0b1c30]">
                        تسجيل استبيان رضا المريض
                    </h3>
                    <p class="text-xs text-[#64748b]">
                        تقييم تجربة المريض وتفعيل الشكاوى التلقائية للجودة
                    </p>
                </div>
            </div>

            @if (!$isPage)
                <button type="button" onclick="closeCreateSurveyModal('{{ $modalId }}')"
                    class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg text-lg transition"
                    title="إغلاق">
                    ✕
                </button>
            @endif
        </div>

        <!-- Form Body -->
        <form id="{{ $modalId }}Form" action="{{ route('surveys.store') }}" method="POST"
            class="p-6 space-y-4 text-xs">
            @csrf

            <!-- Dynamic Auto-Trigger Banner (when opened right after visit completion) -->
            <div id="{{ $modalId }}AutoTriggerBanner"
                class="p-3 bg-emerald-50 border border-emerald-300 rounded-xl text-emerald-900 text-xs flex items-center gap-2.5 hidden">
                <span class="text-base shrink-0">✨</span>
                <div>
                    <strong class="font-bold text-emerald-950">استبيان فوري ناتج عن إتمام الزيارة:</strong>
                    <p class="text-[11px] text-emerald-800 mt-0.5">تم إتمام الزيارة السريرية بنجاح وفتح استبيان الرضا
                        تلقائياً لقياس تجربة المريض وتطبيق معايير الجودة .</p>
                </div>
            </div>

            <!-- Explanatory Banner -->
            <div
                class="p-3 bg-sky-50 border border-sky-200 rounded-xl text-sky-900 text-[11px] leading-relaxed flex items-start gap-2">
                <svg class="w-4 h-4 text-sky-600 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    لا يمكن تسجيل استبيان مكرر لنفس الزيارة.
                </div>
            </div>

            <!-- Target Visit Selection or Display -->
            <div id="{{ $modalId }}VisitContainer">

                {{-- Fixed Visit ID --}}
                <div id="{{ $modalId }}FixedVisit" class="hidden">
                    <label class="font-bold">الزيارة المرتبطة:</label>
                    <strong id="{{ $modalId }}VisitText"></strong>
                </div>

                {{-- Actual Visit ID submitted to Laravel --}}
                <input type="hidden" name="visit_id" id="{{ $modalId }}HiddenVisitId">

            </div>

            <!-- Rating Selection (1-5 Integer) with Star Tiles -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block font-bold text-slate-700">
                        درجة رضا المريض العامة (من 1 إلى 5 نجوم): <span class="text-red-500">*</span>
                    </label>
                    <span class="text-[10px] text-teal-700 font-bold">حقل إلزامي</span>
                </div>

                <div class="grid grid-cols-5 gap-2 text-center" id="{{ $modalId }}RatingRadios">
                    <label class="cursor-pointer">
                        <input type="radio" name="rating" value="5" class="peer sr-only" checked
                            onchange="onModalSurveyRatingChanged('{{ $modalId }}', 5)" />
                        <div
                            class="p-2 border border-slate-200 rounded-xl peer-checked:bg-emerald-50 peer-checked:border-emerald-500 peer-checked:ring-1 peer-checked:ring-emerald-400 transition hover:bg-slate-50">
                            <div class="text-sm font-bold text-emerald-800">5 ★</div>
                            <div class="text-[10px] text-slate-500">ممتاز</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="rating" value="4" class="peer sr-only"
                            onchange="onModalSurveyRatingChanged('{{ $modalId }}', 4)" />
                        <div
                            class="p-2 border border-slate-200 rounded-xl peer-checked:bg-teal-50 peer-checked:border-teal-500 peer-checked:ring-1 peer-checked:ring-teal-400 transition hover:bg-slate-50">
                            <div class="text-sm font-bold text-teal-800">4 ★</div>
                            <div class="text-[10px] text-slate-500">جيد جداً</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="rating" value="3" class="peer sr-only"
                            onchange="onModalSurveyRatingChanged('{{ $modalId }}', 3)" />
                        <div
                            class="p-2 border border-slate-200 rounded-xl peer-checked:bg-amber-50 peer-checked:border-amber-500 peer-checked:ring-1 peer-checked:ring-amber-400 transition hover:bg-slate-50">
                            <div class="text-sm font-bold text-amber-800">3 ★</div>
                            <div class="text-[10px] text-slate-500">مقبول</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="rating" value="2" class="peer sr-only"
                            onchange="onModalSurveyRatingChanged('{{ $modalId }}', 2)" />
                        <div
                            class="p-2 border border-slate-200 rounded-xl peer-checked:bg-orange-50 peer-checked:border-orange-500 peer-checked:ring-1 peer-checked:ring-orange-400 transition hover:bg-slate-50">
                            <div class="text-sm font-bold text-orange-800">2 ★</div>
                            <div class="text-[10px] text-orange-700 font-bold">غير راضٍ</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="rating" value="1" class="peer sr-only"
                            onchange="onModalSurveyRatingChanged('{{ $modalId }}', 1)" />
                        <div
                            class="p-2 border border-slate-200 rounded-xl peer-checked:bg-rose-50 peer-checked:border-rose-500 peer-checked:ring-1 peer-checked:ring-rose-400 transition hover:bg-slate-50">
                            <div class="text-sm font-bold text-rose-800">1 ★</div>
                            <div class="text-[10px] text-rose-700 font-bold">سيء جداً</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Dynamic Trigger Warning Banner (visible when rating < 3) -->
            <div id="{{ $modalId }}Br6Alert"
                class="p-3 bg-rose-50 border border-rose-300 rounded-xl text-rose-900 text-xs hidden space-y-1 transition">
                <div class="flex items-center gap-1.5 font-bold text-rose-800">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span> إنشاء شكوى تلقائية غير مكررة</span>
                </div>
                <p class="text-[11px] text-rose-700 leading-relaxed">
                    تقييم أقل من 3 نجوم! سيقوم النظام تلقائياً بإنشاء تذكرة شكوى رسمية في سجل الشكاوى برقم
                    مستقل وإحالتها فوراً لفريق الجودة والمتابعة السريرية.
                </p>
            </div>

            <!-- Patient Comment / Notes -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label for="{{ $modalId }}Comment" class="block font-bold text-slate-700">
                        ملاحظات وانطباع المريض (Comment):
                        <span class="text-slate-400 font-normal mr-1">(اختياري / حتى 1000 حرف)</span>
                    </label>
                    <span class="text-[10px] text-slate-400 font-mono" id="{{ $modalId }}CommentCounter">1000
                        حرف متبقٍ</span>
                </div>
                <textarea name="comment" id="{{ $modalId }}Comment" rows="3" maxlength="1000"
                    placeholder="سجل انطباع المريض عن الزيارة، تعامل الطبيب والكادر التمريضي، وقت الانتظار..."
                    oninput="document.getElementById('{{ $modalId }}CommentCounter').textContent = (1000 - this.value.length) + ' حرف متبقٍ';"
                    class="w-full p-2.5 bg-white border border-slate-300 rounded-xl text-slate-800 text-xs focus:ring-1 focus:ring-[#00685f] focus:outline-none leading-relaxed"></textarea>
            </div>

            <!-- Modal Action Buttons -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                @if (!$isPage)
                    <button type="button" onclick="closeCreateSurveyModal('{{ $modalId }}')"
                        class="px-4 py-2 font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                        إلغاء
                    </button>
                @else
                    <a href="{{ route('surveys.index') }}"
                        class="px-4 py-2 font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                        العودة للقائمة
                    </a>
                @endif
                <button type="submit"
                    class="px-5 py-2 font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>حفظ الاستبيان وتطبيق القواعد</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    if (typeof window.openCreateSurveyModal === 'undefined') {
        window.openCreateSurveyModal = function(
            modalId = 'createSurveyModal',
            visitId = null,
            isAutoTriggered = false
        ) {
            const modal = document.getElementById(modalId);
            if (!modal) return;

            const fixedVisit = document.getElementById(modalId + 'FixedVisit');
            const visitText = document.getElementById(modalId + 'VisitText');
            const hiddenVisit = document.getElementById(modalId + 'HiddenVisitId');
            const select = document.getElementById(modalId + 'VisitSelect');
            const banner = document.getElementById(modalId + 'AutoTriggerBanner');

            const hasVisit = visitId !== null && visitId !== undefined && visitId !== '';

            if (hasVisit) {
                // Opened from Visits
                fixedVisit.classList.remove('hidden');

                visitText.textContent = `زيارة #${visitId}`;
                hiddenVisit.value = visitId;

            }

            if (banner) {
                banner.classList.toggle('hidden', !isAutoTriggered);
            }

            const rating5 = modal.querySelector('input[name="rating"][value="5"]');

            if (rating5) {
                rating5.checked = true;
                window.onModalSurveyRatingChanged(modalId, 5);
            }

            modal.classList.remove('hidden');
        };

        window.closeCreateSurveyModal = function(modalId) {
            const id = modalId || 'createSurveyModal';
            const modal = document.getElementById(id);
            if (modal) modal.classList.add('hidden');
        };

        window.onModalSurveyRatingChanged = function(modalId, val) {
            const id = modalId || 'createSurveyModal';
            const alertBox = document.getElementById(id + 'Br6Alert');
            if (alertBox) {
                if (Number(val) < 3) {
                    alertBox.classList.remove('hidden');
                } else {
                    alertBox.classList.add('hidden');
                }
            }
        };

        window.handleModalVisitChange = function(modalId, selectElem) {
            const id = modalId || 'createSurveyModal';
            const selectedOpt = selectElem.options[selectElem.selectedIndex];
            const patientInput = document.getElementById(id + 'PatientIdInput');
            if (patientInput && selectedOpt) {
                patientInput.value = selectedOpt.getAttribute('data-patient-id') || '';
            }
        };
    }
</script>
