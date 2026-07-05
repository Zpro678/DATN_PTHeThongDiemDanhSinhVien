@php
    // Dữ liệu do App\Livewire\NotificationBell::render() truyền vào:
    // $notifications (mảng phẳng), $unreadCount (int), $hasUnread (bool).
    // Gom nhóm theo ngày (Hôm nay / Hôm qua / Trong tuần / Trước đó) như Facebook,
    // giữ nguyên thứ tự mới nhất trước mà service đã sắp xếp.
    $grouped = collect($notifications)->groupBy(fn ($n) => $n['date_group'] ?? 'Trước đó');

    // Bản đồ mức độ -> icon + class màu. Đặt tại view để Tailwind quét được các class này.
    $levelStyles = [
        'info' => ['icon' => 'info', 'iconWrapper' => 'bg-blue-100 text-blue-600'],
        'success' => ['icon' => 'check-circle', 'iconWrapper' => 'bg-emerald-100 text-emerald-600'],
        'warning' => ['icon' => 'alert-triangle', 'iconWrapper' => 'bg-amber-100 text-amber-600'],
        'danger' => ['icon' => 'alert-triangle', 'iconWrapper' => 'bg-rose-100 text-rose-600'],
    ];
@endphp

<div class="relative" x-data="{ openNotification: false }" @click.away="openNotification = false">
    <button
        type="button"
        @click="openNotification = !openNotification"
        class="relative rounded-full p-2 text-on-surface-variant transition-colors hover:bg-surface-container-high"
        :class="openNotification ? 'bg-surface-container-high' : ''"
    >
        <x-user.icon name="bell" :size="20" />

        {{-- Badge số thông báo CHƯA ĐỌC; ẩn khi = 0. --}}
        @if ($unreadCount > 0)
            <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full border-2 border-surface bg-error px-1 text-[10px] font-bold leading-none text-white">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div
        x-show="openNotification"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        class="absolute right-0 top-full mt-3 w-80 lg:w-96 rounded-2xl bg-white shadow-xl ring-1 ring-black/5 focus:outline-none z-50 overflow-hidden"
        style="display: none;"
    >
        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-4 py-3">
            <h3 class="text-sm font-bold text-slate-900">Thông báo</h3>
            <div class="flex items-center gap-3">
                @if ($hasUnread)
                    <button type="button" wire:click="markAllRead" class="text-xs font-medium text-primary transition-colors hover:text-primary/80">
                        Đánh dấu đã đọc
                    </button>
                @endif
                @if (! empty($notifications))
                    <button
                        type="button"
                        wire:click="deleteAll"
                        wire:confirm="Xóa tất cả thông báo?"
                        class="inline-flex items-center gap-1 text-xs font-medium text-slate-400 transition-colors hover:text-error"
                    >
                        <x-user.icon name="trash-2" :size="14" />
                        Xóa tất cả
                    </button>
                @endif
            </div>
        </div>

        <div class="scrollbar-custom max-h-[70vh] min-h-[120px] overflow-y-auto overscroll-contain">
            @forelse ($grouped as $group => $items)
                {{-- Tiêu đề nhóm theo ngày (dính khi cuộn, giống Facebook) --}}
                <p class="sticky top-0 z-10 bg-white px-4 pt-3 pb-1 text-xs font-bold uppercase tracking-wide text-slate-400">
                    {{ $group }}
                </p>

                @foreach ($items as $notification)
                    @php
                        $isUnread = (bool) ($notification['unread'] ?? false);
                        // Cho phép truyền sẵn icon/iconWrapper; nếu không thì suy ra từ level.
                        $style = $levelStyles[$notification['level'] ?? 'info'] ?? $levelStyles['info'];
                        $icon = $notification['icon'] ?? $style['icon'];
                        $iconWrapper = $notification['iconWrapper'] ?? $style['iconWrapper'];
                    @endphp

                    <div wire:key="notif-{{ $notification['id'] }}" @class([
                        'group relative flex items-start gap-4 border-b border-slate-50 pl-4 pr-10 py-3 transition-colors',
                        'bg-blue-50/60 hover:bg-blue-50' => $isUnread,
                        'hover:bg-slate-50' => ! $isUnread,
                    ])>
                        <a
                            href="{{ isset($notification['id']) ? route('notifications.read', ['notification' => $notification['id']]) : ($notification['href'] ?? '#') }}"
                            class="flex flex-1 items-start gap-4"
                        >
                            <div class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $iconWrapper }}">
                                <x-user.icon :name="$icon" :size="16" />
                            </div>

                            <div class="flex-1 space-y-1">
                                <p class="text-sm {{ $isUnread ? 'font-bold' : 'font-medium' }} text-slate-900">{{ $notification['title'] ?? 'Thông báo' }}</p>
                                <p class="text-xs text-slate-500">{{ $notification['message'] ?? '' }}</p>
                                <p class="text-[10px] font-medium text-slate-400">{{ $notification['time'] ?? '' }}</p>
                            </div>

                            @if ($isUnread)
                                <div class="mt-2 h-2 w-2 shrink-0 rounded-full bg-primary"></div>
                            @endif
                        </a>

                        {{-- Nút xóa từng thông báo: hiện khi hover / focus, không điều hướng --}}
                        <button
                            type="button"
                            wire:click="deleteNotification('{{ $notification['id'] }}')"
                            title="Xóa thông báo"
                            class="absolute right-2 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full text-slate-400 opacity-0 transition-all hover:bg-rose-100 hover:text-error focus:opacity-100 group-hover:opacity-100"
                        >
                            <x-user.icon name="x" :size="16" />
                        </button>
                    </div>
                @endforeach
            @empty
                <div class="px-4 py-8 text-center text-sm font-medium text-slate-500">
                    Chưa có thông báo mới.
                </div>
            @endforelse
        </div>
    </div>
</div>
