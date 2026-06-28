@props([
    'allUrl' => '#',
    'allLabel' => 'Xem tất cả thông báo',
    'buttonClass' => 'relative rounded-full p-2 text-on-surface-variant transition-colors hover:bg-surface-container-high',
    'activeButtonClass' => 'bg-surface-container-high',
    'indicatorClass' => 'absolute right-2 top-2 h-2 w-2 rounded-full border-2 border-surface bg-error',
    'dropdownClass' => 'absolute right-0 top-full mt-3 w-80 lg:w-96 rounded-2xl bg-white shadow-xl ring-1 ring-black/5 focus:outline-none z-50 overflow-hidden',
    'iconSize' => 20,
    'showIndicator' => true,
    'notifications' => null,
])

@php
    // Component thông báo dùng chung cho layout user/admin.
    // Dữ liệu được layout truyền vào từ App\Services\NotificationService::getDropdownData().
    // Mỗi phần tử nên có: href, level, title, message, time, unread (icon/iconWrapper là tùy chọn).
    $notifications = $notifications ?? [];
    $hasUnread = collect($notifications)->contains(fn ($n) => $n['unread'] ?? false);

    // Bản đồ mức độ -> icon + class màu. Đặt tại view để Tailwind quét được các class này.
    $levelStyles = [
        'info' => ['icon' => 'info', 'iconWrapper' => 'bg-blue-100 text-blue-600'],
        'success' => ['icon' => 'check-circle', 'iconWrapper' => 'bg-emerald-100 text-emerald-600'],
        'warning' => ['icon' => 'alert-triangle', 'iconWrapper' => 'bg-amber-100 text-amber-600'],
        'danger' => ['icon' => 'alert-triangle', 'iconWrapper' => 'bg-rose-100 text-rose-600'],
    ];
@endphp

<div class="relative" x-data="{ openNotification: false, showAll: false }" @click.away="openNotification = false; showAll = false">
    <button
        type="button"
        @click="openNotification = !openNotification"
        class="{{ $buttonClass }}"
        :class="openNotification ? '{{ $activeButtonClass }}' : ''"
    >
        <x-user.icon name="bell" :size="$iconSize" />

        @if ($showIndicator)
            <span class="{{ $indicatorClass }}"></span>
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
        class="{{ $dropdownClass }}"
        style="display: none;"
    >
        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-4 py-3">
            <h3 class="text-sm font-bold text-slate-900">Thông báo mới</h3>
            @if ($hasUnread)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="text-xs font-medium text-primary transition-colors hover:text-primary/80">Đánh dấu đã đọc</button>
                </form>
            @endif
        </div>

        <div class="scrollbar-custom max-h-[500px] overflow-y-auto overscroll-contain">
            @forelse ($notifications as $notification)
                @php
                    $isUnread = (bool) ($notification['unread'] ?? false);
                    $isRead = (bool) ($notification['read'] ?? false);
                    // Cho phép truyền sẵn icon/iconWrapper; nếu không thì suy ra từ level.
                    $style = $levelStyles[$notification['level'] ?? 'info'] ?? $levelStyles['info'];
                    $icon = $notification['icon'] ?? $style['icon'];
                    $iconWrapper = $notification['iconWrapper'] ?? $style['iconWrapper'];
                @endphp

                <a
                    href="{{ $notification['href'] ?? '#' }}"
                    x-show="showAll || {{ $loop->index }} < 5"
                    @class([
                        'flex items-start gap-4 px-4 py-3 transition-colors hover:bg-slate-50',
                        'border-b border-slate-50' => ! $loop->last,
                        'opacity-70' => $isRead,
                    ])
                >
                    <div class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $iconWrapper }}">
                        <x-user.icon :name="$icon" :size="16" />
                    </div>

                    <div class="flex-1 space-y-1">
                        <p class="text-sm font-medium text-slate-900">{{ $notification['title'] ?? 'Thông báo' }}</p>
                        <p class="text-xs text-slate-500">{{ $notification['message'] ?? '' }}</p>
                        <p class="text-[10px] font-medium text-slate-400">{{ $notification['time'] ?? '' }}</p>
                    </div>

                    @if ($isUnread)
                        <div class="mt-2 h-2 w-2 shrink-0 rounded-full bg-primary"></div>
                    @endif
                </a>
            @empty
                <div class="px-4 py-8 text-center text-sm font-medium text-slate-500">
                    Chưa có thông báo mới.
                </div>
            @endforelse
        </div>

        @if(count($notifications) > 5)
            <div class="border-t border-slate-100 bg-slate-50/50 p-2 text-center">
                <button x-show="!showAll" type="button" @click="showAll = true" class="inline-block w-full rounded-lg px-4 py-2 text-xs font-bold text-slate-600 transition-colors hover:bg-slate-200/50 hover:text-slate-900">
                    Xem thêm
                </button>
                <button x-show="showAll" type="button" @click="showAll = false" class="inline-block w-full rounded-lg px-4 py-2 text-xs font-bold text-slate-600 transition-colors hover:bg-slate-200/50 hover:text-slate-900">
                    Thu gọn
                </button>
            </div>
        @endif
    </div>
</div>
