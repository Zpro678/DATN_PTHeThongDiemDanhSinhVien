<x-app-layout variant="lecturer" page-title="Sinh viên - Đã thôi học">
    <div class="max-w-[1400px] mx-auto font-sans">

        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-[24px] font-extrabold text-slate-900 leading-none uppercase tracking-tight flex items-center gap-3">
                <span class="p-2 bg-blue-100 text-blue-600 rounded-xl shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
                Sinh viên
            </h1>
        </div>

        <!-- Tabs -->
        <div class="flex justify-center mb-8">
            <div class="flex w-full max-w-[480px] border-b-[2px] border-slate-200/70">
                <a href="/lecturer/students" class="relative flex-1 flex justify-center items-center gap-1.5 pb-2.5 pt-2 px-1 md:px-2 text-slate-500 hover:text-slate-800 text-[14px] font-bold uppercase tracking-wide whitespace-nowrap transition-all duration-300 group hover:bg-slate-50/80 rounded-t-xl">
                    <span class="absolute bottom-[-2px] left-0 w-full h-[2.5px] bg-slate-300 scale-x-0 group-hover:scale-x-100 transition-transform duration-300 rounded-t-md"></span>
                    <svg class="w-[17px] h-[17px] text-emerald-500 group-hover:scale-110 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    Đang học
                </a>
                <a href="/lecturer/students/warning" class="relative flex-1 flex justify-center items-center gap-1.5 pb-2.5 pt-2 px-1 md:px-2 text-slate-500 hover:text-slate-800 text-[14px] font-bold uppercase tracking-wide whitespace-nowrap transition-all duration-300 group hover:bg-slate-50/80 rounded-t-xl">
                    <span class="absolute bottom-[-2px] left-0 w-full h-[2.5px] bg-slate-300 scale-x-0 group-hover:scale-x-100 transition-transform duration-300 rounded-t-md"></span>
                    <svg class="w-[17px] h-[17px] text-amber-500 group-hover:scale-110 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    Cảnh báo
                </a>
                <a href="/lecturer/students/archived" class="relative flex-1 flex justify-center items-center gap-1.5 pb-2.5 pt-2 px-1 md:px-2 text-blue-600 text-[14px] font-bold uppercase tracking-wide whitespace-nowrap transition-all duration-300 group hover:bg-blue-50/40 rounded-t-xl">
                    <span class="absolute bottom-[-2px] left-0 w-full h-[2.5px] bg-blue-600 rounded-t-md"></span>
                    <svg class="w-[17px] h-[17px] text-rose-500 group-hover:scale-110 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                    Đã thôi học
                </a>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="bg-white border border-slate-200/80 rounded-[20px] shadow-sm mb-8">
            <!-- Card Body -->
            <div class="p-6 sm:p-8">
                <!-- Toolbar -->
                <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                    <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                        <!-- Lớp học -->
                        <div class="relative w-full sm:w-60" :class="{'z-50': open, 'z-10': !open}" x-data="{ open: false, selected: 'Tất cả lớp học' }" @click.outside="open = false">
                            <button @click="open = !open" type="button" class="w-full pl-11 pr-8 py-2.5 bg-white border border-slate-200/80 hover:border-slate-300 rounded-full text-[13.5px] font-semibold text-slate-700 outline-none transition-all shadow-sm cursor-pointer text-left focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 flex items-center justify-between" :class="{'border-blue-500 ring-2 ring-blue-500/20': open}">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                </div>
                                <span x-text="selected" class="block truncate">Tất cả lớp học</span>
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </button>
                            
                            <div x-show="open" 
                                x-transition:enter="transition ease-out duration-200 origin-top"
                                x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-150 origin-top"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                                class="absolute z-50 mt-2 w-full bg-white border border-slate-200 rounded-xl shadow-lg py-1 max-h-60 overflow-auto focus:outline-none" 
                                style="display: none;">
                                <template x-for="option in ['Tất cả lớp học', 'CS402: Thuật toán', 'DB101: Cơ bản SQL']" :key="option">
                                    <div @click="selected = option; open = false" 
                                         class="cursor-pointer select-none relative py-2.5 pl-10 pr-4 hover:bg-slate-50 text-[13.5px] font-medium transition-colors"
                                         :class="{'text-blue-600 bg-blue-50/50': selected === option, 'text-slate-700': selected !== option}">
                                        <span x-text="option" class="block truncate" :class="{'font-semibold': selected === option}"></span>
                                        <span x-show="selected === option" class="absolute inset-y-0 left-0 flex items-center pl-3 text-blue-600">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                        </span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Area -->
                <div class="border border-slate-100 rounded-2xl overflow-hidden">
            <div class="w-full overflow-x-auto">
                <table class="w-full min-w-[900px] text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-[14px] font-bold text-slate-800 uppercase tracking-wider bg-white whitespace-nowrap">
                            <th class="px-6 py-5 text-center">Sinh viên</th>
                            <th class="px-6 py-5 text-center">Email</th>
                            <th class="px-6 py-5 text-center">Lớp học</th>
                            <th class="px-6 py-5 text-center">Ngày thôi học</th>
                            <th class="px-6 py-5 text-center leading-relaxed">Trạng thái</th>
                            <th class="px-6 py-5 text-center leading-relaxed">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-[13px]">
                        <!-- Row 1 -->
                        <tr class="bg-slate-50/50 hover:bg-slate-100/50 transition-colors group">
                            <td class="px-6 py-4 opacity-70">
                                <div class="flex items-center gap-4">
                                    <img src="https://ui-avatars.com/api/?name=Michael+Wang&background=cbd5e1&color=334155" alt="Avatar" class="w-10 h-10 rounded-full border border-slate-300 shadow-sm grayscale">
                                    <div>
                                        <p class="font-bold text-slate-700 text-sm line-through">Michael Wang</p>
                                        <p class="text-[13px] text-slate-500 mt-0.5 font-medium">ID: CS2024-112</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-500 font-medium opacity-70">m.wang@university.edu</td>
                            <td class="px-6 py-4 text-slate-500 font-medium text-sm whitespace-nowrap opacity-70">CS402: Thuật toán</td>
                            <td class="px-6 py-4 text-center text-slate-500 font-medium">
                                12/04/2026
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-bold bg-slate-200 text-slate-600 border border-slate-300">
                                    Đã bảo lưu
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button class="text-blue-600 hover:text-blue-800 font-medium text-sm flex items-center justify-center gap-1 mx-auto">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    Khôi phục
                                </button>
                            </td>
                        </tr>

                        <!-- Row 2 -->
                        <tr class="bg-slate-50/50 hover:bg-slate-100/50 transition-colors group">
                            <td class="px-6 py-4 opacity-70">
                                <div class="flex items-center gap-4">
                                    <img src="https://ui-avatars.com/api/?name=Lisa+Chen&background=cbd5e1&color=334155" alt="Avatar" class="w-10 h-10 rounded-full border border-slate-300 shadow-sm grayscale">
                                    <div>
                                        <p class="font-bold text-slate-700 text-sm line-through">Lisa Chen</p>
                                        <p class="text-[13px] text-slate-500 mt-0.5 font-medium">ID: CS2024-089</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-500 font-medium opacity-70">l.chen@university.edu</td>
                            <td class="px-6 py-4 text-slate-500 font-medium text-sm whitespace-nowrap opacity-70">DB101: Cơ bản SQL</td>
                            <td class="px-6 py-4 text-center text-slate-500 font-medium">
                                05/03/2026
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-bold bg-slate-200 text-slate-600 border border-slate-300">
                                    Rút môn
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button class="text-blue-600 hover:text-blue-800 font-medium text-sm flex items-center justify-center gap-1 mx-auto">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    Khôi phục
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
            </div>
        </div>

    </div>
</x-app-layout>
