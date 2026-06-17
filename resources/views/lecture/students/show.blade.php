<x-app-layout variant="lecturer" page-title="Chi tiết Sinh viên">
    <div class="font-sans space-y-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Chi tiết Sinh viên</h1>
                    <p class="text-sm text-slate-500 mt-1">Xem thông tin và lịch sử điểm danh của sinh viên</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Profile Card -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-8 relative rounded-t-2xl">
                            <!-- Status Badge -->
                            <div class="absolute top-4 right-6">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-white/20 text-white border border-white/30 backdrop-blur-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-400 mr-1.5 shadow-[0_0_8px_rgba(74,222,128,0.8)] animate-pulse"></span>
                                    Đang học
                                </span>
                            </div>

                            <div class="flex items-center gap-4 mt-2">
                                <!-- Avatar -->
                                <div class="p-1 bg-white/20 rounded-2xl shadow-sm backdrop-blur-sm flex-shrink-0">
                                    <div class="w-16 h-16 bg-slate-50 rounded-xl flex items-center justify-center font-bold text-slate-800 text-2xl border border-white/40">
                                        AT
                                    </div>
                                </div>

                                <!-- Name & MSSV -->
                                <div class="min-w-0 flex-1">
                                    <h2 class="text-xl font-bold text-white drop-shadow-sm truncate" title="Alex Thompson">Alex Thompson</h2>
                                    <p class="text-[13px] text-blue-100 font-medium mt-0.5 drop-shadow-sm">MSSV: CS2024-001</p>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 pb-6 pt-6">
                            <!-- Contact Info -->
                            <div class="space-y-4">
                                <div class="flex items-center gap-3 text-slate-600">
                                    <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <span class="text-[14px] font-medium">a.thompson@university.edu</span>
                                </div>
                                <div class="flex items-center gap-3 text-slate-600">
                                    <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m3-4h1m-1 4h1m-5 8h8"></path></svg>
                                    </div>
                                    <span class="text-[14px] font-medium">Lớp: CS402 (Thuật toán)</span>
                                </div>
                                <div class="flex items-center gap-3 text-slate-600">
                                    <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <span class="text-[14px] font-medium">Ngày tham gia: 10/01/2026</span>
                                </div>
                                <div class="flex items-center gap-3 text-slate-600">
                                    <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    </div>
                                    <span class="text-[14px] font-medium">Tài khoản: Đã liên kết</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Summary Stats -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
                        <h3 class="text-[15px] font-bold text-slate-800 mb-4">Tổng quan điểm danh</h3>
                        
                        <!-- Progress bar -->
                        <div class="mb-6">
                            <div class="flex justify-between items-end mb-2">
                                <span class="text-sm font-medium text-slate-600">Tỉ lệ tham gia</span>
                                <span class="text-2xl font-black text-green-600">94%</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2.5">
                                <div class="bg-green-500 h-2.5 rounded-full" style="width: 94%"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 text-center">
                                <div class="text-[24px] font-black text-slate-700">18</div>
                                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mt-1">Tổng buổi</div>
                            </div>
                            <div class="p-4 bg-green-50 rounded-xl border border-green-100 text-center">
                                <div class="text-[24px] font-black text-green-600">17</div>
                                <div class="text-[11px] font-bold text-green-700 uppercase tracking-wider mt-1">Có mặt</div>
                            </div>
                            <div class="p-4 bg-red-50 rounded-xl border border-red-100 text-center">
                                <div class="text-[24px] font-black text-red-600">1</div>
                                <div class="text-[11px] font-bold text-red-700 uppercase tracking-wider mt-1">Vắng mặt</div>
                            </div>
                            <div class="p-4 bg-orange-50 rounded-xl border border-orange-100 text-center">
                                <div class="text-[24px] font-black text-orange-600">0</div>
                                <div class="text-[11px] font-bold text-orange-700 uppercase tracking-wider mt-1">Đi muộn</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: History Table -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col h-full">
                        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                            <h3 class="text-[15px] font-bold text-slate-800">Lịch sử điểm danh</h3>
                            
                            <!-- Filter -->
                            <div class="flex items-center gap-2">
                                <select class="text-sm bg-slate-50 border-slate-200 text-slate-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 py-1.5 pl-3 pr-8">
                                    <option>Tất cả trạng thái</option>
                                    <option>Có mặt</option>
                                    <option>Vắng mặt</option>
                                    <option>Đi muộn</option>
                                </select>
                            </div>
                        </div>

                        <div class="overflow-x-auto flex-1">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-100 text-[12px] font-bold text-slate-500 uppercase tracking-wider bg-slate-50/50">
                                        <th class="px-6 py-4">Ngày học</th>
                                        <th class="px-6 py-4 text-center">Ca học</th>
                                        <th class="px-6 py-4 text-center">Trạng thái</th>
                                        <th class="px-6 py-4">Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-[13px]">
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-slate-800">18/06/2026</div>
                                            <div class="text-slate-500 text-[12px]">Thứ 5</div>
                                        </td>
                                        <td class="px-6 py-4 text-center text-slate-600 font-medium">Ca 1</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-[#f0fdf4] text-green-700 border border-green-200">
                                                Có mặt
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-slate-500">-</td>
                                    </tr>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-slate-800">16/06/2026</div>
                                            <div class="text-slate-500 text-[12px]">Thứ 3</div>
                                        </td>
                                        <td class="px-6 py-4 text-center text-slate-600 font-medium">Ca 1</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-[#fef2f2] text-red-700 border border-red-200">
                                                Vắng mặt
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-slate-500 text-[12px]">Không phép</td>
                                    </tr>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-slate-800">11/06/2026</div>
                                            <div class="text-slate-500 text-[12px]">Thứ 5</div>
                                        </td>
                                        <td class="px-6 py-4 text-center text-slate-600 font-medium">Ca 1</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-[#f0fdf4] text-green-700 border border-green-200">
                                                Có mặt
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-slate-500">-</td>
                                    </tr>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-slate-800">09/06/2026</div>
                                            <div class="text-slate-500 text-[12px]">Thứ 3</div>
                                        </td>
                                        <td class="px-6 py-4 text-center text-slate-600 font-medium">Ca 1</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-[#fff7ed] text-orange-700 border border-orange-200">
                                                Đi muộn
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-slate-500 text-[12px]">Muộn 15 phút</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination Simulation -->
                        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between text-sm text-slate-500 bg-slate-50/50">
                            <span>Hiển thị 1 - 4 của 18 buổi</span>
                            <div class="flex items-center gap-1">
                                <button class="p-1.5 rounded-lg border border-slate-200 bg-white text-slate-400 hover:text-slate-600 disabled:opacity-50" disabled>
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                                </button>
                                <button class="p-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Back Button -->
            <div class="mt-8 flex justify-start">
                <button onclick="window.history.back()" class="inline-flex items-center gap-2 px-4 py-2 bg-white text-slate-600 hover:text-slate-800 hover:bg-slate-50 rounded-xl transition-all shadow-sm border border-slate-200/80 font-medium text-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Quay lại
                </button>
            </div>

    </div>
</x-app-layout>
