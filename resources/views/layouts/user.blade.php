@props(['title' => null])

@php
    $userName = Auth::user()?->name ?? 'Nguyễn Văn A';
    $userRole = Auth::user()?->email ?? 'User';

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
                ['label' => 'Quản lý sinh viên', 'icon' => 'users', 'route' => 'lecturer.students.index', 'active' => 'lecturer.students.*'],
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
        ['type' => 'link', 'label' => 'Hồ sơ cá nhân', 'icon' => 'user-circle', 'route' => 'profile.edit', 'active' => 'profile.*'],
    ];

    $mobileItems = [
        ['label' => 'Tổng quan', 'icon' => 'home', 'route' => 'dashboard', 'active' => 'dashboard'],
        ['label' => 'Chủ lớp', 'icon' => 'shield', 'route' => 'managed-classes', 'active' => ['managed-classes', 'lecturer.classes.*', 'lecturer.class.*', 'lecturer.attendance.*', 'lecturer.students.*', 'lecturer.leave-requests.*']],
        ['label' => 'Học viên', 'icon' => 'user', 'route' => 'joined-classes', 'active' => ['joined-classes', 'student.classes.show']],
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
        <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700,800&display=swap" rel="stylesheet" />

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

                <nav class="flex-1 space-y-4 overflow-y-auto px-3 py-2">
                    <div class="space-y-1">
                        @foreach ($navData as $nav)
                            @if($nav['type'] === 'link')
                                @php
                                    $activePattern = $nav['active'] ?? ($nav['route'] ?? null);
                                    $isActive = $activePattern ? request()->routeIs($activePattern) : false;
                                    $href = isset($nav['route']) ? route($nav['route']) : $nav['href'];
                                @endphp
                                <a
                                    href="{{ $href }}"
                                    @class([
                                        'flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-base font-bold transition-all',
                                        'bg-primary-container text-on-primary-container' => $isActive,
                                        'text-on-surface-variant hover:bg-surface-container-high' => ! $isActive,
                                    ])
                                >
                                    <x-user.icon :name="$nav['icon']" :size="20" class="shrink-0" />
                                    <span class="whitespace-nowrap">{{ $nav['label'] }}</span>
                                </a>
                            @elseif($nav['type'] === 'group')
                                @php
                                    $isGroupActive = false;
                                    if (isset($nav['activePattern'])) {
                                        foreach ($nav['activePattern'] as $pattern) {
                                            if (request()->routeIs($pattern)) {
                                                $isGroupActive = true;
                                                break;
                                            }
                                        }
                                    }
                                @endphp
                                
                                <div x-data="{ open: {{ $isGroupActive ? 'true' : 'false' }} }" class="space-y-1 mt-2">
                                    <button @click="open = !open" class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-base font-bold text-on-surface-variant transition-all hover:bg-surface-container-high focus:outline-none" :class="open ? 'text-primary' : ''">
                                        <div class="flex items-center gap-3">
                                            <x-user.icon :name="$nav['icon']" :size="20" class="shrink-0" />
                                            <span class="whitespace-nowrap">{{ $nav['label'] }}</span>
                                        </div>
                                        <x-user.icon name="chevron-down" :size="16" class="shrink-0 transition-transform duration-200" x-bind:class="open ? 'rotate-180' : ''" />
                                    </button>

                                    <div x-show="open" x-collapse class="space-y-1 pl-9">
                                        @foreach ($nav['items'] as $item)
                                            @php
                                                $activePattern = $item['active'] ?? ($item['route'] ?? null);
                                                $isActive = $activePattern ? request()->routeIs($activePattern) : false;
                                                $href = isset($item['route']) ? route($item['route']) : $item['href'];
                                            @endphp

                                            <a
                                                href="{{ $href }}"
                                                @class([
                                                    'flex items-center gap-3 rounded-lg px-3 py-2.5 text-base font-semibold transition-all',
                                                    'bg-primary/10 text-primary font-bold' => $isActive,
                                                    'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' => ! $isActive,
                                                ])
                                            >
                                                <x-user.icon :name="$item['icon']" :size="18" class="shrink-0" />
                                                <span class="whitespace-nowrap">{{ $item['label'] }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </nav>

                <div class="mt-auto space-y-1 border-t border-outline-variant/20 px-2 pt-2 pb-1">
                    <button type="button" class="flex w-full items-center gap-3 rounded-lg px-3 py-1 text-base font-bold text-on-surface-variant transition-all hover:bg-surface-container-high">
                        <x-user.icon name="help-circle" :size="20" class="shrink-0" />
                        <span class="whitespace-nowrap">Hỗ trợ</span>
                    </button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-1 text-base font-bold text-error transition-all hover:bg-error-container/40">
                            <x-user.icon name="log-out" :size="20" class="shrink-0" />
                            <span class="whitespace-nowrap">Đăng xuất</span>
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
                            <a href="{{ route('joined-classes') }}" class="group flex items-center gap-2 rounded-full border border-outline-variant/20 bg-surface-container px-4 py-2 text-label-md font-bold text-on-surface-variant transition-all hover:bg-surface-container-highest">
                                <x-user.icon name="log-in" :size="16" class="transition-colors group-hover:text-primary" />
                                Tham gia lớp
                            </a>
                        </div>

                        <div class="flex items-center gap-1 md:ml-4 md:border-l md:border-outline-variant/30 md:pl-4">
                            <div class="relative" x-data="{ openNotification: false }" @click.away="openNotification = false">
                                <button type="button" @click="openNotification = !openNotification" class="relative rounded-full p-2 text-on-surface-variant transition-colors hover:bg-surface-container-high" :class="openNotification ? 'bg-surface-container-high' : ''">
                                    <x-user.icon name="bell" :size="20" />
                                    <span class="absolute right-2 top-2 h-2 w-2 rounded-full border-2 border-surface bg-error"></span>
                                </button>
                                
                                <div x-show="openNotification" 
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                                     class="absolute right-0 top-full mt-3 w-80 lg:w-96 rounded-2xl bg-white shadow-xl ring-1 ring-black/5 focus:outline-none z-50 overflow-hidden"
                                     style="display: none;">
                                     
                                    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-4 py-3">
                                        <h3 class="text-sm font-bold text-slate-900">Thông báo mới</h3>
                                        <button class="text-xs font-medium text-primary hover:text-primary/80">Đánh dấu đã đọc</button>
                                    </div>
                                    
                                    <div class="max-h-[360px] overflow-y-auto overscroll-contain">
                                        <!-- Notification Item 1 -->
                                        <a href="#" class="flex items-start gap-4 border-b border-slate-50 px-4 py-3 transition-colors hover:bg-slate-50">
                                            <div class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                                <x-user.icon name="info" :size="16" />
                                            </div>
                                            <div class="flex-1 space-y-1">
                                                <p class="text-sm font-medium text-slate-900">Nhắc nhở nộp minh chứng</p>
                                                <p class="text-xs text-slate-500">Đơn xin nghỉ phép ngày 05/05/2026 của bạn cần bổ sung minh chứng.</p>
                                                <p class="text-[10px] font-medium text-slate-400">10 phút trước</p>
                                            </div>
                                            <div class="h-2 w-2 shrink-0 rounded-full bg-primary mt-2"></div>
                                        </a>

                                        <!-- Notification Item 2 -->
                                        <a href="#" class="flex items-start gap-4 border-b border-slate-50 px-4 py-3 transition-colors hover:bg-slate-50">
                                            <div class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                                                <x-user.icon name="alert-triangle" :size="16" />
                                            </div>
                                            <div class="flex-1 space-y-1">
                                                <p class="text-sm font-medium text-slate-900">Cảnh báo vắng mặt</p>
                                                <p class="text-xs text-slate-500">Bạn đã vắng 3/15 buổi học môn Lập trình Web.</p>
                                                <p class="text-[10px] font-medium text-slate-400">2 giờ trước</p>
                                            </div>
                                            <div class="h-2 w-2 shrink-0 rounded-full bg-primary mt-2"></div>
                                        </a>

                                        <!-- Notification Item 3 (Read) -->
                                        <a href="#" class="flex items-start gap-4 px-4 py-3 transition-colors hover:bg-slate-50 opacity-70">
                                            <div class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                                                <x-user.icon name="check-circle" :size="16" />
                                            </div>
                                            <div class="flex-1 space-y-1">
                                                <p class="text-sm font-medium text-slate-900">Đơn nghỉ phép được duyệt</p>
                                                <p class="text-xs text-slate-500">Giảng viên đã duyệt đơn nghỉ phép ngày 20/04/2026.</p>
                                                <p class="text-[10px] font-medium text-slate-400">1 ngày trước</p>
                                            </div>
                                        </a>
                                    </div>
                                    
                                    <div class="border-t border-slate-100 bg-slate-50/50 p-2 text-center">
                                        <a href="{{ route('student.warnings') }}" class="inline-block w-full rounded-lg px-4 py-2 text-xs font-bold text-slate-600 transition-colors hover:bg-slate-200/50 hover:text-slate-900">
                                            Xem tất cả thông báo
                                        </a>
                                    </div>
                                </div>
                            </div>
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
                                        @if(Auth::user()?->avatar)
                                            <img src="{{ asset('storage/'.Auth::user()->avatar) }}" alt="{{ $userName }}" class="h-full w-full rounded-full object-cover">
                                        @else
                                            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed={{ urlencode($userName) }}&backgroundColor=e5eeff" alt="{{ $userName }}" class="h-full w-full rounded-full object-cover">
                                        @endif
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
                    <a href="{{ route('lecturer.attendance.create') }}" class="flex w-full items-center justify-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-bold text-on-surface shadow-md transition-colors hover:bg-surface-container">
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
