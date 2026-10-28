<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>تسجيل الدخول - {{ config('app.name', 'منظومة الرعاية السريرية') }}</title>
    <meta name="description" content="صفحة تسجيل الدخول لمنظومة الرعاية السريرية وإدارة العيادات">

    <!-- Google Fonts: IBM Plex Sans Arabic & Cairo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@500;600;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f0f4f8] text-[#0b1c30] antialiased selection:bg-[#00685f] selection:text-white font-['IBM_Plex_Sans_Arabic',sans-serif] min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-8 relative">

    <!-- Background Subtle Decorative Shapes -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
        <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full bg-teal-100/60 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 rounded-full bg-emerald-100/50 blur-3xl"></div>
    </div>

    <!-- Login Container -->
    <div class="w-full max-w-md">
        <!-- Brand / Clinic Logo Header -->
        <div class="text-center mb-8 space-y-3">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-[#00685f] text-white font-bold text-2xl shadow-md shadow-teal-900/10">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-[#0b1c30] tracking-tight">
                    تسجيل الدخول للنظام
                </h1>
                <p class="text-xs text-[#64748b] mt-1 font-medium">
                    منظومة إدارة العيادات والمتابعات السريرية المتخصصة
                </p>
            </div>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xl shadow-slate-200/50 p-6 sm:p-8">
            <!-- Flash Errors Alert -->
            @if(session('error'))
                <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs font-semibold flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any() && !session('error'))
                <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs font-semibold flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="space-y-1">
                        @foreach($errors->all() as $err)
                            <p>{{ $err }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Login Form (Only Username & Password) -->
            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Username Field -->
                <div class="space-y-1.5">
                    <label for="username" class="block text-xs font-bold text-slate-700">
                        اسم المستخدم <span class="text-slate-400 font-normal font-sans">(Username)</span>
                        <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            type="text"
                            name="username"
                            id="username"
                            value="{{ old('username') }}"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="أدخل اسم المستخدم أو البريد الإلكتروني"
                            class="w-full pl-3 pr-10 py-3 text-xs bg-[#f8fafc] border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00685f] focus:border-transparent focus:bg-white text-slate-800 transition placeholder:text-slate-400 @error('username') border-rose-400 bg-rose-50/20 @enderror"
                        />
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>
                    @error('username')
                        <p class="text-rose-600 text-[11px] font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Field -->
                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-bold text-slate-700">
                        كلمة المرور <span class="text-slate-400 font-normal font-sans">(Password)</span>
                        <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            name="password"
                            id="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full pl-10 pr-10 py-3 text-xs bg-[#f8fafc] border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00685f] focus:border-transparent focus:bg-white text-slate-800 transition placeholder:text-slate-400 @error('password') border-rose-400 bg-rose-50/20 @enderror"
                        />
                        <!-- Lock Icon (Right in RTL) -->
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <!-- Toggle Password Visibility Button (Left in RTL) -->
                        <button
                            type="button"
                            onclick="togglePasswordVisibility()"
                            class="absolute left-3 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-slate-600 rounded-md transition"
                            title="إظهار / إخفاء كلمة المرور"
                        >
                            <svg id="eyeIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-rose-600 text-[11px] font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button
                        type="submit"
                        class="w-full py-3 px-4 bg-[#00685f] hover:bg-[#005049] text-white font-bold text-xs rounded-xl shadow-md shadow-teal-900/10 transition active:scale-[0.99] flex items-center justify-center gap-2"
                    >
                        <span>تسجيل الدخول</span>
                        <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        <!-- Footer / Secure Clinical Access Notice -->
        <div class="text-center mt-6 text-slate-500 text-[11px] space-y-1">
            <div class="flex items-center justify-center gap-1.5 font-medium">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>بوابة دخول الكادر الطبي والإداري المعتمد</span>
            </div>
            <p class="text-slate-400 font-mono text-[10px]">
                نظام مشفر ومحمي وفق معايير الخصوصية السريرية (HIPAA Compliant)
            </p>
        </div>
    </div>

    <!-- Vanilla Script for Show/Hide Password -->
    <script>
        function togglePasswordVisibility() {
            const pwdInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            if (!pwdInput) return;

            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                if (eyeIcon) {
                    eyeIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    `;
                }
            } else {
                pwdInput.type = 'password';
                if (eyeIcon) {
                    eyeIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    `;
                }
            }
        }
    </script>
</body>
</html>
