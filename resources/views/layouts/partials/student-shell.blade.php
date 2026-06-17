<div class="flex min-h-screen justify-center overflow-x-hidden bg-slate-100 font-sans text-slate-800 dark:bg-slate-950 dark:text-slate-100 lg:flex lg:flex-col lg:h-screen lg:min-h-0 lg:overflow-hidden">
    <div class="relative flex h-screen w-full max-w-md flex-col overflow-hidden rounded-none border-0 bg-slate-50 shadow-none transition-all duration-300 dark:bg-slate-950 md:my-6 md:h-[840px] md:max-h-[95vh] md:rounded-[48px] md:border-[10px] md:border-slate-800 md:shadow-2xl lg:my-0 lg:h-full lg:max-h-full lg:max-w-none lg:flex-row lg:rounded-none lg:border-0 lg:shadow-none">
        
        {{-- Overlay cho Mobile Sidebar --}}
        <div x-cloak x-show="sidebarOpen" class="absolute inset-0 z-[45] bg-slate-900/40 lg:hidden transition-opacity" @click="sidebarOpen = false" x-transition.opacity></div>

        <aside class="absolute top-0 md:top-7 lg:top-0 bottom-0 left-0 z-[60] flex w-64 shrink-0 flex-col justify-between border-r border-slate-200 bg-white shadow-sm transition-transform duration-300 ease-in-out dark:border-slate-800 dark:bg-slate-900 lg:static lg:translate-x-0 lg:flex lg:h-full" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            <div>
                <div class="flex h-16 items-center justify-between border-b border-slate-100 px-5 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-md shadow-blue-500/10">
                            <x-sams.icon name="graduation-cap" class="h-6 w-6" />
                        </div>
                        <div>
                            <span class="block text-sm font-extrabold uppercase leading-none tracking-tight text-slate-800 dark:text-white">SAMS Portal</span>
                            <span class="mt-1 block text-[10px] font-bold uppercase tracking-wider text-blue-600">Cổng sinh viên</span>
                        </div>
                    </div>
                    <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 lg:hidden" @click="sidebarOpen = false">
                        <x-sams.icon name="x" class="h-5 w-5" />
                    </button>
                </div>

                <nav class="space-y-1 p-4">
                    @foreach ($studentNavigation as $item)
                        @php($active = $isActive($item['active']))

                        <a href="{{ url($item['href']) }}" @click="sidebarOpen = false" class="{{ $active ? 'bg-blue-50/80 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300' : 'text-slate-600 hover:bg-slate-100/50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }} group flex w-full items-center justify-between rounded-xl px-3.5 py-3 text-left text-xs font-semibold transition-all">
                            <span class="flex items-center gap-3.5">
                                <x-sams.icon name="{{ $item['icon'] }}" class="{{ $active ? 'text-blue-600 dark:text-blue-300' : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300' }} h-4 w-4 transition-transform duration-150 group-hover:scale-105" />
                                <span>{{ $item['name'] }}</span>
                            </span>

                            @if (($item['badgeCount'] ?? 0) > 0)
                                <span class="rounded-full bg-red-100 px-1.5 py-0.5 font-mono text-[9px] font-black leading-none text-red-600">
                                    {{ $item['badgeCount'] }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="mb-16 space-y-1 p-4 lg:mb-0 border-t border-slate-100 dark:border-slate-800">
                @php($settingsActive = $isActive(['student/profile', 'student/profile/*']))
                
                <a href="{{ url('/student/profile') }}" @click="sidebarOpen = false" class="{{ $settingsActive ? 'bg-blue-50/80 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300' : 'text-slate-600 hover:bg-slate-100/50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }} group flex w-full items-center rounded-xl px-3.5 py-3 text-left text-xs font-semibold transition-all">
                    <span class="flex items-center gap-3.5">
                        <x-sams.icon name="settings" class="{{ $settingsActive ? 'text-blue-600 dark:text-blue-300' : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300' }} h-4 w-4 transition-transform duration-150 group-hover:scale-105" />
                        <span>Cài đặt</span>
                    </span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center rounded-xl border-none bg-transparent px-3.5 py-3 text-left text-xs font-semibold text-red-600 hover:bg-red-50/50 dark:hover:bg-red-950/20 transition-all cursor-pointer group">
                        <span class="flex items-center gap-3.5">
                            <x-sams.icon name="log-out" class="h-4 w-4 text-red-500 transition-transform duration-150 group-hover:scale-105" />
                            <span>Đăng xuất</span>
                        </span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex min-h-0 flex-1 flex-col overflow-hidden bg-slate-50 dark:bg-slate-950">
            <div class="z-50 hidden h-7 shrink-0 items-center justify-between border-b border-slate-50/20 bg-white px-6 pt-1 text-[11px] font-bold text-slate-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 md:flex lg:hidden">
                <span>08:05</span>
                <div class="mx-auto hidden h-4 w-24 rounded-full border border-slate-700/50 bg-slate-800 md:block"></div>
                <div class="flex items-center gap-1">
                    <span class="text-[9px] font-extrabold uppercase tracking-wider text-blue-600">5G SAMS</span>
                    <span class="mb-0.5 inline-block h-2 w-3 rounded-sm bg-slate-800"></span>
                </div>
            </div>

            <header class="z-40 flex h-16 shrink-0 items-center justify-between border-b border-slate-100 bg-white/95 px-4 backdrop-blur-md transition-all dark:border-slate-800 dark:bg-slate-900/95 lg:hidden">
                <div class="flex min-w-0 items-center gap-2.5">
                    <button type="button" class="-ml-2 shrink-0 rounded-lg p-2 text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800 lg:hidden" @click="sidebarOpen = true">
                        <x-sams.icon name="menu" class="h-6 w-6" />
                    </button>
                    <span class="relative flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-blue-500 bg-blue-100 text-sm font-extrabold text-blue-700 shadow-md">
                        {{ $userInitials }}
                        <span class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white bg-emerald-500 dark:border-slate-900"></span>
                    </span>
                    <div class="min-w-0">
                        <h2 class="flex max-w-[150px] items-center gap-1 truncate text-xs font-extrabold uppercase leading-tight text-slate-900 dark:text-white">
                            <span class="truncate">{{ $userShortName }}</span>
                            <x-sams.icon name="sparkles" class="h-3 w-3 shrink-0 text-amber-500" />
                        </h2>
                        <p class="mt-1 text-[10px] font-bold leading-none text-slate-400">MSSV: {{ $userCode }}</p>
                    </div>
                </div>
            </header>


            <header class="z-30 hidden h-16 shrink-0 items-center justify-between border-b border-slate-200 bg-white px-8 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:flex">
                <div class="flex min-w-0 items-center gap-3">
                    <h2 class="truncate text-sm font-bold tracking-wide text-slate-800 dark:text-white">{{ $currentPageTitle }}</h2>
                    <div class="hidden items-center gap-2 rounded-lg border border-slate-200/50 bg-slate-50 px-3 py-1 text-[10px] font-bold text-slate-500 dark:border-slate-800 dark:bg-slate-950 xl:flex">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        <span>Định vị GPS bảo mật hoạt động: 11.4m</span>
                    </div>
                </div>

                {{-- Action items on top app bar --}}
                <div class="flex items-center gap-4">
                    {{-- Notifications --}}
                    <a href="{{ url('/student/notifications') }}" class="relative text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 transition-colors p-2 rounded-full hover:bg-slate-50 dark:hover:bg-slate-800 active:scale-95">
                        <x-sams.icon name="bell" class="h-5 w-5" />
                        @if ($studentUnreadCount > 0)
                            <span class="absolute right-1.5 top-1.5 flex h-2 w-2 rounded-full bg-red-500"></span>
                        @endif
                    </a>

                    {{-- Separator --}}
                    <div class="w-px h-6 bg-slate-200 dark:bg-slate-800"></div>

                    {{-- User Profile / Avatar --}}
                    <a href="{{ url('/student/profile') }}" class="flex items-center gap-3 hover:bg-slate-50 dark:hover:bg-slate-800 p-1 px-2.5 rounded-xl transition-all border border-transparent hover:border-slate-100 dark:hover:border-slate-800">
                        <div class="text-right">
                            <span class="block text-xs font-bold text-slate-800 dark:text-white leading-tight">{{ $userName }}</span>
                            <span class="block text-[10px] text-slate-400 font-bold leading-none mt-0.5">Sinh viên</span>
                        </div>
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-blue-500/25 bg-blue-50 text-xs font-extrabold text-blue-700 shadow-sm">
                            {{ $userInitials }}
                        </span>
                    </a>
                </div>
            </header>

            <main class="sams-scrollbar relative flex-1 overflow-y-auto bg-slate-50/50 dark:bg-slate-950 flex flex-col justify-between">
                <div class="w-full flex-1 pb-24 lg:pb-0">
                    {{ $slot }}
                </div>

                <div class="hidden lg:block">
                    @include('layouts.partials.portal-footer')
                </div>
            </main>

            <nav class="absolute bottom-0 inset-x-0 z-40 flex h-20 items-center justify-around border-t border-slate-100 bg-white/95 px-2 pb-2.5 pt-1 shadow-2xl backdrop-blur-md transition-all dark:border-slate-800 dark:bg-slate-900/95 lg:hidden">
                @foreach ($studentMobileNavigation as $item)
                    @php($active = $isActive($item['active']))

                    <a href="{{ url($item['href']) }}" class="{{ $active ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300' }} flex h-12 w-14 flex-col items-center justify-center bg-transparent transition-all active:scale-90">
                        <x-sams.icon name="{{ $item['icon'] }}" class="h-5 w-5 shrink-0" />
                        <span class="mt-1 text-[9px] font-extrabold leading-none tracking-wide">{{ $item['shortName'] }}</span>
                    </a>

                    @if ($loop->iteration === 2)
                        <div class="relative -top-5">
                            <div class="pointer-events-none absolute inset-0 scale-125 rounded-full bg-blue-600/15"></div>
                            <a href="{{ url('/student/attendance') }}" class="relative flex h-14 w-14 items-center justify-center rounded-full border-none bg-blue-600 text-white shadow-lg shadow-blue-500/20 transition-all hover:bg-blue-700 active:scale-95">
                                <x-sams.icon name="qr-code" class="h-6 w-6 shrink-0 text-white" />
                                @unless ($isAttendanceCompleted)
                                    <span class="absolute right-0 top-0 h-3.5 w-3.5 rounded-full border-2 border-white bg-red-500 shadow-md"></span>
                                @endunless
                            </a>
                            <span class="absolute left-1/2 top-[58px] block -translate-x-1/2 whitespace-nowrap pt-1.5 text-[7px] font-extrabold uppercase leading-none tracking-widest text-slate-400">Ghi danh</span>
                        </div>
                    @endif
                @endforeach
            </nav>
        </div>
    </div>
</div>
