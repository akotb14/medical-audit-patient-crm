@props([
    'title',
    'value',
    'subtitle' => null,
    'trend' => null,
    'trendType' => 'positive', // 'positive' | 'warning' | 'danger' | 'neutral'
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl p-4.5 border border-[#e2e8f0] shadow-xs hover:border-[#cbd5e1] transition group']) }}>
    <div class="flex items-center justify-between mb-2">
        <span class="text-xs font-semibold text-[#64748b]">{{ $title }}</span>
        @if(isset($icon))
            <div class="w-8 h-8 rounded-lg bg-[#eff4ff] text-[#00685f] flex items-center justify-center">
                {{ $icon }}
            </div>
        @endif
    </div>

    <div class="flex items-baseline gap-2 mb-2">
        <span class="text-3xl font-bold font-mono text-[#0b1c30]">
            {{ $value }}
        </span>
        @if($subtitle)
            <span class="text-xs text-[#64748b]">{{ $subtitle }}</span>
        @endif
    </div>

    @if($trend)
        <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100">
            <span class="text-[11px] text-[#64748b]">{{ $trendLabel ?? '' }}</span>
            <span @class([
                'inline-flex items-center gap-1 font-bold text-[11px] px-2 py-0.5 rounded-full',
                'bg-emerald-50 text-emerald-700' => $trendType === 'positive',
                'bg-amber-50 text-amber-800' => $trendType === 'warning',
                'bg-red-50 text-red-700' => $trendType === 'danger',
                'bg-slate-100 text-slate-700' => $trendType === 'neutral',
            ])>
                {{ $trend }}
            </span>
        </div>
    @endif
</div>
