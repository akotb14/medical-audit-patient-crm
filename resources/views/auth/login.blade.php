<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>تسجيل الدخول - {{ config('app.name', 'منظومة الرعاية السريرية') }}</title>
    <meta name="description" content="بوابة دخول الكادر الطبي والإداري إلى منظومة إدارة العيادات والمتابعات السريرية">

    <!-- Google Fonts: IBM Plex Sans Arabic & Cairo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@600;700;800;900&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh bg-[#f5f7fb] font-['IBM_Plex_Sans_Arabic',sans-serif] text-[#0b1c30] antialiased selection:bg-[#00685f] selection:text-white">

    <div class="grid min-h-dvh grid-cols-1 lg:grid-cols-2 xl:grid-cols-[1.05fr_1fr]">

        {{-- ============ Brand panel (large screens only) ============ --}}
        <aside
            class="relative hidden overflow-hidden bg-gradient-to-bl from-[#00796d] via-[#00685f] to-[#02352f] px-10 py-12 text-white lg:flex lg:flex-col lg:justify-between xl:px-16 xl:py-14">

            {{-- Dotted texture + soft glows --}}
            <div class="pointer-events-none absolute inset-0 opacity-20"
                style="background-image: radial-gradient(circle at 1px 1px, rgba(255, 255, 255, 0.65) 1px, transparent 0); background-size: 24px 24px;">
            </div>
            <div class="pointer-events-none absolute -right-20 -top-32 h-80 w-80 rounded-full bg-teal-300/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-32 -left-24 h-80 w-80 rounded-full bg-emerald-200/20 blur-3xl"></div>

            {{-- Identity --}}
            <div class="relative flex items-center gap-3">
                <span
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/25 backdrop-blur-xs">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" stroke-width="1.75" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v8m-4-4h8" />
                    </svg>
                </span>
                <span class="leading-tight">
                    <span class="block font-['Cairo',sans-serif] text-base font-extrabold">
                        مركز الرعاية السريرية المتخصصة
                    </span>
                    <span class="block text-[11px] text-teal-50/70">
                        Specialized Clinical Care Center
                    </span>
                </span>
            </div>

            {{-- Value proposition --}}
            <div class="relative max-w-lg space-y-6">
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-[11px] font-semibold text-teal-50 ring-1 ring-white/15">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-300" aria-hidden="true"></span>
                    منظومة إدارة العيادات والمتابعة السريرية
                </span>

                <h2 class="font-['Cairo',sans-serif] text-3xl font-extrabold leading-[1.35] xl:text-[2.5rem]">
                    كل ملف مريض، وكل زيارة، وكل متابعة — في مكان واحد
                </h2>

                <p class="text-sm leading-relaxed text-teal-50/80">
                    واجهة عربية بالكامل تربط العيادات والأطباء بالزيارات والمتابعات والشكاوى واستبيانات الرضا،
                    بصلاحيات دقيقة لكل دور وظيفي.
                </p>

                <ul class="space-y-3.5 pt-2 text-sm">
                    <li class="flex items-start gap-2.5">
                        <span
                            class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-300/20 text-emerald-200 ring-1 ring-emerald-200/30">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        <span class="leading-snug">
                            <span class="block font-semibold text-white">سجل مرضى موحّد</span>
                            <span class="block text-[11px] text-teal-50/65">ترقيم MRN وبحث فوري في الملفات</span>
                        </span>
                    </li>

                    <li class="flex items-start gap-2.5">
                        <span
                            class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-300/20 text-emerald-200 ring-1 ring-emerald-200/30">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        <span class="leading-snug">
                            <span class="block font-semibold text-white">جدول مواعيد وزيارات</span>
                            <span class="block text-[11px] text-teal-50/65">مرتبط بالعيادات والأطباء وتخصصاتهم</span>
                        </span>
                    </li>

                    <li class="flex items-start gap-2.5">
                        <span
                            class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-300/20 text-emerald-200 ring-1 ring-emerald-200/30">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        <span class="leading-snug">
                            <span class="block font-semibold text-white">متابعة الحالات الحرجة</span>
                            <span class="block text-[11px] text-teal-50/65">تنبيهات للمتابعات المتأخرة والمعلّقة</span>
                        </span>
                    </li>

                    <li class="flex items-start gap-2.5">
                        <span
                            class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-300/20 text-emerald-200 ring-1 ring-emerald-200/30">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        <span class="leading-snug">
                            <span class="block font-semibold text-white">شكاوى واستبيانات رضا</span>
                            <span class="block text-[11px] text-teal-50/65">مرتبطة بكل زيارة ومتابعة</span>
                        </span>
                    </li>
                </ul>
            </div>

            {{-- Compliance note --}}
            <div
                class="relative flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-white/10 pt-6 text-[11px] text-teal-50/70">
                <span class="inline-flex items-center gap-1.5">
                    <svg class="h-3.5 w-3.5 text-emerald-300" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    الوصول مقيّد حسب الدور الوظيفي المعتمد
                </span>
                <span class="hidden h-3 w-px bg-white/20 sm:block" aria-hidden="true"></span>
                <span>© {{ date('Y') }} مركز الرعاية السريرية المتخصصة</span>
            </div>
        </aside>

        {{-- ============ Login panel ============ --}}
        <main class="relative flex flex-col justify-center px-5 py-10 sm:px-8 lg:px-12 xl:px-16">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-64 bg-gradient-to-b from-teal-100/50 to-transparent"
                aria-hidden="true"></div>

            <div class="relative mx-auto w-full max-w-[27rem]">
                {{-- Compact brand header (small screens) --}}
                <div class="mb-7 flex items-center gap-3 lg:hidden">
                    <span
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#00685f] text-white shadow-lg shadow-teal-900/20">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <circle cx="12" cy="12" r="9" stroke-width="1.75" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                d="M12 8v8m-4-4h8" />
                        </svg>
                    </span>
                    <span class="leading-tight">
                        <span class="block text-sm font-extrabold text-[#0b1c30]">مركز الرعاية السريرية المتخصصة</span>
                        <span class="block text-[11px] text-slate-500">منظومة إدارة العيادات والمتابعة السريرية</span>
                    </span>
                </div>

                {{-- Login card --}}
                <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-2xl shadow-slate-900/5 sm:p-8">
                    <div class="mb-5 h-1 w-12 rounded-full bg-gradient-to-l from-[#00685f] to-emerald-400"
                        aria-hidden="true"></div>

                    <h1 class="font-['Cairo',sans-serif] text-xl font-extrabold text-[#0b1c30] sm:text-2xl">
                        تسجيل الدخول
                    </h1>
                    <p class="mt-1 text-xs text-slate-500">
                        أدخل بيانات حسابك للوصول إلى لوحة التحكم السريرية
                    </p>

                    {{-- Authentication errors --}}
                    @if (session('error') || $errors->any())
                        <div role="alert"
                            class="mt-5 flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50/80 p-3.5">
                            <span
                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                            <div class="space-y-1 text-[11px] font-semibold leading-relaxed text-rose-800">
                                @if (session('error'))
                                    <p>{{ session('error') }}</p>
                                @endif
                                @foreach ($errors->all() as $message)
                                    <p>{{ $message }}</p>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <form id="loginForm" action="{{ route('login') }}" method="POST" class="mt-6 space-y-5">
                        @csrf

                        {{-- Username --}}
                        <div class="space-y-1.5">
                            <label for="username" class="block text-xs font-bold text-slate-700">
                                اسم المستخدم <span class="font-sans font-normal text-slate-400">(Username)</span>
                                <span class="text-rose-500" aria-hidden="true">*</span>
                            </label>
                            <div class="relative">
                                <span
                                    class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3.5 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </span>
                                <input type="text" name="username" id="username" value="{{ old('username') }}"
                                    required autofocus autocomplete="username"
                                    placeholder="أدخل اسم المستخدم أو البريد الإلكتروني"
                                    aria-invalid="{{ $errors->has('username') ? 'true' : 'false' }}"
                                    @error('username') aria-describedby="username-error" @enderror
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/80 py-3.5 pe-3.5 ps-11 text-xs font-medium text-slate-800 shadow-xs transition placeholder:text-slate-400 hover:border-slate-300 focus:border-[#00685f] focus:bg-white focus:ring-4 focus:ring-[#00685f]/10 focus:outline-none @error('username') border-rose-300 bg-rose-50/40 focus:border-rose-400 focus:ring-rose-100 @enderror">
                            </div>
                            @error('username')
                                <p id="username-error" class="text-[11px] font-semibold text-rose-600">{{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div class="space-y-1.5">
                            <label for="password" class="block text-xs font-bold text-slate-700">
                                كلمة المرور <span class="font-sans font-normal text-slate-400">(Password)</span>
                                <span class="text-rose-500" aria-hidden="true">*</span>
                            </label>
                            <div class="relative">
                                <span
                                    class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3.5 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </span>
                                <input type="password" name="password" id="password" required
                                    autocomplete="current-password" placeholder="••••••••"
                                    aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                                    @error('password') aria-describedby="password-error" @enderror
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/80 py-3.5 pe-12 ps-11 text-xs font-medium text-slate-800 shadow-xs transition placeholder:text-slate-400 hover:border-slate-300 focus:border-[#00685f] focus:bg-white focus:ring-4 focus:ring-[#00685f]/10 focus:outline-none @error('password') border-rose-300 bg-rose-50/40 focus:border-rose-400 focus:ring-rose-100 @enderror">

                                {{-- Show / hide password --}}
                                <button type="button" id="togglePassword"
                                    class="absolute inset-y-0 end-0 my-auto me-2 flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                                    title="إظهار / إخفاء كلمة المرور" aria-label="إظهار كلمة المرور"
                                    aria-pressed="false" aria-controls="password">
                                    <svg id="eyeIcon" class="h-4 w-4" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg id="eyeOffIcon" class="hidden h-4 w-4" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Caps Lock hint --}}
                            <p id="capsLockHint"
                                class="hidden items-center gap-1.5 pt-1 text-[11px] font-semibold text-amber-700"
                                role="status">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                مفتاح الأحرف الكبيرة (Caps Lock) مُفعّل
                            </p>

                            @error('password')
                                <p id="password-error" class="text-[11px] font-semibold text-rose-600">{{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Submit --}}
                        <div class="pt-1">
                            <button type="submit" id="loginSubmit"
                                class="group flex w-full items-center justify-center gap-2 rounded-xl bg-[#00685f] px-4 py-3.5 text-xs font-bold text-white shadow-lg shadow-teal-900/20 transition hover:bg-[#005149] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#00685f] active:scale-[0.99] disabled:cursor-wait disabled:opacity-75">
                                <svg id="submitSpinner" class="hidden h-4 w-4 animate-spin" fill="none"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4" />
                                    <path class="opacity-90" fill="currentColor"
                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                                </svg>
                                <span id="submitLabel">تسجيل الدخول</span>
                                <svg class="h-4 w-4 rotate-180 transition group-hover:-translate-x-0.5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                        </div>

                        <p class="text-center text-[11px] text-slate-500">
                            نسيت كلمة المرور؟ تواصل مع مدير النظام لإعادة تعيينها.
                        </p>
                    </form>
                </div>

                {{-- Trust footer --}}
                <div class="mt-6 flex items-center justify-center gap-2 text-[10px] font-medium text-slate-500">
                    <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>الوصول مقيّد حسب الدور ومحمي بجلسة دخول آمنة</span>
                </div>
            </div>
        </main>
    </div>

    <script>
        (() => {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.getElementById('togglePassword');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeOffIcon = document.getElementById('eyeOffIcon');
            const capsLockHint = document.getElementById('capsLockHint');
            const loginForm = document.getElementById('loginForm');
            const submitButton = document.getElementById('loginSubmit');
            const submitLabel = document.getElementById('submitLabel');
            const submitSpinner = document.getElementById('submitSpinner');

            if (!passwordInput) {
                return;
            }

            // Show / hide the password value
            toggleButton?.addEventListener('click', () => {
                const willShow = passwordInput.type === 'password';
                passwordInput.type = willShow ? 'text' : 'password';

                eyeIcon?.classList.toggle('hidden', willShow);
                eyeOffIcon?.classList.toggle('hidden', !willShow);
                toggleButton.setAttribute('aria-pressed', String(willShow));
                toggleButton.setAttribute('aria-label', willShow ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور');
            });

            // Warn about Caps Lock while the password field is focused
            const syncCapsLockHint = (event) => {
                if (typeof event.getModifierState !== 'function' || !capsLockHint) {
                    return;
                }

                const isOn = event.getModifierState('CapsLock');
                capsLockHint.classList.toggle('hidden', !isOn);
                capsLockHint.classList.toggle('flex', isOn);
            };

            passwordInput.addEventListener('keydown', syncCapsLockHint);
            passwordInput.addEventListener('keyup', syncCapsLockHint);
            passwordInput.addEventListener('blur', () => {
                capsLockHint?.classList.add('hidden');
                capsLockHint?.classList.remove('flex');
            });

            // Prevent double submissions and show progress
            loginForm?.addEventListener('submit', () => {
                if (!loginForm.checkValidity()) {
                    return;
                }

                submitButton.disabled = true;
                submitSpinner?.classList.remove('hidden');

                if (submitLabel) {
                    submitLabel.textContent = 'جارٍ التحقق من البيانات';
                }
            });
        })();
    </script>
</body>
</html>
