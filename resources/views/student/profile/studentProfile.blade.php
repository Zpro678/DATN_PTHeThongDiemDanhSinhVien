<x-app-layout variant="student" pageTitle="Hồ sơ cá nhân">
    {{-- Main Content Canvas --}}
    <div class="w-full max-w-[1440px] mx-auto p-4 md:p-8 animate-in fade-in duration-300">
        <!-- Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Main Profile Column (Span 8) -->
            <div class="lg:col-span-8 flex flex-col gap-6">
                <!-- Profile Header Card -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm overflow-hidden">
                    <!-- Cover Image / Top Accent -->
                    <div class="h-24 lg:h-32 bg-gradient-to-r from-slate-100 to-blue-50 dark:from-slate-800 dark:to-slate-850 w-full relative">
                        <!-- Subtle Pattern Overlay -->
                        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, #2563eb 1px, transparent 0); background-size: 24px 24px;"></div>
                    </div>
                    <div class="px-6 pb-6 relative">
                        <!-- Avatar -->
                        <div class="absolute -top-12 lg:-top-16 left-6">
                            <div class="w-24 h-24 lg:w-32 lg:h-32 rounded-2xl bg-white dark:bg-slate-900 p-1 shadow-md border border-slate-200/30 dark:border-slate-700/30">
                                <img alt="TRẦN MINH HOÀNG" class="w-full h-full rounded-xl object-cover" src="https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?ixlib=rb-1.2.1&auto=format&fit=crop&w=150&h=150&q=80"/>
                            </div>
                        </div>
                        <!-- Info Area -->
                        <div class="pt-20 lg:pt-6 pl-0 lg:pl-40 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 w-full">
                            <div class="w-full lg:w-auto">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400 rounded text-[10px] font-bold tracking-wider">HỌC VIÊN ĐÀO TẠO CAO ĐẲNG TUYẾN</span>
                                </div>
                                <h1 class="text-xl lg:text-2xl font-bold text-slate-900 dark:text-white uppercase tracking-tight">TRẦN MINH HOÀNG</h1>
                                <p class="text-sm text-slate-500 dark:text-slate-400 flex items-center gap-2 mt-1">
                                    <x-sams.icon name="graduation-cap" class="w-4.5 h-4.5" />
                                    Mã số thẻ trường: <strong class="text-slate-900 dark:text-white">0306231108</strong>
                                </p>
                            </div>
                            <div class="flex gap-2 w-full lg:w-auto mt-2 lg:mt-0">
                                <button class="w-full lg:w-auto px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-blue-600 dark:text-blue-400 rounded-xl font-medium text-sm transition-colors flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                                    Cập nhật
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Details Bento Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Academic Info Card -->
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm">
                        <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-6 border-b border-slate-100 dark:border-slate-800 pb-2">LỊCH TRÌNH HỌC BẠ CAO THẮNG</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-1 text-sm">
                                <span class="text-slate-500 dark:text-slate-450">Lớp sinh viên:</span>
                                <span class="font-bold text-slate-900 dark:text-white text-right">CĐ Kỹ thuật Phần mềm 23A</span>
                            </div>
                            <div class="flex justify-between items-center py-1 text-sm">
                                <span class="text-slate-500 dark:text-slate-450">Khoa quản học:</span>
                                <span class="font-bold text-slate-900 dark:text-white text-right">Khoa Công nghệ Thông tin</span>
                            </div>
                            <div class="flex justify-between items-center py-1 text-sm">
                                <span class="text-slate-500 dark:text-slate-450">Niên khóa khóa đào:</span>
                                <span class="font-bold text-slate-900 dark:text-white text-right">2023 - 2026 (K23)</span>
                            </div>
                            <div class="flex justify-between items-center py-1 text-sm">
                                <span class="text-slate-500 dark:text-slate-450">Cố vấn đào tạo:</span>
                                <a class="font-bold text-blue-600 dark:text-blue-400 hover:underline text-right flex items-center gap-1" href="#">
                                    Thầy Lê Minh Triết
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Info Card -->
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm">
                        <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-6 border-b border-slate-100 dark:border-slate-800 pb-2">LIÊN LẠC CÁ NHÂN SINH VIÊN</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-1 text-sm">
                                <span class="text-slate-500 dark:text-slate-450">Email trường cấp:</span>
                                <span class="font-bold text-slate-900 dark:text-white text-right truncate max-w-[200px]" title="0306231108@caothang.edu.vn">0306231108@caothang.edu.vn</span>
                            </div>
                            <div class="flex justify-between items-center py-1 text-sm">
                                <span class="text-slate-500 dark:text-slate-450">Số điện thoại di:</span>
                                <span class="font-bold text-slate-900 dark:text-white text-right">0987.654.321</span>
                            </div>
                            <div class="flex justify-between items-center py-1 text-sm">
                                <span class="text-slate-500 dark:text-slate-450">Bản kết nối SAMS:</span>
                                <span class="px-2 py-1 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 rounded text-[11px] font-bold tracking-wider border border-emerald-100 dark:border-emerald-900/50 flex items-center gap-1">
                                    <x-sams.icon name="shield-check" class="h-4 w-4 text-emerald-700 dark:text-emerald-400 shrink-0" />
                                    TẬP CHỮ KÝ VERIFIED
                                </span>
                            </div>
                            <div class="flex justify-between items-center py-1 text-sm">
                                <span class="text-slate-500 dark:text-slate-450">Tổng xếp hạng:</span>
                                <span class="font-bold text-blue-600 dark:text-blue-400 text-right">Lao học chuyên cần Tốt</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar Column (Span 4) -->
            <div class="lg:col-span-4 flex flex-col gap-6">
                <!-- Security Device Card (High Fidelity) -->
                <div class="bg-slate-900 dark:bg-slate-950 rounded-2xl border border-slate-800 shadow-lg overflow-hidden text-slate-350 relative group">
                    <!-- Header -->
                    <div class="bg-slate-850/50 p-6 border-b border-slate-800 flex items-start gap-4 relative z-10">
                        <div class="w-10 h-10 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/30">
                            <x-sams.icon name="shield-check" class="h-6 w-6 text-emerald-400 shrink-0" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white uppercase tracking-wide leading-tight">THIẾT BỊ BẢO MẬT SAMS ID ĐIỆN TỬ</h3>
                            <p class="text-[11px] text-slate-400 mt-1">Khóa cứng UUID chống giả trang tọa GPS</p>
                        </div>
                    </div>
                    <!-- Content -->
                    <div class="p-6 relative z-10">
                        <p class="text-xs text-slate-400 mb-6 leading-relaxed">
                            Chuyên bạ học tập đào tạo SAMS của Trần Minh Hoàng được liên kết vào một UUID iPhone di động phần cứng chính hãng, loại bỏ tuyệt đối nguy cơ ghi bạ hộ hoặc chia bạ mã QR.
                        </p>
                        <div class="space-y-3 bg-slate-950/55 p-4 rounded-xl border border-slate-800/50 font-mono text-xs">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Thiết bị:</span>
                                <span class="text-slate-200">iPhone 15 Pro Max (iOS)</span>
                            </div>
                            <div class="w-full h-px bg-slate-800/50"></div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">SAMS UUID:</span>
                                <span class="text-emerald-400 tracking-wider">UUID:F0C2:0306:2311:08A8</span>
                            </div>
                            <div class="w-full h-px bg-slate-800/50"></div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Gói định bạ:</span>
                                <span class="text-slate-200">RFID SECURITY CORE v2.1</span>
                            </div>
                            <div class="w-full h-px bg-slate-800/50"></div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Độ lệch GPS tích hợp:</span>
                                <span class="text-emerald-400 flex items-center gap-1">
                                    11.4 mét (Đạt chuẩn)
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse ml-1"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <!-- Footer Action -->
                    <div class="p-4 border-t border-slate-800 bg-slate-950/20 relative z-10">
                        <button class="w-full py-2.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 rounded-xl text-xs font-bold transition-colors">
                            Reset mô phỏng học bạ
                        </button>
                    </div>
                    <!-- Ambient Tech Background Decoration -->
                    <div class="absolute inset-0 pointer-events-none opacity-[0.03] bg-[linear-gradient(rgba(255,255,255,0.1)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.1)_1px,transparent_1px)] bg-[size:20px_20px]"></div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
