<x-app-layout page-title="Bảng Phân Tích">
    <div class="min-h-screen bg-[#f8fafc] py-8 font-sans">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-[28px] font-bold text-slate-900 mb-1">Biểu đồ báo cáo</h1>
                    <p class="text-slate-500 text-sm">Phân tích chi tiết về điểm danh và hiệu suất của sinh viên.</p>
                </div>
                <div class="flex items-center">
                    <livewire:date-range-picker />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                {{-- Card 1 --}}
                <div class="bg-white rounded-2xl p-7 border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group flex justify-between items-center">
                    <div class="flex-1 min-w-0 mr-4">
                        <p class="text-blue-600 text-sm font-bold uppercase tracking-wider mb-2 truncate">Tỷ Lệ Có Mặt TB</p>
                        <p class="text-4xl font-extrabold text-slate-900 tracking-tight truncate pb-1">94.2%</p>
                    </div>
                    <div class="w-[68px] h-[68px] rounded-2xl bg-[#2563eb] shadow-lg shadow-blue-500/40 flex items-center justify-center text-white shrink-0 group-hover:scale-105 transition-transform duration-300">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                        </svg>
                    </div>
                </div>

                {{-- Card 2 --}}
                <div class="bg-white rounded-2xl p-7 border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group flex justify-between items-center">
                    <div class="flex-1 min-w-0 mr-4">
                        <p class="text-red-500 text-sm font-bold uppercase tracking-wider mb-2 truncate">SV Có Nguy Cơ</p>
                        <p class="text-4xl font-extrabold text-slate-900 tracking-tight truncate pb-1">142</p>
                    </div>
                    <div class="w-[68px] h-[68px] rounded-2xl bg-red-500 shadow-lg shadow-red-500/40 flex items-center justify-center text-white shrink-0 group-hover:scale-105 transition-transform duration-300">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>

                {{-- Card 3 --}}
                <div class="bg-white rounded-2xl p-7 border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group flex justify-between items-center">
                    <div class="flex-1 min-w-0 mr-4">
                        <p class="text-orange-500 text-sm font-bold uppercase tracking-wider mb-2 truncate">Lớp Tiến Bộ Nhất</p>
                        <p class="text-3xl font-extrabold text-slate-900 tracking-tight truncate pb-1">Vật Lý 101</p>
                    </div>
                    <div class="w-[68px] h-[68px] rounded-2xl bg-orange-500 shadow-lg shadow-orange-500/40 flex items-center justify-center text-white shrink-0 group-hover:scale-105 transition-transform duration-300">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

                <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm lg:col-span-2 flex flex-col h-full hover:shadow-md transition-shadow duration-300">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
                        <h2 class="text-[20px] font-bold text-slate-900">Xu Hướng Điểm Danh</h2>
                        <div class="flex items-center gap-6 text-[13px] font-bold bg-slate-50 px-4 py-2 rounded-xl border border-slate-100">
                            <div class="flex items-center gap-2 text-blue-700">
                                <div class="w-3 h-3 rounded-full bg-[#2563eb] shadow-sm"></div> Có Mặt
                            </div>
                            <div class="flex items-center gap-2 text-red-600">
                                <div class="w-3 h-3 rounded-full bg-[#ef4444] shadow-sm"></div> Vắng Mặt
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 relative w-full min-h-[260px] group">
                        <svg class="w-full h-full absolute inset-0" viewBox="0 0 1000 200" preserveAspectRatio="none">
                            <path d="M0,80 C150,50 300,100 500,60 C700,20 850,70 1000,50 L1000,200 L0,200 Z" fill="#eff6ff" opacity="0.8"></path>
                            <path d="M0,80 C150,50 300,100 500,60 C700,20 850,70 1000,50" fill="none" stroke="#2563eb" stroke-width="4" stroke-linecap="round"></path>
                            <path d="M0,170 C150,180 300,195 500,175 C700,150 850,190 1000,180" fill="none" stroke="#ef4444" stroke-width="3" stroke-linecap="round" stroke-dasharray="8 4"></path>
                        </svg>

                        <div class="absolute bottom-[-16px] left-0 right-0 flex justify-between text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <span></span>
                            <span>T3</span>
                            <span>T4</span>
                            <span>T5</span>
                            <span>T6</span>
                            <span>T7</span>
                            <span>CN</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm flex flex-col h-full hover:shadow-md transition-shadow duration-300">
                    <h2 class="text-[20px] font-bold text-slate-900 mb-8">Xếp Hạng Điểm Danh</h2>

                    <div class="flex-1 flex flex-col justify-between gap-6">
                        <div>
                            <p class="text-[11px] font-bold text-blue-600 uppercase tracking-wider mb-5 border-b border-blue-50 pb-2.5 flex items-center gap-2">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Top 2 (Chuỗi Hoàn Hảo)
                            </p>
                            <div class="flex flex-col gap-5">
                                <div class="flex items-center justify-between group">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 text-xs font-bold flex items-center justify-center group-hover:bg-blue-100 transition-colors">AM</div>
                                        <span class="text-sm font-bold text-slate-800">Alice Miller</span>
                                    </div>
                                    <span class="text-sm font-bold text-green-600 bg-green-50 px-2 py-1 rounded-lg">100%</span>
                                </div>
                                <div class="flex items-center justify-between group">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 text-xs font-bold flex items-center justify-center group-hover:bg-blue-100 transition-colors">BW</div>
                                        <span class="text-sm font-bold text-slate-800">Ben White</span>
                                    </div>
                                    <span class="text-sm font-bold text-green-600 bg-green-50 px-2 py-1 rounded-lg">100%</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <p class="text-[11px] font-bold text-red-600 uppercase tracking-wider mb-5 border-b border-red-50 pb-2.5 flex items-center gap-2 mt-4">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                Bottom 2 (Có Nguy Cơ)
                            </p>
                            <div class="flex flex-col gap-5">
                                <div class="flex items-center justify-between group">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 text-xs font-bold flex items-center justify-center group-hover:bg-red-100 transition-colors">CK</div>
                                        <span class="text-sm font-bold text-slate-800">Chris Kim</span>
                                    </div>
                                    <span class="text-sm font-bold text-red-600 bg-red-50 px-2 py-1 rounded-lg">64%</span>
                                </div>
                                <div class="flex items-center justify-between group">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 text-xs font-bold flex items-center justify-center group-hover:bg-red-100 transition-colors">DR</div>
                                        <span class="text-sm font-bold text-slate-800">David Ross</span>
                                    </div>
                                    <span class="text-sm font-bold text-red-600 bg-red-50 px-2 py-1 rounded-lg">62%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col mb-8 hover:shadow-md transition-shadow duration-300">
                <div class="p-6 sm:px-8 sm:py-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-slate-50/50">
                    <div>
                        <h2 class="text-[20px] font-bold text-slate-900">Ma Trận So Sánh Các Khoa</h2>
                        <p class="text-sm text-slate-500 mt-1">Đánh giá hiệu suất chéo giữa các khoa ban</p>
                    </div>
                    <button class="px-4 py-2 bg-white border border-slate-200 text-slate-800 rounded-lg text-sm font-bold hover:bg-slate-50 flex items-center gap-2 shadow-sm transition">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Xuất file CSV
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="bg-white text-[15px] font-bold text-slate-800 uppercase tracking-wider border-b border-slate-200">
                                <th class="px-8 py-5 text-center">Khoa</th>
                                <th class="px-8 py-5 text-center">Tỷ lệ Có Mặt TB</th>
                                <th class="px-8 py-5 text-center">Tỷ lệ Giữ Chân</th>
                                <th class="px-8 py-5 text-center">Điểm Rủi Ro</th>
                                <th class="px-8 py-5 w-48 text-center">Hiệu Suất</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <tr class="border-b border-slate-100 hover:bg-slate-50/80 transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 group-hover:scale-105 transition-transform">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 text-base">Khoa học Máy tính</p>
                                            <p class="text-xs text-slate-500 mt-0.5">42 Khóa học</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5 font-extrabold text-slate-900 text-base">96.8%</td>
                                <td class="px-8 py-5 text-slate-600 font-bold">98.2%</td>
                                <td class="px-8 py-5">
                                    <div class="inline-flex items-center gap-2 text-green-700 font-bold bg-green-50 px-2.5 py-1 rounded-lg border border-green-100 text-xs">
                                        <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div> Thấp
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden border border-slate-200/50">
                                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-full rounded-full" style="width: 90%"></div>
                                    </div>
                                </td>
                            </tr>

                            <tr class="border-b border-slate-100 hover:bg-slate-50/80 transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center text-orange-600 group-hover:scale-105 transition-transform">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25V13.5zm0 2.25h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25V18zm2.498-6.75h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007V13.5zm0 2.25h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007V18zm2.504-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V13.5zm0 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V18zM4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 text-base">Toán học</p>
                                            <p class="text-xs text-slate-500 mt-0.5">28 Khóa học</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5 font-extrabold text-slate-900 text-base">91.4%</td>
                                <td class="px-8 py-5 text-slate-600 font-bold">94.5%</td>
                                <td class="px-8 py-5">
                                    <div class="inline-flex items-center gap-2 text-orange-700 font-bold bg-orange-50 px-2.5 py-1 rounded-lg border border-orange-100 text-xs">
                                        <div class="w-1.5 h-1.5 rounded-full bg-orange-500"></div> TB
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden border border-slate-200/50">
                                        <div class="bg-gradient-to-r from-blue-400 to-blue-500 h-full rounded-full" style="width: 70%"></div>
                                    </div>
                                </td>
                            </tr>

                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center text-red-500 group-hover:scale-105 transition-transform">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 text-base">Vật lý</p>
                                            <p class="text-xs text-slate-500 mt-0.5">15 Khóa học</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5 font-extrabold text-slate-900 text-base">88.2%</td>
                                <td class="px-8 py-5 text-slate-600 font-bold">90.1%</td>
                                <td class="px-8 py-5">
                                    <div class="inline-flex items-center gap-2 text-red-700 font-bold bg-red-50 px-2.5 py-1 rounded-lg border border-red-100 text-xs">
                                        <div class="w-1.5 h-1.5 rounded-full bg-red-500"></div> Cao
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden border border-slate-200/50">
                                        <div class="bg-gradient-to-r from-red-400 to-red-500 h-full rounded-full" style="width: 50%"></div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>