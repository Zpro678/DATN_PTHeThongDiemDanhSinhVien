<x-app-layout page-title="Bảng Phân Tích">
    <div class="min-h-screen bg-[#f8fafc] py-8 font-sans">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-end mb-6">
                <livewire:date-range-picker />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col justify-between hover:-translate-y-1 transition-transform">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                            </svg>
                        </div>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                            +2.4%
                        </span>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-wide mb-1">Tỷ Lệ Có Mặt TB</p>
                        <p class="text-3xl font-bold text-slate-900">94.2%</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col justify-between hover:-translate-y-1 transition-transform">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center text-red-500">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold bg-red-50 text-red-600 uppercase tracking-wider">
                            Báo Động Đỏ
                        </span>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-wide mb-1">SV Có Nguy Cơ</p>
                        <p class="text-3xl font-bold text-slate-900">142</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col justify-between hover:-translate-y-1 transition-transform">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center text-orange-500">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                            </svg>
                        </div>
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold bg-orange-50 text-orange-700 uppercase tracking-wider">
                            Khoa học
                        </span>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-wide mb-1">Lớp Tiến Bộ Nhất</p>
                        <p class="text-3xl font-bold text-slate-900">Vật Lý 101</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm lg:col-span-2 flex flex-col h-full">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-slate-900">Xu Hướng Điểm Danh</h2>
                        <div class="flex items-center gap-5 text-sm font-bold">
                            <div class="flex items-center gap-2 text-blue-700">
                                <div class="w-3 h-3 rounded-full bg-[#2563eb] shadow-sm"></div> Có Mặt
                            </div>
                            <div class="flex items-center gap-2 text-red-600">
                                <div class="w-3 h-3 rounded-full bg-[#ef4444] shadow-sm"></div> Vắng Mặt
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 relative w-full min-h-[220px]">
                        <svg class="w-full h-full absolute inset-0" viewBox="0 0 1000 200" preserveAspectRatio="none">
                            <path d="M0,80 C150,50 300,100 500,60 C700,20 850,70 1000,50 L1000,200 L0,200 Z" fill="#eff6ff" opacity="0.8"></path>
                            <path d="M0,80 C150,50 300,100 500,60 C700,20 850,70 1000,50" fill="none" stroke="#2563eb" stroke-width="4" stroke-linecap="round"></path>
                            <path d="M0,170 C150,180 300,195 500,175 C700,150 850,190 1000,180" fill="none" stroke="#ef4444" stroke-width="3" stroke-linecap="round"></path>
                        </svg>

                        <div class="absolute bottom-[-10px] left-0 right-0 flex justify-between text-[11px] font-bold text-slate-400 uppercase">
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

                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col h-full">
                    <h2 class="text-xl font-bold text-slate-900 mb-6">Xếp Hạng Điểm Danh</h2>

                    <div class="flex-1 flex flex-col justify-between">
                        <div class="mb-6">
                            <p class="text-[11px] font-bold text-blue-600 uppercase tracking-wider mb-4 border-b border-blue-50 pb-2">Top 2 (Chuỗi Hoàn Hảo)</p>
                            <div class="flex flex-col gap-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 text-[10px] font-bold flex items-center justify-center">AM</div>
                                        <span class="text-sm font-bold text-slate-800">Alice Miller</span>
                                    </div>
                                    <span class="text-sm font-bold text-green-600">100%</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 text-[10px] font-bold flex items-center justify-center">BW</div>
                                        <span class="text-sm font-bold text-slate-800">Ben White</span>
                                    </div>
                                    <span class="text-sm font-bold text-green-600">100%</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <p class="text-[11px] font-bold text-red-600 uppercase tracking-wider mb-4 border-b border-red-50 pb-2">Bottom 2 (Có Nguy Cơ)</p>
                            <div class="flex flex-col gap-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-red-50 text-red-600 text-[10px] font-bold flex items-center justify-center">CK</div>
                                        <span class="text-sm font-bold text-slate-800">Chris Kim</span>
                                    </div>
                                    <span class="text-sm font-bold text-red-600">64%</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-red-50 text-red-600 text-[10px] font-bold flex items-center justify-center">DR</div>
                                        <span class="text-sm font-bold text-slate-800">David Ross</span>
                                    </div>
                                    <span class="text-sm font-bold text-red-600">62%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col mb-8">
                <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">Ma Trận So Sánh Các Lớp</h2>
                        <p class="text-sm text-slate-500 font-medium mt-1">Đánh giá hiệu suất chéo giữa các khoa</p>
                    </div>
                    <button class="px-5 py-2.5 bg-[#2563eb] hover:bg-blue-700 text-white text-sm font-bold rounded-lg shadow-sm transition">
                        Xuất file CSV
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="bg-[#f8fafc] text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="px-6 py-4 border-b border-slate-100">Khoa</th>
                                <th class="px-6 py-4 border-b border-slate-100">Tỷ lệ Có Mặt TB</th>
                                <th class="px-6 py-4 border-b border-slate-100">Tỷ lệ Giữ Chân</th>
                                <th class="px-6 py-4 border-b border-slate-100">Điểm Rủi Ro</th>
                                <th class="px-6 py-4 border-b border-slate-100">Hiệu Suất</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 text-[15px]">Khoa học Máy tính</p>
                                            <p class="text-[13px] text-slate-500 mt-0.5">42 Khóa học</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 font-bold text-slate-900">96.8%</td>
                                <td class="px-6 py-5 text-slate-600 font-medium">98.2%</td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-2 text-green-600 font-bold">
                                        <div class="w-2 h-2 rounded-full bg-green-500"></div> Thấp
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="w-32 bg-slate-100 h-1.5 rounded-full">
                                        <div class="bg-[#2563eb] h-1.5 rounded-full" style="width: 90%"></div>
                                    </div>
                                </td>
                            </tr>

                            <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center text-orange-500">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25V13.5zm0 2.25h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25V18zm2.498-6.75h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007V13.5zm0 2.25h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007V18zm2.504-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V13.5zm0 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V18zM4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 text-[15px]">Toán học</p>
                                            <p class="text-[13px] text-slate-500 mt-0.5">28 Khóa học</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 font-bold text-slate-900">91.4%</td>
                                <td class="px-6 py-5 text-slate-600 font-medium">94.5%</td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-2 text-orange-500 font-bold">
                                        <div class="w-2 h-2 rounded-full bg-orange-500"></div> TB
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="w-32 bg-slate-100 h-1.5 rounded-full">
                                        <div class="bg-[#2563eb] h-1.5 rounded-full" style="width: 70%"></div>
                                    </div>
                                </td>
                            </tr>

                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center text-red-500">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 text-[15px]">Vật lý</p>
                                            <p class="text-[13px] text-slate-500 mt-0.5">15 Khóa học</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 font-bold text-slate-900">88.2%</td>
                                <td class="px-6 py-5 text-slate-600 font-medium">90.1%</td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-2 text-red-600 font-bold">
                                        <div class="w-2 h-2 rounded-full bg-red-600"></div> Cao
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="w-32 bg-slate-100 h-1.5 rounded-full">
                                        <div class="bg-[#2563eb] h-1.5 rounded-full" style="width: 50%"></div>
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

