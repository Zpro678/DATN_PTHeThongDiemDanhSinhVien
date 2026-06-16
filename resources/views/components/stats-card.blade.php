@props([
    'title',
    'value',
    'change',
    'isPositive' => true,
    'icon',
    'iconBg',
    'sparklineColor',
    'sparklinePath'
])

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 hover:shadow-md transition-all duration-300 flex flex-col justify-between group">
    <div class="flex justify-between items-start">
        <div class="space-y-1.5">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">
                {{ $title }}
            </span>
            <h3 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                {{ $value }}
            </h3>
        </div>

        <!-- Rounded Icon wrapper -->
        <div class="w-10 h-10 rounded-xl flex items-center justify-center border transition-transform duration-300 group-hover:scale-110 {{ $iconBg }}">
            <x-sams.icon name="{{ $icon }}" class="w-5 h-5" />
        </div>
    </div>

    <div class="flex items-end justify-between mt-5 pt-3 border-t border-slate-100 dark:border-slate-800/80">
        <div class="flex items-center gap-1">
            <span class="inline-flex items-center gap-0.5 text-xs font-bold px-2 py-0.5 rounded-lg {{ $isPositive ? 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400' }}">
                @if($isPositive)
                    <x-sams.icon name="arrow-up-right" class="w-3.5 h-3.5 shrink-0" />
                @else
                    <x-sams.icon name="arrow-down-right" class="w-3.5 h-3.5 shrink-0" />
                @endif
                {{ $change }}
            </span>
            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">vs tuần trước</span>
        </div>

        <!-- Dynamic SVG Mini Trend Chart sparkline -->
        <div class="w-16 h-8 overflow-visible">
            <svg class="w-full h-full overflow-visible" viewBox="0 0 50 20">
                <path
                    d="{{ $sparklinePath }}"
                    class="fill-none stroke-2 stroke-linecap-round stroke-linejoin-round {{ $sparklineColor }}"
                />
            </svg>
        </div>
    </div>
</div>
