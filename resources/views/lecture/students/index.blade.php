<x-app-layout variant="lecturer" page-title="Sinh viên">
    <div class="max-w-[1400px] mx-auto font-sans">

        <div class="mb-6 flex flex-col justify-center">
            <div class="flex items-center gap-3 mb-1.5">
                <span class="p-2 bg-blue-100 text-blue-600 rounded-xl shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
                <h1 class="text-[24px] font-extrabold text-slate-900 leading-none uppercase tracking-tight">Sinh viên</h1>
            </div>
            <p class="text-[14px] text-slate-500 ml-1 mt-1">Xem, tìm kiếm và quản lý toàn bộ sinh viên đang tham gia các lớp học của bạn.</p>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-[20px] shadow-sm mb-8">
            <div class="p-6 sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                    <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                        <div class="relative w-full sm:w-60" :class="{'z-50': open, 'z-10': !open}" x-data="{ open: false, selected: 'Tất cả lớp học' }" @click.outside="open = false">
                            <button @click="open = !open" type="button" class="w-full pl-11 pr-8 py-1.5 bg-white border border-slate-200/80 hover:border-slate-300 rounded-full text-[13.5px] font-semibold text-slate-700 outline-none transition-all shadow-sm cursor-pointer text-left focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 flex items-center justify-between" :class="{'border-blue-500 ring-2 ring-blue-500/20': open}">
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
                                         class="cursor-pointer select-none relative py-2 pl-10 pr-4 hover:bg-slate-50 text-[13.5px] font-medium transition-colors"
                                         :class="{'text-blue-600 bg-blue-50/50': selected === option, 'text-slate-700': selected !== option}">
                                        <span x-text="option" class="block truncate" :class="{'font-semibold': selected === option}"></span>
                                        <span x-show="selected === option" class="absolute inset-y-0 left-0 flex items-center pl-3 text-blue-600">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                        </span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="relative w-full sm:w-56" :class="{'z-50': open, 'z-10': !open}" x-data="{ open: false, selected: 'Tất cả rủi ro' }" @click.outside="open = false">
                            <button @click="open = !open" type="button" class="w-full pl-11 pr-8 py-1.5 bg-white border border-slate-200/80 hover:border-slate-300 rounded-full text-[13.5px] font-semibold text-slate-700 outline-none transition-all shadow-sm cursor-pointer text-left focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 flex items-center justify-between" :class="{'border-amber-500 ring-2 ring-amber-500/20': open}">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                </div>
                                <span x-text="selected" class="block truncate">Tất cả rủi ro</span>
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
                                <template x-for="option in ['Tất cả rủi ro', 'Nghiêm trọng', 'Cao', 'Ổn định']" :key="option">
                                    <div @click="selected = option; open = false" 
                                         class="cursor-pointer select-none relative py-2 pl-10 pr-4 hover:bg-slate-50 text-[13.5px] font-medium transition-colors"
                                         :class="{'text-amber-600 bg-amber-50/50': selected === option, 'text-slate-700': selected !== option}">
                                        <span x-text="option" class="block truncate" :class="{'font-semibold': selected === option}"></span>
                                        <span x-show="selected === option" class="absolute inset-y-0 left-0 flex items-center pl-3 text-amber-600">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                        </span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Right side Toolbar (Search) -->
                    <div class="relative w-full md:w-72">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" placeholder="Tìm kiếm sinh viên, email..." class="w-full pl-10 pr-4 py-1.5 bg-white border border-slate-200/80 hover:border-slate-300 rounded-full text-[13.5px] text-slate-700 outline-none transition-all shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>

                </div>

                <div class="border border-slate-100 rounded-2xl overflow-hidden">
            <div class="w-full overflow-x-auto">
                <table class="w-full min-w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-[14px] font-bold text-slate-800 uppercase tracking-wider bg-white ">
                            <th class="px-6 py-5 text-center">Sinh viên</th>
                            <th class="px-6 py-5 text-center">Email</th>
                            <th class="px-6 py-5 text-center">Lớp học</th>
                            <th class="px-6 py-5 text-center">Tỉ lệ điểm danh</th>
                            <th class="px-6 py-5 text-center leading-relaxed">Rủi ro</th>
                            <th class="px-6 py-5 text-center leading-relaxed">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-[13px]">
                        <tr class="hover:bg-blue-50/40 transition-colors group cursor-pointer" onclick="window.location='/lecturer/students/1/detail'">

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <img src="https://ui-avatars.com/api/?name=Alex+Thompson&background=f1f5f9&color=0f172a" alt="Avatar" class="w-10 h-10 rounded-full border border-slate-200 shadow-sm">
                                    <div>
                                        <p class="font-bold text-slate-900 text-sm">Alex Thompson</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 font-medium">a.thompson@university.edu</td>
                            <td class="px-6 py-4 text-slate-600 font-medium text-sm "><div class="line-clamp-2" title="CS402: Thuật toán">CS402: Thuật toán</div></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4 w-48">
                                    <div class="flex-1 bg-slate-100 rounded-full h-2">
                                        <div class="bg-green-500 h-2 rounded-full" style="width: 94%"></div>
                                    </div>
                                    <span class="font-bold text-green-600 text-sm w-10">94%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-bold bg-[#f0fdf4] text-green-700 border border-green-200/60">
                                    Ổn định
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="relative inline-block text-left" x-data="{ openMenu: false }" @click.outside="openMenu = false">
                                    <button @click="openMenu = !openMenu" class="text-slate-400 hover:text-slate-600 focus:outline-none">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                                    </button>
                                    
                                    <div x-show="openMenu" x-transition x-cloak class="absolute right-0 z-50 mt-2 w-40 origin-top-right rounded-xl bg-white border border-slate-200 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none py-1">
                                        <button @click="$dispatch('open-edit-student-modal'); openMenu = false" class="text-slate-700 hover:bg-slate-50 flex w-full items-center gap-2 px-4 py-2 text-sm font-medium transition-colors">
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            Sửa thông tin
                                        </button>
                                        <button class="text-red-600 hover:bg-red-50 flex w-full items-center gap-2 px-4 py-2 text-sm font-medium transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            Xóa sinh viên
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        
                        <tr class="hover:bg-blue-50/40 transition-colors group cursor-pointer" onclick="window.location='/lecturer/students/1/detail'">

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <img src="https://ui-avatars.com/api/?name=Elena+Rodriguez&background=fee2e2&color=991b1b" alt="Avatar" class="w-10 h-10 rounded-full border border-slate-200 shadow-sm">
                                    <div>
                                        <p class="font-bold text-slate-900 text-sm">Elena Rodriguez</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 font-medium">e.rod@university.edu</td>
                            <td class="px-6 py-4 text-slate-600 font-medium text-sm "><div class="line-clamp-2" title="DB101: Cơ bản SQL">DB101: Cơ bản SQL</div></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4 w-48">
                                    <div class="flex-1 bg-slate-100 rounded-full h-2">
                                        <div class="bg-red-500 h-2 rounded-full" style="width: 62%"></div>
                                    </div>
                                    <span class="font-bold text-red-600 text-sm w-10">62%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] uppercase tracking-wider font-bold bg-[#fef2f2] text-red-700 border border-red-200">
                                    NGHIÊM TRỌNG
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="relative inline-block text-left" x-data="{ openMenu: false }" @click.outside="openMenu = false">
                                    <button @click="openMenu = !openMenu" class="text-slate-400 hover:text-slate-600 focus:outline-none">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                                    </button>
                                    
                                    <div x-show="openMenu" x-transition x-cloak class="absolute right-0 z-50 mt-2 w-40 origin-top-right rounded-xl bg-white border border-slate-200 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none py-1">
                                        <button @click="$dispatch('open-edit-student-modal'); openMenu = false" class="text-slate-700 hover:bg-slate-50 flex w-full items-center gap-2 px-4 py-2 text-sm font-medium transition-colors">
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            Sửa thông tin
                                        </button>
                                        <button class="text-red-600 hover:bg-red-50 flex w-full items-center gap-2 px-4 py-2 text-sm font-medium transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            Xóa sinh viên
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-blue-50/40 transition-colors group cursor-pointer" onclick="window.location='/lecturer/students/1/detail'">

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <img src="https://ui-avatars.com/api/?name=Marcus+Chen&background=ffedd5&color=c2410c" alt="Avatar" class="w-10 h-10 rounded-full border border-slate-200 shadow-sm">
                                    <div>
                                        <p class="font-bold text-slate-900 text-sm">Marcus Chen</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 font-medium">m.chen@university.edu</td>
                            <td class="px-6 py-4 text-slate-600 font-medium text-sm "><div class="line-clamp-2" title="CS402: Thuật toán">CS402: Thuật toán</div></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4 w-48">
                                    <div class="flex-1 bg-slate-100 rounded-full h-2">
                                        <div class="bg-orange-500 h-2 rounded-full" style="width: 78%"></div>
                                    </div>
                                    <span class="font-bold text-orange-500 text-sm w-10">78%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-bold bg-[#fff7ed] text-orange-700 border border-orange-200/60">
                                    Cao
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="relative inline-block text-left" x-data="{ openMenu: false }" @click.outside="openMenu = false">
                                    <button @click="openMenu = !openMenu" class="text-slate-400 hover:text-slate-600 focus:outline-none">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                                    </button>
                                    
                                    <div x-show="openMenu" x-transition x-cloak class="absolute right-0 z-50 mt-2 w-40 origin-top-right rounded-xl bg-white border border-slate-200 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none py-1">
                                        <button @click="$dispatch('open-edit-student-modal'); openMenu = false" class="text-slate-700 hover:bg-slate-50 flex w-full items-center gap-2 px-4 py-2 text-sm font-medium transition-colors">
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            Sửa thông tin
                                        </button>
                                        <button class="text-red-600 hover:bg-red-50 flex w-full items-center gap-2 px-4 py-2 text-sm font-medium transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            Xóa sinh viên
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-blue-50/40 transition-colors group cursor-pointer" onclick="window.location='/lecturer/students/1/detail'">

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <img src="https://ui-avatars.com/api/?name=Sarah+Jenkins&background=e0f2fe&color=0369a1" alt="Avatar" class="w-10 h-10 rounded-full border border-slate-200 shadow-sm">
                                    <div>
                                        <p class="font-bold text-slate-900 text-sm">Sarah Jenkins</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 font-medium">s.jenkins@university.edu</td>
                            <td class="px-6 py-4 text-slate-600 font-medium text-sm "><div class="line-clamp-2" title="DB101: Cơ bản SQL">DB101: Cơ bản SQL</div></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4 w-48">
                                    <div class="flex-1 bg-slate-100 rounded-full h-2">
                                        <div class="bg-blue-500 h-2 rounded-full" style="width: 87%"></div>
                                    </div>
                                    <span class="font-bold text-blue-600 text-sm w-10">87%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-bold bg-[#eff6ff] text-blue-700 border border-blue-200/60">
                                    Trung bình
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="relative inline-block text-left" x-data="{ openMenu: false }" @click.outside="openMenu = false">
                                    <button @click="openMenu = !openMenu" class="text-slate-400 hover:text-slate-600 focus:outline-none">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                                    </button>
                                    
                                    <div x-show="openMenu" x-transition x-cloak class="absolute right-0 z-50 mt-2 w-40 origin-top-right rounded-xl bg-white border border-slate-200 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none py-1">
                                        <button @click="$dispatch('open-edit-student-modal'); openMenu = false" class="text-slate-700 hover:bg-slate-50 flex w-full items-center gap-2 px-4 py-2 text-sm font-medium transition-colors">
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            Sửa thông tin
                                        </button>
                                        <button class="text-red-600 hover:bg-red-50 flex w-full items-center gap-2 px-4 py-2 text-sm font-medium transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            Xóa sinh viên
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
            </div>
        </div>

    </div>

    <!-- Edit Student Modal -->
    <div x-data="{ open: false }" 
         @open-edit-student-modal.window="open = true"
         @keydown.escape.window="open = false"
         x-show="open" 
         class="relative z-[100]" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true"
         x-cloak>
         
         <!-- Background backdrop -->
         <div x-show="open" 
              x-transition:enter="ease-out duration-300" 
              x-transition:enter-start="opacity-0" 
              x-transition:enter-end="opacity-100" 
              x-transition:leave="ease-in duration-200" 
              x-transition:leave-start="opacity-100" 
              x-transition:leave-end="opacity-0" 
              class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>

         <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <!-- Modal panel -->
                <div x-show="open" @click.away="open = false"
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-200">
                    
                    <form action="#" method="POST" @submit.prevent="open = false; alert('Đã lưu thông tin sinh viên thành công!');">
                        <div class="border-b border-slate-200 bg-slate-50 px-6 py-4 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-slate-900" id="modal-title">Sửa thông tin học viên</h3>
                            <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                                <span class="sr-only">Đóng</span>
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <div class="px-6 py-5 space-y-4">
                            <!-- MSSV -->
                            <div>
                                <label for="student_code" class="block text-sm font-semibold text-slate-700">Mã sinh viên (MSSV) <span class="text-red-500">*</span></label>
                                <input type="text" name="student_code" id="student_code" value="CS2024-001" required
                                    class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono text-slate-800">
                            </div>

                            <!-- Họ tên -->
                            <div>
                                <label for="full_name" class="block text-sm font-semibold text-slate-700">Họ và tên <span class="text-red-500">*</span></label>
                                <input type="text" name="full_name" id="full_name" value="Alex Thompson" required
                                    class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-slate-800">
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-semibold text-slate-700">Email</label>
                                <input type="email" name="email" id="email" value="a.thompson@university.edu"
                                    class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-slate-800">
                            </div>

                            <!-- Trạng thái -->
                            <div>
                                <label for="status" class="block text-sm font-semibold text-slate-700">Trạng thái</label>
                                <select name="status" id="status" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-slate-800">
                                    <option value="active" selected>Đang học (Hoạt động)</option>
                                    <option value="dropped">Nghỉ học</option>
                                </select>
                            </div>
                        </div>

                        <div class="border-t border-slate-200 bg-slate-50 px-6 py-4 flex items-center justify-end gap-3">
                            <button type="button" @click="open = false" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Hủy
                            </button>
                            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
         </div>
    </div>
</x-app-layout>
