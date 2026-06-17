<x-app-layout variant="admin" page-title="Gói dịch vụ">
    <div class="max-w-[1400px] mx-auto">

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-[28px] font-bold text-slate-900 dark:text-white mb-1">Quản lý gói dịch vụ</h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm">Xem và cấu hình các gói dịch vụ Free/Pro trên hệ thống.</p>
            </div>
            <div class="flex items-center gap-5">
                <button class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl text-sm font-semibold hover:from-blue-700 hover:to-blue-800 flex items-center gap-2.5 shadow-sm shadow-blue-500/30 transition-all duration-200">
                    <x-sams.icon name="search" class="w-4 h-4 text-white" />
                    Tìm kiếm
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">

            {{-- Nút thêm gói dịch vụ mới --}}
            <a href="{{ route('admin.packages.create-mock') }}" class="group bg-white dark:bg-slate-800 rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-600 shadow-sm flex flex-col justify-center items-center h-full min-h-[320px] hover:bg-blue-50 hover:dark:bg-slate-700/50 hover:border-blue-400 transition-all duration-500 ease-out cursor-pointer overflow-hidden focus:outline-none focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-900/50">
                <div class="w-16 h-16 bg-blue-600 rounded-2xl shadow-md shadow-blue-500/25 border border-blue-600 flex justify-center items-center mb-5 group-hover:scale-105 group-hover:shadow-blue-500/40 transition-all duration-500 ease-out">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-700 dark:text-slate-200 group-hover:text-blue-700 dark:group-hover:text-blue-400 transition-colors duration-500 ease-out">Thêm gói dịch vụ mới</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1.5 group-hover:text-slate-600 dark:group-hover:text-slate-300 transition-colors duration-500 ease-out">Tạo cấu hình gói mới</p>
            </a>

            {{-- Card Gói Cơ Bản --}}
            <div class="group bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col h-full overflow-hidden hover:shadow-lg dark:hover:shadow-slate-900/50 transition-all duration-300">
                {{-- Gradient accent bar --}}
                <div class="h-1 bg-gradient-to-r from-blue-500 via-indigo-500 to-violet-500"></div>

                <div class="px-6 pt-6 pb-4">
                    <div class="flex justify-between items-start mb-3">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white leading-tight pr-2 truncate">Gói Cơ Bản (Free)</h2>
                        <button class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg p-1.5 mt-0.5 shrink-0 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="flex items-center gap-2.5 mb-3">
                        <span class="inline-flex items-center bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 text-[11px] font-bold px-2.5 py-1 rounded-lg uppercase tracking-wide border border-blue-100 dark:border-blue-500/20">FREE</span>
                        <span class="inline-flex items-center bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[11px] font-bold px-2.5 py-1 rounded-lg border border-emerald-100 dark:border-emerald-500/20">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5"></span>
                            Đang hoạt động
                        </span>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Sử dụng cho giảng viên cá nhân</p>
                </div>

                <div class="bg-gradient-to-br from-slate-50 to-slate-100/50 dark:from-slate-900/50 dark:to-slate-800/30 border border-slate-100 dark:border-slate-700 rounded-xl p-4 mx-6 mb-3 mt-auto">
                    <div class="flex justify-between items-center">
                        <div class="flex-1 text-center">
                            <p class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider mb-1.5">Lớp học</p>
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-white leading-none tracking-tight">5</p>
                        </div>

                        <div class="w-px h-10 bg-slate-200/80 dark:bg-slate-700"></div>

                        <div class="flex-1 text-center">
                            <p class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider mb-1.5">SV / Lớp</p>
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-white leading-none tracking-tight">50</p>
                        </div>

                        <div class="w-px h-10 bg-slate-200/80 dark:bg-slate-700"></div>

                        <div class="flex-1 text-center">
                            <p class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider mb-1.5">Giá</p>
                            <div class="flex flex-col items-center">
                                <p class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 leading-none tracking-tight">0đ</p>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 font-semibold bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded">Vĩnh viễn</p>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-3 pt-3 border-t border-slate-200/60 dark:border-slate-700/60">
                        <p class="text-slate-500 dark:text-slate-400 text-xs font-medium flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Giới hạn tính năng
                        </p>
                    </div>
                </div>

                <div class="px-6 pb-6 pt-2 flex gap-3">
                    <a href="{{ route('admin.packages.show-mock') }}" class="w-[45%] flex justify-center items-center py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 hover:dark:bg-slate-700 transition-all">
                        Xem chi tiết
                    </a>
                    <a href="{{ route('admin.packages.edit-mock') }}" class="w-[55%] py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl text-sm font-semibold hover:from-blue-700 hover:to-blue-800 transition-all flex justify-center items-center gap-2 shadow-sm shadow-blue-500/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Sửa gói
                    </a>
                </div>
            </div>

            {{-- Card Gói Pro --}}
            <div class="group bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col h-full overflow-hidden hover:shadow-lg dark:hover:shadow-slate-900/50 transition-all duration-300">
                {{-- Gradient accent bar --}}
                <div class="h-1 bg-gradient-to-r from-amber-400 via-yellow-500 to-orange-500"></div>

                <div class="px-6 pt-6 pb-4">
                    <div class="flex justify-between items-start mb-3">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white leading-tight pr-2 truncate">Gói Chuyên Nghiệp (Pro)</h2>
                        <button class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg p-1.5 mt-0.5 shrink-0 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="flex items-center gap-2.5 mb-3">
                        <span class="inline-flex items-center bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-500 text-[11px] font-bold px-2.5 py-1 rounded-lg uppercase tracking-wide border border-amber-100 dark:border-amber-500/20">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1l2.928 6.856L20 8.59l-5.072 4.574L16.18 20 10 16.146 3.82 20l1.252-6.836L0 8.59l7.072-.734L10 1z" clip-rule="evenodd" /></svg>
                            PRO
                        </span>
                        <span class="inline-flex items-center bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[11px] font-bold px-2.5 py-1 rounded-lg border border-emerald-100 dark:border-emerald-500/20">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5"></span>
                            Đang hoạt động
                        </span>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Không giới hạn cho tổ chức/trường học</p>
                </div>

                <div class="bg-gradient-to-br from-slate-50 to-slate-100/50 dark:from-slate-900/50 dark:to-slate-800/30 border border-slate-100 dark:border-slate-700 rounded-xl p-4 mx-6 mb-3 mt-auto">
                    <div class="flex justify-between items-center">
                        <div class="flex-1 text-center">
                            <p class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider mb-1.5">Lớp học</p>
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-white leading-none tracking-tight">∞</p>
                        </div>

                        <div class="w-px h-10 bg-slate-200/80 dark:bg-slate-700"></div>

                        <div class="flex-1 text-center">
                            <p class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider mb-1.5">SV / Lớp</p>
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-white leading-none tracking-tight">∞</p>
                        </div>

                        <div class="w-px h-10 bg-slate-200/80 dark:bg-slate-700"></div>

                        <div class="flex-1 text-center">
                            <p class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider mb-1.5">Giá</p>
                            <div class="flex flex-col items-center">
                                <p class="text-2xl font-extrabold text-blue-600 dark:text-blue-400 leading-none tracking-tight">Liên hệ</p>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 font-semibold bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded">Theo hợp đồng</p>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-3 pt-3 border-t border-slate-200/60 dark:border-slate-700/60">
                        <p class="text-slate-500 dark:text-slate-400 text-xs font-medium flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Mở khóa tất cả tính năng
                        </p>
                    </div>
                </div>

                <div class="px-6 pb-6 pt-2 flex gap-3">
                    <a href="{{ route('admin.packages.show-mock') }}" class="w-[45%] flex justify-center items-center py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 hover:dark:bg-slate-700 transition-all">
                        Xem chi tiết
                    </a>
                    <a href="{{ route('admin.packages.edit-mock') }}" class="w-[55%] py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 text-white rounded-xl text-sm font-semibold hover:from-amber-600 hover:to-amber-700 transition-all flex justify-center items-center gap-2 shadow-sm shadow-amber-500/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Sửa gói
                    </a>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>
