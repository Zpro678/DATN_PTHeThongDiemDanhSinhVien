<x-app-layout variant="lecturer" page-title="Lớp học">
    <div class="max-w-[1400px] mx-auto">

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <div class="mb-6 flex justify-between items-center">
                    <h1 class="text-[24px] font-extrabold text-slate-900 leading-none uppercase tracking-tight flex items-center gap-3">
                        <span class="p-2 bg-blue-100 text-blue-600 rounded-xl shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </span>
                        Lớp học của tôi
                    </h1>
                </div>
                <p class="text-slate-500 text-sm">Quản lý lịch học và điểm danh sinh viên của tất cả các khoa.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto mt-4 md:mt-0">
                <button class="flex-1 md:flex-none relative px-5 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-semibold hover:bg-slate-50 hover:text-slate-900 hover:border-slate-300 hover:shadow-md flex justify-center items-center gap-2.5 shadow-sm transition-all duration-200 overflow-visible">
                    <span class="absolute -top-2.5 -right-2.5 inline-flex items-center gap-0.5 bg-gradient-to-r from-amber-400 to-yellow-500 text-white text-[8px] font-bold px-1.5 py-0.5 rounded-full uppercase tracking-wider shadow-md z-10 animate-pulse">
                        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1l2.928 6.856L20 8.59l-5.072 4.574L16.18 20 10 16.146 3.82 20l1.252-6.836L0 8.59l7.072-.734L10 1z" clip-rule="evenodd" /></svg>
                        PRO
                    </span>
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Xuất báo cáo
                </button>
                <button onclick="document.getElementById('filterModalClass').classList.remove('hidden')" class="flex-1 md:flex-none px-5 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl text-sm font-semibold hover:from-blue-700 hover:to-blue-800 flex justify-center items-center gap-2.5 shadow-sm shadow-blue-500/30 transition-all duration-200">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Bộ lọc
                </button>
            </div>
        </div>
        @include('lecture.popup.filterClassPopup')


        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">

            {{-- Nút thêm lớp học mới --}}
            <a href="/lecturer/class/create" class="group bg-white/60 rounded-2xl border-2 border-dashed border-slate-300 shadow-sm flex flex-col justify-center items-center h-full min-h-[320px] hover:bg-blue-50/40 hover:border-blue-300 hover:shadow-md transition-all duration-500 ease-out cursor-pointer overflow-hidden focus:outline-none focus:ring-4 focus:ring-blue-100">
                <div class="w-16 h-16 bg-blue-600 rounded-2xl shadow-md shadow-blue-500/25 border border-blue-600 flex justify-center items-center mb-5 group-hover:scale-105 group-hover:shadow-blue-500/40 transition-all duration-500 ease-out">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-600 group-hover:text-blue-600 transition-colors duration-500 ease-out">Thêm lớp học mới</h3>
                <p class="text-sm text-slate-400 mt-1.5 group-hover:text-slate-500 transition-colors duration-500 ease-out">Tạo một lớp học hoặc import</p>
            </a>

            {{-- Card lớp học 1 --}}
            <div class="group bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col h-full overflow-hidden hover:shadow-lg transition-all duration-300">
                {{-- Gradient accent bar --}}
                <div class="h-1 bg-gradient-to-r from-blue-500 via-indigo-500 to-violet-500"></div>

                <div class="px-6 pt-6 pb-4">
                    <div class="flex justify-between items-start mb-3">
                        <h2 class="text-lg font-bold text-slate-900 leading-snug pr-2 line-clamp-2">Cấu trúc dữ liệu và giải thuật toán cao cấp</h2>
                        <button class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg p-1.5 mt-0.5 shrink-0 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="flex flex-wrap items-center gap-2.5 mb-3">
                        <span class="inline-flex items-center bg-blue-50 text-blue-600 text-[11px] font-bold px-2.5 py-1 rounded-lg uppercase tracking-wide border border-blue-100">CS402</span>
                        <span class="inline-flex items-center bg-emerald-50 text-emerald-600 text-[11px] font-bold px-2.5 py-1 rounded-lg border border-emerald-100">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5"></span>
                            Đang hoạt động
                        </span>
                    </div>
                    <p class="text-slate-500 text-sm font-medium">Công Nghệ Thông Tin - HK1 - 2024</p>
                </div>

                <div class="bg-gradient-to-br from-slate-50 to-slate-100/50 border border-slate-100 rounded-xl p-4 mx-6 mb-3 mt-auto">
                    <div class="grid grid-cols-3 divide-x divide-slate-200/80 items-center">
                        <div class="text-center px-1 min-w-0">
                            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1.5 truncate">Sinh viên</p>
                            <p class="text-2xl font-extrabold text-slate-900 leading-none tracking-tight truncate">45</p>
                        </div>

                        <div class="text-center px-1 min-w-0">
                            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1.5 truncate">Đã điểm danh</p>
                            <p class="text-2xl font-extrabold text-emerald-600 leading-none tracking-tight truncate">37</p>
                        </div>

                        <div class="text-center px-1 min-w-0">
                            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1.5 truncate">Chưa Đ.Danh</p>
                            <p class="text-2xl font-extrabold text-red-500 leading-none tracking-tight truncate">8</p>
                        </div>
                    </div>

                    <div class="text-center mt-3 pt-3 border-t border-slate-200/60">
                        <p class="text-slate-500 text-xs font-medium flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Phiên gần nhất: 21/10/2023
                        </p>
                    </div>
                </div>

                <div class="px-6 pb-6 pt-2 flex flex-col 2xl:flex-row gap-3">
                    <a href="/lecturer/class/show" class="w-full 2xl:w-[45%] py-2 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-semibold hover:bg-slate-50 hover:border-slate-300 transition-all truncate px-2 text-center">
                        Xem chi tiết
                    </a>
                    <button class="w-full 2xl:w-[55%] py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl text-sm font-semibold hover:from-blue-700 hover:to-blue-800 transition-all flex justify-center items-center gap-2 shadow-sm shadow-blue-500/30 px-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="truncate">Bắt đầu điểm danh</span>
                    </button>
                </div>
            </div>

            {{-- Card lớp học 2 --}}
            <div class="group bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col h-full overflow-hidden hover:shadow-lg transition-all duration-300">
                {{-- Gradient accent bar --}}
                <div class="h-1 bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500"></div>

                <div class="px-6 pt-6 pb-4">
                    <div class="flex justify-between items-start mb-3">
                        <h2 class="text-lg font-bold text-slate-900 leading-snug pr-2 line-clamp-2">Cấu trúc dữ liệu và giải thuật toán cao cấp</h2>
                        <button class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg p-1.5 mt-0.5 shrink-0 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="flex flex-wrap items-center gap-2.5 mb-3">
                        <span class="inline-flex items-center bg-blue-50 text-blue-600 text-[11px] font-bold px-2.5 py-1 rounded-lg uppercase tracking-wide border border-blue-100">CS402</span>
                        <span class="inline-flex items-center bg-emerald-50 text-emerald-600 text-[11px] font-bold px-2.5 py-1 rounded-lg border border-emerald-100">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5"></span>
                            Đang hoạt động
                        </span>
                    </div>
                    <p class="text-slate-500 text-sm font-medium">Công Nghệ Thông Tin - HK1 - 2024</p>
                </div>

                <div class="bg-gradient-to-br from-slate-50 to-slate-100/50 border border-slate-100 rounded-xl p-4 mx-6 mb-3 mt-auto">
                    <div class="grid grid-cols-3 divide-x divide-slate-200/80 items-center">
                        <div class="text-center px-1 min-w-0">
                            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1.5 truncate">Sinh viên</p>
                            <p class="text-2xl font-extrabold text-slate-900 leading-none tracking-tight truncate">45</p>
                        </div>

                        <div class="text-center px-1 min-w-0">
                            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1.5 truncate">Đã điểm danh</p>
                            <p class="text-2xl font-extrabold text-emerald-600 leading-none tracking-tight truncate">37</p>
                        </div>

                        <div class="text-center px-1 min-w-0">
                            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1.5 truncate">Chưa Đ.Danh</p>
                            <p class="text-2xl font-extrabold text-red-500 leading-none tracking-tight truncate">8</p>
                        </div>
                    </div>

                    <div class="text-center mt-3 pt-3 border-t border-slate-200/60">
                        <p class="text-slate-500 text-xs font-medium flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Phiên gần nhất: 21/10/2023
                        </p>
                    </div>
                </div>

                <div class="px-6 pb-6 pt-2 flex flex-col 2xl:flex-row gap-3">
                    <a href="/lecturer/class/show" class="w-full 2xl:w-[45%] py-2 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-semibold hover:bg-slate-50 hover:border-slate-300 transition-all truncate px-2 text-center">
                        Xem chi tiết
                    </a>
                    <button class="w-full 2xl:w-[55%] py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl text-sm font-semibold hover:from-blue-700 hover:to-blue-800 transition-all flex justify-center items-center gap-2 shadow-sm shadow-blue-500/30 px-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="truncate">Bắt đầu điểm danh</span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
