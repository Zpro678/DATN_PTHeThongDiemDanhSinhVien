@props(['title' => null, 'activeNav' => null])

@php
    $userName = Auth::user()?->name ?? 'Nguyễn Văn A';
    $userRole = Auth::user()?->email ?? 'User';
    // Dữ liệu thông báo cho dropdown ở header (lấy từ DB qua NotificationService).
    $notificationData = app(\App\Services\NotificationService::class)->getDropdownData(Auth::user());
    $matchesActive = function ($activePattern) use ($activeNav): bool {
        $patterns = is_array($activePattern) ? $activePattern : [$activePattern];

        foreach ($patterns as $pattern) {
            if ($activeNav) {
                if (\Illuminate\Support\Str::is($pattern, $activeNav)) {
                    return true;
                }

                continue;
            }

            if (request()->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    };

    $navData = [
        ['type' => 'link', 'label' => 'Tổng quan', 'icon' => 'layout-dashboard', 'route' => 'dashboard'],
        [
            'type' => 'group',
            'label' => 'Không gian Chủ lớp',
            'icon' => 'shield',
            'activePattern' => ['managed-classes', 'lecturer.classes.*', 'lecturer.class.*', 'lecturer.attendance.*', 'lecturer.students.*', 'lecturer.leave-requests.*', 'create-class'],
            'items' => [
                ['label' => 'Lớp tôi quản lý', 'icon' => 'book-open', 'route' => 'managed-classes', 'active' => ['managed-classes', 'lecturer.classes.*', 'lecturer.class.*', 'create-class']],
                ['label' => 'Điểm danh', 'icon' => 'calendar-check', 'route' => 'lecturer.attendance.index', 'active' => 'lecturer.attendance.*'],
                ['label' => 'Quản lý học viên', 'icon' => 'users', 'route' => 'lecturer.students.index', 'active' => 'lecturer.students.*'],
                ['label' => 'Duyệt đơn xin nghỉ', 'icon' => 'file-text', 'route' => 'lecturer.leave-requests.index', 'active' => 'lecturer.leave-requests.*'],
            ],
        ],
        [
            'type' => 'group',
            'label' => 'Không gian Học viên',
            'icon' => 'user',
            'activePattern' => ['joined-classes', 'student.classes.show', 'student.attendance.history', 'student.attendance.stats', 'student.leave-requests.*'],
            'items' => [
                ['label' => 'Lớp tôi tham gia', 'icon' => 'book-open', 'route' => 'joined-classes', 'active' => ['joined-classes', 'student.classes.show']],
                ['label' => 'Lịch sử điểm danh', 'icon' => 'history', 'route' => 'student.attendance.history', 'active' => 'student.attendance.history'],
                ['label' => 'Thống kê chuyên cần', 'icon' => 'bar-chart', 'route' => 'student.attendance.stats', 'active' => 'student.attendance.stats'],
                ['label' => 'Xin nghỉ phép', 'icon' => 'file-text', 'route' => 'student.leave-requests.create', 'active' => 'student.leave-requests.*'],
            ],
        ],
        ['type' => 'link', 'label' => 'Cảnh báo', 'icon' => 'alert-triangle', 'route' => 'student.warnings', 'active' => 'student.warnings'],
        ['type' => 'link', 'label' => 'Nâng cấp gói', 'icon' => 'zap', 'route' => 'upgrade', 'active' => 'upgrade'],
        ['type' => 'link', 'label' => 'Hồ sơ cá nhân', 'icon' => 'user-circle', 'route' => 'profile.edit', 'active' => 'profile.*'],
    ];

    $mobileItems = [
        ['label' => 'Tổng quan', 'icon' => 'home', 'route' => 'dashboard', 'active' => 'dashboard'],
        ['label' => 'Chủ lớp', 'icon' => 'shield', 'route' => 'managed-classes', 'active' => ['managed-classes', 'lecturer.classes.*', 'lecturer.class.*', 'lecturer.attendance.*', 'lecturer.students.*', 'lecturer.leave-requests.*']],
        ['label' => 'Học viên', 'icon' => 'user', 'route' => 'joined-classes', 'active' => ['joined-classes', 'student.classes.*', 'student.attendance.*', 'student.leave-requests.*']],
        ['label' => 'Hồ sơ', 'icon' => 'user-circle', 'route' => 'profile.edit', 'active' => 'profile.*'],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ? $title.' - ' : '' }}{{ config('app.name', 'EduTrack') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('favicon.png?v=' . time()) }}">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div x-data="{ showFabMenu: false }" class="relative flex h-screen overflow-hidden bg-background text-on-surface">
            <aside class="z-50 hidden h-screen w-sidebar-width shrink-0 flex-col gap-stack-sm border-r border-outline-variant/20 bg-surface-container-lowest p-stack-md md:flex">
                <div class="mb-4 px-4 py-6">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-white shadow-lg shadow-primary/20">
                            <x-user.icon name="school" :size="24" />
                        </span>
                        <span>
                            <span class="block font-headline-md text-headline-sm font-bold leading-tight text-primary">EduTrack</span>
                            <span class="block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant/70">Hệ thống điểm danh</span>
                        </span>
                    </a>
                </div>

                <nav class="flex-1 space-y-4 overflow-y-auto overflow-x-hidden px-3 py-2">
                    <div class="space-y-2">
                        @foreach ($navData as $nav)
                            @if($nav['type'] === 'link')
                                @php
                                    $activePattern = $nav['active'] ?? ($nav['route'] ?? null);
                                    $isActive = $activePattern ? $matchesActive($activePattern) : false;
                                    $href = isset($nav['route']) ? route($nav['route']) : $nav['href'];
                                @endphp
                                <a
                                    href="{{ $href }}"
                                    @class([
                                        'flex w-full items-center gap-3 rounded-lg px-3 py-3.5 text-base font-bold transition-all',
                                        'bg-primary-container text-on-primary-container' => $isActive,
                                        'text-on-surface-variant hover:bg-surface-container-high' => ! $isActive,
                                    ])
                                >
                                    <x-user.icon :name="$nav['icon']" :size="20" class="shrink-0" />
                                    <span class="truncate">{{ $nav['label'] }}</span>
                                </a>
                            @elseif($nav['type'] === 'group')
                                @php
                                    $isGroupActive = false;
                                    if (isset($nav['activePattern'])) {
                                        $isGroupActive = $matchesActive($nav['activePattern']);
                                    }
                                @endphp
                                
                                <div x-data="{ open: {{ $isGroupActive ? 'true' : 'false' }} }" class="space-y-2 mt-3">
                                    <button @click="open = !open" class="flex w-full items-center justify-between rounded-lg px-3 py-3.5 text-base font-bold text-on-surface-variant transition-all hover:bg-surface-container-high focus:outline-none" :class="open ? 'text-primary' : ''">
                                        <div class="flex items-center gap-3">
                                            <x-user.icon :name="$nav['icon']" :size="20" class="shrink-0" />
                                            <span class="truncate">{{ $nav['label'] }}</span>
                                        </div>
                                        <x-user.icon name="chevron-down" :size="16" class="shrink-0 transition-transform duration-200" x-bind:class="open ? 'rotate-180' : ''" />
                                    </button>

                                    <div x-show="open" x-collapse class="space-y-2 pl-9 pt-1">
                                        @foreach ($nav['items'] as $item)
                                            @php
                                                $activePattern = $item['active'] ?? ($item['route'] ?? null);
                                                $isActive = $activePattern ? $matchesActive($activePattern) : false;
                                                $href = isset($item['route']) ? route($item['route']) : $item['href'];
                                            @endphp

                                            <a
                                                href="{{ $href }}"
                                                @class([
                                                    'flex items-center gap-3 rounded-lg px-3 py-3.5 text-base font-semibold transition-all',
                                                    'bg-primary/10 text-primary font-bold' => $isActive,
                                                    'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' => ! $isActive,
                                                ])
                                            >
                                                <x-user.icon :name="$item['icon']" :size="18" class="shrink-0" />
                                                <span class="truncate">{{ $item['label'] }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </nav>

                <div class="mt-auto space-y-1 border-t border-outline-variant/20 px-2 pt-2 pb-1">
                    <a href="{{ route('support') }}" class="flex w-full items-center gap-3 rounded-lg px-3 py-1 text-base font-bold text-on-surface-variant transition-all hover:bg-surface-container-high">
                        <x-user.icon name="help-circle" :size="20" class="shrink-0" />
                        <span class="truncate">{{ __('Hỗ trợ') ?? 'Hỗ trợ' }}</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-1 text-base font-bold text-error transition-all hover:bg-error-container/40">
                            <x-user.icon name="log-out" :size="20" class="shrink-0" />
                            <span class="truncate">{{ __('Đăng xuất') ?? 'Đăng xuất' }}</span>
                        </button>
                    </form>
                </div>
            </aside>

            <main class="flex h-full min-w-0 flex-1 flex-col overflow-hidden">
                <header class="sticky top-0 z-40 flex h-16 w-full items-center justify-between border-b border-outline-variant/30 bg-surface/80 px-gutter shadow-sm backdrop-blur-md">
                    <div class="max-w-xl flex-1">
                    </div>

                    <div class="ml-4 flex items-center gap-4">
                        <div class="hidden items-center gap-2 md:flex">
                            <a href="{{ route('create-class') }}" class="flex items-center gap-2 rounded-full bg-primary py-2 pl-3 pr-4 text-label-md font-bold text-white transition-all hover:shadow-lg active:scale-95">
                                <x-user.icon name="plus" :size="16" />
                                Học phần mới
                            </a>
                            <button type="button" x-on:click="$dispatch('open-join-class-modal')" class="group flex items-center gap-2 rounded-full border border-outline-variant/20 bg-surface-container px-4 py-2 text-label-md font-bold text-on-surface-variant transition-all hover:bg-surface-container-highest">
                                <x-user.icon name="log-in" :size="16" class="transition-colors group-hover:text-primary" />
                                Tham gia lớp
                            </button>
                        </div>

                        <div class="flex items-center gap-1 md:ml-4 md:border-l md:border-outline-variant/30 md:pl-4">
                            <x-notification-dropdown
                                :all-url="route('notifications')"
                                :notifications="$notificationData['items']"
                                :show-indicator="$notificationData['has_unread']"
                            />
                            <button type="button" class="hidden rounded-full p-2 text-on-surface-variant transition-colors hover:bg-surface-container-high sm:block">
                                <x-user.icon name="settings" :size="20" />
                            </button>

                            <div class="ml-2 relative" x-data="{ openProfile: false }" @click.away="openProfile = false">
                                <button type="button" @click="openProfile = !openProfile" class="flex items-center gap-3 focus:outline-none">
                                    <div class="hidden text-right sm:block">
                                        <p class="font-label-md font-bold leading-none text-on-surface">{{ $userName }}</p>
                                        <p class="mt-1 max-w-[150px] truncate text-[10px] text-on-surface-variant">{{ $userRole }}</p>
                                    </div>
                                    <div class="h-10 w-10 shrink-0 overflow-hidden rounded-full border-2 border-primary/20 bg-surface-container p-0.5 transition-transform hover:scale-105" :class="openProfile ? 'ring-2 ring-primary ring-offset-2' : ''">
                                        <img src="{{ Auth::user()?->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode('Guest').'&color=FFFFFF&background=4285F4' }}" alt="{{ $userName }}" class="h-full w-full rounded-full object-cover">
                                    </div>
                                </button>

                                <div x-show="openProfile" 
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                                     class="absolute right-0 mt-3 w-56 origin-top-right rounded-xl bg-white shadow-xl ring-1 ring-black/5 z-50 divide-y divide-gray-100" style="display: none;" x-cloak>
                                    <div class="px-4 py-3">
                                        <p class="truncate text-base font-bold text-gray-900">{{ Auth::user()?->email ?? 'user@example.com' }}</p>
                                    </div>
                                    <div class="py-1">
                                        <a href="{{ route('upgrade') }}" class="group flex items-center px-4 py-2 text-sm font-bold text-primary hover:bg-primary/5 transition-colors">
                                            <x-user.icon name="zap" :size="18" class="mr-3 text-primary" />
                                            Nâng cấp gói
                                        </a>
                                        <a href="{{ route('profile.edit') }}" class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-slate-50 hover:text-primary transition-colors">
                                            <x-user.icon name="user" :size="18" class="mr-3 text-gray-400 group-hover:text-primary transition-colors" />
                                            Thông tin cá nhân
                                        </a>
                                    </div>
                                    <div class="py-1">
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="group flex w-full items-center px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 transition-colors">
                                                <x-user.icon name="log-out" :size="18" class="mr-3 text-rose-500" />
                                                Đăng xuất
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <div id="main-scroll-area" class="relative flex-1 overflow-auto pb-24 md:pb-0">
                    {{ $slot }}
                </div>
            </main>

            <nav class="pb-safe fixed bottom-0 left-0 right-0 z-40 flex h-16 items-center justify-around border-t border-outline-variant/20 bg-surface/90 px-2 backdrop-blur-lg md:hidden">
                <a href="{{ route($mobileItems[0]['route']) }}" @class([
                    'flex h-full w-full flex-col items-center justify-center transition-colors',
                    'text-primary' => $matchesActive($mobileItems[0]['active']),
                    'text-on-surface-variant hover:text-primary' => ! $matchesActive($mobileItems[0]['active']),
                ])>
                    <x-user.icon :name="$mobileItems[0]['icon']" :size="20" />
                    <span class="mt-1 text-[10px] font-bold">{{ $mobileItems[0]['label'] }}</span>
                </a>
                <a href="{{ route($mobileItems[1]['route']) }}" @class([
                    'flex h-full w-full flex-col items-center justify-center transition-colors',
                    'text-primary' => $matchesActive($mobileItems[1]['active']),
                    'text-on-surface-variant hover:text-primary' => ! $matchesActive($mobileItems[1]['active']),
                ])>
                    <x-user.icon :name="$mobileItems[1]['icon']" :size="20" />
                    <span class="mt-1 text-[10px] font-bold">{{ $mobileItems[1]['label'] }}</span>
                </a>

                <div class="relative flex w-16 justify-center"></div>

                <a href="{{ route($mobileItems[2]['route']) }}" @class([
                    'flex h-full w-full flex-col items-center justify-center transition-colors',
                    'text-primary' => $matchesActive($mobileItems[2]['active']),
                    'text-on-surface-variant hover:text-primary' => ! $matchesActive($mobileItems[2]['active']),
                ])>
                    <x-user.icon :name="$mobileItems[2]['icon']" :size="20" />
                    <span class="mt-1 text-[10px] font-bold">{{ $mobileItems[2]['label'] }}</span>
                </a>
                <a href="{{ route($mobileItems[3]['route']) }}" @class([
                    'flex h-full w-full flex-col items-center justify-center transition-colors',
                    'text-primary' => $matchesActive($mobileItems[3]['active']),
                    'text-on-surface-variant hover:text-primary' => ! $matchesActive($mobileItems[3]['active']),
                ])>
                    <x-user.icon :name="$mobileItems[3]['icon']" :size="20" />
                    <span class="mt-1 text-[10px] font-bold">{{ $mobileItems[3]['label'] }}</span>
                </a>
            </nav>

            <div class="fixed bottom-6 left-1/2 z-50 -translate-x-1/2 md:hidden">
                <button
                    type="button"
                    x-on:click="showFabMenu = ! showFabMenu"
                    class="flex h-14 w-14 items-center justify-center rounded-full bg-primary text-white shadow-lg shadow-primary/30 transition-all hover:scale-105 active:scale-95"
                    aria-label="Mở thao tác nhanh"
                >
                    <x-user.icon name="plus" :size="28" class="transition-transform duration-300" x-bind:class="{ 'rotate-45': showFabMenu }" />
                </button>

                <div
                    x-cloak
                    x-bind:class="showFabMenu ? 'scale-100 opacity-100' : 'scale-0 opacity-0 pointer-events-none'"
                    class="absolute bottom-16 left-1/2 mb-2 flex w-48 origin-bottom -translate-x-1/2 flex-col items-center gap-3 transition-all"
                >
                    <a href="#" class="flex w-full items-center justify-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-bold text-on-surface shadow-md transition-colors hover:bg-surface-container">
                        <x-user.icon name="send" :size="16" class="text-secondary" />
                        Gửi đơn xin nghỉ
                    </a>
                    <a href="{{ route('lecturer.attendance.create') }}" class="flex w-full items-center justify-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-bold text-on-surface shadow-md transition-colors hover:bg-surface-container">
                        <x-user.icon name="check-square" :size="16" class="text-tertiary" />
                        Tạo điểm danh
                    </a>
                    <a href="{{ route('joined-classes') }}" class="flex w-full items-center justify-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-bold text-on-surface shadow-md transition-colors hover:bg-surface-container">
                        <x-user.icon name="qr-code" :size="16" class="text-primary" />
                        Quét QR
                    </a>
                    <button type="button" x-on:click="$dispatch('open-join-class-modal')" class="flex w-full items-center justify-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-bold text-on-surface shadow-md transition-colors hover:bg-surface-container">
                        <x-user.icon name="log-in" :size="16" class="text-on-surface-variant" />
                        Tham gia lớp
                    </button>
                </div>
            </div>
        </div>
        
        <x-notification.notification />
        <livewire:student.join-class />
        <livewire:lecturer.class-settings />
    </body>
</html>
