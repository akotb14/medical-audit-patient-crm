@extends('layouts.app')

@section('title', 'إدارة الشكاوى والاعتراضات (Complaints)')

@section('content')
    <div class="space-y-6">

        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-2xl font-bold text-[#0b1c30]">إدارة الشكاوى واعتراضات المرضى</h1>
                    <span
                        class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-800 border border-rose-200 font-mono">
                        جدول complaints
                    </span>
                </div>
                <p class="text-xs text-[#64748b] mt-1">
                    دورة حياة التحقيق والمعالجة: جديدة (new) ← مسندة (assigned) ← قيد التحقيق (investigating) ← تم الحل
                    (resolved) ← مغلقة (closed)
                </p>
            </div>
            <div class="flex items-center gap-2.5">
                @can('create', App\Models\Complaint::class)
                    <a href="{{ route('complaints.create') }}"
                        class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>+ تسجيل شكوى يدوية (Manual Complaint)</span>
                    </a>
                @endcan
                @can('viewAny', App\Models\Survey::class)
                    <a href="{{ route('surveys.index') }}"
                        class="flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 rounded-lg shadow-xs transition">
                        <span>استبيانات الرضا ➜</span>
                    </a>
                @endcan
            </div>
        </div>

        <!-- Quick Complaints KPI Stats Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
            <!-- Total -->
            <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold text-slate-500">إجمالي الشكاوى</p>
                    <p class="text-xl font-bold text-[#0b1c30] mt-0.5">{{ $stats->total }}</p>
                </div>
                <div
                    class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-sm">
                    📁
                </div>
            </div>

            <!-- New -->
            <div
                class="bg-white p-3.5 rounded-xl border border-blue-200 shadow-xs flex items-center justify-between bg-blue-50/10">
                <div>
                    <p class="text-[11px] font-semibold text-blue-700">جديدة (New)</p>
                    <p class="text-xl font-bold text-blue-800 mt-0.5">{{ $stats->new }}</p>
                </div>
                <div
                    class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">
                    🆕
                </div>
            </div>

            <!-- Investigating & Assigned -->
            <div
                class="bg-white p-3.5 rounded-xl border border-amber-200 shadow-xs flex items-center justify-between bg-amber-50/10">
                <div>
                    <p class="text-[11px] font-semibold text-amber-700">قيد التحقيق</p>
                    <p class="text-xl font-bold text-amber-800 mt-0.5">{{ $stats->assigned + $stats->investigating }}</p>
                </div>
                <div
                    class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xs">
                    🔍
                </div>
            </div>

            <!-- Resolved (BR-7) -->
            <div
                class="bg-white p-3.5 rounded-xl border border-teal-200 shadow-xs flex items-center justify-between bg-teal-50/10">
                <div>
                    <p class="text-[11px] font-semibold text-teal-700">تم الحل (Resolved)</p>
                    <p class="text-xl font-bold text-teal-800 mt-0.5">{{ $stats->resolved }}</p>
                </div>
                <div
                    class="w-8 h-8 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center font-bold text-xs">
                    ✓
                </div>
            </div>

            <!-- Closed (BR-7) -->
            <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold text-slate-500">مغلقة (Closed)</p>
                    <p class="text-xl font-bold text-slate-700 mt-0.5">{{ $stats->closed }}</p>
                </div>
                <div
                    class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-xs">
                    🔒
                </div>
            </div>

            <!-- From Surveys (BR-6) -->
            <div
                class="bg-white p-3.5 rounded-xl border border-rose-200 shadow-xs flex items-center justify-between bg-rose-50/10">
                <div>
                    <p class="text-[11px] font-semibold text-rose-700">تلقائية من الاستبيان</p>
                    <p class="text-xl font-bold text-rose-800 mt-0.5">{{ $stats->from_surveys }}</p>
                </div>
                <div
                    class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-xs">
                    ⭐
                </div>
            </div>
        </div>

        <!-- BR-7 Explanatory Banner -->
        <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 text-xs flex items-start gap-2.5">
            <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div class="leading-relaxed">
                <span>لا يمكن نقل الشكوى إلى حالة "تم الحل " أو "مغلقة " إلا بعد تدوين ملاحظة الإجراء التصحيحي وحل المشكلة
                    .</span>
            </div>
        </div>

        <!-- Filter Bar (Status, Assignee, Source, Search) -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs space-y-3">
            <form action="{{ route('complaints.index') }}" method="GET"
                class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="flex-1 flex flex-wrap items-center gap-2.5">
                    <!-- Search -->
                    <div class="relative flex-1 min-w-[220px]">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="بحث بالعنوان، التفاصيل، ملاحظات الحل، اسم المريض أو MRN..."
                            class="w-full pl-3 pr-9 py-2 text-xs bg-[#f8fafc] border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#00685f] text-slate-800 text-right" />
                        <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <!-- Status Filter -->
                    <div class="flex items-center gap-1.5 text-xs">
                        <span class="text-slate-400 text-[11px]">الحالة:</span>
                        <select name="status"
                            class="px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-slate-700 text-xs focus:ring-1 focus:ring-[#00685f]">
                            <option value="all" @selected(request('status') == 'all' || !request('status'))>كافة المراحل</option>
                            <option value="new" @selected(request('status') == 'new')>جديدة (new)</option>
                            <option value="assigned" @selected(request('status') == 'assigned')>مسندة (assigned)</option>
                            <option value="investigating" @selected(request('status') == 'investigating')>قيد التحقيق (investigating)</option>
                            <option value="resolved" @selected(request('status') == 'resolved')>تم الحل (resolved)</option>
                            <option value="closed" @selected(request('status') == 'closed')>مغلقة (closed)</option>
                        </select>
                    </div>

                    <!-- Assignee Filter (FR-6) -->
                    <div class="flex items-center gap-1.5 text-xs">
                        <span class="text-slate-400 text-[11px]">المسند إليه:</span>
                        <select name="assigned_to"
                            class="px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-slate-700 text-xs focus:ring-1 focus:ring-[#00685f]">
                            <option value="all" @selected(request('assigned_to') == 'all' || !request('assigned_to'))>كافة الموظفين</option>
                            <option value="unassigned" @selected(request('assigned_to') == 'unassigned')>غير مسندة (Unassigned)</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected(request('assigned_to') == $user->id)>{{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Source Filter (FR-6) -->
                    <div class="flex items-center gap-1.5 text-xs">
                        <span class="text-slate-400 text-[11px]">المصدر:</span>
                        <select name="source"
                            class="px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-slate-700 text-xs focus:ring-1 focus:ring-[#00685f]">
                            <option value="all" @selected(request('source') == 'all' || !request('source'))>كافة المصادر</option>
                            <option value="survey" @selected(request('source') == 'survey')>استبيان رضا</option>
                            <option value="manual" @selected(request('source') == 'manual')>يدوي / مباشر</option>
                        </select>
                    </div>

                    <button type="submit"
                        class="px-3.5 py-2 text-xs font-semibold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg transition">
                        تصفية
                    </button>

                    @if (request()->hasAny(['search', 'status', 'assigned_to', 'source']))
                        <a href="{{ route('complaints.index') }}"
                            class="px-3 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                            إعادة ضبط
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Complaints Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right border-collapse text-xs">
                    <thead class="bg-[#f8fafc] text-[#64748b] text-[11px] font-bold border-b border-[#e2e8f0]">
                        <tr>
                            <th class="py-3 px-3.5 w-12 text-center font-mono">#</th>
                            <th class="py-3 px-3.5 min-w-[170px]">المريض ورقم الملف (Patient)</th>
                            <th class="py-3 px-3.5 min-w-[130px]">المصدر (Source)</th>
                            <th class="py-3 px-3.5 min-w-[220px]">عنوان الشكوى والتفاصيل (Title & Desc)</th>
                            <th class="py-3 px-3.5 min-w-[140px]">الحالة (Status Stepper)</th>
                            <th class="py-3 px-3.5 min-w-[150px]">المحقق المسند إليه (Assignee)</th>
                            <th class="py-3 px-3.5 min-w-[180px]">ملاحظة الحل (Resolution Note)</th>
                            <th class="py-3 px-3.5 min-w-[110px]">التاريخ</th>
                            <th class="py-3 px-3.5 text-center w-28">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e2e8f0]">
                        @if (isset($complaints) && count($complaints) > 0)
                            @foreach ($complaints as $c)
                                <tr class="hover:bg-teal-50/20 transition">
                                    <!-- ID -->
                                    <td class="py-3.5 px-3.5 text-center font-mono font-bold text-slate-500">
                                        #{{ $c->id }}
                                    </td>

                                    <!-- Patient -->
                                    <td class="py-3.5 px-3.5">
                                        @if ($c->patient)
                                            <div>
                                                @if ($c->patient->trashed())
                                                    <span class="font-bold text-slate-900 block">
                                                        {{ $c->patient->fullname }}
                                                    </span>
                                                @else
                                                    <a href="{{ route('patients.show', $c->patient) }}"
                                                        class="font-bold text-slate-900 hover:text-[#00685f] transition block">
                                                        {{ $c->patient->fullname }}
                                                    </a>
                                                @endif
                                                <div
                                                    class="flex items-center gap-1.5 text-[11px] text-slate-500 font-mono mt-0.5">
                                                    <span
                                                        class="bg-slate-100 text-slate-700 px-1.5 py-0.2 rounded">{{ $c->patient->mrn }}</span>
                                                    <a href="tel:{{ $c->patient->phone }}"
                                                        class="text-teal-700 hover:underline">
                                                        {{ $c->patient->phone }}
                                                    </a>
                                                    <x-archived-badge :patient="$c->patient" />
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-slate-400 italic">غير محدد</span>
                                        @endif
                                    </td>

                                    <!-- Source (survey | manual) -->
                                    <td class="py-3.5 px-3.5 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $c->source_badge_classes }}">
                                            @if ($c->source === 'survey')
                                                <span>⭐</span>
                                                <span>استبيان</span>
                                            @else
                                                <span>✍️</span>
                                                <span>يدوي مباشر</span>
                                            @endif
                                        </span>
                                        @can('view', $c->survey)
                                            @if ($c->survey_id)
                                                <a href="{{ route('surveys.show', $c->survey_id) }}"
                                                    class="text-[10px] text-teal-700 block hover:underline font-mono mt-0.5">
                                                    استبيان #{{ $c->survey_id }}
                                                </a>
                                            @endif
                                        @endcan
                                    </td>

                                    <!-- Title & Description -->
                                    <td class="py-3.5 px-3.5">
                                        <a href="{{ route('complaints.show', $c) }}"
                                            class="font-bold text-slate-900 hover:text-[#00685f] transition block text-xs">
                                            {{ $c->title }}
                                        </a>
                                        <p class="text-[11px] text-slate-500 leading-relaxed line-clamp-2 mt-0.5"
                                            title="{{ $c->description }}">
                                            {{ $c->description }}
                                        </p>
                                    </td>

                                    <!-- Status Badge -->
                                    <td class="py-3.5 px-3.5 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $c->status_badge_classes }}">
                                            @if ($c->status === 'new')
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                            @elseif($c->status === 'resolved' || $c->status === 'closed')
                                                <span class="font-bold">✓</span>
                                            @else
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            @endif
                                            <span>{{ $c->status_label }}</span>
                                        </span>
                                    </td>

                                    <!-- Assignee (FR-6) -->
                                    <td class="py-3.5 px-3.5">
                                        @if ($c->assignee)
                                            <div class="flex items-center gap-1.5">
                                                <div
                                                    class="w-6 h-6 rounded-full bg-purple-100 text-purple-800 flex items-center justify-center font-bold text-[10px]">
                                                    {{ mb_substr($c->assignee->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <span
                                                        class="font-semibold text-slate-800 block text-xs">{{ $c->assignee->name }}</span>
                                                    <span
                                                        class="text-[10px] text-slate-400 block">{{ $c->assignee->roleNames() ?: 'بدون دور' }}</span>
                                                </div>
                                            </div>
                                        @else
                                            @can('assign', $c)
                                                @if ($c->status === 'new')
                                                    <button type="button" onclick="openAssignModal({{ $c }})"
                                                        class="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-bold text-purple-700 bg-purple-50 hover:bg-purple-100 rounded-md border border-purple-200 transition">
                                                        <span>+ إسناد لمحقق</span>
                                                    </button>
                                                @endif
                                            @endcan
                                        @endif
                                    </td>

                                    <!-- Resolution Note (BR-7) -->
                                    <td class="py-3.5 px-3.5">
                                        @if ($c->resolution_note)
                                            <div>
                                                <span class="text-teal-800 font-semibold block text-[11px] line-clamp-2"
                                                    title="{{ $c->resolution_note }}">
                                                    ✓ {{ $c->resolution_note }}
                                                </span>
                                                @if ($c->resolved_at)
                                                    <span class="text-[10px] text-slate-400 font-mono block mt-0.5">
                                                        حل: {{ $c->resolved_at->format('Y-m-d H:i') }}
                                                    </span>
                                                @endif
                                            </div>
                                        @elseif(in_array($c->status, ['resolved', 'closed']))
                                            <span class="text-rose-600 font-bold text-[10px]">مفقود !</span>
                                        @else
                                            <span class="text-slate-400 italic text-[11px]">بانتظار التحقيق</span>
                                        @endif
                                    </td>

                                    <!-- Date -->
                                    <td class="py-3.5 px-3.5 text-slate-500 font-mono text-[11px]">
                                        <div>{{ $c->created_at ? $c->created_at->format('Y-m-d') : '-' }}</div>
                                        <div class="text-[10px] text-slate-400">
                                            {{ $c->created_at ? $c->created_at->format('h:i A') : '' }}</div>
                                    </td>

                                    <!-- Actions -->
                                    <td class="py-3.5 px-3.5 text-center">
                                        <div class="inline-flex items-center gap-1 justify-center">
                                            @can('view', $c)
                                                <a href="{{ route('complaints.show', $c) }}"
                                                    class="p-1.5 text-slate-600 hover:text-teal-700 hover:bg-teal-50 rounded-lg border border-slate-200 transition"
                                                    title="عرض التفاصيل ومسار التحقيق">
                                                    👁️
                                                </a>
                                            @endcan
                                            @can('update', $c)
                                                <a href="{{ route('complaints.edit', $c) }}"
                                                    class="p-1.5 text-slate-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg border border-slate-200 transition"
                                                    title="تعديل الشكوى">
                                                    ✏️
                                                </a>
                                            @endcan
                                            @can('delete', $c)
                                                <form action="{{ route('complaints.destroy', $c) }}" method="POST"
                                                    onsubmit="return confirm('هل أنت متأكد من حذف هذه الشكوى؟')"
                                                    class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="p-1.5 text-slate-400 hover:text-red-700 hover:bg-red-50 rounded-lg border border-slate-200 transition"
                                                        title="حذف">
                                                        🗑️
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center mx-auto text-xl mb-2">
                                        🛡️</div>
                                    <p class="font-bold text-slate-700 text-sm">لا توجد شكاوى أو اعتراضات مسجلة</p>
                                    <p class="text-xs text-slate-400 mt-1">تظهر هنا الشكاوى المنشأة يدوياً أو التلقائية من
                                        استبيانات الرضا ذات التقييم المنخفض </p>
                                    <a href="{{ route('complaints.create') }}"
                                        class="inline-flex items-center gap-1.5 mt-3 px-4 py-2 text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg shadow-sm transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span>+ تسجيل شكوى يدوية</span>
                                    </a>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if (isset($complaints) && method_exists($complaints, 'links') && $complaints->hasPages())
                <div class="p-4 border-t border-slate-100 bg-[#f8fafc]">
                    {{ $complaints->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal: Quick Assign Complaint to User (FR-6) -->
    <div id="assignComplaintModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs hidden">
        <div
            class="bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-200 overflow-hidden transform transition-all text-right animate-in fade-in zoom-in-95 duration-150">
            <div class="p-5 border-b border-slate-100 bg-[#f8fafc] flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-sm">
                        👤
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-[#0b1c30]">إسناد الشكوى لمحقق جودة </h3>
                        <p class="text-[11px] text-[#64748b]">تعيين موظف مسؤول لتحديث الحالة إلى مسندة (assigned)</p>
                    </div>
                </div>
                <button type="button" onclick="closeAssignModal()"
                    class="w-7 h-7 flex items-center justify-center text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-200/50 text-base">
                    ✕
                </button>
            </div>

            <form id="assignComplaintForm" action="" method="POST" class="p-6 space-y-4 text-xs">
                @csrf
                @method('PATCH')
                <div>
                    <span class="text-slate-500 text-[11px]">الشكوى:</span>
                    <p id="assignModalComplaintTitle" class="font-bold text-slate-800 mt-0.5">---</p>
                </div>

                <div>
                    <label for="assign_to_user_id" class="block font-bold text-slate-700 mb-1.5">اختر المحقق / الموظف
                        المسؤول: <span class="text-red-500">*</span></label>
                    <select name="assigned_to" id="assign_to_user_id" required
                        class="w-full p-2.5 bg-white border border-slate-300 rounded-lg text-slate-800 text-xs focus:ring-1 focus:ring-purple-600">
                        <option value="">-- اختر موظف الجودة --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->roleNames() ?: 'بدون دور' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" onclick="closeAssignModal()"
                        class="px-4 py-2 font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                        إلغاء
                    </button>
                    <button type="submit"
                        class="px-5 py-2 font-bold text-white bg-purple-700 hover:bg-purple-800 rounded-lg shadow-sm transition">
                        تأكيد الإسناد (Assign)
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAssignModal(complaint) {
            const modal = document.getElementById('assignComplaintModal');
            const form = document.getElementById('assignComplaintForm');
            const titleElem = document.getElementById('assignModalComplaintTitle');
            console.log(complaint)

            if (form) form.action = "complaints/" + complaint['id'] + "/assign";
            if (titleElem) titleElem.textContent = `#${complaint['id']}: ${complaint['title']}`;
            if (modal) modal.classList.remove('hidden');
        }

        function closeAssignModal() {
            const modal = document.getElementById('assignComplaintModal');
            if (modal) modal.classList.add('hidden');
        }
    </script>
@endsection
