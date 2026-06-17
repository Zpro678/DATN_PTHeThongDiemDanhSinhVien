<x-app-layout variant="lecturer" page-title="Đơn xin nghỉ - Chờ duyệt">
    <div class="max-w-[1400px] mx-auto font-sans">

        <div class="mb-8 flex flex-col lg:flex-row lg:items-start justify-between gap-6">
            <div>
                <h1 class="text-[24px] font-extrabold text-slate-900 leading-none uppercase tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-blue-100 text-blue-600 rounded-xl shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </span>
                    ĐƠN XIN NGHỈ PHÉP
                </h1>
                <p class="text-[14px] text-slate-500 mt-3">Xem xét và phê duyệt các yêu cầu vắng mặt của sinh viên trong tuần này.</p>
            </div>
            <div class="flex flex-wrap items-center gap-4 mt-2 lg:mt-0">
                <div class="bg-white border border-slate-200 rounded-2xl px-5 py-4 shadow-sm min-w-[150px]">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Cần xử lý</p>
                    </div>
                    <p class="text-[28px] font-extrabold text-slate-800 leading-none">12</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl px-5 py-4 shadow-sm min-w-[180px]">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Đã duyệt hôm nay</p>
                    </div>
                    <p class="text-[28px] font-extrabold text-slate-800 leading-none">45</p>
                </div>
            </div>
        </div>       
        


        <div class="bg-white border border-slate-200/80 rounded-[20px] shadow-sm mb-8">
            <div class="p-6 sm:p-8">
                <div class="flex flex-wrap items-end gap-4 mb-6">
                    <!-- Lớp học -->
                    <div class="w-full sm:flex-1 relative" x-data="{ open: false, selected: 'Tất cả các lớp' }" @click.outside="open = false">
                        <label class="block text-[12px] font-medium text-slate-500 mb-1.5 ml-1">Lớp học</label>
                        <button @click="open = !open" type="button" class="w-full px-4 py-2 bg-white border border-slate-200/80 rounded-xl text-[13px] font-medium text-slate-700 hover:border-slate-300 transition-colors flex items-center justify-between">
                            <span x-text="selected" class="truncate">Tất cả các lớp</span>
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="open" 
                            x-transition:enter="transition ease-out duration-200 origin-top"
                            x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-150 origin-top"
                            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                            x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                            class="absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-xl shadow-lg py-1 max-h-60 overflow-auto" 
                            style="display: none;">
                            <template x-for="option in ['Tất cả các lớp', 'CS402: Thuật toán', 'DB101: Cơ bản SQL']" :key="option">
                                <div @click="selected = option; open = false" 
                                     class="cursor-pointer select-none px-4 py-2 hover:bg-slate-50 text-[13px] font-medium text-slate-700">
                                    <span x-text="option" class="block truncate"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Trạng thái -->
                    <div class="w-full sm:flex-1 relative" x-data="{ open: false, selected: 'Chờ duyệt' }" @click.outside="open = false">
                        <label class="block text-[12px] font-medium text-slate-500 mb-1.5 ml-1">Trạng thái</label>
                        <button @click="open = !open" type="button" class="w-full px-4 py-2 bg-white border border-slate-200/80 rounded-xl text-[13px] font-medium text-slate-700 hover:border-slate-300 transition-colors flex items-center justify-between">
                            <span x-text="selected" class="truncate">Chờ duyệt</span>
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="open" class="absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-xl shadow-lg py-1 max-h-60 overflow-auto" style="display: none;">
                            <template x-for="option in ['Tất cả', 'Chờ duyệt', 'Đã duyệt', 'Đã từ chối']" :key="option">
                                <div @click="selected = option; open = false" class="cursor-pointer select-none px-4 py-2 hover:bg-slate-50 text-[13px] font-medium text-slate-700">
                                    <span x-text="option" class="block truncate"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Từ ngày -->
                    <div class="w-full sm:flex-1">
                        <label class="block text-[12px] font-medium text-slate-500 mb-1.5 ml-1">Từ ngày</label>
                        <div class="relative">
                            <input type="text" placeholder="mm/dd/yyyy" class="w-full pl-3 pr-8 py-2 bg-white border border-slate-200/80 rounded-xl text-[13px] font-medium text-slate-700 hover:border-slate-300 transition-colors placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                            <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Đến ngày -->
                    <div class="w-full sm:flex-1">
                        <label class="block text-[12px] font-medium text-slate-500 mb-1.5 ml-1">Đến ngày</label>
                        <div class="relative">
                            <input type="text" placeholder="mm/dd/yyyy" class="w-full pl-3 pr-8 py-2 bg-white border border-slate-200/80 rounded-xl text-[13px] font-medium text-slate-700 hover:border-slate-300 transition-colors placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                            <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Filter button -->
                    <div class="w-full sm:w-auto shrink-0">
                        <button class="w-full sm:w-auto px-5 py-2 bg-[#f1f5f9] hover:bg-[#e2e8f0] text-slate-700 rounded-xl text-[13px] font-semibold flex items-center justify-center gap-2 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                            Lọc
                        </button>
                    </div>
                </div>

                <div class="border border-slate-100 rounded-2xl overflow-hidden">
                    <div class="w-full overflow-x-auto">
                        <table class="w-full min-w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 text-[12px] font-extrabold text-slate-800 uppercase tracking-wider bg-white ">
                                    <th class="px-4 py-4 text-left">Sinh viên</th>
                                    <th class="px-4 py-4 text-left">Lớp / Môn học</th>
                                    <th class="px-4 py-4 text-center">Thời gian</th>
                                    <th class="px-4 py-4 text-left">Lý do</th>
                                    <th class="px-4 py-4 text-center">Minh chứng</th>
                                    <th class="px-4 py-4 text-center leading-relaxed whitespace-nowrap">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-[13px]">
                                <tr class="hover:bg-blue-50/40 transition-colors group cursor-pointer" onclick="window.location='/lecturer/students/leave/1'">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center justify-center shrink-0 w-10 h-10 rounded-full bg-blue-100 text-blue-600 font-bold text-sm">
                                                NH
                                            </div>
                                            <div class="min-w-[120px] max-w-[150px]">
                                                <p class="font-bold text-slate-900 text-[13px] leading-snug truncate">Nguyễn Văn Hoàng</p>
                                                <p class="text-[11px] text-slate-500 mt-0.5">SV: 20214567</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">
                                        <p class="font-bold text-slate-800 text-[13px]">CS101</p>
                                        <p class="text-[11px] text-slate-500 mt-0.5 max-w-[130px] leading-snug">Nhập môn Khoa học Máy tính</p>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2 text-slate-700 min-w-[100px]">
                                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            <div class="flex flex-col text-left">
                                                <span class="text-[13px] font-medium">24/10/2023</span>
                                                <span class="text-[11px] text-slate-500 mt-0.5">(Sáng)</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600"><div class="line-clamp-2 min-w-[150px] max-w-[200px] text-[12px] leading-snug">Em bị ốm sốt cao từ đêm qua nên không thể tham gia lớp...</div></td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center shrink-0 text-blue-600">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                            </div>
                                            <button class="text-slate-400 hover:text-slate-600 p-1 rounded-full" onclick="event.stopPropagation()">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1.5" onclick="event.stopPropagation()">
                                            <button class="px-4 py-2 text-white bg-[#0e3b9c] hover:bg-blue-800 rounded-lg text-[13px] font-bold transition-colors shadow-sm" title="Duyệt">
                                                Duyệt
                                            </button>
                                            <button class="px-2 py-1.5 text-red-500 bg-white border border-red-200 hover:bg-red-50 rounded-lg text-[11px] font-bold uppercase transition-colors text-center w-[60px] leading-tight shadow-sm" title="Từ chối">
                                                TỪ<br>CHỐI
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr class="hover:bg-blue-50/40 transition-colors group cursor-pointer" onclick="window.location='/lecturer/students/leave/2'">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center justify-center shrink-0 w-10 h-10 rounded-full bg-blue-100 text-blue-600 font-bold text-sm">
                                                LT
                                            </div>
                                            <div class="min-w-[120px] max-w-[150px]">
                                                <p class="font-bold text-slate-900 text-[13px] leading-snug truncate">Lê Thị Thanh</p>
                                                <p class="text-[11px] text-slate-500 mt-0.5">SV: 20218901</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">
                                        <p class="font-bold text-slate-800 text-[13px]">ENG202</p>
                                        <p class="text-[11px] text-slate-500 mt-0.5 max-w-[130px] leading-snug">Tiếng Anh Học thuật</p>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2 text-slate-700 min-w-[100px]">
                                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            <div class="flex flex-col text-left">
                                                <span class="text-[13px] font-medium">25/10/2023</span>
                                                <span class="text-[11px] text-slate-500 mt-0.5">(Chiều)</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600"><div class="line-clamp-2 min-w-[150px] max-w-[200px] text-[12px] leading-snug">Gia đình có việc đột xuất cần về quê gấp. Em xin phép vắn...</div></td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <span class="text-[12px] italic text-slate-400">Không có</span>
                                            <button class="text-slate-400 hover:text-slate-600 p-1 rounded-full" onclick="event.stopPropagation()">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1.5" onclick="event.stopPropagation()">
                                            <button class="px-4 py-2 text-white bg-[#0e3b9c] hover:bg-blue-800 rounded-lg text-[13px] font-bold transition-colors shadow-sm" title="Duyệt">
                                                Duyệt
                                            </button>
                                            <button class="px-2 py-1.5 text-red-500 bg-white border border-red-200 hover:bg-red-50 rounded-lg text-[11px] font-bold uppercase transition-colors text-center w-[60px] leading-tight shadow-sm" title="Từ chối">
                                                TỪ<br>CHỐI
                                            </button>
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
</x-app-layout>
