<x-app-layout variant="lecturer" page-title="Quản lý điểm danh">
    <div class="max-w-[1400px] mx-auto">

        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-[28px] font-bold text-slate-900 mb-1">Quản lý điểm danh</h1>
                <p class="text-slate-500 text-sm">Quản lý phiên điểm danh cho tất cả các lớp.</p>
            </div>
            <div class="flex items-center gap-5">
                <button class="relative px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-semibold hover:bg-slate-50 hover:text-slate-900 hover:border-slate-300 hover:shadow-md flex items-center gap-2.5 shadow-sm transition-all duration-200 overflow-visible">
                    <span class="absolute -top-2.5 -right-2.5 inline-flex items-center gap-0.5 bg-gradient-to-r from-amber-400 to-yellow-500 text-white text-[8px] font-bold px-1.5 py-0.5 rounded-full uppercase tracking-wider shadow-md z-10 animate-pulse">
                        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1l2.928 6.856L20 8.59l-5.072 4.574L16.18 20 10 16.146 3.82 20l1.252-6.836L0 8.59l7.072-.734L10 1z" clip-rule="evenodd" /></svg>
                        PRO
                    </span>
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <div class="group bg-white rounded-2xl p-7 border border-slate-200 shadow-sm flex justify-between items-start gap-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg cursor-pointer">
                <div class="min-w-0">
                    <p class="text-slate-500 text-xs font-semibold mb-4 uppercase tracking-wider whitespace-nowrap transition-colors group-hover:text-blue-600">Tổng số lớp</p>
                    <p class="text-4xl font-extrabold text-slate-900 tracking-tight">12</p>
                </div>
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white shadow-lg shadow-blue-500/25 flex-shrink-0 transition-all duration-300 group-hover:scale-110">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6.878V6a2.25 2.25 0 012.25-2.25h7.5A2.25 2.25 0 0118 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 004.5 9v.878m13.5-3A2.25 2.25 0 0119.5 9v.878m0 0a2.246 2.246 0 00-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0121 12v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6c0-.98.626-1.813 1.5-2.122" />
                    </svg>
                </div>
            </div>

            <div class="group bg-white rounded-2xl p-7 border border-slate-200 shadow-sm flex justify-between items-start gap-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg cursor-pointer">
                <div class="min-w-0">
                    <p class="text-slate-500 text-xs font-semibold mb-4 uppercase tracking-wider whitespace-nowrap transition-colors group-hover:text-indigo-600">Phiên đang diễn ra</p>
                    <p class="text-4xl font-extrabold text-slate-900 tracking-tight">01</p>
                </div>
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-white shadow-lg shadow-indigo-500/25 flex-shrink-0 transition-all duration-300 group-hover:scale-110">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9.75L16.5 12l-2.25 2.25m-4.5 0L7.5 12l2.25-2.25M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" />
                    </svg>
                </div>
            </div>

            <div class="group bg-white rounded-2xl p-7 border border-slate-200 shadow-sm flex justify-between items-start gap-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg cursor-pointer">
                <div class="min-w-0">
                    <p class="text-slate-500 text-xs font-semibold mb-4 uppercase tracking-wider whitespace-nowrap transition-colors group-hover:text-emerald-600">Điểm danh hôm nay</p>
                    <p class="text-4xl font-extrabold text-slate-900 tracking-tight">92%</p>
                </div>
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-white shadow-lg shadow-emerald-500/25 flex-shrink-0 transition-all duration-300 group-hover:scale-110">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z" />
                    </svg>
                </div>
            </div>

            <div class="group bg-white rounded-2xl p-7 border border-slate-200 shadow-sm flex justify-between items-start gap-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg cursor-pointer">
                <div class="min-w-0">
                    <p class="text-slate-500 text-xs font-semibold mb-4 uppercase tracking-wider whitespace-nowrap transition-colors group-hover:text-violet-600">SV đã điểm danh</p>
                    <p class="text-4xl font-extrabold text-slate-900 tracking-tight">348</p>
                </div>
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-violet-500 to-violet-700 flex items-center justify-center text-white shadow-lg shadow-violet-500/25 flex-shrink-0 transition-all duration-300 group-hover:scale-110">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Chọn phương thức điểm danh --}}
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-1.5 h-7 bg-gradient-to-b from-blue-500 to-blue-700 rounded-full"></div>
                <h2 class="text-xl font-bold text-slate-900">Chọn phương thức điểm danh</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Điểm danh thủ công --}}
                <button class="group relative flex items-start gap-6 text-left p-7 bg-white border-2 border-slate-200 rounded-2xl shadow-sm hover:border-amber-400 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-amber-500/20 overflow-hidden">

                    <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-bl from-amber-100/40 to-transparent rounded-bl-full opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white shadow-lg shadow-amber-400/30 flex-shrink-0 group-hover:scale-110 transition-transform duration-300 relative z-10">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                        </svg>
                    </div>

                   <a href="{{ route('lecturer.attendance.manual') }}" class ="block">
                     <div class="flex-1 relative z-10" >
                        <h3 class="text-lg font-bold text-slate-900 mb-1.5 group-hover:text-amber-700 transition-colors">Điểm danh thủ công</h3>
                        <p class="text-sm text-slate-500 leading-relaxed mb-4">Giảng viên gọi tên và đánh dấu trạng thái có mặt, vắng, muộn cho từng sinh viên.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-700 text-xs font-medium rounded-full border border-amber-200/60">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                                Gọi tên trực tiếp
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-700 text-xs font-medium rounded-full border border-amber-200/60">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                                Kiểm soát hoàn toàn
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-700 text-xs font-medium rounded-full border border-amber-200/60">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                                Không cần thiết bị
                            </span>
                        </div>
                    </div>
                   </a>

                    <svg class="w-5 h-5 text-slate-300 flex-shrink-0 mt-1 group-hover:text-amber-500 group-hover:translate-x-1 transition-all duration-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>

                {{-- Link / QR + GPS --}}
                <a href="{{ route('attendance.qr.setup') }}" class="group relative flex items-start gap-6 text-left p-7 bg-white border-2 border-blue-200 rounded-2xl shadow-sm hover:border-blue-400 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-blue-500/20 overflow-hidden">

                    <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-bl from-blue-100/40 to-transparent rounded-bl-full opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-blue-500/30 flex-shrink-0 group-hover:scale-110 transition-transform duration-300 relative z-10">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5z" />
                        </svg>
                    </div>

                    <div class="flex-1 relative z-10">
                        <div class="flex items-center gap-2.5 mb-1.5">
                            <h3 class="text-lg font-bold text-slate-900 group-hover:text-blue-700 transition-colors">Link / QR + GPS</h3>
                            <span class="inline-flex items-center gap-1 bg-gradient-to-r from-blue-500 to-blue-600 text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider shadow-sm">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" /></svg>
                                Khuyên dùng
                            </span>
                        </div>
                        <p class="text-sm text-slate-500 leading-relaxed mb-4">Sinh viên quét mã QR hoặc truy cập link để điểm danh, kết hợp xác thực vị trí GPS.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-blue-700 text-xs font-medium rounded-full border border-blue-200/60">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                                Quét QR nhanh
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-blue-700 text-xs font-medium rounded-full border border-blue-200/60">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                                Xác thực GPS
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-blue-700 text-xs font-medium rounded-full border border-blue-200/60">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                                Chống gian lận
                            </span>
                        </div>
                    </div>

                    <svg class="w-5 h-5 text-slate-300 flex-shrink-0 mt-1 group-hover:text-blue-500 group-hover:translate-x-1 transition-all duration-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </a>

            </div>
        </div>

    </div>
</x-app-layout>
