@props(['title' => null, 'activeNav' => null])

@php
    $userName = Auth::user()?->name ?? 'Người dùng';
    $userEmail = Auth::user()?->email ?? 'user@example.com';

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

    // ── Điều hướng nhóm theo vai trò (dùng cho dropdown top-nav & drawer mobile) ──
    $teachItems = [
        ['label' => 'Lớp tôi quản lý', 'icon' => 'book-open', 'route' => 'managed-classes', 'active' => ['managed-classes', 'lecturer.classes.*', 'lecturer.class.*', 'create-class'], 'desc' => 'Học phần bạn làm chủ lớp'],
        ['label' => 'Điểm danh', 'icon' => 'calendar-check', 'route' => 'lecturer.attendance.index', 'active' => 'lecturer.attendance.*', 'desc' => 'Mở phiên & ghi nhận có mặt'],
        ['label' => 'Quản lý học viên', 'icon' => 'users', 'route' => 'lecturer.students.index', 'active' => 'lecturer.students.*', 'desc' => 'Danh sách & hồ sơ học viên'],
        ['label' => 'Duyệt đơn xin nghỉ', 'icon' => 'file-text', 'route' => 'lecturer.leave-requests.index', 'active' => 'lecturer.leave-requests.*', 'desc' => 'Phê duyệt yêu cầu nghỉ phép'],
    ];

    $learnItems = [
        ['label' => 'Lớp tôi tham gia', 'icon' => 'graduation-cap', 'route' => 'joined-classes', 'active' => ['joined-classes', 'student.classes.show'], 'desc' => 'Các lớp bạn đang theo học'],
        ['label' => 'Lịch sử điểm danh', 'icon' => 'history', 'route' => 'student.attendance.history', 'active' => 'student.attendance.history', 'desc' => 'Nhật ký check-in của bạn'],
        ['label' => 'Thống kê chuyên cần', 'icon' => 'bar-chart', 'route' => 'student.attendance.stats', 'active' => 'student.attendance.stats', 'desc' => 'Tỷ lệ & xu hướng đi học'],
        ['label' => 'Xin nghỉ phép', 'icon' => 'send', 'route' => 'student.leave-requests.create', 'active' => 'student.leave-requests.*', 'desc' => 'Gửi yêu cầu nghỉ có phép'],
    ];

    $navMenus = [
        ['label' => 'Giảng dạy', 'icon' => 'shield', 'items' => $teachItems],
        ['label' => 'Học tập', 'icon' => 'graduation-cap', 'items' => $learnItems],
    ];

    // Mức active của từng dropdown = có item con nào đang active không.
    $menuActive = function (array $items) use ($matchesActive): bool {
        foreach ($items as $it) {
            if ($matchesActive($it['active'] ?? $it['route'])) {
                return true;
            }
        }
        return false;
    };

    $dashboardActive = $matchesActive('dashboard');
    $warningsActive = $matchesActive('student.warnings');

    // Cấu trúc đầy đủ cho drawer mobile.
    $navSections = [
        ['label' => null, 'items' => [
            ['label' => 'Tổng quan', 'icon' => 'layout-dashboard', 'route' => 'dashboard', 'active' => 'dashboard'],
        ]],
        ['label' => 'Giảng dạy', 'items' => $teachItems],
        ['label' => 'Học tập', 'items' => $learnItems],
        ['label' => 'Khác', 'items' => [
            ['label' => 'Cảnh báo', 'icon' => 'alert-triangle', 'route' => 'student.warnings', 'active' => 'student.warnings'],
            ['label' => 'Nâng cấp gói', 'icon' => 'zap', 'route' => 'upgrade', 'active' => 'upgrade'],
        ]],
    ];

    $mobileItems = [
        ['label' => 'Tổng quan', 'icon' => 'home', 'route' => 'dashboard', 'active' => 'dashboard'],
        ['label' => 'Giảng dạy', 'icon' => 'shield', 'route' => 'managed-classes', 'active' => ['managed-classes', 'lecturer.*', 'create-class']],
        ['label' => 'Học tập', 'icon' => 'graduation-cap', 'route' => 'joined-classes', 'active' => ['joined-classes', 'student.*']],
    ];

    $firstChar = function_exists('mb_substr') ? mb_substr($userName, 0, 1, 'UTF-8') : substr($userName, 0, 1);
    $userInitial = function_exists('mb_strtoupper') ? mb_strtoupper($firstChar, 'UTF-8') : strtoupper($firstChar);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name', 'EduTrack') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('favicon.png?v=' . time()) }}">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-surface font-sans antialiased text-on-surface">
        <div x-data="{ navOpen: false }" class="flex min-h-screen flex-col">

            {{-- ============================ TOP NAV (≥ md) ============================ --}}
            <header class="sticky top-0 z-40 border-b border-outline-variant bg-white/90 backdrop-blur-md">
                <div class="mx-auto flex h-16 max-w-[1400px] items-center gap-3 px-4 sm:px-6 lg:px-8">

                    {{-- Hamburger (mobile) --}}
                    <button type="button" x-on:click="navOpen = true"
                        class="grid h-9 w-9 shrink-0 place-items-center rounded-lg text-on-surface-variant hover:bg-surface-container md:hidden">
                        <x-user.icon name="menu" :size="20" />
                    </button>

                    {{-- Brand --}}
                    <a href="{{ route('dashboard') }}" wire:navigate class="flex shrink-0 items-center gap-2.5">
                        <span class="grid h-9 w-9 place-items-center rounded-xl bg-primary text-white shadow-sm shadow-primary/30">
                            <x-user.icon name="school" :size="20" />
                        </span>
                        <span class="leading-tight">
                            <span class="block text-[16px] font-extrabold tracking-tight text-on-surface">EduTrack</span>
                            <span class="hidden text-[10px] font-semibold uppercase tracking-wider text-on-surface-variant lg:block">Hệ thống điểm danh</span>
                        </span>
                    </a>

                    {{-- Primary nav (desktop) --}}
                    <nav class="ml-4 hidden h-16 items-center gap-1 md:flex">
                        {{-- Tổng quan --}}
                        <a href="{{ route('dashboard') }}" wire:navigate
                            @class([
                                'relative inline-flex h-16 items-center gap-2 px-3 text-sm font-semibold transition-colors',
                                'text-primary after:absolute after:inset-x-3 after:bottom-0 after:h-0.5 after:rounded-full after:bg-primary' => $dashboardActive,
                                'text-on-surface-variant hover:text-on-surface' => ! $dashboardActive,
                            ])>
                            <x-user.icon name="layout-dashboard" :size="18" /> Tổng quan
                        </a>

                        {{-- Dropdown nhóm vai trò --}}
                        @foreach ($navMenus as $menu)
                            @php $groupActive = $menuActive($menu['items']); @endphp
                            <div class="relative h-16" x-data="{ open: false }" x-on:mouseleave="open = false">
                                <button type="button" x-on:click="open = !open" x-on:mouseenter="open = true"
                                    @class([
                                        'relative inline-flex h-16 items-center gap-2 px-3 text-sm font-semibold transition-colors',
                                        'text-primary after:absolute after:inset-x-3 after:bottom-0 after:h-0.5 after:rounded-full after:bg-primary' => $groupActive,
                                        'text-on-surface-variant hover:text-on-surface' => ! $groupActive,
                                    ])>
                                    <x-user.icon :name="$menu['icon']" :size="18" /> {{ $menu['label'] }}
                                    <x-user.icon name="chevron-down" :size="15" class="transition-transform" x-bind:class="open && 'rotate-180'" />
                                </button>

                                <div x-cloak x-show="open"
                                    x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                    class="absolute left-0 top-[60px] z-50 w-[320px] overflow-hidden rounded-2xl border border-outline-variant bg-white p-2 shadow-xl shadow-slate-900/10">
                                    @foreach ($menu['items'] as $item)
                                        @php $isActive = $matchesActive($item['active'] ?? $item['route']); @endphp
                                        <a href="{{ route($item['route']) }}" wire:navigate
                                            @class([
                                                'group flex items-start gap-3 rounded-xl p-2.5 transition-colors',
                                                'bg-primary/[0.07]' => $isActive,
                                                'hover:bg-surface-container' => ! $isActive,
                                            ])>
                                            <span @class([
                                                'mt-0.5 grid h-9 w-9 shrink-0 place-items-center rounded-lg',
                                                'bg-primary text-white' => $isActive,
                                                'bg-surface-container text-on-surface-variant group-hover:bg-primary/10 group-hover:text-primary' => ! $isActive,
                                            ])>
                                                <x-user.icon :name="$item['icon']" :size="18" />
                                            </span>
                                            <span class="min-w-0">
                                                <span @class(['block text-sm font-semibold', 'text-primary' => $isActive, 'text-on-surface' => ! $isActive])>{{ $item['label'] }}</span>
                                                <span class="block truncate text-xs text-on-surface-variant">{{ $item['desc'] }}</span>
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        {{-- Cảnh báo --}}
                        <a href="{{ route('student.warnings') }}" wire:navigate
                            @class([
                                'relative inline-flex h-16 items-center gap-2 px-3 text-sm font-semibold transition-colors',
                                'text-primary after:absolute after:inset-x-3 after:bottom-0 after:h-0.5 after:rounded-full after:bg-primary' => $warningsActive,
                                'text-on-surface-variant hover:text-on-surface' => ! $warningsActive,
                            ])>
                            <x-user.icon name="alert-triangle" :size="18" /> Cảnh báo
                        </a>
                    </nav>

                    {{-- Right cluster --}}
                    <div class="ml-auto flex items-center gap-1.5 sm:gap-2">
                        {{-- Tạo / Tham gia (desktop) --}}
                        <div class="relative hidden sm:block" x-data="{ openCreate: false }" x-on:click.away="openCreate = false">
                            <button type="button" x-on:click="openCreate = !openCreate"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-3.5 py-2 text-sm font-semibold text-white shadow-sm shadow-primary/30 transition-colors hover:bg-primary-container">
                                <x-user.icon name="plus" :size="16" /> <span class="hidden lg:inline">Tạo / Tham gia</span><span class="lg:hidden">Tạo</span>
                                <x-user.icon name="chevron-down" :size="15" class="transition-transform" x-bind:class="openCreate && 'rotate-180'" />
                            </button>
                            <div x-cloak x-show="openCreate"
                                x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95 -translate-y-1" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                class="absolute right-0 mt-2 w-64 origin-top-right overflow-hidden rounded-2xl border border-outline-variant bg-white p-2 shadow-xl shadow-slate-900/10">
                                <a href="{{ route('create-class') }}" wire:navigate class="group flex items-start gap-3 rounded-xl p-2.5 transition-colors hover:bg-surface-container">
                                    <span class="mt-0.5 grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-primary/10 text-primary"><x-user.icon name="plus" :size="18" /></span>
                                    <span><span class="block text-sm font-semibold text-on-surface">Học phần mới</span><span class="block text-xs text-on-surface-variant">Tạo lớp bạn làm chủ</span></span>
                                </a>
                                <button type="button" x-on:click="openCreate = false; $dispatch('open-join-class-modal')" class="group flex w-full items-start gap-3 rounded-xl p-2.5 text-left transition-colors hover:bg-surface-container">
                                    <span class="mt-0.5 grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-tertiary/10 text-tertiary"><x-user.icon name="log-in" :size="18" /></span>
                                    <span><span class="block text-sm font-semibold text-on-surface">Tham gia lớp</span><span class="block text-xs text-on-surface-variant">Nhập mã lớp để vào học</span></span>
                                </button>
                            </div>
                        </div>

                        {{-- Notifications --}}
                        <x-notification-dropdown
                            :notifications="$notificationData['items']"
                            :show-indicator="$notificationData['has_unread']"
                        />

                        {{-- Avatar --}}
                        <div class="relative" x-data="{ openProfile: false }" x-on:click.away="openProfile = false">
                            <button type="button" x-on:click="openProfile = !openProfile" class="flex items-center gap-2 rounded-xl p-1 pr-1.5 transition-colors hover:bg-surface-container focus:outline-none">
                                <span class="grid h-9 w-9 shrink-0 place-items-center overflow-hidden rounded-full bg-primary/10 text-sm font-bold text-primary ring-1 ring-primary/15">
                                    @if(Auth::user()?->avatar)
                                        <img src="{{ asset('storage/'.Auth::user()->avatar) }}" alt="{{ $userName }}" class="h-full w-full object-cover">
                                    @else
                                        {{ $userInitial }}
                                    @endif
                                </span>
                                <x-user.icon name="chevron-down" :size="16" class="hidden text-on-surface-variant sm:block" />
                            </button>

                            <div x-cloak x-show="openProfile"
                                 x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95 -translate-y-1" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                 class="absolute right-0 mt-2 w-64 origin-top-right overflow-hidden rounded-2xl border border-outline-variant bg-white shadow-xl shadow-slate-900/10">
                                <div class="flex items-center gap-3 border-b border-outline-variant px-4 py-3">
                                    <span class="grid h-10 w-10 shrink-0 place-items-center overflow-hidden rounded-full bg-primary/10 text-sm font-bold text-primary">
                                        @if(Auth::user()?->avatar)
                                            <img src="{{ asset('storage/'.Auth::user()->avatar) }}" alt="{{ $userName }}" class="h-full w-full object-cover">
                                        @else
                                            {{ $userInitial }}
                                        @endif
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-on-surface">{{ $userName }}</p>
                                        <p class="truncate text-xs text-on-surface-variant">{{ $userEmail }}</p>
                                    </div>
                                </div>
                                <div class="py-1">
                                    <a href="{{ route('upgrade') }}" wire:navigate class="flex items-center gap-3 px-4 py-2.5 text-sm font-semibold text-primary transition-colors hover:bg-primary/5">
                                        <x-user.icon name="zap" :size="17" /> Nâng cấp gói
                                    </a>
                                    <a href="{{ route('profile.edit') }}" wire:navigate class="flex items-center gap-3 px-4 py-2.5 text-sm text-on-surface transition-colors hover:bg-surface-container">
                                        <x-user.icon name="user" :size="17" class="text-on-surface-variant" /> Thông tin cá nhân
                                    </a>
                                    <a href="{{ route('support') }}" wire:navigate class="flex items-center gap-3 px-4 py-2.5 text-sm text-on-surface transition-colors hover:bg-surface-container">
                                        <x-user.icon name="help-circle" :size="17" class="text-on-surface-variant" /> Hỗ trợ
                                    </a>
                                </div>
                                <div class="border-t border-outline-variant py-1">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-error transition-colors hover:bg-error/10">
                                            <x-user.icon name="log-out" :size="17" /> Đăng xuất
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- ============ MOBILE DRAWER (< md) ============ --}}
            <div x-cloak x-show="navOpen" class="fixed inset-0 z-[80] md:hidden">
                <div x-show="navOpen" x-transition.opacity x-on:click="navOpen = false" class="absolute inset-0 bg-on-background/40 backdrop-blur-sm"></div>
                <aside x-show="navOpen"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                    class="absolute inset-y-0 left-0 flex w-[280px] max-w-[82%] flex-col bg-white shadow-xl">
                    <div class="flex h-16 items-center justify-between border-b border-outline-variant px-5">
                        <div class="flex items-center gap-2.5">
                            <span class="grid h-9 w-9 place-items-center rounded-xl bg-primary text-white"><x-user.icon name="school" :size="20" /></span>
                            <span class="text-[15px] font-extrabold text-on-surface">EduTrack</span>
                        </div>
                        <button type="button" x-on:click="navOpen = false" class="rounded-lg p-1.5 text-on-surface-variant hover:bg-surface-container">
                            <x-user.icon name="x" :size="20" />
                        </button>
                    </div>
                    <nav class="scrollbar-custom flex-1 space-y-6 overflow-y-auto px-3 py-5">
                        @foreach ($navSections as $section)
                            <div class="space-y-1">
                                @if ($section['label'])
                                    <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-on-surface-variant/70">{{ $section['label'] }}</p>
                                @endif
                                @foreach ($section['items'] as $item)
                                    @php $isActive = $matchesActive($item['active'] ?? $item['route']); @endphp
                                    <a href="{{ route($item['route']) }}" wire:navigate x-on:click="navOpen = false"
                                        @class([
                                            'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                                            'bg-primary/10 text-primary' => $isActive,
                                            'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' => ! $isActive,
                                        ])>
                                        <x-user.icon :name="$item['icon']" :size="18" class="shrink-0" />
                                        <span class="truncate">{{ $item['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @endforeach
                    </nav>
                    <div class="space-y-1 border-t border-outline-variant p-3">
                        <a href="{{ route('support') }}" wire:navigate x-on:click="navOpen = false" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-on-surface-variant transition-colors hover:bg-surface-container hover:text-on-surface">
                            <x-user.icon name="help-circle" :size="18" class="shrink-0" /> <span>Hỗ trợ</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-error transition-colors hover:bg-error/10">
                                <x-user.icon name="log-out" :size="18" class="shrink-0" /> <span>Đăng xuất</span>
                            </button>
                        </form>
                    </div>
                </aside>
            </div>

            {{-- ============ ONGOING SESSION BANNER ============ --}}
            @auth
                @if(request()->routeIs('managed-classes', 'lecturer.*', 'create-class'))
                    @php
                        $ongoingSession = \App\Models\ClassSession::query()
                            ->where('status', 'active')
                            ->whereHas('meeting.courseClass', fn ($q) => $q->where('owner_user_id', auth()->id()))
                            ->with(['meeting.courseClass:id,name,join_key,owner_user_id'])
                            ->latest()
                            ->first();
                    @endphp
                    @if($ongoingSession)
                        <div class="border-b border-primary/20 bg-primary/5 px-4 py-2.5 sm:px-6 lg:px-8">
                            <div class="mx-auto flex max-w-[1400px] items-center justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-2.5">
                                    <span class="relative flex h-2.5 w-2.5 shrink-0">
                                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-primary/60"></span>
                                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-primary"></span>
                                    </span>
                                    <p class="truncate text-sm font-semibold text-on-surface">
                                        Phiên điểm danh đang diễn ra
                                        <span class="font-normal text-on-surface-variant">— {{ $ongoingSession->meeting->courseClass->join_key ?? '' }} · {{ $ongoingSession->name }}</span>
                                    </p>
                                </div>
                                <a href="{{ $ongoingSession->qr_token ? route('lecturer.attendance.qr.session', $ongoingSession) : route('lecturer.attendance.manual.session', $ongoingSession) }}" class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-primary px-3.5 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-primary-container">
                                    Xem ngay <x-user.icon name="arrow-right" :size="14" />
                                </a>
                            </div>
                        </div>
                    @endif
                @endif
            @endauth

            {{-- ============ MAIN CONTENT ============
                 Mỗi trang tự bọc `mx-auto max-w-7xl … p-4/6/8 pb-24` của riêng nó,
                 nên main giữ trong suốt để tránh container/padding lồng nhau. --}}
            <main class="relative flex-1">
                {{ $slot }}
            </main>

            {{-- ============ MOBILE BOTTOM NAV (< md) ============ --}}
            <nav class="pb-safe fixed inset-x-0 bottom-0 z-40 grid h-16 grid-cols-4 border-t border-outline-variant bg-white/95 backdrop-blur-lg md:hidden">
                @foreach ($mobileItems as $mi)
                    @php $isActive = $matchesActive($mi['active']); @endphp
                    <a href="{{ route($mi['route']) }}" wire:navigate @class([
                        'flex flex-col items-center justify-center gap-1 text-[10px] font-medium transition-colors',
                        'text-primary' => $isActive,
                        'text-on-surface-variant' => ! $isActive,
                    ])>
                        <x-user.icon :name="$mi['icon']" :size="20" />
                        {{ $mi['label'] }}
                    </a>
                @endforeach
                <button type="button" x-on:click="navOpen = true" class="flex flex-col items-center justify-center gap-1 text-[10px] font-medium text-on-surface-variant">
                    <x-user.icon name="menu" :size="20" />
                    Thêm
                </button>
            </nav>
        </div>

        <x-notification.notification />
        <livewire:student.join-class />
    </body>
</html>
