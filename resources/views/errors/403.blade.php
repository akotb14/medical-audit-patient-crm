<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">

        <title>403 · Access Denied — {{ config('app.name', 'Laravel') }}</title>

        @fonts

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] flex items-center justify-center min-h-screen p-6 lg:p-8 antialiased">
        <main class="w-full max-w-[28rem] lg:max-w-xl text-center">
            <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg px-6 py-10 lg:px-12 lg:py-14">
                <span class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-[#fff2f2] dark:bg-[#1D0002] text-[#f53003] dark:text-[#FF4433]">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="4" y="10" width="16" height="10" rx="2" />
                        <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                    </svg>
                </span>

                <p class="mt-6 text-6xl lg:text-7xl font-semibold tracking-tight leading-none text-[#f53003] dark:text-[#FF4433]">403</p>

                <h1 class="mt-4 text-2xl font-medium">Access Denied</h1>

                <p class="mt-2 text-sm leading-6 text-[#706f6c] dark:text-[#A1A09A]">
                    You don't have permission to access this page.
                </p>

                <div class="mt-8 flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-3">
                    <a
                        href="{{ Route::has('home') ? route('home') : url('/') }}"
                        class="inline-block px-5 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] rounded-sm border border-black dark:border-[#eeeeec] text-white dark:text-[#1C1C1A] text-sm leading-normal transition-colors hover:bg-black dark:hover:bg-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#f53003] dark:focus-visible:ring-[#FF4433] focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-[#161615]"
                    >
                        Go Home
                    </a>

                    <a
                        href="{{ url('/') }}"
                        onclick="history.back(); return false;"
                        class="inline-block px-5 py-2 rounded-sm border border-[#19140035] hover:border-[#1915014a] text-[#1b1b18] dark:text-[#EDEDEC] dark:border-[#3E3E3A] dark:hover:border-[#62605b] text-sm leading-normal transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#f53003] dark:focus-visible:ring-[#FF4433] focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-[#161615]"
                    >
                        Go Back
                    </a>
                </div>
            </div>
        </main>
    </body>
</html>
