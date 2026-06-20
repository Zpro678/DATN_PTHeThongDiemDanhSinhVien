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

    $sections = [
        [
            'title' => 'Hệ thống',
            'items' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard'), 'icon' => 'layout-dashboard', 'active' => ['admin.dashboard']],
                ['label' => 'Quản lý tài khoản', 'href' => route('admin.users.index'), 'icon' => 'user-square', 'active' => ['admin.users.*']],
                ['label' => 'Quản lý gói dịch vụ', 'href' => route('admin.packages.index'), 'icon' => 'star', 'active' => ['admin.packages.*']],
                ['label' => 'Nhật ký hệ thống', 'href' => route('admin.logs.index'), 'icon' => 'activity', 'active' => ['admin.logs.*']],
            ],
        ],
        [
            'title' => 'Nghiệp vụ',
            'items' => [
                ['label' => 'Quản lý điểm danh', 'href' => route('admin.attendance.index'), 'icon' => 'calendar-check', 'active' => ['admin.attendance.*']],
                ['label' => 'Báo cáo & thống kê', 'href' => route('admin.reports.index'), 'icon' => 'bar-chart', 'active' => ['admin.reports.*']],
            ],
        ],
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
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-slate-50 text-slate-900 antialiased" style="font-family: 'Inter', 'Plus Jakarta Sans', sans-serif;">
        <div
            x-data="{ sidebarOpen: false, userMenuOpen: false }"
            class="admin-shell-bg min-h-screen"
        >
            <aside class="admin-sidebar fixed left-0 top-0 z-50 hidden h-screen w-sidebar-width flex-col gap-stack-sm border-r border-outline-variant/20 bg-surface-container-lowest p-stack-md xl:flex">
                <div class="mb-4 px-4 py-6">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-white shadow-lg shadow-primary/20">
                            <x-user.icon name="school" :size="24" />
                        </span>
                        <span>
                            <span class="block font-headline-md text-headline-sm font-bold leading-tight text-primary">EduTrack</span>
                            <span class="block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant/70">Hệ thống điểm danh</span>
                        </span>
                    </a>
                </div>

                <nav class="flex-1 space-y-4 overflow-y-auto px-3 py-2">
                    @foreach ($sections as $section)
                        <div class="space-y-1">
                            <div class="mb-2 px-3 text-[10px] font-bold uppercase tracking-widest text-on-surface-variant/70">
                                {{ $section['title'] }}
                            </div>

                            @foreach ($section['items'] as $item)
                                @php
                                    $isItemActive = $isActive($item['active'] ?? []);
                                @endphp
                                <a
                                    href="{{ $item['href'] }}"
                                    @class([
                                        'flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-base font-bold transition-all',
                                        'bg-primary-container text-on-primary-container' => $isItemActive,
                                        'text-on-surface-variant hover:bg-surface-container-high' => ! $isItemActive,
                                    ])
                                >
                                    <x-user.icon name="{{ $item['icon'] }}" :size="20" class="shrink-0" />
                                    <span class="whitespace-nowrap">{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endforeach
                </nav>

                <div class="mt-auto space-y-1 border-t border-outline-variant/20 px-2 pt-2 pb-1">
                    <a href="{{ route('admin.settings.index') }}" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-base font-bold text-on-surface-variant transition-all hover:bg-surface-container-high">
                        <x-user.icon name="settings" :size="20" class="shrink-0" />
                        <span class="whitespace-nowrap">Cấu hình hệ thống</span>
                    </a>

                    <a href="{{ route('profile.edit') }}" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-base font-bold text-on-surface-variant transition-all hover:bg-surface-container-high">
                        <x-user.icon name="user-circle" :size="20" class="shrink-0" />
                        <span class="whitespace-nowrap">Hồ sơ cá nhân</span>
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-base font-bold text-error transition-all hover:bg-error-container/40">
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
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-white shadow-lg shadow-primary/20">
                                    <x-user.icon name="school" :size="24" />
                                </span>
                                <span>
                                    <span class="block font-headline-md text-headline-sm font-bold leading-tight text-primary">EduTrack</span>
                                    <span class="block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant/70">Hệ thống điểm danh</span>
                                </span>
                            </a>
                            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg bg-surface-container-high text-on-surface-variant hover:text-on-surface" @click="sidebarOpen = false">
                                <x-user.icon name="x" :size="20" />
                            </button>
                        </div>
                    </div>

                    <nav class="flex-1 space-y-4 overflow-y-auto px-3 py-2">
                        @foreach ($sections as $section)
                            <div class="space-y-1">
                                <div class="mb-2 px-3 text-[10px] font-bold uppercase tracking-widest text-on-surface-variant/70">
                                    {{ $section['title'] }}
                                </div>

                                @foreach ($section['items'] as $item)
                                    @php
                                        $isItemActive = $isActive($item['active'] ?? []);
                                    @endphp
                                    <a
                                        href="{{ $item['href'] }}"
                                        @class([
                                            'flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-base font-bold transition-all',
                                            'bg-primary-container text-on-primary-container' => $isItemActive,
                                            'text-on-surface-variant hover:bg-surface-container-high' => ! $isItemActive,
                                        ])
                                        @click="sidebarOpen = false"
                                    >
                                        <x-user.icon name="{{ $item['icon'] }}" :size="20" class="shrink-0" />
                                        <span class="whitespace-nowrap">{{ $item['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @endforeach
                    </nav>

                    <div class="mt-auto space-y-1 border-t border-outline-variant/20 px-2 pt-2 pb-1">
                        <a href="{{ route('admin.settings.index') }}" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-base font-bold text-on-surface-variant transition-all hover:bg-surface-container-high" @click="sidebarOpen = false">
                            <x-user.icon name="settings" :size="20" class="shrink-0" />
                            <span class="whitespace-nowrap">Cấu hình hệ thống</span>
                        </a>

                        <a href="{{ route('profile.edit') }}" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-base font-bold text-on-surface-variant transition-all hover:bg-surface-container-high" @click="sidebarOpen = false">
                            <x-user.icon name="user-circle" :size="20" class="shrink-0" />
                            <span class="whitespace-nowrap">Hồ sơ cá nhân</span>
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-base font-bold text-error transition-all hover:bg-error-container/40">
                                <x-user.icon name="log-out" :size="20" class="shrink-0" />
                                <span class="whitespace-nowrap">Đăng xuất</span>
                            </button>
                        </form>
                    </div>
                </aside>
            </div>

            <div class="min-h-screen xl:pl-sidebar-width">
                <header class="admin-topbar sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200/80 bg-white/85 px-4 backdrop-blur-xl sm:px-6">
                    <div class="flex min-w-0 items-center gap-3">
                        <button type="button" class="admin-soft-button flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 xl:hidden" @click="sidebarOpen = true">
                            <x-user.icon name="menu" :size="20" />
                        </button>

                        <div class="hidden items-center gap-2 text-xs font-bold tracking-tight text-slate-400 sm:flex">
                            <span>Hệ thống SAMS</span>
                            <span>/</span>
                            <span class="truncate text-[11px] font-extrabold uppercase tracking-wider text-slate-800">{{ $title ?: 'Dashboard' }}</span>
                        </div>

                        <h1 class="truncate text-sm font-extrabold text-slate-900 sm:hidden">{{ $title ?: 'Dashboard' }}</h1>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-4">
                        <div class="relative hidden md:block">
                            <x-user.icon name="search" :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                            <input type="text" placeholder="Tìm giảng viên, ngành, khoa..." class="w-64 rounded-xl border border-slate-200 bg-slate-50/80 py-2 pl-10 pr-4 text-xs font-medium text-slate-900 placeholder:text-slate-400 transition focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <button type="button" class="admin-soft-button relative flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50">
                            <x-user.icon name="bell" :size="16" />
                            <span class="absolute right-2 top-2 h-2 w-2 rounded-full bg-rose-500"></span>
                        </button>

                        <div class="relative">
                            <button type="button" class="admin-soft-button flex items-center gap-2 rounded-xl border border-slate-200 bg-white py-1.5 pl-1.5 pr-2.5 transition hover:bg-slate-50" @click="userMenuOpen = ! userMenuOpen">
                                @if(Auth::user()?->avatar)
                                    <img src="{{ asset('storage/'.Auth::user()->avatar) }}" alt="{{ $userName }}" class="h-7 w-7 rounded-lg object-cover">
                                @else
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100 text-xs font-extrabold text-blue-700">{{ $userInitial }}</span>
                                @endif
                                <span class="hidden text-left sm:block">
                                    <span class="block text-[11px] font-extrabold leading-tight text-slate-900">{{ $userName }}</span>
                                    <span class="block py-0.5 text-[9px] font-bold leading-none text-emerald-500">Admin</span>
                                </span>
                                <x-user.icon name="chevron-down" :size="14" class="text-slate-400" />
                            </button>

                            <div x-cloak x-show="userMenuOpen" class="fixed inset-0 z-40" @click="userMenuOpen = false"></div>
                            <div x-cloak x-show="userMenuOpen" class="absolute right-0 z-50 mt-2 w-52 rounded-2xl border border-slate-200 bg-white p-2.5 shadow-xl" x-transition>
                                <div class="border-b border-slate-100 px-3.5 py-2 text-left">
                                    <p class="text-[10px] font-bold uppercase text-slate-400">Hồ sơ đăng nhập</p>
                                    <p class="text-xs font-extrabold text-slate-900">{{ $userEmail }}</p>
                                </div>

                                <a href="{{ route('profile.edit') }}" class="mt-2 block rounded-xl px-3 py-2 text-xs font-bold text-slate-600 transition-colors hover:bg-slate-50">
                                    Thông tin cá nhân
                                </a>

                                <a href="{{ route('admin.dashboard') }}" class="mt-1 block rounded-xl px-3 py-2 text-xs font-bold text-slate-600 transition-colors hover:bg-slate-50">
                                    Về dashboard
                                </a>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="mt-1 block w-full rounded-xl px-3 py-2.5 text-left text-xs font-bold text-rose-600 transition-colors hover:bg-rose-50">
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
        @livewireScripts
    </body>
</html>
