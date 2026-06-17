<x-app-layout variant="lecturer" page-title="Đơn xin nghỉ - Đã duyệt">
    <div class="max-w-[1400px] mx-auto font-sans">

        <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <h1 class="text-[24px] font-extrabold text-slate-900 leading-none uppercase tracking-tight flex items-center gap-3">
                <span class="p-2 bg-blue-100 text-blue-600 rounded-xl shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </span>
                Đơn xin nghỉ
            </h1>

        </div>       
        
        <div class="flex justify-center mb-8">
            <div class="flex w-full max-w-[480px] border-b-[2px] border-slate-200/70">
                <a href="/lecturer/students/leave" class="relative flex-1 flex justify-center items-center gap-1.5 pb-2.5 pt-2 px-1 md:px-2 text-slate-500 hover:text-slate-800 text-[14px] font-bold uppercase tracking-wide  transition-all duration-300 group hover:bg-slate-50/80 rounded-t-xl">
                    <span class="absolute bottom-[-2px] left-0 w-full h-[2.5px] bg-slate-300 scale-x-0 group-hover:scale-x-100 transition-transform duration-300 rounded-t-md"></span>
                    <svg class="w-[17px] h-[17px] text-orange-500 group-hover:scale-110 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Chờ duyệt
                    <span class="ml-1 bg-red-500 text-white text-[10px] px-1.5 py-0.5 rounded-full font-bold">3</span>
                </a>
                <a href="/lecturer/students/leave/approve" class="relative flex-1 flex justify-center items-center gap-1.5 pb-2.5 pt-2 px-1 md:px-2 text-blue-600 text-[14px] font-bold uppercase tracking-wide  transition-all duration-300 group hover:bg-blue-50/40 rounded-t-xl">
                    <span class="absolute bottom-[-2px] left-0 w-full h-[2.5px] bg-blue-600 rounded-t-md"></span>
                    <svg class="w-[17px] h-[17px] text-emerald-500 group-hover:scale-110 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Đã duyệt
                </a>
                <a href="/lecturer/students/leave/reject" class="relative flex-1 flex justify-center items-center gap-1.5 pb-2.5 pt-2 px-1 md:px-2 text-slate-500 hover:text-slate-800 text-[14px] font-bold uppercase tracking-wide  transition-all duration-300 group hover:bg-slate-50/80 rounded-t-xl">
                    <span class="absolute bottom-[-2px] left-0 w-full h-[2.5px] bg-slate-300 scale-x-0 group-hover:scale-x-100 transition-transform duration-300 rounded-t-md"></span>
                    <svg class="w-[17px] h-[17px] text-rose-500 group-hover:scale-110 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Đã từ chối
                </a>
            </div>
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
                    </div>

                    <!-- Right side Toolbar (Search) -->
                    <div class="relative w-full md:w-80">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" placeholder="Tìm kiếm sinh viên, ngày xin nghỉ..." class="w-full pl-10 pr-4 py-1.5 bg-white border border-slate-200/80 hover:border-slate-300 rounded-full text-[13.5px] text-slate-700 outline-none transition-all shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>
                </div>

                <div class="border border-slate-100 rounded-2xl overflow-hidden">
                    <div class="w-full overflow-x-auto">
                        <table class="w-full min-w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 text-[14px] font-bold text-slate-800 uppercase tracking-wider bg-white ">
                                    <th class="px-6 py-5 text-center">Sinh viên</th>
                                    <th class="px-6 py-5 text-center">Lớp học</th>
                                    <th class="px-6 py-5 text-center">Ngày xin nghỉ</th>
                                    <th class="px-6 py-5 text-center">Lý do</th>
                                    <th class="px-6 py-5 text-center">Minh chứng</th>
                                    <th class="px-6 py-5 text-center leading-relaxed whitespace-nowrap">Trạng thái</th>
                                    <th class="px-6 py-5 text-center leading-relaxed whitespace-nowrap">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-[13px]">
                                <tr class="hover:bg-blue-50/40 transition-colors group cursor-pointer" onclick="window.location='/lecturer/students/leave/1'">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <img src="https://ui-avatars.com/api/?name=Sarah+Jenkins&background=e0f2fe&color=0369a1" alt="Avatar" class="w-10 h-10 rounded-full border border-slate-200 shadow-sm">
                                            <div>
                                                <p class="font-bold text-slate-900 text-sm line-clamp-2">Sarah Jenkins</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 font-medium text-sm"><div class="line-clamp-2">DB101: Cơ bản SQL</div></td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="inline-flex flex-col text-slate-800">
                                            <span class="font-bold">14/06/2026</span>
                                            <span class="text-[12px] text-slate-500">Ca 2 (10:00 - 12:30)</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600"><div class="line-clamp-2 min-w-[150px]">Đại diện tham gia cuộc thi Olympic Tin học toàn quốc.</div></td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="#" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-xs font-bold hover:bg-blue-100 transition-colors" onclick="event.stopPropagation()">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                            giay-cu-di-thi.pdf
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-bold bg-[#f0fdf4] text-green-700 border border-green-200/60 whitespace-nowrap">
                                            Đã duyệt
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <button class="text-slate-400 hover:text-slate-600 p-1.5 rounded-md hover:bg-slate-100 transition-colors" title="Xem chi tiết" onclick="event.stopPropagation(); window.location='/lecturer/students/leave/1'">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
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
