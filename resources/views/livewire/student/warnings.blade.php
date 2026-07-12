<div class="w-full space-y-6 px-6 py-6 pb-24 sm:px-10 lg:px-16 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <section class="rounded-[20px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-2 inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-slate-500">
            <x-user.icon name="alert-triangle" :size="14" />
            Hệ thống cảnh báo
        </div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Cảnh báo học tập</h1>
        <p class="mt-2 text-sm text-slate-500">
            Cập nhật các cảnh báo về điểm danh, kỷ luật và các vấn đề cần lưu ý trong quá trình học.
        </p>
    </section>

    @php
        // Bảng màu phẳng theo phong cách Clean SaaS (đặt tại view để Tailwind quét được class).
        $styles = [
            'danger' => [
                'iconBox' => 'bg-rose-50 text-rose-600',
                'badge' => 'bg-rose-50 text-rose-600',
                'link' => 'text-rose-600 hover:text-rose-700',
                'label' => 'Khẩn cấp',
            ],
            'warning' => [
                'iconBox' => 'bg-amber-50 text-amber-600',
                'badge' => 'bg-amber-50 text-amber-600',
                'link' => 'text-amber-600 hover:text-amber-700',
                'label' => 'Cảnh báo',
            ],
            'info' => [
                'iconBox' => 'bg-blue-50 text-blue-600',
                'badge' => 'bg-blue-50 text-blue-600',
                'link' => 'text-blue-600 hover:text-blue-700',
                'label' => 'Nhắc nhở',
            ],
        ];
    @endphp

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($warnings as $warning)
            @php $style = $styles[$warning['type']] ?? $styles['info']; @endphp
            <div class="flex flex-col rounded-[20px] border border-slate-200 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md">
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex h-11 w-11 items-center justify-center rounded-full {{ $style['iconBox'] }}">
                        <x-user.icon :name="$warning['icon']" :size="20" />
                    </div>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $style['badge'] }}">{{ $style['label'] }}</span>
                </div>
                <h3 class="mb-1.5 text-base font-bold text-slate-900">{{ $warning['title'] }}</h3>
                <p class="mb-5 flex-1 text-sm leading-relaxed text-slate-500">{{ $warning['message'] }}</p>
                <div class="flex items-center justify-between border-t border-slate-100 pt-4">
                    <span class="text-xs font-bold text-slate-400">{{ $warning['date'] }}</span>
                    <a href="{{ route($warning['route'], $warning['params'] ?? []) }}" wire:navigate class="inline-flex items-center gap-1.5 text-sm font-bold {{ $style['link'] }} hover:underline">
                        {{ $warning['action_label'] }} <x-user.icon name="arrow-right" :size="16" />
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-[20px] border border-slate-200 bg-white p-10 text-center shadow-sm">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                    <x-user.icon name="check-circle" :size="28" />
                </div>
                <h3 class="text-lg font-bold text-slate-900">Chưa có cảnh báo nào</h3>
                <p class="mt-2 text-sm text-slate-500">Các lớp học hiện chưa ghi nhận rủi ro chuyên cần hoặc đơn xin nghỉ cần bổ sung.</p>
            </div>
        @endforelse
    </div>
</div>
