@props(['name', 'class' => 'w-3.5 h-3.5'])

{{-- One inline-SVG icon set for action buttons, drawn with the same stroke
     weight and line caps as the sidebar. The accessible name comes from the
     surrounding link or button (its title attribute), so the glyph itself is
     hidden from assistive technology. --}}
<svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" stroke-width="1.8"
    stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
    @switch($name)
        @case('eye')
            <path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z" />
            <circle cx="12" cy="12" r="3" />
        @break

        @case('pencil')
            <path d="M16.9 4.5a2 2 0 0 1 2.8 2.8L7.5 19.5l-4.5 1.5 1.5-4.5L16.9 4.5Z" />
            <path d="M15 6.4 17.6 9" />
        @break

        @case('trash')
            <path d="M4 7h16" />
            <path d="M9.5 7V5.5A1.5 1.5 0 0 1 11 4h2a1.5 1.5 0 0 1 1.5 1.5V7" />
            <path d="M6 7l.9 12.1A2 2 0 0 0 8.9 21h6.2a2 2 0 0 0 2-1.9L18 7" />
            <path d="M10 11v6M14 11v6" />
        @break

        @case('check')
            <path d="M4.5 12.5l5 5 10-11" />
        @break

        @case('building')
            <path d="M4 21V6.5A1.5 1.5 0 0 1 5.5 5h8A1.5 1.5 0 0 1 15 6.5V21" />
            <path d="M15 10h3.5A1.5 1.5 0 0 1 20 11.5V21" />
            <path d="M2.5 21h19" />
            <path d="M8 9v.01M8 13v.01M8 17v.01M11.5 9v.01M11.5 13v.01M11.5 17v.01" />
        @break

        @case('users')
            <circle cx="9" cy="8" r="3.2" />
            <path d="M3 20a6 6 0 0 1 12 0" />
            <path d="M16 5.2a3.2 3.2 0 0 1 0 6.1" />
            <path d="M17.5 14.2A5.6 5.6 0 0 1 21 19.4" />
        @break

        @case('stethoscope')
            <path d="M4.5 3v5a4.5 4.5 0 0 0 9 0V3" />
            <path d="M3 3h3M12 3h3" />
            <path d="M9 12.5V15a5 5 0 0 0 10 0v-1.5" />
            <circle cx="19" cy="11.5" r="2" />
        @break
    @endswitch
</svg>