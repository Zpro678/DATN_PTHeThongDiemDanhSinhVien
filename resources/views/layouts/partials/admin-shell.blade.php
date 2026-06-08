<div class="flex min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    <aside class="fixed left-0 top-0 z-40 hidden h-screen w-64 shrink-0 flex-col border-r border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 xl:flex">
        <div class="flex h-16 items-center gap-3 border-b border-slate-200 bg-slate-50/60 px-6 dark:border-slate-800 dark:bg-slate-950/20">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-white shadow-md shadow-blue-500/20">
                <x-sams.icon name="graduation-cap" class="h-5 w-5" />
            </div>
            <div>
                <span class="block text-sm font-extrabold leading-tight tracking-tight text-slate-950 dark:text-slate-50">SAMS Hub</span>
                <span class="mt-0.5 block text-[10px] font-bold uppercase leading-none tracking-wider text-slate-400">Admin Portal</span>
            </div>
        </div>

        <nav class="sams-scrollbar flex-1 space-y-7 overflow-y-auto px-4 py-6">
            @foreach ($adminMenuGroups as $group)
                <div class="space-y-1.5">
                    <h4 class="mb-2 px-3 text-[10px] font-extrabold uppercase tracking-widest text-slate-400 dark:text-slate-500">
                        {{ $group['title'] }}
                    </h4>

                    <div class="space-y-0.5">
                        @foreach ($group['items'] as $item)
                            @php($active = $isActive($item['active']))

                            <a href="{{ url($item['href']) }}" class="{{ $active ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/10' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800/60 dark:hover:text-white' }} group flex items-center justify-between rounded-xl px-3.5 py-2.5 text-xs font-bold transition-all duration-150">
                                <span class="flex items-center gap-2.5">
                                    <x-sams.icon name="{{ $item['icon'] }}" class="{{ $active ? 'text-white' : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300' }} h-4 w-4 shrink-0 transition-colors" />
                                    <span>{{ $item['name'] }}</span>
                                </span>

                                @isset($item['badge'])
                                    <span class="{{ $active ? 'bg-blue-700/80 text-white' : 'bg-slate-100 text-slate-500 dark:bg-slate-800' }} rounded-md px-1.5 py-0.5 text-[10px] font-extrabold">
                                        {{ $item['badge'] }}
                                    </span>
                                @endisset
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>

        <div class="space-y-3 border-t border-slate-200 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-900/40">
            @php($settingsActive = $isActive(['admin/settings', 'admin/settings/*']))

            <a href="{{ url('/admin/settings') }}" class="{{ $settingsActive ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/10' : 'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800' }} flex items-center gap-2.5 rounded-xl px-3.5 py-2.5 text-xs font-bold transition-all duration-150">
                <x-sams.icon name="settings" class="h-4 w-4" />
                <span>Cấu hình hệ thống</span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-2.5 rounded-xl px-3.5 py-2.5 text-left text-xs font-bold text-red-600 transition-all hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-950/20">
                    <x-sams.icon name="log-out" class="h-4 w-4" />
                    <span>Đăng xuất hệ thống</span>
                </button>
            </form>
        </div>
    </aside>

    <div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-50 flex xl:hidden" x-transition.opacity>
        <button type="button" class="fixed inset-0 bg-slate-950/50 backdrop-blur-sm" aria-label="Đóng menu" @click="sidebarOpen = false"></button>

        <aside class="relative flex h-screen w-64 flex-col border-r border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
            <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-6 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <x-sams.icon name="graduation-cap" class="h-6 w-6 text-blue-600" />
                    <span class="text-sm font-extrabold text-slate-900 dark:text-white">SAMS Admin</span>
                </div>
                <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-400 dark:bg-slate-800" @click="sidebarOpen = false">
                    <x-sams.icon name="x" class="h-4 w-4" />
                </button>
            </div>

            <nav class="sams-scrollbar flex-1 space-y-6 overflow-y-auto">
                @foreach ($adminMenuGroups as $group)
                    <div class="space-y-1">
                        <h4 class="mb-2 px-2 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
                            {{ $group['title'] }}
                        </h4>

                        @foreach ($group['items'] as $item)
                            @php($active = $isActive($item['active']))

                            <a href="{{ url($item['href']) }}" class="{{ $active ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }} flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-bold transition-all" @click="sidebarOpen = false">
                                <x-sams.icon name="{{ $item['icon'] }}" class="h-4 w-4 shrink-0" />
                                <span>{{ $item['name'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </nav>

            <div class="mt-auto space-y-1.5 border-t border-slate-200 pt-4 dark:border-slate-800">
                <a href="{{ url('/admin/settings') }}" class="{{ $settingsActive ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }} flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-xs font-bold transition-all" @click="sidebarOpen = false">
                    <x-sams.icon name="settings" class="h-4 w-4 shrink-0" />
                    <span>Cấu hình hệ thống</span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-xs font-bold text-red-600 transition-all hover:bg-red-50 dark:hover:bg-red-950/20">
                        <x-sams.icon name="log-out" class="h-4 w-4" />
                        <span>Đăng xuất</span>
                    </button>
                </form>
            </div>
        </aside>
    </div>

    <div class="flex min-h-screen flex-1 flex-col xl:pl-64">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 dark:border-slate-800 dark:bg-slate-900 sm:px-6">
            <div class="flex min-w-0 items-center gap-3">
                <button type="button" class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-50 dark:border-slate-800 dark:text-slate-400 dark:hover:bg-slate-800 xl:hidden" @click="sidebarOpen = true">
                    <x-sams.icon name="menu" class="h-5 w-5" />
                </button>

                <div class="hidden items-center gap-2.5 text-xs font-bold tracking-tight text-slate-400 sm:flex">
                    <span>Hệ thống SAMS</span>
                    <span>/</span>
                    <span class="truncate text-[11px] font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-100">{{ $currentPageTitle }}</span>
                </div>

                <h1 class="truncate text-sm font-extrabold text-slate-900 dark:text-white sm:hidden">{{ $currentPageTitle }}</h1>
            </div>

            <div class="flex items-center gap-2 sm:gap-4">
                <div class="relative hidden md:block">
                    <x-sams.icon name="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input type="text" placeholder="Tìm giảng viên, ngành, khoa..." class="w-64 rounded-xl border border-slate-200 bg-slate-50 py-2 pl-10 pr-4 text-xs font-medium text-slate-900 transition-all placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-800 dark:bg-slate-950/50 dark:text-slate-100" />
                </div>

                <button type="button" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400 dark:hover:bg-slate-800" title="Đổi giao diện sáng tối" @click="darkMode = ! darkMode">
                    <x-sams.icon x-show="! darkMode" name="moon" class="h-4 w-4" />
                    <x-sams.icon x-cloak x-show="darkMode" name="sun" class="h-4 w-4 text-amber-500" />
                </button>

                <button type="button" class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400 dark:hover:bg-slate-800">
                    <x-sams.icon name="bell" class="h-4 w-4" />
                    <span class="absolute right-2 top-2 h-2 w-2 rounded-full bg-rose-500"></span>
                </button>

                <div class="relative">
                    <button type="button" class="flex items-center gap-2.5 rounded-xl border border-slate-200 bg-white py-1.5 pl-1.5 pr-2.5 transition hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-800" @click="userMenuOpen = ! userMenuOpen">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 bg-blue-100 text-xs font-extrabold text-blue-700 dark:border-slate-700">{{ $userInitials }}</span>
                        <span class="hidden text-left sm:block">
                            <span class="block text-[11px] font-extrabold leading-tight text-slate-900 dark:text-slate-50">{{ $userName }}</span>
                            <span class="block py-0.5 text-[9px] font-bold leading-none text-emerald-500 dark:text-emerald-400">Super Admin</span>
                        </span>
                        <x-sams.icon name="chevron-down" class="h-3.5 w-3.5 text-slate-400" />
                    </button>

                    <div x-cloak x-show="userMenuOpen" class="fixed inset-0 z-40" @click="userMenuOpen = false"></div>
                    <div x-cloak x-show="userMenuOpen" class="absolute right-0 z-50 mt-2 w-48 rounded-2xl border border-slate-200 bg-white p-2.5 shadow-xl dark:border-slate-800 dark:bg-slate-900" x-transition>
                        <div class="border-b border-slate-100 px-3.5 py-2 text-left dark:border-slate-800">
                            <p class="text-[10px] font-bold uppercase text-slate-400">Hồ sơ đăng nhập</p>
                            <p class="text-xs font-extrabold text-slate-900 dark:text-slate-50">Trung tâm quản lý</p>
                        </div>

                        <a href="{{ route('profile.edit') }}" class="mt-2 flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-bold text-slate-600 transition-colors hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800">
                            Bảo mật hệ thống
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="mt-1 flex w-full items-center gap-2 rounded-xl px-3 py-2.5 text-left text-xs font-bold text-rose-600 transition-colors hover:bg-rose-50 dark:hover:bg-rose-950/20">
                                Đăng xuất Admin
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="sams-scrollbar flex-1 overflow-y-auto p-4 md:p-8">
            {{ $slot }}
        </main>

        @include('layouts.partials.portal-footer')
    </div>
</div>
