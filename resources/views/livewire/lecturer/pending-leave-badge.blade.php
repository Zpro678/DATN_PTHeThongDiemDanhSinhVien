<span x-data x-init="(@js($this->realtimeChannels())).forEach(ch => window.listenRealtime && window.listenRealtime(ch, () => $wire.$refresh(), 300))">
    @if ($pendingCount > 0)
        <span class="inline-flex h-5 min-w-5 shrink-0 items-center justify-center rounded-full bg-error px-1.5 text-[11px] font-bold leading-none text-white">
            {{ $pendingCount > 99 ? '99+' : $pendingCount }}
        </span>
    @endif
</span>
