<div class="flex h-screen overflow-hidden bg-slate-50 font-sans text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    <div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-[45] bg-slate-900/40 lg:hidden" @click="sidebarOpen = false" x-transition.opacity></div>

    <aside class="fixed inset-y-0 left-0 z-50 flex w-72 flex-shrink-0 flex-col border-r border-slate-200 bg-white transition-transform duration-300 ease-in-out dark:border-slate-800 dark:bg-slate-900 lg:static lg:translate-x-0" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
        <div class="flex items-center justify-between p-6">
            <div>
                <h1 class="text-2xl font-bold text-blue-600">SAMS Admin</h1>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Cổng thông tin khoa</p>
            </div>

            <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 lg:hidden" @click="sidebarOpen = false">
                <x-sams.icon name="x" class="h-5 w-5" />
            </button>
        </div>

        <nav class="sams-scrollbar flex-1 space-y-2 overflow-y-auto px-4 py-2">
            @foreach ($lecturerNavigation as $item)
                @php($active = $isActive($item['active']))

                <a href="{{ url($item['href']) }}" class="{{ $active ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }} group flex items-center rounded-xl px-4 py-3.5 text-sm font-medium transition-all" @click="sidebarOpen = false">
                    <x-sams.icon name="{{ $item['icon'] }}" class="{{ $active ? 'text-blue-700 dark:text-blue-300' : 'text-slate-500 group-hover:text-slate-700 dark:group-hover:text-slate-300' }} mr-4 h-5 w-5" />
                    <span>{{ $item['name'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="mb-16 space-y-2 border-t border-slate-200 p-4 dark:border-slate-800 lg:mb-0">
            @php($settingsActive = $isActive(['settings', 'settings/*', 'profile', 'profile/*']))

            <a href="{{ route('profile.edit') }}" class="{{ $settingsActive ? 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-white' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800' }} flex items-center rounded-xl px-4 py-3.5 text-sm font-medium transition-all" @click="sidebarOpen = false">
                <x-sams.icon name="settings" class="mr-4 h-5 w-5 text-slate-500" />
                <span>Cài đặt</span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center rounded-xl border-none bg-transparent px-4 py-3.5 text-left text-sm font-medium text-red-600 transition-all hover:bg-red-50 dark:hover:bg-red-950/20">
                    <x-sams.icon name="log-out" class="mr-4 h-5 w-5 text-red-500" />
                    <span>Đăng xuất</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="flex flex-1 flex-col overflow-hidden">
        <header class="z-40 flex h-16 flex-shrink-0 items-center justify-between border-b border-slate-200 bg-white px-4 dark:border-slate-800 dark:bg-slate-900 sm:px-6">
            <div class="flex min-w-0 flex-1 items-center gap-3">
                <button type="button" class="-ml-2 shrink-0 rounded-lg p-2 text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800 lg:hidden" @click="sidebarOpen = true">
                    <x-sams.icon name="menu" class="h-6 w-6" />
                </button>

                <h2 class="max-w-[150px] shrink-0 truncate text-base font-bold text-slate-900 dark:text-white sm:max-w-xs lg:hidden">
                    {{ $currentPageTitle }}
                </h2>

                <div class="relative hidden w-full max-w-md sm:block">
                    <x-sams.icon name="search" class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                    <input type="text" placeholder="Tìm kiếm sinh viên, lớp học, hoặc phiên điểm danh..." class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-10 pr-4 text-sm outline-none transition-colors focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500 dark:border-slate-800 dark:bg-slate-950/50 dark:text-slate-100 dark:focus:bg-slate-900" />
                </div>
            </div>

            <div class="flex shrink-0 items-center gap-2 sm:gap-4">
                <button type="button" class="relative rounded-full p-2 text-slate-400 transition-colors hover:bg-slate-50 hover:text-slate-600 dark:hover:bg-slate-800">
                    <x-sams.icon name="bell" class="h-5 w-5" />
                    <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full border-2 border-white bg-red-500 dark:border-slate-900"></span>
                </button>

                <div class="hidden h-8 w-px bg-slate-200 dark:bg-slate-800 sm:block"></div>

                <div class="flex items-center gap-3">
                    <div class="hidden text-right md:block">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $userName }}</p>
                        <p class="text-xs font-medium text-slate-500">Giảng viên</p>
                    </div>
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full border border-slate-200 bg-blue-100 text-sm font-bold text-blue-700 dark:border-slate-700 sm:h-10 sm:w-10">
                        {{ $userInitials }}
                    </span>
                </div>
            </div>
        </header>

        <main class="sams-scrollbar flex-1 overflow-y-auto flex flex-col justify-between">
            <div class="mx-auto max-w-7xl p-4 md:p-8 w-full flex-1 pb-20 md:pb-6">
                {{ $slot }}
            </div>

            @include('layouts.partials.portal-footer')
        </main>
    </div>

    <nav class="fixed bottom-0 left-0 right-0 z-40 flex h-16 select-none items-center justify-around border-t border-slate-200 bg-white px-2 shadow-lg shadow-slate-900/5 dark:border-slate-800 dark:bg-slate-900 md:hidden">
        @foreach ($lecturerMobileNavigation as $item)
            @php($active = $isActive($item['active']))

            <a href="{{ url($item['href']) }}" class="{{ $active ? 'text-blue-600 dark:text-blue-400' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }} flex h-full flex-1 flex-col items-center justify-center py-1.5 transition-colors">
                <x-sams.icon name="{{ $item['icon'] }}" class="mb-1 h-5 w-5" />
                <span class="text-[10px] font-semibold tracking-tight">{{ $item['name'] }}</span>
            </a>
        @endforeach
    </nav>
</div>
