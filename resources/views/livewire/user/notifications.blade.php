<div class="mx-auto max-w-[1400px] p-4 pb-24 sm:p-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
    @php
        // Bản đồ mức độ -> icon + class màu (đặt tại view để Tailwind quét được class).
        $levelStyles = [
            'info' => ['icon' => 'info', 'iconBox' => 'bg-blue-100 text-blue-600 ring-blue-200'],
            'success' => ['icon' => 'check-circle', 'iconBox' => 'bg-emerald-100 text-emerald-600 ring-emerald-200'],
            'warning' => ['icon' => 'alert-triangle', 'iconBox' => 'bg-amber-100 text-amber-600 ring-amber-200'],
            'danger' => ['icon' => 'alert-triangle', 'iconBox' => 'bg-rose-100 text-rose-600 ring-rose-200'],
        ];
        $groups = collect($notifications->items())->groupBy('date_group');
    @endphp

    <section class="mb-6 flex flex-col justify-between gap-4 rounded-[2rem] border border-outline-variant/10 bg-white p-6 shadow-sm md:flex-row md:items-center">
        <div>
            <div class="mb-2 inline-flex items-center gap-1.5 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-blue-600">
                <x-user.icon name="bell" :size="14" />
                Trung tâm thông báo
            </div>
            <h1 class="text-2xl font-extrabold uppercase tracking-tight text-slate-900">Tất cả thông báo</h1>
            <p class="mt-2 text-sm text-slate-500">
                Toàn bộ thông báo về điểm danh, đơn xin nghỉ và hoạt động lớp học của bạn.
            </p>
        </div>

        <button
            type="button"
            wire:click="markAllAsRead"
            class="inline-flex shrink-0 items-center gap-2 self-start rounded-xl border border-outline-variant/30 bg-white px-4 py-2.5 text-sm font-bold text-on-surface-variant shadow-sm transition-colors hover:bg-surface-container md:self-auto"
        >
            <x-user.icon name="check-circle" :size="18" />
            Đánh dấu tất cả đã đọc
        </button>
    </section>

    <div class="grid grid-cols-12 gap-6">
        {{-- Rail lọc theo danh mục --}}
        <aside class="col-span-12 lg:col-span-3">
            <div class="lg:sticky lg:top-6 rounded-2xl border border-outline-variant/20 bg-white p-3 shadow-sm">
                <nav class="flex gap-2 overflow-x-auto lg:flex-col lg:overflow-visible">
                    @foreach ($navItems as $item)
                        @php $isActive = $filter === $item['key']; @endphp
                        <button
                            type="button"
                            wire:click="setFilter('{{ $item['key'] }}')"
                            @class([
                                'group flex shrink-0 items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition-colors lg:w-full',
                                'bg-primary text-white shadow-sm' => $isActive,
                                'text-on-surface-variant hover:bg-surface-container' => ! $isActive,
                            ])
                        >
                            <x-user.icon :name="$item['icon']" :size="18" />
                            <span class="flex-1 text-left">{{ $item['label'] }}</span>
                            <span @class([
                                'min-w-[1.5rem] rounded-full px-2 py-0.5 text-center text-xs font-bold',
                                'bg-white/20 text-white' => $isActive,
                                'bg-surface-container-high text-on-surface-variant' => ! $isActive,
                            ])>{{ $item['count'] }}</span>
                        </button>
                    @endforeach
                </nav>
            </div>
        </aside>

        {{-- Danh sách thông báo nhóm theo ngày --}}
        <div class="col-span-12 space-y-6 lg:col-span-9">
            @forelse ($groups as $groupLabel => $groupItems)
                <div>
                    <h2 class="mb-3 flex items-center gap-2 px-1 text-xs font-bold uppercase tracking-widest text-slate-400">
                        {{ $groupLabel }}
                        <span class="h-px flex-1 bg-outline-variant/20"></span>
                    </h2>
                    <div class="space-y-3">
                        @foreach ($groupItems as $notification)
                            @php
                                $style = $levelStyles[$notification['level'] ?? 'info'] ?? $levelStyles['info'];
                                $isUnread = (bool) ($notification['unread'] ?? false);
                            @endphp

                            <a
                                href="{{ $notification['href'] ?? '#' }}"
                                @class([
                                    'flex items-start gap-4 rounded-2xl border bg-white p-4 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md',
                                    'border-primary/30 bg-primary/[0.03]' => $isUnread,
                                    'border-outline-variant/20' => ! $isUnread,
                                ])
                            >
                                <div class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl shadow-sm ring-1 ring-inset {{ $style['iconBox'] }}">
                                    <x-user.icon :name="$style['icon']" :size="22" stroke-width="2.5" />
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <p class="font-bold text-slate-900">{{ $notification['title'] }}</p>
                                        @if ($isUnread)
                                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-primary"></span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-sm leading-relaxed text-slate-600">{{ $notification['message'] }}</p>
                                    <p class="mt-2 text-xs font-medium text-slate-400">{{ $notification['time'] }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="rounded-[2rem] border border-emerald-100 bg-white p-10 text-center shadow-sm">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                        <x-user.icon name="bell" :size="28" stroke-width="2.5" />
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">
                        {{ $filter === 'all' ? 'Chưa có thông báo nào' : 'Không có thông báo phù hợp' }}
                    </h3>
                    <p class="mt-2 text-sm text-slate-500">
                        {{ $filter === 'all'
                            ? 'Khi có hoạt động điểm danh, đơn xin nghỉ hoặc nhắc nhở, thông báo sẽ xuất hiện ở đây.'
                            : 'Thử chọn một bộ lọc khác ở cột bên trái.' }}
                    </p>
                </div>
            @endforelse

            @if ($notifications->hasPages())
                <div class="rounded-2xl border border-outline-variant/20 bg-white px-4 py-3 shadow-sm">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
