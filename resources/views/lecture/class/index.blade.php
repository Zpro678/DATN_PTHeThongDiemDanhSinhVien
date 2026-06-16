<x-app-layout variant="lecturer" page-title="Lớp học">
    <div class="min-h-screen bg-[#f8fafc] font-sans">

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 mb-1">Lớp học của tôi</h1>
                <p class="text-slate-500 text-sm">Quản lý lịch học và điểm danh sinh viên của tất cả các khoa.</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm font-semibold hover:bg-slate-50 hover:text-slate-900 flex items-center gap-2 shadow-sm transition">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Xuất báo cáo
                </button>
                <button onclick="document.getElementById('filterModalClass').classList.remove('hidden')" class="px-4 py-2.5 bg-[#2563eb] text-white rounded-lg text-sm font-medium hover:bg-blue-700 flex items-center gap-2 shadow-sm shadow-blue-500/30 transition">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Bộ lọc
                </button>
            </div>
        </div>
        @include('lecture.popup.filterClassPopup')


        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">

            <button class="bg-slate-50/50 rounded-2xl border-2 border-dashed border-slate-300 shadow-sm flex flex-col justify-center items-center h-full min-h-[320px] hover:bg-slate-50 hover:border-blue-400 hover:text-blue-600 transition group cursor-pointer overflow-hidden focus:outline-none focus:ring-4 focus:ring-blue-100">
                <div class="w-14 h-14 bg-white rounded-full shadow-sm border border-slate-100 flex justify-center items-center mb-4 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-slate-400 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-600 group-hover:text-blue-600 transition-colors">Thêm lớp học mới</h3>
                <p class="text-sm text-slate-400 mt-1">Tạo một lớp học hoặc import</p>
            </button>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col h-full overflow-hidden">
                <div class="px-6 pt-6 pb-4">
                    <div class="flex justify-between items-start mb-2">
                        <h2 class="text-xl font-bold text-slate-900 leading-tight pr-2 truncate">Cấu trúc dữ liệu và giải thuật toán cao cấp</h2>
                        <button class="text-slate-400 hover:text-slate-600 mt-1 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="mb-3">
                        <span class="inline-block bg-blue-50 text-[#2563eb] text-[11px] font-bold px-2 py-0.5 rounded uppercase tracking-wide">CS402</span>
                    </div>
                    <p class="text-slate-500 text-sm font-medium">Công Nghệ Thông Tin - HK1 - 2024</p>
                </div>

                <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 mx-6 mb-2 mt-auto min-h-[124px] flex flex-col justify-between">
                    <div class="flex justify-between items-center">
                        <div class="flex-1 text-center">
                            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1.5">Sinh viên</p>
                            <p class="text-2xl font-bold text-slate-900 leading-none">45</p>
                        </div>

                        <div class="w-px h-10 bg-slate-200"></div>

                        <div class="flex-1 text-center">
                            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1.5">Đã điểm danh</p>
                            <p class="text-2xl font-bold text-emerald-600 leading-none">37</p>
                        </div>

                        <div class="w-px h-10 bg-slate-200"></div>

                        <div class="flex-1 text-center">
                            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1.5">Chưa Đ.Danh</p>
                            <p class="text-2xl font-bold text-red-500 leading-none">8</p>
                        </div>
                    </div>

                    <div class="text-center mt-3 pt-3 border-t border-slate-200/60">
                        <p class="text-slate-500 text-xs font-medium">Phiên gần nhất: 21/10/2023</p>
                    </div>
                </div>

                <div class="px-6 pb-6 pt-3 flex gap-3">
                    <button class="w-[45%] py-2.5 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm font-semibold hover:bg-slate-50 transition">
                        Xem chi tiết
                    </button>
                    <button class="w-[55%] py-2.5 bg-[#2563eb] text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition flex justify-center items-center gap-2 shadow-sm shadow-blue-500/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Bắt đầu điểm danh
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col h-full overflow-hidden">
                <div class="px-6 pt-6 pb-4">
                    <div class="flex justify-between items-start mb-2">
                        <h2 class="text-xl font-bold text-slate-900 leading-tight pr-2 truncate">Cấu trúc dữ liệu và giải thuật toán cao cấp</h2>
                        <button class="text-slate-400 hover:text-slate-600 mt-1 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="mb-3">
                        <span class="inline-block bg-blue-50 text-[#2563eb] text-[11px] font-bold px-2 py-0.5 rounded uppercase tracking-wide">CS402</span>
                    </div>
                    <p class="text-slate-500 text-sm font-medium">Công Nghệ Thông Tin - HK1 - 2024</p>
                </div>

                <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 mx-6 mb-2 mt-auto min-h-[124px] flex flex-col justify-between">
                    <div class="flex justify-between items-center">
                        <div class="flex-1 text-center">
                            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1.5">Sinh viên</p>
                            <p class="text-2xl font-bold text-slate-900 leading-none">45</p>
                        </div>

                        <div class="w-px h-10 bg-slate-200"></div>

                        <div class="flex-1 text-center">
                            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1.5">Đã điểm danh</p>
                            <p class="text-2xl font-bold text-emerald-600 leading-none">37</p>
                        </div>

                        <div class="w-px h-10 bg-slate-200"></div>

                        <div class="flex-1 text-center">
                            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1.5">Chưa Đ.Danh</p>
                            <p class="text-2xl font-bold text-red-500 leading-none">8</p>
                        </div>
                    </div>

                    <div class="text-center mt-3 pt-3 border-t border-slate-200/60">
                        <p class="text-slate-500 text-xs font-medium">Phiên gần nhất: 21/10/2023</p>
                    </div>
                </div>

                <div class="px-6 pb-6 pt-3 flex gap-3">
                    <button class="w-[45%] py-2.5 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm font-semibold hover:bg-slate-50 transition">
                        Xem chi tiết
                    </button>
                    <button class="w-[55%] py-2.5 bg-[#2563eb] text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition flex justify-center items-center gap-2 shadow-sm shadow-blue-500/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Bắt đầu điểm danh
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>