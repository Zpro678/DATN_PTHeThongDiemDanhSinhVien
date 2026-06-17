<x-app-layout variant="lecturer" page-title="Bảng điều khiển">
    <div class="max-w-[1400px] mx-auto font-sans">

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <h1 class="text-[24px] font-extrabold text-slate-900 leading-none uppercase tracking-tight flex items-center gap-3">
                <span class="p-2 bg-blue-100 text-blue-600 rounded-xl shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                </span>
                Bảng điều khiển
            </h1>

        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-2xl p-7 border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-default group">
                <div class="flex items-center justify-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white shadow-lg shadow-blue-500/25 group-hover:scale-110 transition-transform duration-300 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <p class="text-slate-500 text-[13px] font-bold uppercase tracking-wide">Tổng số sinh viên</p>
                </div>
                <p class="text-3xl font-extrabold text-slate-900 tracking-tight text-center">1,248</p>
            </div>

            <div class="bg-white rounded-2xl p-7 border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-default group">
                <div class="flex items-center justify-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-white shadow-lg shadow-emerald-500/25 group-hover:scale-110 transition-transform duration-300 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <p class="text-slate-500 text-[13px] font-bold uppercase tracking-wide">Tỉ lệ điểm danh TB</p>
                </div>
                <p class="text-3xl font-extrabold text-slate-900 tracking-tight text-center">88.4%</p>
            </div>

            <div class="bg-white rounded-2xl p-7 border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-default group">
                <div class="flex items-center justify-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-violet-700 flex items-center justify-center text-white shadow-lg shadow-violet-500/25 group-hover:scale-110 transition-transform duration-300 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <p class="text-slate-500 text-[13px] font-bold uppercase tracking-wide">Lớp đã hoàn thành</p>
                </div>
                <p class="text-3xl font-extrabold text-slate-900 tracking-tight text-center">42</p>
            </div>

            <div class="bg-white rounded-2xl p-7 border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-default group">
                <div class="flex items-center justify-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white shadow-lg shadow-amber-500/25 group-hover:scale-110 transition-transform duration-300 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <p class="text-slate-500 text-[13px] font-bold uppercase tracking-wide">Sắp diễn ra hôm nay</p>
                </div>
                <p class="text-3xl font-extrabold text-slate-900 tracking-tight text-center">02</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col lg:col-span-2">
                <h3 class="text-lg font-bold text-slate-900 mb-8">Xu hướng điểm danh hàng tuần</h3>
                <div class="flex-1 flex items-end justify-around h-60 mt-4 px-2">
                    <div class="flex flex-col items-center gap-3 w-1/5">
                        <div class="w-12 sm:w-16 bg-[#2563eb] rounded-sm" style="height: 140px;"></div>
                        <span class="text-xs text-slate-500 font-medium">Th2</span>
                    </div>
                    <div class="flex flex-col items-center gap-3 w-1/5">
                        <div class="w-12 sm:w-16 bg-[#2563eb] rounded-sm" style="height: 180px;"></div>
                        <span class="text-xs text-slate-500 font-medium">Th3</span>
                    </div>
                    <div class="flex flex-col items-center gap-3 w-1/5">
                        <div class="w-12 sm:w-16 bg-[#2563eb] rounded-sm" style="height: 160px;"></div>
                        <span class="text-xs text-slate-500 font-medium">Th4</span>
                    </div>
                    <div class="flex flex-col items-center gap-3 w-1/5">
                        <div class="w-12 sm:w-16 bg-[#2563eb] rounded-sm" style="height: 190px;"></div>
                        <span class="text-xs text-slate-500 font-medium">Th5</span>
                    </div>
                    <div class="flex flex-col items-center gap-3 w-1/5">
                        <div class="w-12 sm:w-16 bg-blue-200 rounded-sm" style="height: 110px;"></div>
                        <span class="text-xs text-slate-500 font-medium">Th6</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm lg:col-span-1">
                <h3 class="text-lg font-bold text-slate-900 mb-6">Thông báo khẩn cấp</h3>
                <div class="flex flex-col gap-4">
                    <div class="bg-[#fff1f2] border border-red-100 rounded-xl p-5 flex gap-4">
                        <div class="text-red-500 flex-shrink-0 mt-0.5">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-red-600 mb-1">Cảnh báo điểm danh thấp</h4>
                            <p class="text-sm text-red-500/90 leading-relaxed">Nhóm C đã giảm xuống dưới 75% điểm danh trung bình tuần này. Cần xem xét.</p>
                        </div>
                    </div>
                    <div class="bg-[#eff6ff] border border-blue-100 rounded-xl p-5 flex gap-4">
                        <div class="text-[#2563eb] flex-shrink-0 mt-0.5">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-[#2563eb] mb-1">Bảo trì hệ thống</h4>
                            <p class="text-sm text-blue-600/90 leading-relaxed">Cổng thông tin sẽ ngừng hoạt động trong 1 giờ vào lúc 2 giờ sáng Chủ Nhật để tiến hành nâng cấp định kỳ.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col mb-6">
            <div class="flex justify-between items-start p-6 border-b border-slate-100">
                <div>
                    <h3 class="text-xl font-bold text-slate-900">Phiên gần đây</h3>
                    <p class="text-sm text-slate-500 mt-1">Danh sách các phiên điểm danh gần nhất của bạn</p>
                </div>
                <div class="flex items-center gap-4">
                    <a href="#" class="text-blue-600 text-sm font-medium hover:underline">Xem tất cả</a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="border-b border-slate-100 text-[14px] font-bold text-slate-800 uppercase tracking-wider">
                            <th class="w-[35%] px-6 py-5 text-center">Mã khóa học</th>
                            <th class="w-[15%] px-6 py-5 text-center">Ngày</th>
                            <th class="w-[20%] px-6 py-5 text-center">Điểm danh</th>
                            <th class="w-[20%] px-6 py-5 text-center">Trạng thái</th>
                            <th class="w-[10%] px-6 py-5 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-[13px]">
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-blue-50/50 border border-blue-100 flex items-center justify-center text-blue-600 flex-shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 text-base">CS402: Thuật toán</p>
                                        <p class="text-xs text-slate-500 mt-0.5">L-304 · Nhóm A</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-bold text-slate-900 text-base">24/10/2024</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-3">
                                    <span class="font-extrabold text-slate-900 text-base w-9">94%</span>
                                    <div class="w-20 bg-slate-200 rounded-full h-1.5">
                                        <div class="bg-[#2563eb] h-1.5 rounded-full" style="width: 94%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-green-50 text-green-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                    Đã hoàn thành
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center">
                                    <button class="w-8 h-8 inline-flex justify-center items-center rounded-lg border border-transparent text-slate-400 hover:text-slate-700 hover:border-slate-200 hover:bg-slate-50 hover:shadow-sm transition-all focus:outline-none">
                                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-orange-50/50 border border-orange-100 flex items-center justify-center text-orange-600 flex-shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 text-base">DB101: Cơ bản SQL</p>
                                        <p class="text-xs text-slate-500 mt-0.5">L-102 · Nhóm C</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-bold text-slate-900 text-base">23/10/2024</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-3">
                                    <span class="font-extrabold text-slate-900 text-base w-9">82%</span>
                                    <div class="w-20 bg-slate-200 rounded-full h-1.5">
                                        <div class="bg-[#2563eb] h-1.5 rounded-full" style="width: 82%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-blue-50 text-blue-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    Đang diễn ra
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center">
                                    <button class="w-8 h-8 inline-flex justify-center items-center rounded-lg border border-transparent text-slate-400 hover:text-slate-700 hover:border-slate-200 hover:bg-slate-50 hover:shadow-sm transition-all focus:outline-none">
                                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-red-50/50 border border-red-100 flex items-center justify-center text-red-600 flex-shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 text-base">PY201: Python Nâng cao</p>
                                        <p class="text-xs text-slate-500 mt-0.5">L-201 · Nhóm B</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-bold text-slate-900 text-base">23/10/2024</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-3">
                                    <span class="font-extrabold text-slate-400 text-base w-9">--%</span>
                                    <div class="w-20 bg-slate-100 rounded-full h-1.5">
                                        <div class="bg-slate-200 h-1.5 rounded-full" style="width: 0%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-blue-50 text-blue-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    Đang diễn ra
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center">
                                    <button class="w-8 h-8 inline-flex justify-center items-center rounded-lg border border-transparent text-slate-400 hover:text-slate-700 hover:border-slate-200 hover:bg-slate-50 hover:shadow-sm transition-all focus:outline-none">
                                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
