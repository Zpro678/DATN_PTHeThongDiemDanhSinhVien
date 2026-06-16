<!-- ======================= -->
<!-- AuthIllustration (Bên trái) -->
<!-- ======================= -->
<div id="auth-illustration" class="relative hidden lg:flex lg:w-1/2 bg-slate-900 flex-col justify-between px-8 py-6 xl:py-8 text-white border-r border-slate-800">
    <!-- Decorative gradient glowing orbs -->
    <div class="absolute top-[-20%] left-[-20%] w-[80%] h-[80%] rounded-full bg-blue-600/20 blur-[120px]"></div>
    <div class="absolute bottom-[-20%] right-[-20%] w-[80%] h-[80%] rounded-full bg-indigo-500/10 blur-[120px]"></div>
    
    <!-- Wave lines accent pattern in background -->
    <div class="absolute inset-0 opacity-[0.03] mix-blend-overlay pointer-events-none select-none">
        <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                    <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#grid)" />
        </svg>
    </div>

    <!-- Center Wrapper -->
    <div class="relative z-10 flex-1 flex flex-col justify-center items-center w-full">
        <div class="w-full max-w-lg">
            
            <!-- Top Brand Logo Section -->
            <div class="flex items-center justify-center gap-4 mb-8">
                <div class="w-12 h-12 rounded-xl bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/20">
                    <i data-lucide="graduation-cap" class="w-8 h-8 text-white"></i>
                </div>
                <div>
                    <span class="text-2xl font-extrabold tracking-tight bg-gradient-to-r from-blue-400 to-indigo-200 bg-clip-text text-transparent">
                        SmartAttendance
                    </span>
                    <span class="text-xs font-bold text-slate-400 block tracking-widest uppercase mt-0.5">
                        Hệ thống giáo dục
                    </span>
                </div>
            </div>

            <!-- Center content: Text and features summary -->
            <h1 class="text-4xl font-extrabold tracking-tight text-slate-100 leading-tight mb-3 text-center">
                Hệ Thống Quản Lý <br />
                <span class="bg-gradient-to-r from-blue-400 via-indigo-400 to-emerald-400 bg-clip-text text-transparent">
                    Điểm Danh Sinh Viên
                </span>
            </h1>
            
            <p class="text-slate-400 text-sm lg:text-base leading-relaxed mb-6 text-justify">
                Giải pháp điểm danh thông minh, chống gian lận qua QR động và GPS, hỗ trợ giảng viên tự động hóa đánh giá chuyên cần nhanh chóng.
            </p>

            <!-- Feature Grid / List -->
            <div class="space-y-2">
            <!-- Feature 1 -->
            <div class="flex gap-4 items-start p-2 rounded-2xl transition-colors hover:bg-slate-800/30">
                <div class="w-9 h-9 rounded-xl bg-slate-800 flex items-center justify-center shrink-0 border border-slate-700/50">
                    <i data-lucide="qr-code" class="w-5 h-5 text-blue-500"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-200 flex items-center gap-1.5">
                        Điểm danh QR động
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                    </h3>
                    <p class="text-xs text-slate-400 mt-1 leading-normal">Thay đổi mã QR liên tục nhằm chống gian lận điểm danh.</p>
                </div>
            </div>
            
            <!-- Feature 2 -->
            <div class="flex gap-4 items-start p-2 rounded-2xl transition-colors hover:bg-slate-800/30">
                <div class="w-9 h-9 rounded-xl bg-slate-800 flex items-center justify-center shrink-0 border border-slate-700/50">
                    <i data-lucide="map-pin" class="w-5 h-5 text-emerald-500"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-200 flex items-center gap-1.5">
                        Xác thực định vị GPS
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                    </h3>
                    <p class="text-xs text-slate-400 mt-1 leading-normal">Đảm bảo sinh viên thực sự có mặt tại phòng học chỉ định.</p>
                </div>
            </div>

            <!-- Feature 3 -->
            <div class="flex gap-4 items-start p-2 rounded-2xl transition-colors hover:bg-slate-800/30">
                <div class="w-9 h-9 rounded-xl bg-slate-800 flex items-center justify-center shrink-0 border border-slate-700/50">
                    <i data-lucide="bar-chart-3" class="w-5 h-5 text-amber-500"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-200 flex items-center gap-1.5">
                        Báo cáo chuyên cần
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                    </h3>
                    <p class="text-xs text-slate-400 mt-1 leading-normal">Thống kê tự động, trực quan và xuất dữ liệu dễ dàng.</p>
                </div>
            </div>

            <!-- Feature 4 -->
            <div class="flex gap-4 items-start p-2 rounded-2xl transition-colors hover:bg-slate-800/30">
                <div class="w-9 h-9 rounded-xl bg-slate-800 flex items-center justify-center shrink-0 border border-slate-700/50">
                    <i data-lucide="shield-alert" class="w-5 h-5 text-rose-500"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-200 flex items-center gap-1.5">
                        Cảnh báo sinh viên nguy cơ
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                    </h3>
                    <p class="text-xs text-slate-400 mt-1 leading-normal">Phát hiện sớm sinh viên vắng nhiều quá giới hạn cho phép.</p>
                </div>
            </div>
            </div>
        </div>
    </div>

    <!-- Bottom Footer Section -->
    <div class="relative z-10 pt-4 border-t border-slate-800 flex items-center justify-between text-xs text-slate-500">
        <span>© 2026 SmartAttendance. All rights reserved.</span>
        <div class="flex gap-3 font-medium">
            <a href="#" class="hover:text-slate-300 transition-colors">Điều khoản</a>
            <span>·</span>
            <a href="#" class="hover:text-slate-300 transition-colors">Bảo mật</a>
        </div>
    </div>
</div>
