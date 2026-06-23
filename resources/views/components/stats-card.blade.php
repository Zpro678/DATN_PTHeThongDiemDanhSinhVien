@props([
    'title',
    'value',
    'change' => null,
    'isPositive' => true,
    'icon',
    'iconBg',
    'sparklineColor' => null,
    'sparklinePath' => null,
])

<div class="admin-card admin-card-hover group flex {{ ($change || $sparklinePath) ? 'min-h-[172px]' : '' }} h-full flex-col justify-between overflow-hidden rounded-2xl border p-5">
    <div class="relative z-10 flex items-start justify-between gap-4">
        <div class="space-y-1.5">
            <span class="block text-[11px] font-bold uppercase tracking-widest text-slate-400">
                {{ $title }}
            </span>
            <h3 class="text-[28px] font-black leading-none tracking-tight text-slate-900">
                {{ $value }}
            </h3>
        </div>

        <div class="{{ $iconBg }} flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border shadow-sm transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
            <x-user.icon name="{{ $icon }}" :size="20" />
        </div>
    </div>

    @if($change || $sparklinePath)
    <div class="relative z-10 mt-5 flex items-end justify-between border-t border-slate-100/80 pt-3">
        @if($change)
        <div class="flex items-center gap-1">
            <span class="inline-flex items-center gap-0.5 rounded-lg px-2 py-0.5 text-xs font-bold {{ $isPositive ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                @if ($isPositive)
                    <x-user.icon name="arrow-up-right" :size="14" />
                @else
                    <x-user.icon name="arrow-down-right" :size="14" />
                @endif
                {{ $change }}
            </span>
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">vs tuần trước</span>
        </div>
        @else
        <div></div>
        @endif

        @if($sparklinePath)
        <div class="h-8 w-16 overflow-visible opacity-90 transition-opacity group-hover:opacity-100">
            <svg class="h-full w-full overflow-visible" viewBox="0 0 50 20" aria-hidden="true">
                <path
                    d="{{ $sparklinePath }}"
                    class="fill-none stroke-[2.6] stroke-linecap-round stroke-linejoin-round {{ $sparklineColor }}"
                />
            </svg>
        </div>
        @endif
    </div>
    @endif
</div>
