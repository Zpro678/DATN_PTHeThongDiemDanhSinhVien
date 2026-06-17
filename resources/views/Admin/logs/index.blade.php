<x-app-layout variant="admin" page-title="Nhật ký hệ thống">
    <div class="max-w-[1200px] mx-auto">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-[28px] font-bold text-slate-900 dark:text-white mb-1">Nhật ký hệ thống</h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm">Theo dõi dòng sự kiện và các hoạt động thay đổi trên hệ thống.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="relative">
                    <x-sams.icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 dark:text-slate-500" />
                    <input type="text" placeholder="Tìm kiếm nhật ký..." class="pl-10 pr-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-64 bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm transition-colors">
                </div>
                <livewire:date-range-picker />
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 md:p-8 shadow-sm transition-colors">
            
            <div class="space-y-8 relative before:absolute before:top-2 before:bottom-2 before:left-[17px] before:w-0.5 before:bg-slate-100 dark:before:bg-slate-700">
                
                <!-- Hoạt động 1 -->
                <div class="flex gap-4 md:gap-6 items-start relative group">
                    <div class="w-9 h-9 rounded-full bg-slate-50 dark:bg-slate-800 border-2 border-white dark:border-slate-900 flex items-center justify-center shrink-0 z-10 shadow-sm group-hover:scale-105 transition-transform">
                        <x-sams.icon name="plus-circle" class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div class="flex-1 bg-slate-50/50 dark:bg-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-700/50 border border-slate-100 dark:border-slate-700/60 p-4 rounded-2xl transition-colors">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                            <span class="px-2.5 py-1 text-[10px] font-extrabold rounded-md tracking-wider bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-500/20">
                                LỚP HỌC
                            </span>
                            <span class="text-xs text-slate-400 dark:text-slate-500 font-medium flex items-center gap-1.5">
                                <x-sams.icon name="calendar" class="w-3.5 h-3.5" />
                                17/06/2026 10:30
                            </span>
                        </div>
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 leading-relaxed">
                            <span class="text-blue-600 dark:text-blue-400 font-bold">ThS. Nguyễn Khắc Nhật</span> đã tạo lớp chuyên đề mới Học máy ứng dụng (CS-403)
                        </p>
                    </div>
                </div>

                <!-- Hoạt động 2 -->
                <div class="flex gap-4 md:gap-6 items-start relative group">
                    <div class="w-9 h-9 rounded-full bg-slate-50 dark:bg-slate-800 border-2 border-white dark:border-slate-900 flex items-center justify-center shrink-0 z-10 shadow-sm group-hover:scale-105 transition-transform">
                        <x-sams.icon name="wifi" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 animate-pulse" />
                    </div>
                    <div class="flex-1 bg-slate-50/50 dark:bg-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-700/50 border border-slate-100 dark:border-slate-700/60 p-4 rounded-2xl transition-colors">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                            <span class="px-2.5 py-1 text-[10px] font-extrabold rounded-md tracking-wider bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/20">
                                PHIÊN ĐIỂM DANH
                            </span>
                            <span class="text-xs text-slate-400 dark:text-slate-500 font-medium flex items-center gap-1.5">
                                <x-sams.icon name="calendar" class="w-3.5 h-3.5" />
                                17/06/2026 09:15
                            </span>
                        </div>
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 leading-relaxed">
                            <span class="text-blue-600 dark:text-blue-400 font-bold">TS. Hoàng Minh</span> đã mở phiên điểm danh lớp Thiết kế Hướng đối tượng - Room B302
                        </p>
                    </div>
                </div>

                <!-- Hoạt động 3 -->
                <div class="flex gap-4 md:gap-6 items-start relative group">
                    <div class="w-9 h-9 rounded-full bg-slate-50 dark:bg-slate-800 border-2 border-white dark:border-slate-900 flex items-center justify-center shrink-0 z-10 shadow-sm group-hover:scale-105 transition-transform">
                        <x-sams.icon name="user-check" class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <div class="flex-1 bg-slate-50/50 dark:bg-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-700/50 border border-slate-100 dark:border-slate-700/60 p-4 rounded-2xl transition-colors">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                            <span class="px-2.5 py-1 text-[10px] font-extrabold rounded-md tracking-wider bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/20">
                                ĐIỂM DANH
                            </span>
                            <span class="text-xs text-slate-400 dark:text-slate-500 font-medium flex items-center gap-1.5">
                                <x-sams.icon name="calendar" class="w-3.5 h-3.5" />
                                17/06/2026 08:50
                            </span>
                        </div>
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 leading-relaxed">
                            Hơn 54 sinh viên đã điểm danh thành công lớp <span class="font-bold text-slate-900 dark:text-white">An toàn hệ thống thông tin</span>
                        </p>
                    </div>
                </div>

                <!-- Hoạt động 4 -->
                <div class="flex gap-4 md:gap-6 items-start relative group">
                    <div class="w-9 h-9 rounded-full bg-slate-50 dark:bg-slate-800 border-2 border-white dark:border-slate-900 flex items-center justify-center shrink-0 z-10 shadow-sm group-hover:scale-105 transition-transform">
                        <x-sams.icon name="alert-triangle" class="w-4 h-4 text-rose-600 dark:text-rose-400" />
                    </div>
                    <div class="flex-1 bg-slate-50/50 dark:bg-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-700/50 border border-slate-100 dark:border-slate-700/60 p-4 rounded-2xl transition-colors">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                            <span class="px-2.5 py-1 text-[10px] font-extrabold rounded-md tracking-wider bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-100 dark:border-rose-500/20">
                                CẢNH BÁO
                            </span>
                            <span class="text-xs text-slate-400 dark:text-slate-500 font-medium flex items-center gap-1.5">
                                <x-sams.icon name="calendar" class="w-3.5 h-3.5" />
                                16/06/2026 15:45
                            </span>
                        </div>
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 leading-relaxed">
                            Hệ thống cảnh báo: <span class="text-rose-600 dark:text-rose-400 font-bold">SV Trần Tuấn Vũ</span> vắng quá 30% môn Mạng máy tính
                        </p>
                    </div>
                </div>

                <!-- Hoạt động 5 -->
                <div class="flex gap-4 md:gap-6 items-start relative group">
                    <div class="w-9 h-9 rounded-full bg-slate-50 dark:bg-slate-800 border-2 border-white dark:border-slate-900 flex items-center justify-center shrink-0 z-10 shadow-sm group-hover:scale-105 transition-transform">
                        <x-sams.icon name="settings" class="w-4 h-4 text-amber-500 dark:text-amber-400" />
                    </div>
                    <div class="flex-1 bg-slate-50/50 dark:bg-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-700/50 border border-slate-100 dark:border-slate-700/60 p-4 rounded-2xl transition-colors">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                            <span class="px-2.5 py-1 text-[10px] font-extrabold rounded-md tracking-wider bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-500/20">
                                CẤU HÌNH
                            </span>
                            <span class="text-xs text-slate-400 dark:text-slate-500 font-medium flex items-center gap-1.5">
                                <x-sams.icon name="calendar" class="w-3.5 h-3.5" />
                                16/06/2026 14:00
                            </span>
                        </div>
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 leading-relaxed">
                            <span class="text-blue-600 dark:text-blue-400 font-bold">Super Admin</span> đã thay đổi cấu hình tính toán tỷ lệ chuyên cần mặc định hệ thống
                        </p>
                    </div>
                </div>

            </div>

            <!-- Pagination dummy -->
            <div class="mt-8 pt-6 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Hiển thị <span class="font-bold text-slate-900 dark:text-white">1</span> đến <span class="font-bold text-slate-900 dark:text-white">5</span> trong số <span class="font-bold text-slate-900 dark:text-white">12,408</span> bản ghi</p>
                <div class="flex items-center gap-1">
                    <button class="px-3 py-1.5 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-semibold text-slate-400 dark:text-slate-600 cursor-not-allowed">Trước</button>
                    <button class="px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800 rounded-lg text-xs font-bold text-blue-700 dark:text-blue-400">1</button>
                    <button class="px-3 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-700 border border-transparent rounded-lg text-xs font-semibold text-slate-600 dark:text-slate-400 transition-colors">2</button>
                    <button class="px-3 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-700 border border-transparent rounded-lg text-xs font-semibold text-slate-600 dark:text-slate-400 transition-colors">3</button>
                    <span class="px-2 text-slate-400 dark:text-slate-600">...</span>
                    <button class="px-3 py-1.5 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg text-xs font-semibold text-slate-600 dark:text-slate-400 transition-colors">Sau</button>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
