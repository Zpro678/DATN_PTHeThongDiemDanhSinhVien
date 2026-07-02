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
        ['label' => 'Xin nghỉ phép', 'icon' => 'send', 'route' => 'student.leave-requests.history', 'active' => 'student.leave-requests.*', 'desc' => 'Gửi yêu cầu nghỉ có phép'],
    ];

    // $navMenus = [
    //     ['label' => 'Giảng dạy', 'icon' => 'shield', 'items' => $teachItems],
    //     ['label' => 'Học tập', 'icon' => 'graduation-cap', 'items' => $learnItems],
    // ];

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
    $teachGroupActive = $menuActive($teachItems);
    $learnGroupActive = $menuActive($learnItems);

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

        <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name', 'Attendia Tech') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('favicon.png?v=' . time()) }}">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-surface font-sans antialiased text-on-surface">
        <div
            x-data="{
                navOpen: false,
                sidebarCollapsed: (localStorage.getItem('sidebarCollapsed') === '1'),
            }"
            x-init="$watch('sidebarCollapsed', value => localStorage.setItem('sidebarCollapsed', value ? '1' : '0'))"
            class="flex min-h-screen flex-col bg-surface"
        >

            {{-- ============================ TOP NAVBAR (full width) ============================ --}}
            <header class="sticky top-0 z-40 h-16 shrink-0 border-b border-outline-variant bg-white">
                <div class="flex h-16 w-full items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">

                    {{-- Left cluster: hamburger (mobile) / sidebar toggle (desktop) + Brand --}}
                    <div class="flex min-w-0 items-center gap-2 sm:gap-3">
                        <button type="button" x-on:click="navOpen = true"
                            class="grid h-9 w-9 shrink-0 place-items-center rounded-lg text-on-surface-variant hover:bg-surface-container md:hidden">
                            <x-user.icon name="menu" :size="20" />
                        </button>

                        {{-- Desktop sidebar toggle — đặt trong rail 76px canh giữa trùng cột icon của sidebar --}}
                        <div class="hidden shrink-0 place-items-center w-[76px] -ml-4 sm:-ml-6 lg:-ml-8 md:grid">
                            <button type="button" x-on:click="sidebarCollapsed = !sidebarCollapsed"
                                class="grid h-9 w-9 place-items-center rounded-lg text-on-surface-variant transition-colors hover:bg-surface-container hover:text-on-surface"
                                :aria-label="sidebarCollapsed ? 'Mở rộng thanh bên' : 'Thu gọn thanh bên'">
                                <x-user.icon name="menu" :size="20" />
                            </button>
                        </div>

                        <a href="{{ route('dashboard') }}" wire:navigate class="flex min-w-0 shrink-0 items-center gap-2.5">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-primary text-white shadow-sm shadow-primary/30">
                                <x-user.icon name="school" :size="18" />
                            </span>
                            <span class="hidden leading-tight sm:block">
                                <span class="block text-[16px] font-extrabold tracking-tight text-on-surface">Attendia Tech</span>
                                <span class="block text-[10px] font-semibold uppercase tracking-wider text-on-surface-variant">Hệ thống điểm danh</span>
                            </span>
                        </a>
                    </div>

                    {{-- Right cluster --}}
                    <div class="ml-auto flex items-center gap-2 sm:gap-3">

                        {{-- Tham gia (desktop) --}}
                        <button type="button" x-data x-on:click="$dispatch('open-join-class-modal')" class="hidden sm:flex h-10 items-center gap-2 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 px-4 text-sm font-semibold transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 shadow-sm border border-emerald-200">
                            <x-user.icon name="log-in" :size="18" />
                            Tham gia
                        </button>

                        {{-- Tạo lớp (desktop) --}}
                        <a href="{{ route('create-class') }}" wire:navigate class="hidden sm:flex h-10 items-center gap-2 rounded-xl bg-primary px-4 text-sm font-semibold text-white shadow-sm shadow-primary/30 transition-all hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                            <x-user.icon name="plus" :size="18" />
                            Tạo lớp
                        </a>

                        {{-- Mobile Create/Join Menu --}}
                        <div class="sm:hidden relative" x-data="{ openCreate: false }" x-on:click.away="openCreate = false">
                            <button type="button" x-on:click="openCreate = !openCreate" class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary transition-colors hover:bg-primary/20 focus:outline-none">
                                <x-user.icon name="plus" :size="20" />
                            </button>
                            <div x-cloak x-show="openCreate" class="absolute right-0 mt-2 w-48 rounded-xl border border-outline-variant bg-white p-2 shadow-xl z-50">
                                <a href="{{ route('create-class') }}" wire:navigate class="flex items-center gap-3 rounded-lg p-2 hover:bg-surface-container">
                                    <div class="grid h-8 w-8 place-items-center rounded bg-primary/10 text-primary"><x-user.icon name="plus" :size="16" /></div>
                                    <span class="text-sm font-semibold text-on-surface">Tạo lớp</span>
                                </a>
                                <button type="button" x-on:click="openCreate = false; $dispatch('open-join-class-modal')" class="flex w-full items-center gap-3 rounded-lg p-2 hover:bg-surface-container mt-1 text-left">
                                    <div class="grid h-8 w-8 place-items-center rounded bg-emerald-50 text-emerald-600"><x-user.icon name="log-in" :size="16" /></div>
                                    <span class="text-sm font-semibold text-on-surface">Tham gia</span>
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

            {{-- ============================ BODY: SIDEBAR + CONTENT ============================ --}}
            <div class="flex min-h-0 flex-1">

                {{-- ============================ SIDEBAR (≥ md) ============================ --}}
                <aside
                    class="sticky top-16 hidden h-[calc(100vh-4rem)] shrink-0 flex-col overflow-hidden border-r border-outline-variant bg-white md:flex"
                    :class="sidebarCollapsed ? 'w-[76px]' : 'w-[260px]'"
                >
                    <nav class="scrollbar-custom flex-1 space-y-2 overflow-y-auto overflow-x-hidden px-3 py-5">
                        {{-- Tổng quan --}}
                        <a href="{{ route('dashboard') }}" wire:navigate title="Tổng quan"
                            :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                            @class([
                                'flex items-center gap-3 rounded-lg px-3 py-3 text-[17px] font-medium transition-colors',
                                'bg-primary/10 text-primary' => $dashboardActive,
                                'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' => ! $dashboardActive,
                            ])>
                            <x-user.icon name="layout-dashboard" :size="24" class="shrink-0" />
                            <span class="truncate" x-show="!sidebarCollapsed">Tổng quan</span>
                        </a>

                        {{-- Giảng dạy --}}
                        <a href="{{ route('managed-classes') }}" wire:navigate title="Giảng dạy"
                            :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                            @class([
                                'flex items-center gap-3 rounded-lg px-3 py-3 text-[17px] font-medium transition-colors',
                                'bg-primary/10 text-primary' => $teachGroupActive,
                                'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' => ! $teachGroupActive,
                            ])>
                            <x-user.icon name="shield" :size="24" class="shrink-0" />
                            <span class="truncate" x-show="!sidebarCollapsed">Giảng dạy</span>
                        </a>

                        {{-- Học tập --}}
                        <a href="{{ route('joined-classes') }}" wire:navigate title="Học tập"
                            :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                            @class([
                                'flex items-center gap-3 rounded-lg px-3 py-3 text-[17px] font-medium transition-colors',
                                'bg-primary/10 text-primary' => $learnGroupActive,
                                'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' => ! $learnGroupActive,
                            ])>
                            <x-user.icon name="graduation-cap" :size="24" class="shrink-0" />
                            <span class="truncate" x-show="!sidebarCollapsed">Học tập</span>
                        </a>

                        {{-- Cảnh báo --}}
                        <a href="{{ route('student.warnings') }}" wire:navigate title="Cảnh báo"
                            :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                            @class([
                                'flex items-center gap-3 rounded-lg px-3 py-3 text-[17px] font-medium transition-colors',
                                'bg-primary/10 text-primary' => $warningsActive,
                                'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' => ! $warningsActive,
                            ])>
                            <x-user.icon name="alert-triangle" :size="24" class="shrink-0" />
                            <span class="truncate" x-show="!sidebarCollapsed">Cảnh báo</span>
                        </a>
                    </nav>

                    <div class="space-y-1 border-t border-outline-variant p-3">
                        <a href="{{ route('support') }}" wire:navigate title="Hỗ trợ"
                            :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                            class="flex items-center gap-3 rounded-lg px-3 py-2 text-[17px] font-medium text-on-surface-variant transition-colors hover:bg-surface-container hover:text-on-surface">
                            <x-user.icon name="help-circle" :size="24" class="shrink-0" /> <span x-show="!sidebarCollapsed">Hỗ trợ</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" title="Đăng xuất"
                                :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-[17px] font-medium text-error transition-colors hover:bg-error/10">
                                <x-user.icon name="log-out" :size="24" class="shrink-0" /> <span x-show="!sidebarCollapsed">Đăng xuất</span>
                            </button>
                        </form>
                    </div>
                </aside>

                {{-- ============================ MAIN CONTENT WRAPPER ============================ --}}
                <div class="flex min-w-0 flex-1 flex-col">

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
                                    <span class="text-[15px] font-extrabold text-on-surface">Attendia Tech</span>
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
                                                    'flex items-center gap-3 rounded-lg px-3 py-2 text-[17px] font-medium transition-colors',
                                                    'bg-primary/10 text-primary' => $isActive,
                                                    'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' => ! $isActive,
                                                ])>
                                                <x-user.icon :name="$item['icon']" :size="24" class="shrink-0" />
                                                <span class="truncate">{{ $item['label'] }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @endforeach
                            </nav>
                            <div class="space-y-1 border-t border-outline-variant p-3">
                                <a href="{{ route('support') }}" wire:navigate x-on:click="navOpen = false" class="flex items-center gap-3 rounded-lg px-3 py-2 text-[17px] font-medium text-on-surface-variant transition-colors hover:bg-surface-container hover:text-on-surface">
                                    <x-user.icon name="help-circle" :size="24" class="shrink-0" /> <span>Hỗ trợ</span>
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-[17px] font-medium text-error transition-colors hover:bg-error/10">
                                        <x-user.icon name="log-out" :size="24" class="shrink-0" /> <span>Đăng xuất</span>
                                    </button>
                                </form>
                            </div>
                        </aside>
                    </div>


                    {{-- ============ MAIN CONTENT ============
                         Mỗi trang tự bọc `mx-auto max-w-7xl … p-4/6/8 pb-24` của riêng nó,
                         nên main giữ trong suốt để tránh container/padding lồng nhau. --}}
                    <main class="relative flex-1 flex flex-col">
                        {{-- Body Sub-Nav Tabs --}}
                        @if ($teachGroupActive || $learnGroupActive)
                            <div class="w-full bg-surface border-b border-outline-variant/50 sticky top-16 z-30">
                                <div class="flex h-14 w-full items-center gap-8 px-6 sm:px-10 lg:px-16 overflow-x-auto [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
                                    @if ($teachGroupActive)
                                        @foreach ($teachItems as $item)
                                            @php $isActive = $matchesActive($item['active'] ?? $item['route']); @endphp
                                            <a href="{{ route($item['route']) }}" wire:navigate
                                                @class([
                                                    'relative inline-flex h-14 shrink-0 items-center px-2 text-[15px] font-medium transition-colors',
                                                    'text-[#1a73e8] after:absolute after:left-2 after:right-2 after:bottom-0 after:h-1 after:rounded-t-[4px] after:bg-[#1a73e8]' => $isActive,
                                                    'text-[#3c4043] hover:text-[#1a73e8] after:absolute after:left-2 after:right-2 after:bottom-0 after:h-1 after:rounded-t-[4px] after:bg-[#1a73e8] after:scale-x-0 hover:after:scale-x-100 after:transition-transform after:duration-300 after:origin-center' => ! $isActive,
                                                ])>
                                                {{ $item['label'] }}
                                            </a>
                                        @endforeach
                                    @elseif ($learnGroupActive)
                                        @foreach ($learnItems as $item)
                                            @php $isActive = $matchesActive($item['active'] ?? $item['route']); @endphp
                                            <a href="{{ route($item['route']) }}" wire:navigate
                                                @class([
                                                    'relative inline-flex h-14 shrink-0 items-center px-2 text-[15px] font-medium transition-colors',
                                                    'text-[#1a73e8] after:absolute after:left-2 after:right-2 after:bottom-0 after:h-1 after:rounded-t-[4px] after:bg-[#1a73e8]' => $isActive,
                                                    'text-[#3c4043] hover:text-[#1a73e8] after:absolute after:left-2 after:right-2 after:bottom-0 after:h-1 after:rounded-t-[4px] after:bg-[#1a73e8] after:scale-x-0 hover:after:scale-x-100 after:transition-transform after:duration-300 after:origin-center' => ! $isActive,
                                                ])>
                                                {{ $item['label'] }}
                                            </a>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endif

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
            </div>
        </div>

        <x-notification.notification />
        <livewire:student.join-class />
    </body>
</html>
