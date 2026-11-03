@props(['code' => '404', 'title' => 'حدث خطأ', 'message' => ''])

@php
    $tone = match ((string) $code) {
        '403' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-amber-200'],
        '419' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-amber-200'],
        '429' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-amber-200'],
        '404' => ['bg' => 'bg-teal-50', 'text' => 'text-[#00685f]', 'border' => 'border-teal-200'],
        default => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'border' => 'border-rose-200'],
    };
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ $code }} · {{ $title }} — {{ config('app.name', 'منظومة الرعاية السريرية') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@500;600;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body
    class="relative flex min-h-dvh items-center justify-center bg-[#f0f4f8] p-4 font-['IBM_Plex_Sans_Arabic',sans-serif] text-[#0b1c30] antialiased selection:bg-[#00685f] selection:text-white sm:p-6">

    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden" aria-hidden="true">
        <div class="absolute -right-40 -top-40 h-96 w-96 rounded-full bg-teal-100/60 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 h-96 w-96 rounded-full bg-emerald-100/50 blur-3xl"></div>
    </div>

    <main class="w-full max-w-md text-center">
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/50 sm:p-10">
            <div
                class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border {{ $tone['border'] }} {{ $tone['bg'] }} {{ $tone['text'] }}">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                </svg>
            </div>

            <p class="mt-6 font-mono text-6xl font-extrabold leading-none tracking-tight {{ $tone['text'] }}">
                {{ $code }}
            </p>

            <h1 class="mt-4 text-xl font-bold text-[#0b1c30]">{{ $title }}</h1>

            <p class="mt-2 text-xs leading-6 text-slate-500">
                {{ $message }}
            </p>

            <div class="mt-8 flex flex-col items-stretch justify-center gap-2.5 sm:flex-row sm:items-center">
                <a href="{{ route('home') }}"
                    class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#00685f] px-5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#005049] active:scale-[0.99]">
                    العودة إلى النظام
                </a>

                <button type="button" onclick="history.back(); return false;"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-xs font-bold text-slate-700 transition hover:bg-slate-50">
                    رجوع
                </button>
            </div>
        </div>

        <p class="mt-6 text-[11px] text-slate-400">
            مركز الرعاية السريرية المتخصصة
        </p>
    </main>
</body>
</html>