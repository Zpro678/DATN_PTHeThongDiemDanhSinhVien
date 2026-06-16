<x-app-layout variant="lecturer" page-title="Quản lý điểm danh">
    <div class="max-w-[1400px] mx-auto">

        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-[28px] font-bold text-slate-900 mb-1">Quản lý điểm danh</h1>
                <p class="text-slate-500 text-sm">Quản lý phiên điểm danh cho tất cả các lớp.</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm font-semibold hover:bg-slate-50 hover:text-slate-900 flex items-center gap-2 shadow-sm transition">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Xuất dữ liệu
                </button>
                <button class="px-4 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 flex items-center gap-2 shadow-sm shadow-emerald-500/30 transition">
                    <svg class="w-4 h-4 text-white/90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Báo cáo điểm danh
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="group bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex justify-between items-start transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:border-blue-200 cursor-pointer">
                <div>
                    <p class="text-slate-500 text-xs font-bold mb-3 uppercase tracking-wider transition-colors group-hover:text-blue-600">Tổng số lớp</p>
                    <p class="text-4xl font-bold text-slate-900">12</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center text-slate-500 transition-all duration-300 group-hover:scale-110 group-hover:bg-blue-50 group-hover:text-blue-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6.878V6a2.25 2.25 0 012.25-2.25h7.5A2.25 2.25 0 0118 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 004.5 9v.878m13.5-3A2.25 2.25 0 0119.5 9v.878m0 0a2.246 2.246 0 00-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0121 12v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6c0-.98.626-1.813 1.5-2.122" />
                    </svg>
                </div>
            </div>

            <div class="group bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex justify-between items-start transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:border-blue-200 cursor-pointer">
                <div>
                    <p class="text-slate-500 text-xs font-bold mb-3 uppercase tracking-wider transition-colors group-hover:text-blue-600">Phiên đang diễn ra</p>
                    <p class="text-4xl font-bold text-slate-900">01</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center text-slate-500 transition-all duration-300 group-hover:scale-110 group-hover:bg-blue-50 group-hover:text-blue-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9.75L16.5 12l-2.25 2.25m-4.5 0L7.5 12l2.25-2.25M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" />
                    </svg>
                </div>
            </div>

            <div class="group bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex justify-between items-start transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:border-blue-200 cursor-pointer">
                <div>
                    <p class="text-slate-500 text-xs font-bold mb-3 uppercase tracking-wider transition-colors group-hover:text-blue-600">Điểm danh hôm nay</p>
                    <div class="flex items-end gap-3 mb-1">
                        <p class="text-4xl font-bold text-slate-900">92%</p>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center text-slate-500 transition-all duration-300 group-hover:scale-110 group-hover:bg-blue-50 group-hover:text-blue-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z" />
                    </svg>
                </div>
            </div>

            <div class="group bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex justify-between items-start transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:border-blue-200 cursor-pointer">
                <div>
                    <p class="text-slate-500 text-xs font-bold mb-3 uppercase tracking-wider transition-colors group-hover:text-blue-600">SV đã điểm danh</p>
                    <p class="text-4xl font-bold text-slate-900">348</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center text-slate-500 transition-all duration-300 group-hover:scale-110 group-hover:bg-blue-50 group-hover:text-blue-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="mb-8">
            <h2 class="text-lg font-bold text-slate-900 mb-5">Chọn phương thức điểm danh</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <button class="group relative flex flex-col items-center text-center p-8 bg-amber-50/40 border-2 border-amber-100/60 rounded-2xl shadow-sm hover:bg-amber-50 hover:border-amber-300 hover:shadow-md transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-amber-500/20 overflow-hidden">
                    
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-amber-200/20 rounded-full blur-2xl group-hover:bg-amber-300/30 transition-all duration-500"></div>

                    <div class="w-16 h-16 mb-5 rounded-2xl bg-amber-100/80 border border-amber-200 flex items-center justify-center text-amber-500 group-hover:scale-110 group-hover:text-amber-600 transition-transform duration-300 shadow-sm relative z-10">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9zm3.75 11.625a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>

                    <span class="text-lg font-bold text-amber-800 uppercase tracking-wide mb-2 relative z-10">Điểm danh thủ công</span>
                    <p class="text-sm text-amber-700/80 leading-relaxed max-w-xs relative z-10">Giảng viên tự gọi tên và đánh dấu trạng thái vắng/có mặt cho từng sinh viên trong lớp.</p>
                </button>

                <button class="group relative flex flex-col items-center text-center p-8 bg-blue-50/40 border-2 border-blue-200/80 rounded-2xl shadow-sm hover:bg-blue-50 hover:border-blue-400 hover:shadow-md transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-blue-500/20 overflow-hidden">
                    
                    <span class="absolute top-4 right-4 bg-blue-100 text-blue-700 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow-sm z-20">
                        Khuyên dùng
                    </span>

                    <div class="absolute -top-10 -left-10 w-32 h-32 bg-blue-200/20 rounded-full blur-2xl group-hover:bg-blue-300/30 transition-all duration-500"></div>

                    <div class="w-16 h-16 mb-5 rounded-2xl bg-blue-100/80 border border-blue-200 flex items-center justify-center text-blue-600 group-hover:scale-110 group-hover:text-blue-700 transition-transform duration-300 shadow-sm relative z-10">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 15.75A2.25 2.25 0 0017.25 18 2.25 2.25 0 0019.5 15.75 2.25 2.25 0 0017.25 13.5 2.25 2.25 0 0015 15.75z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 21v-3" />
                        </svg>
                    </div>

                    <span class="text-lg font-bold text-blue-800 uppercase tracking-wide mb-2 relative z-10">Link / QR + GPS</span>
                    <p class="text-sm text-blue-700/80 leading-relaxed max-w-xs relative z-10">Sinh viên tự quét mã QR hoặc truy cập Link để xác thực điểm danh qua vị trí GPS.</p>
                </button>

            </div>
        </div>
        
    </div>
</x-app-layout>