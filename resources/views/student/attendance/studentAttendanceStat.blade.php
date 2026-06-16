<x-app-layout variant="student" pageTitle="Thống kê chuyên cần">
    {{-- DESKTOP STATS --}}
    <div class="hidden lg:block w-full h-full p-6 lg:p-8 max-w-7xl mx-auto">
        <div class="space-y-6 animate-in fade-in duration-300">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chart-column w-6.5 h-6.5 text-blue-600 shrink-0" aria-hidden="true"><path d="M3 3v16a2 2 0 0 0 2 2h16"></path><path d="M18 17V9"></path><path d="M13 17V5"></path><path d="M8 17v-3"></path></svg>
                    Thống kê chuyên cần &amp; Phân tích Học bạ
                </h1>
                <p class="text-xs text-slate-500 font-semibold mt-1">Báo cáo hiệu suất chuyên cần cá nhân, xu hướng học thuật và dự báo rủi ro cấm thi SAMS</p>
            </div>
            
            <div class="p-5 rounded-3xl border bg-rose-50/75 border-rose-200 text-rose-950 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-sm">
                <div class="flex gap-3.5">
                    <span class="p-3 rounded-2xl shrink-0 bg-rose-100 text-rose-700">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-triangle-alert w-6 h-6" aria-hidden="true"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>
                    </span>
                    <div class="space-y-1">
                        <h3 class="text-sm font-black uppercase tracking-tight flex items-center gap-2">Cảnh báo: Bạn có 1 môn học đang có nguy cơ cấm thi!</h3>
                        <p class="text-[11.5px] font-medium leading-relaxed max-w-2xl opacity-90 font-sans">Theo quy chế của trường Cao đẳng kỹ thuật Cao Thắng, sinh viên vắng quá 20% tổng số tiết học lý thuyết/thực hành sẽ bị cấm thi trực tiếp. Vui lòng kiểm tra kỹ danh sách cảnh báo bên dưới.</p>
                    </div>
                </div>
                <button class="bg-rose-600 hover:bg-rose-700 text-white font-black text-[10px] uppercase tracking-wider px-4 py-2.5 rounded-xl border-none shrink-0 transition-transform active:scale-95 cursor-pointer">Kiểm tra ngay</button>
            </div>
            
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4.5">
                <div class="bg-blue-50/30 border border-slate-200 rounded-3xl p-5 shadow-sm space-y-2"><span class="text-[10px] font-black uppercase text-slate-400 tracking-wider block">Tỷ lệ chuyên cần</span><div class="flex items-baseline gap-1.5 pt-1"><strong class="text-2xl font-black font-mono leading-none text-blue-600">91.9%</strong></div><p class="text-[10.5px] font-semibold text-slate-500 leading-none">Trung bình tất cả môn học</p><span class="text-[9px] text-slate-400 font-medium block border-t border-slate-100 pt-2 font-mono uppercase">Sàn quy hoạch 80%</span></div>
                <div class="bg-emerald-50/30 border border-slate-200 rounded-3xl p-5 shadow-sm space-y-2"><span class="text-[10px] font-black uppercase text-slate-400 tracking-wider block">Số buổi có mặt</span><div class="flex items-baseline gap-1.5 pt-1"><strong class="text-2xl font-black font-mono leading-none text-emerald-600">89 tiết</strong></div><p class="text-[10.5px] font-semibold text-slate-500 leading-none">Ghi nhận thực tế lớp học</p><span class="text-[9px] text-slate-400 font-medium block border-t border-slate-100 pt-2 font-mono uppercase">Xác thực vân tay/mống mắt</span></div>
                <div class="bg-rose-50/30 border border-slate-200 rounded-3xl p-5 shadow-sm space-y-2"><span class="text-[10px] font-black uppercase text-slate-400 tracking-wider block">Số buổi vắng</span><div class="flex items-baseline gap-1.5 pt-1"><strong class="text-2xl font-black font-mono leading-none text-rose-500">6 tiết</strong></div><p class="text-[10.5px] font-semibold text-slate-500 leading-none">Vắng không phép/trừ chuẩn</p><span class="text-[9px] text-slate-400 font-medium block border-t border-slate-100 pt-2 font-mono uppercase">Tối đa chấp nhận 15%</span></div>
                <div class="bg-amber-50/30 border border-slate-200 rounded-3xl p-5 shadow-sm space-y-2"><span class="text-[10px] font-black uppercase text-slate-400 tracking-wider block">Số lần đi muộn</span><div class="flex items-baseline gap-1.5 pt-1"><strong class="text-2xl font-black font-mono leading-none text-amber-500">3 lần</strong></div><p class="text-[10.5px] font-semibold text-slate-500 leading-none">Đi trễ quá giờ quy định</p><span class="text-[9px] text-slate-400 font-medium block border-t border-slate-100 pt-2 font-mono uppercase">Bao gồm trễ không xếp phép</span></div>
            </div>
            
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
                <div class="xl:col-span-8 bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-4">
                    <div class="flex justify-between items-center select-none pb-2 border-b border-slate-100">
                        <div><h3 class="text-xs font-black uppercase text-slate-800 tracking-wider font-sans">Attendance Trend (Xu hướng Chuyên cần)</h3><p class="text-[10px] text-slate-400 font-semibold mt-0.5 font-sans">Biểu đồ chỉ số duy trì phong độ chuyên cần theo thời gian lưu viết</p></div><span class="text-[9px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded uppercase font-mono">SAMS Realtime Tracker</span>
                    </div>
                    <div class="h-[240px] w-full pt-2 bg-slate-50/50 rounded-2xl border border-slate-100 flex items-center justify-center text-slate-400 font-medium text-xs shadow-inner">
                         [Biểu đồ đường xu hướng chuyên cần - Sẽ được render bằng thư viện JS]
                    </div>
                    <div class="flex justify-center gap-6 mt-1 text-[9.5px] font-bold text-slate-500 font-sans select-none"><span class="flex items-center gap-1.5"><span class="w-2.5 h-1 bg-blue-600 rounded"></span> Chỉ số chuyên bạ (100 = Có mặt, 70 = Trễ, 0 = Vắng)</span></div>
                </div>
                
                <div class="xl:col-span-4 bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-4">
                    <div class="flex justify-between items-center select-none pb-2 border-b border-slate-100">
                        <div><h3 class="text-xs font-black uppercase text-slate-800 tracking-wider font-sans">Attendance Distribution</h3><p class="text-[10px] text-slate-400 font-semibold mt-0.5 font-sans">Tỷ lệ tương thuộc các trạng thái ghi điểm</p></div><span class="text-[9px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded uppercase font-sans font-mono">Bản đồ tròn</span>
                    </div>
                    <div class="h-[210px] w-full flex items-center justify-center relative bg-slate-50/50 rounded-2xl border border-slate-100 shadow-inner">
                         [Biểu đồ tròn phân bố chuyên cần]
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-[9.5px] font-semibold text-slate-600 leading-relaxed pt-2">
                        <div class="flex items-center gap-1.5 p-1 bg-slate-50 border border-slate-100 rounded-lg"><span class="w-2.5 h-2.5 rounded-sm shrink-0" style="background-color: rgb(16, 185, 129);"></span><span class="truncate">Có mặt: <strong class="font-mono text-slate-900 font-bold">89t</strong></span></div>
                        <div class="flex items-center gap-1.5 p-1 bg-slate-50 border border-slate-100 rounded-lg"><span class="w-2.5 h-2.5 rounded-sm shrink-0" style="background-color: rgb(245, 158, 11);"></span><span class="truncate">Muộn: <strong class="font-mono text-slate-900 font-bold">3t</strong></span></div>
                        <div class="flex items-center gap-1.5 p-1 bg-slate-50 border border-slate-100 rounded-lg"><span class="w-2.5 h-2.5 rounded-sm shrink-0" style="background-color: rgb(239, 68, 68);"></span><span class="truncate">Vắng: <strong class="font-mono text-slate-900 font-bold">6t</strong></span></div>
                        <div class="flex items-center gap-1.5 p-1 bg-slate-50 border border-slate-100 rounded-lg"><span class="w-2.5 h-2.5 rounded-sm shrink-0" style="background-color: rgb(99, 102, 241);"></span><span class="truncate">Có phép: <strong class="font-mono text-slate-900 font-bold">1t</strong></span></div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-4">
                <div class="flex justify-between items-center select-none pb-2 border-b border-slate-100">
                    <div><h3 class="text-xs font-black uppercase text-slate-800 tracking-wider font-sans">Attendance By Subject (Hiệu suất chuyên cần theo môn)</h3><p class="text-[10px] text-slate-400 font-semibold mt-0.5 font-sans">So sánh tỉ lệ đi kèm tiết vắng chi tiết giữa các học phần môn học hiện tại</p></div><span class="text-[9px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded uppercase font-sans font-mono">Cầu biên so sánh</span>
                </div>
                <div class="h-[220px] w-full pt-1 bg-slate-50/50 rounded-2xl border border-slate-100 flex items-center justify-center text-slate-400 font-medium text-xs shadow-inner">
                    [Biểu đồ cột hiệu suất chuyên cần theo môn]
                </div>
                <div class="flex justify-center gap-6 text-[9.5px] font-bold text-slate-500 font-sans select-none pt-1">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-blue-500"></span> Đạt tiêu chuẩn (≥ 80%)</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-rose-500 animate-pulse"></span> Dưới tiêu chuẩn (&lt; 80% - Có nguy cơ cấm thi)</span>
                </div>
            </div>
        
            <!-- Warning section -->
            <div id="warning-section-scroll" class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-4 scroll-mt-6">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 select-none">
                    <div class="flex items-center gap-2">
                        <span class="bg-rose-50 p-1.5 rounded-xl border border-rose-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-triangle-alert w-4 h-4 text-rose-600" aria-hidden="true"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>
                        </span>
                        <h3 class="text-xs font-black uppercase text-slate-900 tracking-wider">Cảnh báo rủi ro chuyên cần học phần</h3>
                    </div>
                    <span class="text-[10px] font-black text-rose-600 font-mono">Định vị ngưỡng 80%</span>
                </div>
                <div class="space-y-4">
                    <div class="p-3.5 bg-yellow-50/75 border border-yellow-250 rounded-2xl text-slate-800 text-[11px] font-medium leading-relaxed font-sans">🚨 <strong>LƯU Ý QUAN TRỌNG:</strong> Sinh viên cần đạt tỷ lệ đi học lý thuyết và thực hành tối thiểu <strong>80%</strong> số tiết của môn học mới đủ điều kiện xét duyệt tham gia kỳ thi cuối kỳ theo thông báo khảo thí trường Cao đẳng kỹ thuật Cao Thắng. Đảm bảo nộp đơn giải trình kèm minh chứng số hợp lệ nếu vắng có phép.</div>
                    <div class="space-y-3">
                        <div class="p-5 bg-slate-50 border border-slate-200 hover:border-rose-300 rounded-2xl transition-all flex flex-col md:flex-row md:items-center justify-between gap-5">
                            <div class="space-y-1 md:max-w-xs w-full font-sans">
                                <span class="bg-slate-200 text-slate-700 px-2 py-0.5 rounded text-[9px] font-mono font-bold block w-fit uppercase">PH102</span>
                                <h4 class="text-xs font-black text-slate-900 leading-snug pt-1">PH102: Vật lý đại cương 2</h4>
                                <span class="text-[10px] text-slate-450 block font-semibold">Thầy Lâm Văn Tiến | 2 Tín chỉ</span>
                            </div>
                            <div class="flex-1 grid grid-cols-2 sm:grid-cols-3 gap-4 font-semibold text-slate-700 font-sans">
                                <div><span class="text-[9px] uppercase font-bold text-slate-404 block select-none">Tỷ lệ hiện tại</span><span class="text-sm font-black font-mono text-rose-600 block mt-0.5">72%</span></div>
                                <div><span class="text-[9px] uppercase font-bold text-slate-404 block select-none">Số buổi vắng</span><span class="text-sm font-black font-mono text-slate-900 block mt-0.5">4 / 14 tiết</span></div>
                                <div class="col-span-2 sm:col-span-1"><span class="text-[9px] uppercase font-bold text-slate-404 block select-none">Ngưỡng vắng tối đa</span><span class="text-sm font-black font-mono text-slate-500 block mt-0.5">3 tiết vắng</span></div>
                            </div>
                            <div>
                                <button class="bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 font-bold text-[10px] px-3 py-2 rounded-xl transition-all shadow-sm">Nộp minh chứng</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- MOBILE STATS (Placeholder using Mobile Dashboard Layout) --}}
    <div class="lg:hidden w-full h-full font-sans">
        <div class="p-4 space-y-4 animate-in fade-in slide-in-from-bottom-2 duration-300">
            <div class="bg-gradient-to-br from-blue-600 via-indigo-650 to-indigo-800 text-white rounded-2xl p-4.5 relative overflow-hidden shadow-md border border-blue-500/30">
                <div class="absolute right-[-10px] bottom-[-10px] w-36 h-36 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute right-4 top-4 flex flex-col items-end opacity-40 font-mono text-[8px] select-none text-white/85"><div class="w-8 h-1 bg-white/60 mb-0.5 rounded-xs"></div><div class="w-6 h-1 bg-white/60 mb-0.5 rounded-xs"></div><div class="w-7 h-1 bg-white/60 rounded-xs"></div></div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white/10 backdrop-blur-md rounded-full text-[9px] font-extrabold uppercase tracking-widest mb-3.5 border border-white/15">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-graduation-cap w-3.5 h-3.5 text-blue-200" aria-hidden="true"><path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"></path><path d="M22 10v6"></path><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"></path></svg> 
                    Cao đẳng Kỹ thuật Cao Thắng
                </span>
                <h1 class="text-lg font-black text-white leading-tight tracking-tight">Thống kê chuyên cần</h1>
                <p class="text-[10.5px] text-blue-100/90 font-medium mt-1.5 leading-relaxed">Vui lòng xem chi tiết báo cáo chuyên cần trên giao diện máy tính để truy cập hệ thống biểu đồ đầy đủ nhất.</p>
            </div>
            
            <div class="bg-white border border-rose-100 rounded-2xl p-4 shadow-xs">
                <h3 class="text-xs font-black text-rose-600 uppercase tracking-widest mb-3 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-triangle-alert w-4 h-4"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>
                    Cảnh báo (1 môn)
                </h3>
                <div class="p-3 bg-rose-50 border border-rose-100 rounded-xl">
                    <h4 class="text-xs font-black text-slate-900 leading-snug">PH102: Vật lý đại cương 2</h4>
                    <p class="text-[10px] text-rose-600 mt-1 font-bold">Vắng: 4/14 tiết (72%) - Nguy cơ cấm thi</p>
                </div>
            </div>

            <div class="bg-white border border-slate-100 rounded-2xl p-4 shadow-xs">
                <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest mb-3">Tỷ Lệ Chuyên Cần Tổng Quan</h3>
                <div class="flex items-center justify-between gap-3">
                    <div class="relative shrink-0 w-24 h-24 flex items-center justify-center">
                        <svg class="w-full h-full transform -rotate-90">
                            <circle cx="48" cy="48" r="38" stroke="#F1F5F9" stroke-width="8" fill="transparent"></circle>
                            <circle cx="48" cy="48" r="38" stroke="#2563EB" stroke-width="8" fill="transparent" stroke-dasharray="238.76104167282426" stroke-dashoffset="18.145839167134632" stroke-linecap="round" class="transition-all duration-500"></circle>
                        </svg>
                        <div class="absolute flex flex-col items-center">
                            <span class="text-base font-black text-slate-850 leading-none">91.9%</span>
                            <span class="text-[8px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">Tích luỹ</span>
                        </div>
                    </div>
                    <div class="flex-1 grid grid-cols-2 gap-2">
                        <div class="p-2 bg-slate-50/50 rounded-xl border border-slate-100 text-center flex flex-col justify-center"><span class="block text-[8px] text-slate-400 font-extrabold uppercase">Có mặt</span><span class="text-xs font-black text-slate-800 mt-1 font-mono">89 tiết</span></div>
                        <div class="p-2 bg-slate-50/50 rounded-xl border border-slate-100 text-center flex flex-col justify-center"><span class="block text-[8px] text-slate-400 font-extrabold uppercase">Vắng</span><span class="text-xs font-black text-rose-600 mt-1 font-mono">6 tiết</span></div>
                        <div class="p-2 bg-slate-50/50 rounded-xl border border-slate-100 text-center flex flex-col justify-center"><span class="block text-[8px] text-slate-400 font-extrabold uppercase">Đi muộn</span><span class="text-xs font-black text-amber-600 mt-1 font-mono">3 lần</span></div>
                        <div class="p-2 bg-slate-50/50 rounded-xl border border-slate-100 text-center flex flex-col justify-center"><span class="block text-[8px] text-slate-400 font-extrabold uppercase">Sàn</span><span class="text-xs font-black text-blue-600 mt-1 font-mono">80%</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
