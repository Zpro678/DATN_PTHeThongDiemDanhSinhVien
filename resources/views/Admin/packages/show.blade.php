<x-app-layout variant="admin" page-title="Chi tiết gói dịch vụ">
    <div class="max-w-[1200px] mx-auto space-y-6">
        
        <!-- Breadcrumbs -->
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                        <x-sams.icon name="layout-dashboard" class="w-4 h-4 mr-2" />
                        Trang chủ
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-slate-400 dark:text-slate-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <a href="{{ route('admin.packages.index') }}" class="ml-1 text-sm font-medium text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 md:ml-2 transition-colors">Gói dịch vụ</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-slate-400 dark:text-slate-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 text-sm font-medium text-slate-500 dark:text-slate-400 md:ml-2">Chi tiết Gói Chuyên Nghiệp (Pro)</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Header Hero Section -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden relative">
            <!-- Decorative background -->
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 rounded-full bg-amber-400/20 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 rounded-full bg-orange-500/10 blur-3xl"></div>
            
            <!-- Top Gradient Bar -->
            <div class="h-2 w-full bg-gradient-to-r from-amber-400 via-yellow-500 to-orange-500"></div>

            <div class="p-8 md:p-10 relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <span class="inline-flex items-center bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-500 text-xs font-bold px-3 py-1.5 rounded-lg uppercase tracking-wide border border-amber-200 dark:border-amber-500/20 shadow-sm">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1l2.928 6.856L20 8.59l-5.072 4.574L16.18 20 10 16.146 3.82 20l1.252-6.836L0 8.59l7.072-.734L10 1z" clip-rule="evenodd" /></svg>
                            PRO PACKAGE
                        </span>
                        <span class="inline-flex items-center bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-bold px-3 py-1.5 rounded-lg border border-emerald-100 dark:border-emerald-500/20">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full mr-2 animate-pulse"></span>
                            Đang hoạt động
                        </span>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white mb-2">Gói Chuyên Nghiệp (Pro)</h1>
                    <p class="text-slate-500 dark:text-slate-400 text-base md:text-lg max-w-2xl">Giải pháp toàn diện không giới hạn dành cho các tổ chức, trung tâm đào tạo và trường học có quy mô lớn.</p>
                </div>

                <div class="flex-shrink-0 flex flex-col items-end">
                    <div class="text-right mb-4">
                        <p class="text-slate-400 dark:text-slate-500 text-sm font-semibold uppercase tracking-wider mb-1">Chi phí</p>
                        <div class="flex flex-col items-end">
                            <p class="text-4xl font-extrabold text-blue-600 dark:text-blue-400 leading-none">Liên hệ</p>
                            <span class="inline-flex items-center bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold px-2 py-1 rounded-md mt-2">
                                Thời hạn: Theo hợp đồng
                            </span>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('admin.packages.index') }}" class="px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm">
                            Quay lại
                        </a>
                        <a href="{{ route('admin.packages.edit-mock') }}" class="px-5 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 text-white rounded-xl text-sm font-semibold hover:from-amber-600 hover:to-amber-700 shadow-sm shadow-amber-500/30 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Chỉnh sửa
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Column: Metrics & Limitations -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Limit Overview Grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-blue-50 dark:bg-blue-500/10 rounded-xl flex items-center justify-center mb-4 border border-blue-100 dark:border-blue-500/20 text-blue-600 dark:text-blue-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 text-sm font-semibold uppercase tracking-wider mb-1">Số lớp học</p>
                        <p class="text-3xl font-extrabold text-slate-900 dark:text-white">Không giới hạn</p>
                    </div>
                    
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-500/10 rounded-xl flex items-center justify-center mb-4 border border-emerald-100 dark:border-emerald-500/20 text-emerald-600 dark:text-emerald-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 text-sm font-semibold uppercase tracking-wider mb-1">Sinh viên / Lớp</p>
                        <p class="text-3xl font-extrabold text-slate-900 dark:text-white">Không giới hạn</p>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm hover:shadow-md transition-shadow col-span-2 md:col-span-1">
                        <div class="w-12 h-12 bg-purple-50 dark:bg-purple-500/10 rounded-xl flex items-center justify-center mb-4 border border-purple-100 dark:border-purple-500/20 text-purple-600 dark:text-purple-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 text-sm font-semibold uppercase tracking-wider mb-1">Dung lượng lưu trữ</p>
                        <p class="text-3xl font-extrabold text-slate-900 dark:text-white">100 GB</p>
                    </div>
                </div>

                <!-- Feature List -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tính năng bao gồm</h3>
                    </div>
                    <div class="p-6">
                        <ul class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
                            <li class="flex items-start">
                                <div class="shrink-0 w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center border border-emerald-200 dark:border-emerald-500/30 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="ml-3 text-slate-700 dark:text-slate-300 font-medium text-sm md:text-base">Điểm danh bằng QR Code / Link</span>
                            </li>
                            <li class="flex items-start">
                                <div class="shrink-0 w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center border border-emerald-200 dark:border-emerald-500/30 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="ml-3 text-slate-700 dark:text-slate-300 font-medium text-sm md:text-base">Quản lý chuyên cần & cảnh báo</span>
                            </li>
                            <li class="flex items-start">
                                <div class="shrink-0 w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center border border-emerald-200 dark:border-emerald-500/30 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="ml-3 text-slate-700 dark:text-slate-300 font-medium text-sm md:text-base">Xác thực vị trí GPS</span>
                            </li>
                            <li class="flex items-start">
                                <div class="shrink-0 w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center border border-emerald-200 dark:border-emerald-500/30 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="ml-3 text-slate-700 dark:text-slate-300 font-medium text-sm md:text-base">Nhận diện IP & Thiết bị chống gian lận</span>
                            </li>
                            <li class="flex items-start">
                                <div class="shrink-0 w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center border border-emerald-200 dark:border-emerald-500/30 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="ml-3 text-slate-700 dark:text-slate-300 font-medium text-sm md:text-base">Xuất báo cáo Excel / PDF nâng cao</span>
                            </li>
                            <li class="flex items-start">
                                <div class="shrink-0 w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center border border-emerald-200 dark:border-emerald-500/30 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="ml-3 text-slate-700 dark:text-slate-300 font-medium text-sm md:text-base">Import sinh viên hàng loạt</span>
                            </li>
                            <li class="flex items-start">
                                <div class="shrink-0 w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center border border-emerald-200 dark:border-emerald-500/30 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="ml-3 text-slate-700 dark:text-slate-300 font-medium text-sm md:text-base">Cập nhật dữ liệu Realtime</span>
                            </li>
                            <li class="flex items-start">
                                <div class="shrink-0 w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center border border-amber-200 dark:border-amber-500/30 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                                </div>
                                <span class="ml-3 text-slate-700 dark:text-slate-300 font-bold text-sm md:text-base">Hỗ trợ kỹ thuật 24/7 (Ưu tiên)</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Right Column: Usage Stats & Settings -->
            <div class="space-y-6">
                <!-- Usage Stats -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Thống kê sử dụng</h3>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-slate-500 dark:text-slate-400 text-sm font-medium">Tổ chức đăng ký</span>
                            <span class="text-slate-900 dark:text-white font-bold text-lg">45</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2 mb-6">
                            <div class="bg-amber-500 h-2 rounded-full" style="width: 35%"></div>
                        </div>

                        <div class="flex items-center justify-between mb-2">
                            <span class="text-slate-500 dark:text-slate-400 text-sm font-medium">Tổng số User</span>
                            <span class="text-slate-900 dark:text-white font-bold text-lg">1,208</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2 mb-6">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: 65%"></div>
                        </div>

                        <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-700 text-center">
                            <p class="text-sm text-slate-500 dark:text-slate-400 mb-3">Gói này chiếm <strong class="text-slate-800 dark:text-slate-200">35%</strong> tổng doanh thu tháng này.</p>
                            <button class="text-sm text-blue-600 dark:text-blue-400 font-semibold hover:text-blue-800 dark:hover:text-blue-300 transition-colors">Xem danh sách Khách hàng &rarr;</button>
                        </div>
                    </div>
                </div>

                <!-- API & Integration -->
                <div class="bg-slate-900 dark:bg-slate-950 rounded-2xl border border-slate-800 dark:border-slate-800 shadow-lg overflow-hidden text-white relative">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                    </div>
                    <div class="p-6 relative z-10">
                        <h3 class="text-lg font-bold mb-2">Tích hợp API</h3>
                        <p class="text-slate-400 text-sm mb-4">Gói Pro cho phép cấp phát API Key để tích hợp với hệ thống nội bộ của trường đại học (SSO, LMS).</p>
                        <span class="inline-flex items-center bg-emerald-500/20 text-emerald-400 text-xs font-bold px-2.5 py-1 rounded border border-emerald-500/30">
                            Đã kích hoạt
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
