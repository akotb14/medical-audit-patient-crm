@php
    $hasAlerts = session('error') || $errors->any() || session('success') || session('message');
@endphp

{{-- Notifications Container --}}
@if ($hasAlerts)
<div class="space-y-3" dir="rtl">
    {{-- General Error Message --}}
    @if (session('error'))
        <div id="errorAlert" role="alert"
            class="relative overflow-hidden rounded-xl border border-red-200 bg-red-50/80 shadow-sm">
            <div class="flex items-start gap-3 p-4 sm:p-5">

                {{-- Icon --}}
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-100 text-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="10" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5m0 4h.01" />
                    </svg>
                </div>

                {{-- Content --}}
                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-bold text-red-900">
                        تعذر إتمام العملية
                    </h3>

                    <p class="mt-1 text-sm leading-6 text-red-800">
                        {{ session('error') }}
                    </p>
                </div>

                {{-- Close Button --}}
                <button type="button" onclick="document.getElementById('errorAlert').remove()"
                    class="shrink-0 rounded-lg p-1.5 text-red-400 transition hover:bg-red-100 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-400"
                    aria-label="إغلاق التنبيه">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>

            </div>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div id="validationAlert" role="alert"
            class="relative overflow-hidden rounded-xl border border-red-200 bg-red-50/80 shadow-sm">
            <div class="flex items-start gap-3 p-4 sm:p-5">

                {{-- Icon --}}
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-100 text-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="10" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5m0 4h.01" />
                    </svg>
                </div>

                {{-- Content --}}
                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-bold text-red-900">
                        يرجى مراجعة البيانات المدخلة
                    </h3>

                    <p class="mt-1 text-xs leading-5 text-red-700">
                        حدثت بعض الأخطاء أثناء معالجة الطلب.
                    </p>

                    <ul class="mt-3 list-inside list-disc space-y-1.5 text-sm leading-6 text-red-800">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>

                {{-- Close Button --}}
                <button type="button" onclick="document.getElementById('validationAlert').remove()"
                    class="shrink-0 rounded-lg p-1.5 text-red-400 transition hover:bg-red-100 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-400"
                    aria-label="إغلاق التنبيه">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>

            </div>
        </div>
    @endif


    {{-- Success Message --}}
    @if (session('success'))
        <div id="successAlert" role="status"
            class="relative overflow-hidden rounded-xl border border-emerald-200 bg-emerald-50/80 shadow-sm">
            <div class="flex items-start gap-3 p-4">

                {{-- Icon --}}
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="10" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8 12 2.5 2.5L16 9" />
                    </svg>
                </div>

                {{-- Content --}}
                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-bold text-emerald-900">
                        تمت العملية بنجاح
                    </h3>

                    <p class="mt-1 text-sm leading-6 text-emerald-800">
                        {{ session('success') }}
                    </p>
                </div>

                {{-- Close Button --}}
                <button type="button" onclick="document.getElementById('successAlert').remove()"
                    class="shrink-0 rounded-lg p-1.5 text-emerald-500 transition hover:bg-emerald-100 hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                    aria-label="إغلاق التنبيه">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>

            </div>
        </div>
    @endif


    {{-- General Information Message --}}
    @if (session('message'))
        <div id="infoAlert" role="status"
            class="relative overflow-hidden rounded-xl border border-sky-200 bg-sky-50/80 shadow-sm">
            <div class="flex items-start gap-3 p-4">

                {{-- Icon --}}
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-sky-100 text-sky-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="10" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 11v5m0-8h.01" />
                    </svg>
                </div>

                {{-- Content --}}
                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-bold text-sky-900">
                        إشعار
                    </h3>

                    <p class="mt-1 text-sm leading-6 text-sky-800">
                        {{ session('message') }}
                    </p>
                </div>

                {{-- Close Button --}}
                <button type="button" onclick="document.getElementById('infoAlert').remove()"
                    class="shrink-0 rounded-lg p-1.5 text-sky-500 transition hover:bg-sky-100 hover:text-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-400"
                    aria-label="إغلاق التنبيه">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>

            </div>
        </div>
    @endif

</div>
@endif
