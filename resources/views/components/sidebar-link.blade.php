@props(['href', 'active' => false, 'label' => '', 'tone' => 'teal'])

@php
    // A distinct accent per module keeps the navigation scannable on the dark panel.
    $iconTones = [
        'teal' => 'bg-teal-400/20 text-teal-200 group-hover:bg-teal-400/30',
        'sky' => 'bg-sky-400/20 text-sky-200 group-hover:bg-sky-400/30',
        'rose' => 'bg-rose-400/20 text-rose-200 group-hover:bg-rose-400/30',
        'amber' => 'bg-amber-400/20 text-amber-200 group-hover:bg-amber-400/30',
        'orange' => 'bg-orange-400/20 text-orange-200 group-hover:bg-orange-400/30',
        'cyan' => 'bg-cyan-400/20 text-cyan-200 group-hover:bg-cyan-400/30',
        'indigo' => 'bg-indigo-400/20 text-indigo-200 group-hover:bg-indigo-400/30',
        'emerald' => 'bg-emerald-400/20 text-emerald-200 group-hover:bg-emerald-400/30',
        'violet' => 'bg-violet-400/20 text-violet-200 group-hover:bg-violet-400/30',
    ];

    $iconTone = $iconTones[$tone] ?? $iconTones['teal'];
@endphp

<a href="{{ $href }}" @if ($active) aria-current="page" @endif
    @class([
        'group flex w-full items-center gap-2.5 rounded-xl px-2 py-2 text-xs font-semibold transition duration-150 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white',
        'bg-white text-[#023b36] shadow-md shadow-black/20' => $active,
        'text-teal-50/85 hover:bg-white/10 hover:text-white' => ! $active,
    ])>
    <span @class([
        'flex h-7 w-7 shrink-0 items-center justify-center rounded-lg transition duration-150',
        'bg-[#00685f]/10 text-[#00685f]' => $active,
        $iconTone => ! $active,
    ]) aria-hidden="true">
        {{ $slot }}
    </span>

    <span class="min-w-0 flex-1 leading-snug">{{ $label }}</span>

    {{-- Directional hint: slides in on hover (points forward in RTL) --}}
    <svg @class([
        'h-3.5 w-3.5 shrink-0 transition duration-150',
        'text-[#00685f]/50' => $active,
        '-translate-x-1 text-white/60 opacity-0 group-hover:translate-x-0 group-hover:opacity-100' => ! $active,
    ]) fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
    </svg>
</a>
