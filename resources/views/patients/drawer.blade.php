<!-- Patient Inspector Contextual Drawer (Sliding from the LEFT in RTL) -->
<div id="patientDrawerOverlay" class="fixed inset-0 z-50 overflow-hidden hidden">
    <!-- 40% Backdrop -->
    <div id="patientDrawerBackdrop" class="absolute inset-0 bg-[#0f172a]/40 backdrop-blur-xs transition-opacity"></div>

    <div class="fixed inset-y-0 left-0 max-w-full flex">
        <div id="patientDrawerPanel"
            class="w-screen max-w-md sm:max-w-xl bg-white shadow-2xl flex flex-col border-r border-[#e2e8f0] transform -translate-x-full transition-transform duration-300 ease-in-out">
            <!-- Drawer Header -->
            <div class="p-5 border-b border-[#e2e8f0] bg-[#f8fafc] flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div id="drawerAvatar"
                        class="w-12 h-12 rounded-xl bg-[#00685f] text-white flex items-center justify-center font-bold text-lg shadow-sm">
                        س
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 id="drawerPatientName" class="font-bold text-lg text-[#0b1c30]">سليمان فهد المنصور</h3>
                            <span id="drawerPatientMrn"
                                class="font-mono text-xs font-bold text-[#00685f] bg-[#eff4ff] px-2 py-0.5 rounded border border-teal-200">
                                MRN-84920
                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-[#64748b] mt-0.5">
                            <span id="drawerGender">ذكر</span>
                            <span>·</span>
                            <span id="drawerAge">العمر: 58 سنة</span>
                            <span>·</span>
                            <span id="drawerBlood"
                                class="font-bold text-rose-700 bg-rose-50 px-1.5 py-0.2 rounded font-mono">
                                فصيلة الدم: A+
                            </span>
                        </div>
                    </div>
                </div>
                <button type="button" onclick="window.clinicalApp.closeDrawer()"
                    class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-200 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Drawer Quick Actions -->
            <div class="p-3 bg-white border-b border-[#e2e8f0] flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <button type="button" onclick="window.clinicalApp.bookForCurrentDrawerPatient()"
                        class="px-3 py-1.5 text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg shadow-xs transition">
                        + حجز موعد جديد
                    </button>
                    <button type="button" id="drawerSmsBtn" onclick="window.clinicalApp.sendPatientSms()"
                        class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition flex items-center gap-1">
                        <span>إرسال تنبيه SMS</span>
                    </button>
                </div>
                <button type="button" onclick="window.print()"
                    class="p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                </button>
            </div>

            <!-- Drawer Tab Headers -->
            <div class="flex border-b border-[#e2e8f0] bg-[#f8fafc] px-4 text-xs font-semibold">
                <button type="button" data-tab="overview"
                    class="drawer-tab-btn py-3 px-3 border-b-2 border-[#00685f] text-[#00685f]">
                    الملخص السريري والحساسية
                </button>
                <button type="button" data-tab="history"
                    class="drawer-tab-btn py-3 px-3 border-b-2 border-transparent text-[#64748b] hover:text-slate-900">
                    سجل الزيارات
                </button>
            </div>

            <!-- Drawer Body -->
            <div class="flex-1 overflow-y-auto p-5 space-y-5">
                <div id="drawerTabOverview" class="space-y-4">
                    <!-- Allergies Alert -->
                    <div class="bg-[#fff1f2] border border-[#fecdd3] rounded-xl p-4">
                        <div class="flex items-center gap-2 text-xs font-bold text-[#be123c] mb-2">
                            <span>تنبيه الحساسية الدوائية والغذائية:</span>
                        </div>
                        <div id="drawerAllergiesList" class="flex flex-wrap gap-2"></div>
                    </div>

                    <!-- Chronic Diseases -->
                    <div class="bg-white border border-[#e2e8f0] rounded-xl p-4 space-y-2">
                        <div class="text-xs font-bold text-[#0b1c30]">الأمراض والمتابعات المزمنة:</div>
                        <div id="drawerChronicList" class="flex flex-wrap gap-2 pt-1"></div>
                    </div>

                    <!-- Demographics -->
                    <div class="bg-[#f8fafc] border border-[#e2e8f0] rounded-xl p-4 space-y-3 text-xs">
                        <div class="font-bold text-slate-800 border-b border-slate-200 pb-2">بيانات الاتصال والطوارئ:
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <span class="text-slate-500 block text-[11px]">الهوية الوطنية:</span>
                                <span id="drawerNationalId" class="font-mono font-bold text-slate-800"></span>
                            </div>
                            <div>
                                <span class="text-slate-500 block text-[11px]">رقم الجوال:</span>
                                <span id="drawerPhone" class="font-mono font-bold text-teal-800"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="drawerTabHistory" class="space-y-3 hidden">
                    <div id="drawerHistoryList" class="space-y-2 text-xs"></div>
                </div>
            </div>
        </div>
    </div>
</div>