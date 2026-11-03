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
<body class="bg-[#f7f8f9] text-[#0b1c30] antialiased selection:bg-[#00685f] selection:text-white font-['IBM_Plex_Sans_Arabic',sans-serif]">
    <!-- Keyboard skip link -->
    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:fixed focus:right-4 focus:top-4 focus:z-[60] focus:rounded-lg focus:bg-[#00685f] focus:px-4 focus:py-2 focus:text-xs focus:font-bold focus:text-white focus:shadow-lg">
        تخطَّ إلى المحتوى الرئيسي
    </a>

    <div class="app-shell flex h-dvh flex-col">
        <!-- Top Operational Header -->
        @include('layouts.header')

        <div class="flex min-h-0 flex-1 overflow-hidden">
            <!-- Sidebar Navigation (Right side in RTL, drawer on mobile) -->
            @include('layouts.sidebar')

            <!-- Main Content Canvas -->
            <main id="main-content" tabindex="-1"
                class="mx-auto min-w-0 w-full max-w-[1600px] flex-1 space-y-6 overflow-y-auto p-4 focus:outline-none sm:p-6 lg:p-8">
                <x-alerts />
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Sidebar navigation: drawer on small screens, collapsible column on large screens -->
    <script>
        (() => {
            const sidebar = document.getElementById('app-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const toggleButton = document.getElementById('sidebar-open');
            const closeButton = document.getElementById('sidebar-close');
            const desktop = window.matchMedia('(min-width: 1024px)');
            const storageKey = 'clinical-sidebar-collapsed';

            if (!sidebar || !overlay || !toggleButton) {
                return;
            }

            const readCollapsed = () => {
                try {
                    return window.localStorage.getItem(storageKey) === '1';
                } catch (error) {
                    return false;
                }
            };

            const writeCollapsed = (value) => {
                try {
                    window.localStorage.setItem(storageKey, value ? '1' : '0');
                } catch (error) {
                    // Storage may be unavailable (private mode); the toggle still works for this view.
                }
            };

            let collapsed = readCollapsed();
            let drawerOpen = false;

            // Display is driven here rather than by classes because the two states
            // (drawer, collapsed column) apply at different breakpoints.
            const render = () => {
                const isDesktop = desktop.matches;
                const isVisible = isDesktop ? !collapsed : drawerOpen;

                sidebar.style.display = isVisible ? 'flex' : 'none';
                overlay.style.display = !isDesktop && drawerOpen ? 'block' : 'none';
                document.body.classList.toggle('overflow-hidden', !isDesktop && drawerOpen);
                toggleButton.setAttribute('aria-expanded', String(isVisible));
                toggleButton.setAttribute('aria-label', isVisible ? 'إخفاء القائمة الجانبية' : 'إظهار القائمة الجانبية');
            };

            toggleButton.addEventListener('click', () => {
                if (desktop.matches) {
                    collapsed = !collapsed;
                    writeCollapsed(collapsed);
                } else {
                    drawerOpen = !drawerOpen;
                }

                render();
            });

            const closeDrawer = () => {
                drawerOpen = false;
                render();
            };

            closeButton?.addEventListener('click', closeDrawer);
            overlay.addEventListener('click', closeDrawer);

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeDrawer();
                }
            });

            desktop.addEventListener('change', closeDrawer);

            render();
        })();
    </script>

    <!-- Modals & Drawers Stacks -->
    {{-- @include('patients.drawer') --}}
    @stack('modals')
    @stack('scripts')
</body>
</html>
