@props([
    'title',
    'subtitle' => null,
    'badge' => null,
    'badgeColor' => 'teal',
])

@php
    $badgeColors = [
        'teal' => 'bg-teal-50 text-teal-800 border-teal-200',
        'emerald' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        'amber' => 'bg-amber-50 text-amber-800 border-amber-200',
        'rose' => 'bg-rose-50 text-rose-800 border-rose-200',
        'slate' => 'bg-slate-100 text-slate-700 border-slate-200',
    ][$badgeColor] ?? 'bg-teal-50 text-teal-800 border-teal-200';
@endphp

<div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden flex flex-col justify-between">
    <!-- Header -->
    <div class="p-5 border-b border-slate-100 flex items-center justify-between gap-3">
        <div>
            <h3 class="font-bold text-sm text-[#0b1c30]">{{ $title }}</h3>
            @if($subtitle)
                <p class="text-[11px] text-[#64748b] mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>
        @if($badge)
            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $badgeColors }}">
                {{ $badge }}
            </span>
        @endif
    </div>

    <!-- Chart / Visual Content -->
    <div class="p-5">
        {{ $slot }}
    </div>
</div>
