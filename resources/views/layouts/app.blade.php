<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Clinical Care RTL') }} - @yield('title', 'لوحة التحكم السريرية')</title>

    <!-- Google Fonts: IBM Plex Sans Arabic & Cairo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS & Native JS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f8f9ff] text-[#0b1c30] antialiased selection:bg-[#00685f] selection:text-white font-['IBM_Plex_Sans_Arabic',sans-serif]">
    <div class="min-h-screen flex flex-col">
        <!-- Top Operational Header -->
        @include('layouts.header')

        <div class="flex-1 flex overflow-hidden">
            <!-- Sidebar Navigation (Right side in RTL) -->
            @include('layouts.sidebar')

            <!-- Main Content Canvas -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">
                {{ $slot ?? '' }}
                <x-alerts/>
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Modals & Drawers Stacks -->
    {{-- @include('patients.drawer') --}}
    @stack('modals')
    @stack('scripts')
</body>
</html>
