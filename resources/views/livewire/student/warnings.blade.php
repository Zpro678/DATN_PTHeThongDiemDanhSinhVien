<div class="mx-auto max-w-[1400px] space-y-6 p-4 pb-24 sm:p-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <section class="flex flex-col justify-between gap-4 rounded-2xl border border-outline-variant/10 bg-white p-6 shadow-sm md:flex-row md:items-center">
        <div>
            <div class="mb-2 inline-flex items-center gap-1.5 rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-rose-600">
                <x-user.icon name="alert-triangle" :size="14" />
                Hệ thống cảnh báo
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Cảnh báo học tập</h1>
            <p class="mt-2 text-sm text-slate-500">
                Cập nhật các cảnh báo về điểm danh, kỷ luật và các vấn đề cần lưu ý trong quá trình học.
            </p>
        </div>
    </section>

    @php
        $styles = [
            'danger' => [
                'card' => 'border-rose-100 bg-gradient-to-b from-rose-50/50 to-white',
                'halo' => 'bg-rose-100/40',
                'iconBox' => 'bg-rose-100 text-rose-600 ring-rose-200',
                'iconText' => 'text-rose-500',
                'divider' => 'border-rose-100/60',
                'link' => 'text-rose-600 hover:text-rose-700',
            ],
            'warning' => [
                'card' => 'border-amber-100 bg-gradient-to-b from-amber-50/50 to-white',
                'halo' => 'bg-amber-100/40',
                'iconBox' => 'bg-amber-100 text-amber-600 ring-amber-200',
                'iconText' => 'text-amber-500',
                'divider' => 'border-amber-100/60',
                'link' => 'text-amber-600 hover:text-amber-700',
            ],
            'info' => [
                'card' => 'border-blue-100 bg-gradient-to-b from-blue-50/50 to-white',
                'halo' => 'bg-blue-100/40',
                'iconBox' => 'bg-blue-100 text-blue-600 ring-blue-200',
                'iconText' => 'text-blue-500',
                'divider' => 'border-blue-100/60',
                'link' => 'text-blue-600 hover:text-blue-700',
            ],
        ];
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($warnings as $warning)
            @php $style = $styles[$warning['type']] ?? $styles['info']; @endphp
            <div class="group relative flex flex-col overflow-hidden rounded-2xl border {{ $style['card'] }} p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                <div class="absolute -right-4 -top-4 rounded-full {{ $style['halo'] }} p-6">
                    <x-user.icon :name="$warning['icon']" :size="72" class="{{ $style['iconText'] }} opacity-10" />
                </div>
                <div class="relative z-10 flex flex-1 flex-col">
                    <div class="mb-5 inline-flex items-center justify-center self-start rounded-2xl p-3.5 shadow-sm ring-1 ring-inset {{ $style['iconBox'] }}">
                        <x-user.icon :name="$warning['icon']" :size="24" stroke-width="2.5" />
                    </div>
                    <h3 class="mb-2 text-xl font-bold text-slate-900">{{ $warning['title'] }}</h3>
                    <p class="mb-6 flex-1 text-sm leading-relaxed text-slate-600">{{ $warning['message'] }}</p>
                    <div class="flex items-center justify-between border-t {{ $style['divider'] }} pt-4">
                        <span class="text-xs font-bold text-slate-400">{{ $warning['date'] }}</span>
                        <a href="{{ route($warning['route']) }}" class="inline-flex items-center gap-1.5 text-sm font-bold {{ $style['link'] }} hover:underline">
                            {{ $warning['action_label'] }} <x-user.icon name="arrow-right" :size="16" stroke-width="2.5" />
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border border-emerald-100 bg-white p-10 text-center shadow-sm">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                    <x-user.icon name="check-circle" :size="28" stroke-width="2.5" />
                </div>
                <h3 class="text-lg font-bold text-slate-900">Chưa có cảnh báo nào</h3>
                <p class="mt-2 text-sm text-slate-500">Các lớp học hiện chưa ghi nhận rủi ro chuyên cần hoặc đơn xin nghỉ cần bổ sung.</p>
            </div>
        @endforelse
    </div>
</div>
