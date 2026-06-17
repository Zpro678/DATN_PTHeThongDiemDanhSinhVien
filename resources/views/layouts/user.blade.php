@props(['title' => null])

@php
    $userName = Auth::user()?->name ?? 'Nguyễn Văn A';
    $userRole = Auth::user()?->email ?? 'User';

    $navItems = [
        ['label' => 'Tổng quan', 'icon' => 'layout-dashboard', 'route' => 'dashboard'],
        ['label' => 'Lớp tôi quản lý', 'icon' => 'book-open', 'route' => 'managed-classes'],
        ['label' => 'Lớp tôi tham gia', 'icon' => 'log-in', 'route' => 'joined-classes'],
        ['label' => 'Không gian Chủ lớp', 'icon' => 'shield', 'href' => route('dashboard').'#admin', 'active' => 'dashboard'],
        ['label' => 'Không gian Học viên', 'icon' => 'user', 'href' => route('dashboard').'#student', 'active' => 'dashboard'],
        ['label' => 'Tham gia lớp', 'icon' => 'log-in', 'href' => '#join'],
        ['label' => 'Lịch sử điểm danh', 'icon' => 'history', 'href' => '#history'],
        ['label' => 'Thông báo', 'icon' => 'bell', 'href' => '#notifications'],
        ['label' => 'Gói dịch vụ', 'icon' => 'package', 'href' => '#subscription'],
        ['label' => 'Hồ sơ cá nhân', 'icon' => 'user-circle', 'route' => 'profile.edit', 'active' => 'profile.*'],
    ];

    $mobileItems = [
        ['label' => 'Tổng quan', 'icon' => 'home', 'route' => 'dashboard', 'active' => 'dashboard'],
        ['label' => 'Chủ lớp', 'icon' => 'shield', 'route' => 'managed-classes', 'active' => 'managed-classes'],
        ['label' => 'Học viên', 'icon' => 'user', 'route' => 'joined-classes', 'active' => 'joined-classes'],
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

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

        @livewireStyles
        @livewireScriptConfig
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

                <nav class="flex-1 space-y-1 overflow-y-auto px-2">
                    @foreach ($navItems as $item)
                        @php
                            $activePattern = $item['active'] ?? ($item['route'] ?? null);
                            $isActive = $activePattern ? request()->routeIs($activePattern) : false;
                            $href = isset($item['route']) ? route($item['route']) : $item['href'];
                        @endphp

                        <a
                            href="{{ $href }}"
                            @class([
                                'flex items-center gap-3 rounded-lg px-4 py-3 font-body-lg transition-all',
                                'active-nav-shadow bg-primary-container font-bold text-on-primary-container' => $isActive,
                                'text-on-surface-variant hover:bg-surface-container-high' => ! $isActive,
                            ])
                        >
                            <x-user.icon :name="$item['icon']" :size="20" />
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="mt-auto space-y-1 border-t border-outline-variant/20 px-2 pt-4">
                    <button type="button" class="flex w-full items-center gap-3 rounded-lg px-4 py-3 text-left font-body-lg text-on-surface-variant transition-all hover:bg-surface-container-high">
                        <x-user.icon name="help-circle" :size="20" />
                        <span>Hỗ trợ</span>
                    </button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-4 py-3 text-left font-body-lg text-error transition-all hover:bg-error-container/40">
                            <x-user.icon name="log-out" :size="20" />
                            <span>Đăng xuất</span>
                        </button>
                    </form>
                </div>
            </aside>

            <main class="flex h-full min-w-0 flex-1 flex-col overflow-hidden">
                <header class="sticky top-0 z-40 flex h-16 w-full items-center justify-between border-b border-outline-variant/30 bg-surface/80 px-gutter shadow-sm backdrop-blur-md">
                    <div class="max-w-xl flex-1">
                        <label class="flex w-full items-center rounded-full border border-outline-variant/30 bg-surface-container-low px-4 py-1.5">
                            <x-user.icon name="search" :size="20" class="mr-2 text-outline" />
                            <input
                                type="text"
                                placeholder="Tìm lớp, sinh viên, buổi điểm danh..."
                                class="w-full border-none bg-transparent p-0 text-body-md text-on-surface placeholder:text-on-surface-variant focus:outline-none focus:ring-0"
                            >
                        </label>
                    </div>

                    <div class="ml-4 flex items-center gap-4">
                        <div class="hidden items-center gap-2 md:flex">
                            <a href="{{ route('create-class') }}" class="flex items-center gap-2 rounded-full bg-primary py-2 pl-3 pr-4 text-label-md font-bold text-white transition-all hover:shadow-lg active:scale-95">
                                <x-user.icon name="plus" :size="16" />
                                Học phần mới
                            </a>
                            <a href="{{ route('joined-classes') }}" class="group flex items-center gap-2 rounded-full border border-outline-variant/20 bg-surface-container px-4 py-2 text-label-md font-bold text-on-surface-variant transition-all hover:bg-surface-container-highest">
                                <x-user.icon name="log-in" :size="16" class="transition-colors group-hover:text-primary" />
                                Tham gia lớp
                            </a>
                        </div>

                        <div class="flex items-center gap-1 md:ml-4 md:border-l md:border-outline-variant/30 md:pl-4">
                            <button type="button" class="relative rounded-full p-2 text-on-surface-variant transition-colors hover:bg-surface-container-high">
                                <x-user.icon name="bell" :size="20" />
                                <span class="absolute right-2 top-2 h-2 w-2 rounded-full border-2 border-surface bg-error"></span>
                            </button>
                            <button type="button" class="hidden rounded-full p-2 text-on-surface-variant transition-colors hover:bg-surface-container-high sm:block">
                                <x-user.icon name="settings" :size="20" />
                            </button>

                            <div class="ml-2 flex items-center gap-3">
                                <div class="hidden text-right sm:block">
                                    <p class="font-label-md font-bold leading-none text-on-surface">{{ $userName }}</p>
                                    <p class="mt-1 max-w-[150px] truncate text-[10px] text-on-surface-variant">{{ $userRole }}</p>
                                </div>
                                <div class="h-10 w-10 shrink-0 overflow-hidden rounded-full border-2 border-primary/20 bg-surface-container p-0.5">
                                    <img
                                        src="https://api.dicebear.com/7.x/avataaars/svg?seed={{ urlencode($userName) }}&backgroundColor=e5eeff"
                                        alt="{{ $userName }}"
                                        class="h-full w-full rounded-full object-cover"
                                    >
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
                    'text-primary' => request()->routeIs($mobileItems[0]['active']),
                    'text-on-surface-variant hover:text-primary' => ! request()->routeIs($mobileItems[0]['active']),
                ])>
                    <x-user.icon :name="$mobileItems[0]['icon']" :size="20" />
                    <span class="mt-1 text-[10px] font-bold">{{ $mobileItems[0]['label'] }}</span>
                </a>
                <a href="{{ route($mobileItems[1]['route']) }}" @class([
                    'flex h-full w-full flex-col items-center justify-center transition-colors',
                    'text-primary' => request()->routeIs($mobileItems[1]['active']),
                    'text-on-surface-variant hover:text-primary' => ! request()->routeIs($mobileItems[1]['active']),
                ])>
                    <x-user.icon :name="$mobileItems[1]['icon']" :size="20" />
                    <span class="mt-1 text-[10px] font-bold">{{ $mobileItems[1]['label'] }}</span>
                </a>

                <div class="relative flex w-16 justify-center"></div>

                <a href="{{ route($mobileItems[2]['route']) }}" @class([
                    'flex h-full w-full flex-col items-center justify-center transition-colors',
                    'text-primary' => request()->routeIs($mobileItems[2]['active']),
                    'text-on-surface-variant hover:text-primary' => ! request()->routeIs($mobileItems[2]['active']),
                ])>
                    <x-user.icon :name="$mobileItems[2]['icon']" :size="20" />
                    <span class="mt-1 text-[10px] font-bold">{{ $mobileItems[2]['label'] }}</span>
                </a>
                <a href="{{ route($mobileItems[3]['route']) }}" @class([
                    'flex h-full w-full flex-col items-center justify-center transition-colors',
                    'text-primary' => request()->routeIs($mobileItems[3]['active']),
                    'text-on-surface-variant hover:text-primary' => ! request()->routeIs($mobileItems[3]['active']),
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
                    <a href="{{ route('managed-classes') }}" class="flex w-full items-center justify-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-bold text-on-surface shadow-md transition-colors hover:bg-surface-container">
                        <x-user.icon name="check-square" :size="16" class="text-tertiary" />
                        Tạo điểm danh
                    </a>
                    <a href="{{ route('joined-classes') }}" class="flex w-full items-center justify-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-bold text-on-surface shadow-md transition-colors hover:bg-surface-container">
                        <x-user.icon name="qr-code" :size="16" class="text-primary" />
                        Quét QR
                    </a>
                    <a href="{{ route('joined-classes') }}" class="flex w-full items-center justify-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-bold text-on-surface shadow-md transition-colors hover:bg-surface-container">
                        <x-user.icon name="log-in" :size="16" class="text-on-surface-variant" />
                        Tham gia lớp
                    </a>
                </div>
            </div>
        </div>
    </body>
</html>
