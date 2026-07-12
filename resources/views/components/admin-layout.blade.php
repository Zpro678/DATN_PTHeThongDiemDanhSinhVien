@props(['title' => null])

@php
    $user = auth()->user();
    $userName = $user?->name ?: 'Quản trị viên';
    $userEmail = $user?->email ?: 'admin@example.com';
    $firstCharacter = function_exists('mb_substr') ? mb_substr($userName, 0, 1, 'UTF-8') : substr($userName, 0, 1);
    $userInitial = function_exists('mb_strtoupper') ? mb_strtoupper($firstCharacter, 'UTF-8') : strtoupper($firstCharacter);

    $isActive = function ($patterns): bool {
        foreach ((array) $patterns as $pattern) {
            if (request()->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    };

    $menuItems = [
        ['label' => 'Dashboard', 'href' => route('admin.dashboard'), 'icon' => 'layout-dashboard', 'active' => ['admin.dashboard']],
        ['label' => 'Quản lý tài khoản', 'href' => route('admin.users.index'), 'icon' => 'user-square', 'active' => ['admin.users.*']],
        ['label' => 'Quản lý gói dịch vụ', 'href' => route('admin.packages.index'), 'icon' => 'star', 'active' => ['admin.packages.*']],
        ['label' => 'Quản lý giao dịch', 'href' => route('admin.transactions.index'), 'icon' => 'receipt', 'active' => ['admin.transactions.index']],
        [
            'label' => 'Nhật ký hệ thống',
            'icon' => 'activity',
            'href' => route('admin.logs.index'),
            'active' => ['admin.logs.*'],
        ],
        [
            'label' => 'Gửi thông báo',
            'icon' => 'send',
            'href' => route('admin.broadcast'),
            'active' => ['admin.broadcast'],
        ],
        [
            'label' => 'Quản lý phản hồi',
            'icon' => 'message-square',
            'href' => route('admin.feedbacks'),
            'active' => ['admin.feedbacks'],
        ],
        ['label' => 'Báo cáo & thống kê', 'href' => route('admin.reports.index'), 'icon' => 'bar-chart', 'active' => ['admin.reports.*']],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ? $title.' - ' : '' }}{{ config('app.name', 'Attendia Tech') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
        
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        
        <!-- Icons -->
    </head>
    <body class="bg-slate-50 text-slate-900 font-sans antialiased">
        <div
            x-data="{ sidebarOpen: false, userMenuOpen: false }"
            class="admin-shell-bg min-h-screen"
        >
            <aside class="admin-sidebar fixed left-0 top-0 z-50 hidden h-screen w-sidebar-width flex-col gap-stack-sm border-r border-outline-variant/20 bg-surface-container-lowest p-stack-md xl:flex">
                <div class="mb-4 px-4 py-6">
                    <a href="{{ route('admin.dashboard') }}" wire:navigate class="flex items-center gap-3">
                        @if(!empty($app_logo_path))
                            <img src="{{ asset('storage/' . $app_logo_path) }}" alt="{{ config('app.name') }}" class="h-10 w-10 rounded-xl object-cover drop-shadow-[0_2px_5px_rgba(15,23,42,0.22)]">
                        @else
                            <img src="{{ asset('favicon.svg') }}" alt="{{ config('app.name') }}" class="h-10 w-10 rounded-xl drop-shadow-[0_2px_5px_rgba(15,23,42,0.22)]">
                        @endif
                        <span>
                            <span class="block font-headline-md text-headline-sm font-bold leading-tight text-primary">{{ config('app.name') }}</span>
                            <span class="block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant/70">Hệ thống điểm danh</span>
                        </span>
                    </a>
                </div>

                <nav class="flex-1 space-y-2 overflow-y-auto overflow-x-hidden scrollbar-custom px-3 py-2" x-data x-init="$el.scrollTop = sessionStorage.getItem('sidebarScroll') || 0; $el.addEventListener('scroll', () => sessionStorage.setItem('sidebarScroll', $el.scrollTop))">
                    @foreach ($menuItems as $item)
                        @php
                            $isItemActive = $isActive($item['active'] ?? []);
                        @endphp
                        <a wire:navigate
                                    href="{{ $item['href'] }}"
                                    @class([
                                        'flex w-full items-center gap-3 rounded-lg px-3 py-3 text-[15px] font-medium transition-all',
                                        'bg-primary-container text-on-primary-container' => $isItemActive,
                                        'text-on-surface-variant hover:bg-surface-container-high' => ! $isItemActive,
                                    ])
                                >
                                    <x-user.icon name="{{ $item['icon'] }}" :size="20" class="shrink-0" />
                                    <span class="whitespace-nowrap">{{ $item['label'] }}</span>
                                </a>
                    @endforeach
                </nav>

                <div class="mt-auto space-y-1 border-t border-outline-variant/20 px-2 pt-2 pb-1">
                    <a href="{{ route('admin.settings.index') }}" wire:navigate 
                        @class([
                            'flex w-full items-center gap-3 rounded-lg px-3 py-3 text-[15px] font-medium transition-all',
                            'bg-primary-container text-on-primary-container' => request()->routeIs('admin.settings.*'),
                            'text-on-surface-variant hover:bg-surface-container-high' => !request()->routeIs('admin.settings.*'),
                        ])
                    >
                        <x-user.icon name="settings" :size="20" class="shrink-0" />
                        <span class="whitespace-nowrap">Cấu hình hệ thống</span>
                    </a>

                    <a href="{{ route('profile.edit') }}" wire:navigate 
                        @class([
                            'flex w-full items-center gap-3 rounded-lg px-3 py-3 text-[15px] font-medium transition-all',
                            'bg-primary-container text-on-primary-container' => request()->routeIs('profile.*'),
                            'text-on-surface-variant hover:bg-surface-container-high' => !request()->routeIs('profile.*'),
                        ])
                    >
                        <x-user.icon name="user-circle" :size="20" class="shrink-0" />
                        <span class="whitespace-nowrap">Hồ sơ cá nhân</span>
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-3 text-[15px] font-medium text-error transition-all hover:bg-error-container/40">
                            <x-user.icon name="log-out" :size="20" class="shrink-0" />
                            <span class="whitespace-nowrap">Đăng xuất</span>
                        </button>
                    </form>
                </div>
            </aside>

            <div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-50 xl:hidden" x-transition.opacity>
                <button type="button" class="fixed inset-0 bg-slate-950/50 backdrop-blur-sm" aria-label="Đóng menu" @click="sidebarOpen = false"></button>

                <aside class="relative flex h-screen w-sidebar-width flex-col gap-stack-sm border-r border-outline-variant/20 bg-surface-container-lowest p-stack-md" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
                    <div class="mb-4 px-4 py-6">
                        <div class="flex items-center justify-between">
                            <a href="{{ route('admin.dashboard') }}" wire:navigate class="flex items-center gap-3">
                                @if(!empty($app_logo_path))
                                    <img src="{{ asset('storage/' . $app_logo_path) }}" alt="{{ config('app.name') }}" class="h-10 w-10 rounded-xl object-cover drop-shadow-[0_2px_5px_rgba(15,23,42,0.22)]">
                                @else
                                    <img src="{{ asset('favicon.svg') }}" alt="{{ config('app.name') }}" class="h-10 w-10 rounded-xl drop-shadow-[0_2px_5px_rgba(15,23,42,0.22)]">
                                @endif
                                <span>
                                    <span class="block font-headline-md text-headline-sm font-bold leading-tight text-primary">{{ config('app.name') }}</span>
                                    <span class="block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant/70">Hệ thống điểm danh</span>
                                </span>
                            </a>
                            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg bg-surface-container-high text-on-surface-variant hover:text-on-surface" @click="sidebarOpen = false">
                                <x-user.icon name="x" :size="20" />
                            </button>
                        </div>
                    </div>

                    <nav class="flex-1 space-y-2 overflow-y-auto overflow-x-hidden scrollbar-custom px-3 py-2" x-data x-init="$el.scrollTop = sessionStorage.getItem('sidebarScrollMobile') || 0; $el.addEventListener('scroll', () => sessionStorage.setItem('sidebarScrollMobile', $el.scrollTop))">
                        @foreach ($menuItems as $item)
                            @php
                                $isItemActive = $isActive($item['active'] ?? []);
                            @endphp
                            <a wire:navigate
                                        href="{{ $item['href'] }}"
                                        @class([
                                            'flex w-full items-center gap-3 rounded-lg px-3 py-3 text-[15px] font-medium transition-all',
                                            'bg-primary-container text-on-primary-container' => $isItemActive,
                                            'text-on-surface-variant hover:bg-surface-container-high' => ! $isItemActive,
                                        ])
                                        @click="sidebarOpen = false"
                                    >
                                        <x-user.icon name="{{ $item['icon'] }}" :size="20" class="shrink-0" />
                                        <span class="whitespace-nowrap">{{ $item['label'] }}</span>
                                    </a>
                        @endforeach
                    </nav>

                    <div class="mt-auto space-y-1 border-t border-outline-variant/20 px-2 pt-2 pb-1">
                        <a href="{{ route('admin.settings.index') }}" wire:navigate 
                            @class([
                                'flex w-full items-center gap-3 rounded-lg px-3 py-3 text-[15px] font-medium transition-all',
                                'bg-primary-container text-on-primary-container' => request()->routeIs('admin.settings.*'),
                                'text-on-surface-variant hover:bg-surface-container-high' => !request()->routeIs('admin.settings.*'),
                            ]) 
                            @click="sidebarOpen = false"
                        >
                            <x-user.icon name="settings" :size="20" class="shrink-0" />
                            <span class="whitespace-nowrap">Cấu hình hệ thống</span>
                        </a>

                        <a href="{{ route('profile.edit') }}" wire:navigate 
                            @class([
                                'flex w-full items-center gap-3 rounded-lg px-3 py-3 text-[15px] font-medium transition-all',
                                'bg-primary-container text-on-primary-container' => request()->routeIs('profile.*'),
                                'text-on-surface-variant hover:bg-surface-container-high' => !request()->routeIs('profile.*'),
                            ]) 
                            @click="sidebarOpen = false"
                        >
                            <x-user.icon name="user-circle" :size="20" class="shrink-0" />
                            <span class="whitespace-nowrap">Hồ sơ cá nhân</span>
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-3 text-[15px] font-medium text-error transition-all hover:bg-error-container/40">
                                <x-user.icon name="log-out" :size="20" class="shrink-0" />
                                <span class="whitespace-nowrap">Đăng xuất</span>
                            </button>
                        </form>
                    </div>
                </aside>
            </div>

            <div class="min-h-screen xl:pl-sidebar-width">
                <x-maintenance-banner />
                <header class="admin-topbar sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200/80 bg-white/85 px-4 backdrop-blur-xl sm:px-6">
                    <div class="flex min-w-0 items-center gap-3">
                        <button type="button" class="admin-soft-button flex h-10 w-10 items-center justify-center rounded-xl border border-transparent bg-transparent text-slate-500 transition hover:bg-slate-50 xl:hidden" @click="sidebarOpen = true">
                            <x-user.icon name="menu" :size="20" />
                        </button>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-4">
                        <livewire:notification-bell />

                        <div class="relative ml-2">
                            <button type="button" class="flex items-center gap-3 transition" @click="userMenuOpen = ! userMenuOpen">
                                <div class="hidden text-right sm:block">
                                    <span class="block text-[15px] font-bold text-slate-900">{{ $userName }}</span>
                                    <span class="block text-xs text-slate-500">{{ $userEmail }}</span>
                                </div>
                                <div class="relative flex h-10 w-10 items-center justify-center rounded-full border-2 border-blue-600 bg-blue-50 p-0.5">
                                    @if(Auth::user()?->avatar)
                                        <img src="{{ Auth::user()->avatar_url }}" alt="{{ $userName }}" class="h-full w-full rounded-full object-cover" referrerpolicy="no-referrer">
                                    @else
                                        <span class="flex h-full w-full items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-700">{{ $userInitial }}</span>
                                    @endif
                                </div>
                            </button>

                            <div x-cloak x-show="userMenuOpen" class="fixed inset-0 z-40" @click="userMenuOpen = false"></div>
                            <div x-cloak x-show="userMenuOpen" class="absolute right-0 z-50 mt-2 w-64 rounded-2xl border border-slate-100 bg-white p-3 shadow-xl" x-transition>
                                <div class="mb-2 px-3 py-2">
                                    <p class="text-[15px] font-bold text-slate-900">{{ $userEmail }}</p>
                                </div>

                                <a href="{{ route('profile.edit') }}" wire:navigate class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[15px] font-medium text-slate-600 transition-colors hover:bg-slate-50">
                                    <x-user.icon name="user" :size="18" class="text-slate-400" />
                                    Thông tin cá nhân
                                </a>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="mt-1 flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-[15px] font-medium text-rose-600 transition-colors hover:bg-rose-50">
                                        <x-user.icon name="log-out" :size="18" class="text-rose-500" />
                                        Đăng xuất
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </header>

                <main class="admin-page px-4 py-5 sm:px-6 lg:px-8">
                    {{ $slot }}
                </main>
            </div>
        </div>
        <x-notification.notification />
        @livewireScripts
    </body>
</html>
