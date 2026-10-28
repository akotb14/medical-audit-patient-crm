@props([
    'title',
    'value',
    'subvalue' => null,
    'icon' => '📊',
    'trend' => null,
    'trendPositive' => true,
    'color' => 'teal', // teal, emerald, amber, rose, blue, purple
    'link' => null,
    'badge' => null,
    'footer' => null,
    'progress' => null, // 0 to 100
])

@php
    $colorClasses = [
        'teal' => [
            'border' => 'border-teal-200 hover:border-teal-400',
            'bg_icon' => 'bg-teal-50 text-[#00685f]',
            'text_val' => 'text-[#00685f]',
            'progress' => 'bg-[#00685f]',
            'badge' => 'bg-teal-50 text-teal-800 border-teal-200',
        ],
        'emerald' => [
            'border' => 'border-emerald-200 hover:border-emerald-400',
            'bg_icon' => 'bg-emerald-50 text-emerald-700',
            'text_val' => 'text-emerald-800',
            'progress' => 'bg-emerald-600',
            'badge' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        ],
        'amber' => [
            'border' => 'border-amber-200 hover:border-amber-400',
            'bg_icon' => 'bg-amber-50 text-amber-700',
            'text_val' => 'text-amber-800',
            'progress' => 'bg-amber-500',
            'badge' => 'bg-amber-50 text-amber-800 border-amber-200',
        ],
        'rose' => [
            'border' => 'border-rose-200 hover:border-rose-400',
            'bg_icon' => 'bg-rose-50 text-rose-700',
            'text_val' => 'text-rose-800',
            'progress' => 'bg-rose-600',
            'badge' => 'bg-rose-50 text-rose-800 border-rose-200',
        ],
        'blue' => [
            'border' => 'border-blue-200 hover:border-blue-400',
            'bg_icon' => 'bg-blue-50 text-blue-700',
            'text_val' => 'text-blue-800',
            'progress' => 'bg-blue-600',
            'badge' => 'bg-blue-50 text-blue-800 border-blue-200',
        ],
        'purple' => [
            'border' => 'border-purple-200 hover:border-purple-400',
            'bg_icon' => 'bg-purple-50 text-purple-700',
            'text_val' => 'text-purple-800',
            'progress' => 'bg-purple-600',
            'badge' => 'bg-purple-50 text-purple-800 border-purple-200',
        ],
    ][$color] ?? [
        'border' => 'border-slate-200 hover:border-slate-300',
        'bg_icon' => 'bg-slate-100 text-slate-700',
        'text_val' => 'text-slate-900',
        'progress' => 'bg-slate-700',
        'badge' => 'bg-slate-100 text-slate-700 border-slate-200',
    ];
@endphp

<div
    @if($link) onclick="window.location.href='{{ $link }}'" @endif
    class="bg-white rounded-2xl p-5 border {{ $colorClasses['border'] }} shadow-xs hover:shadow-md transition-all duration-200 {{ $link ? 'cursor-pointer group' : '' }} flex flex-col justify-between"
>
    <!-- Card Header -->
    <div>
        <div class="flex items-center justify-between gap-2 mb-3">
            <span class="text-xs font-semibold text-slate-500 leading-tight">{{ $title }}</span>
            <div class="w-9 h-9 rounded-xl {{ $colorClasses['bg_icon'] }} flex items-center justify-center font-bold text-base shrink-0 shadow-2xs group-hover:scale-105 transition-transform">
                {{ $icon }}
            </div>
        </div>

        <!-- Metric Value -->
        <div class="flex items-baseline gap-2 mb-1.5">
            <span class="text-3xl font-extrabold font-mono tracking-tight {{ $colorClasses['text_val'] }}">{{ $value }}</span>
            @if($subvalue)
                <span class="text-xs text-slate-500 font-medium">{{ $subvalue }}</span>
            @endif
        </div>

        @if(!is_null($progress))
            <!-- Mini Progress Bar -->
            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden my-2.5">
                <div class="{{ $colorClasses['progress'] }} h-1.5 rounded-full transition-all duration-500" style="width: {{ min(100, max(0, $progress)) }}%"></div>
            </div>
        @endif
    </div>

    <!-- Card Footer -->
    <div class="flex items-center justify-between text-xs pt-3 mt-1 border-t border-slate-100">
        <span class="text-[11px] text-slate-500 truncate">{{ $footer ?? '' }}</span>
        @if($badge)
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $colorClasses['badge'] }}">
                {{ $badge }}
            </span>
        @elseif($trend)
            <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $trendPositive ? 'text-emerald-700 bg-emerald-50' : 'text-rose-700 bg-rose-50' }}">
                <span>{{ $trendPositive ? '↑' : '↓' }}</span>
                <span>{{ $trend }}</span>
            </span>
        @endif
    </div>
</div>
