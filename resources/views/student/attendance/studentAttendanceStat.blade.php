<x-app-layout variant="student" pageTitle="Thống kê chuyên cần">
    <div x-data="{ activeSubject: null, showList: false }" class="w-full h-full">
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
                    <div class="bg-blue-50/30 border border-slate-200 rounded-3xl p-5 shadow-sm space-y-2"><span class="text-[10px] font-black uppercase text-slate-405 tracking-wider block">Tỷ lệ chuyên cần</span><div class="flex items-baseline gap-1.5 pt-1"><strong class="text-2xl font-black font-mono leading-none text-blue-600">91.9%</strong></div><p class="text-[10.5px] font-semibold text-slate-500 leading-none">Trung bình tất cả môn học</p><span class="text-[9px] text-slate-400 font-medium block border-t border-slate-100 pt-2 font-mono uppercase">Sàn quy hoạch 80%</span></div>
                    <div class="bg-emerald-50/30 border border-slate-200 rounded-3xl p-5 shadow-sm space-y-2"><span class="text-[10px] font-black uppercase text-slate-455 tracking-wider block">Số buổi có mặt</span><div class="flex items-baseline gap-1.5 pt-1"><strong class="text-2xl font-black font-mono leading-none text-emerald-600">89 tiết</strong></div><p class="text-[10.5px] font-semibold text-slate-500 leading-none">Ghi nhận thực tế lớp học</p><span class="text-[9px] text-slate-400 font-medium block border-t border-slate-100 pt-2 font-mono uppercase">Xác thực vân tay/mống mắt</span></div>
                    <div class="bg-rose-50/30 border border-slate-200 rounded-3xl p-5 shadow-sm space-y-2"><span class="text-[10px] font-black uppercase text-slate-405 tracking-wider block">Số buổi vắng</span><div class="flex items-baseline gap-1.5 pt-1"><strong class="text-2xl font-black font-mono leading-none text-rose-500">6 tiết</strong></div><p class="text-[10.5px] font-semibold text-slate-500 leading-none">Vắng không phép/trừ chuẩn</p><span class="text-[9px] text-slate-400 font-medium block border-t border-slate-100 pt-2 font-mono uppercase">Tối đa chấp nhận 15%</span></div>
                    <div class="bg-amber-50/30 border border-slate-200 rounded-3xl p-5 shadow-sm space-y-2"><span class="text-[10px] font-black uppercase text-slate-405 tracking-wider block">Số lần đi muộn</span><div class="flex items-baseline gap-1.5 pt-1"><strong class="text-2xl font-black font-mono leading-none text-amber-500">3 lần</strong></div><p class="text-[10.5px] font-semibold text-slate-500 leading-none">Đi trễ quá giờ quy định</p><span class="text-[9px] text-slate-400 font-medium block border-t border-slate-100 pt-2 font-mono uppercase">Bao gồm trễ không xếp phép</span></div>
                </div>
                
                {{-- SECTION SUBJECT CARDS --}}
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-5">
                    <div class="flex justify-between items-center select-none pb-2 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <h3 class="text-xs font-black uppercase text-slate-800 dark:text-white tracking-wider">Biến động chuyên cần theo môn học</h3>
                            <p class="text-[10px] text-slate-550 dark:text-slate-400 font-semibold mt-0.5">Thống kê chi tiết số tiết học và tỉ lệ chuyên cần tích lũy của từng học phần</p>
                        </div>
                        <button @click="showList = !showList" class="text-xs text-blue-600 hover:underline border-none bg-transparent font-bold cursor-pointer transition-all">
                            <span x-text="showList ? 'Xem dạng thẻ' : 'Xem danh sách'">Xem danh sách</span>
                        </button>
                    </div>


                    <div x-show="!showList" x-transition class="flex overflow-x-auto gap-6 pb-4 snap-x scroll-smooth no-scrollbar">
                        {{-- Card 1: DB101 --}}
                        <div onclick="window.location.href='{{ route('student.classes.detail', ['class' => 'DB101']) }}'" class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer overflow-hidden group flex flex-col min-h-[250px] shrink-0 w-[280px] sm:w-[320px] snap-start">
                            <div class="h-32 bg-gradient-to-br from-blue-600 to-blue-800 p-5 flex flex-col justify-end">
                                <h3 class="text-sm font-black text-white leading-tight">Thiết kế &amp; Quản trị SQL</h3>
                            </div>
                            <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                                <div class="space-y-2">
                                    <p class="text-[11px] text-slate-550 dark:text-slate-400 font-bold">Giảng viên: Thầy Lê Hoàng Đạt</p>
                                    <div class="grid grid-cols-2 gap-y-1.5 text-[10.5px] font-semibold text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-2.5">
                                        <div>Mã lớp: <span class="text-slate-800 dark:text-slate-200 font-mono font-bold">DB101_L02</span></div>
                                        <div>Tổng tiết: <span class="text-slate-855 dark:text-slate-200 font-bold font-mono">16</span></div>
                                        <div>Có mặt: <span class="text-emerald-600 font-bold font-mono">15</span></div>
                                        <div>Vắng: <span class="text-rose-500 font-bold font-mono">0</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card 2: PY201 --}}
                        <div onclick="window.location.href='{{ route('student.classes.detail', ['class' => 'PY201']) }}'" class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer overflow-hidden group flex flex-col min-h-[250px] shrink-0 w-[280px] sm:w-[320px] snap-start">
                            <div class="h-32 bg-gradient-to-br from-blue-600 to-blue-800 p-5 flex flex-col justify-end">
                                <h3 class="text-sm font-black text-white leading-tight">Phát triển Web Python</h3>
                            </div>
                            <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                                <div class="space-y-2">
                                    <p class="text-[11px] text-slate-550 dark:text-slate-400 font-bold">Giảng viên: Cô Trần Thị Thu Thủy</p>
                                    <div class="grid grid-cols-2 gap-y-1.5 text-[10.5px] font-semibold text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-2.5">
                                        <div>Mã lớp: <span class="text-slate-800 dark:text-slate-200 font-mono font-bold">PY201_L01</span></div>
                                        <div>Tổng tiết: <span class="text-slate-855 dark:text-slate-200 font-bold font-mono">15</span></div>
                                        <div>Có mặt: <span class="text-emerald-600 font-bold font-mono">13</span></div>
                                        <div>Vắng: <span class="text-rose-500 font-bold font-mono">0</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card 3: PH102 --}}
                        <div onclick="window.location.href='{{ route('student.classes.detail', ['class' => 'PH102']) }}'" class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer overflow-hidden group flex flex-col min-h-[250px] shrink-0 w-[280px] sm:w-[320px] snap-start">
                            <div class="h-32 bg-gradient-to-br from-red-500 to-red-700 p-5 flex flex-col justify-end">
                                <h3 class="text-sm font-black text-white leading-tight">Vật lý đại cương 2</h3>
                            </div>
                            <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                                <div class="space-y-2">
                                    <p class="text-[11px] text-slate-550 dark:text-slate-400 font-bold">Giảng viên: Thầy Lâm Văn Tiến</p>
                                    <div class="grid grid-cols-2 gap-y-1.5 text-[10.5px] font-semibold text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-2.5">
                                        <div>Mã lớp: <span class="text-slate-800 dark:text-slate-200 font-mono font-bold">PH102_L04</span></div>
                                        <div>Tổng tiết: <span class="text-slate-855 dark:text-slate-200 font-bold font-mono">14</span></div>
                                        <div>Có mặt: <span class="text-emerald-600 font-bold font-mono">10</span></div>
                                        <div>Vắng: <span class="text-rose-600 font-bold font-mono">4</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card 4: NET301 --}}
                        <div onclick="window.location.href='{{ route('student.classes.detail', ['class' => 'NET301']) }}'" class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer overflow-hidden group flex flex-col min-h-[250px] shrink-0 w-[280px] sm:w-[320px] snap-start">
                            <div class="h-32 bg-gradient-to-br from-blue-600 to-blue-800 p-5 flex flex-col justify-end">
                                <h3 class="text-sm font-black text-white leading-tight">Lý thuyết Mạng Máy Tính</h3>
                            </div>
                            <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                                <div class="space-y-2">
                                    <p class="text-[11px] text-slate-550 dark:text-slate-400 font-bold">Giảng viên: TS. Lê Quang Linh</p>
                                    <div class="grid grid-cols-2 gap-y-1.5 text-[10.5px] font-semibold text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-2.5">
                                        <div>Mã lớp: <span class="text-slate-800 dark:text-slate-200 font-mono font-bold">NET301_L01</span></div>
                                        <div>Tổng tiết: <span class="text-slate-855 dark:text-slate-200 font-bold font-mono">12</span></div>
                                        <div>Có mặt: <span class="text-emerald-600 font-bold font-mono">11</span></div>
                                        <div>Vắng: <span class="text-rose-500 font-bold font-mono">0</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Dạng danh sách rút gọn --}}
                    <div x-show="showList" x-transition class="space-y-3">
                        <div onclick="window.location.href='{{ route('student.classes.detail', ['class' => 'DB101']) }}'" class="bg-slate-50 dark:bg-slate-950/40 border border-slate-150 dark:border-slate-800 p-4 rounded-2xl flex justify-between items-center cursor-pointer active:bg-slate-100 transition-colors">
                            <div class="space-y-1">
                                <span class="text-[9px] font-mono font-bold bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded uppercase">DB101</span>
                                <h4 class="text-xs font-black text-slate-800 dark:text-white leading-tight">Thiết kế &amp; Quản trị SQL</h4>
                                <p class="text-[10px] text-slate-550 dark:text-slate-400 mt-1 font-semibold">Tỷ lệ: 93.8% • GV: Thầy Lê Hoàng Đạt • Học phần: 16 tiết (Có mặt: 15, Vắng: 0)</p>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-slate-400"><path d="m9 18 6-6-6-6"></path></svg>
                        </div>
                        <div onclick="window.location.href='{{ route('student.classes.detail', ['class' => 'PY201']) }}'" class="bg-slate-50 dark:bg-slate-950/40 border border-slate-150 dark:border-slate-800 p-4 rounded-2xl flex justify-between items-center cursor-pointer active:bg-slate-100 transition-colors">
                            <div class="space-y-1">
                                <span class="text-[9px] font-mono font-bold bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded uppercase">PY201</span>
                                <h4 class="text-xs font-black text-slate-800 dark:text-white leading-tight">Phát triển Web Python</h4>
                                <p class="text-[10px] text-slate-550 dark:text-slate-400 mt-1 font-semibold">Tỷ lệ: 86.7% • GV: Cô Trần Thị Thu Thủy • Học phần: 15 tiết (Có mặt: 13, Vắng: 0)</p>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-slate-400"><path d="m9 18 6-6-6-6"></path></svg>
                        </div>
                        <div onclick="window.location.href='{{ route('student.classes.detail', ['class' => 'PH102']) }}'" class="bg-white border border-rose-200 p-4 rounded-2xl flex justify-between items-center cursor-pointer active:bg-rose-50/20 transition-colors">
                            <div class="space-y-1">
                                <span class="text-[9px] font-mono font-bold bg-rose-50 text-rose-600 px-1.5 py-0.5 rounded uppercase">PH102</span>
                                <h4 class="text-xs font-black text-slate-800 dark:text-white leading-tight">Vật lý đại cương 2</h4>
                                <p class="text-[10px] text-rose-600 mt-1 font-bold">Tỷ lệ: 71.4% • GV: Thầy Lâm Văn Tiến • Học phần: 14 tiết (Có mặt: 10, Vắng: 4)</p>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-rose-455"><path d="m9 18 6-6-6-6"></path></svg>
                        </div>
                        <div onclick="window.location.href='{{ route('student.classes.detail', ['class' => 'NET301']) }}'" class="bg-slate-50 dark:bg-slate-950/40 border border-slate-150 dark:border-slate-800 p-4 rounded-2xl flex justify-between items-center cursor-pointer active:bg-slate-100 transition-colors">
                            <div class="space-y-1">
                                <span class="text-[9px] font-mono font-bold bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded uppercase">NET301</span>
                                <h4 class="text-xs font-black text-slate-800 dark:text-white leading-tight">Lý thuyết Mạng Máy Tính</h4>
                                <p class="text-[10px] text-slate-550 dark:text-slate-400 mt-1 font-semibold">Tỷ lệ: 91.7% • GV: TS. Lê Quang Linh • Học phần: 12 tiết (Có mặt: 11, Vắng: 0)</p>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-slate-400"><path d="m9 18 6-6-6-6"></path></svg>
                        </div>
                    </div>
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
                            <div onclick="window.location.href='{{ route('student.classes.detail', ['class' => 'PH102']) }}'" class="p-5 bg-slate-50 border border-slate-200 hover:border-rose-300 cursor-pointer rounded-2xl transition-all flex flex-col md:flex-row md:items-center justify-between gap-5">
                                <div class="space-y-1 md:max-w-xs w-full font-sans">
                                    <span class="bg-slate-200 text-slate-700 px-2 py-0.5 rounded text-[9px] font-mono font-bold block w-fit uppercase">PH102</span>
                                    <h4 class="text-xs font-black text-slate-900 leading-snug pt-1">PH102: Vật lý đại cương 2</h4>
                                    <span class="text-[10px] text-slate-455 block font-semibold">Thầy Lâm Văn Tiến | 2 Tín chỉ</span>
                                </div>
                                <div class="flex-1 grid grid-cols-2 sm:grid-cols-3 gap-4 font-semibold text-slate-700 font-sans">
                                    <div><span class="text-[9px] uppercase font-bold text-slate-404 block select-none">Tỷ lệ hiện tại</span><span class="text-sm font-black font-mono text-rose-600 block mt-0.5">72%</span></div>
                                    <div><span class="text-[9px] uppercase font-bold text-slate-404 block select-none">Số buổi vắng</span><span class="text-sm font-black font-mono text-slate-900 block mt-0.5">4 / 14 tiết</span></div>
                                    <div class="col-span-2 sm:col-span-1"><span class="text-[9px] uppercase font-bold text-slate-404 block select-none">Ngưỡng vắng tối đa</span><span class="text-sm font-black font-mono text-slate-500 block mt-0.5">3 tiết vắng</span></div>
                                </div>
                                <div>
                                    <button onclick="event.stopPropagation(); window.location.href='{{ route('student.leaves.index') }}'" class="bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 font-bold text-[10px] px-3 py-2 rounded-xl transition-all shadow-sm">Nộp minh chứng</button>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
        </div>{{-- end hidden lg:block --}}
        
        {{-- MOBILE STATS --}}
        <div class="lg:hidden w-full h-full font-sans">
            <div class="p-4 pb-28 space-y-4 animate-in fade-in slide-in-from-bottom-2 duration-300">

                {{-- HERO HEADER --}}
                <div class="bg-gradient-to-br from-blue-600 via-indigo-650 to-indigo-800 text-white rounded-2xl p-5 relative overflow-hidden shadow-md border border-blue-500/30">
                    <div class="absolute right-[-10px] bottom-[-10px] w-36 h-36 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute right-4 top-4 flex flex-col items-end opacity-30 select-none">
                        <div class="w-8 h-1 bg-white/70 mb-0.5 rounded-full"></div>
                        <div class="w-5 h-1 bg-white/70 mb-0.5 rounded-full"></div>
                        <div class="w-7 h-1 bg-white/70 rounded-full"></div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white/10 backdrop-blur-md rounded-full text-[9px] font-extrabold uppercase tracking-widest mb-3 border border-white/15">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-200"><path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"></path><path d="M22 10v6"></path><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"></path></svg>
                        Cao đẳng Kỹ thuật Cao Thắng
                    </span>
                    <h1 class="text-lg font-black text-white leading-tight tracking-tight">Thống kê chuyên cần</h1>
                    <p class="text-[10.5px] text-blue-100/85 font-medium mt-1.5 leading-relaxed">Báo cáo hiệu suất chuyên cần, xu hướng học thuật và dự báo rủi ro cấm thi.</p>
                </div>

                {{-- WARNING BANNER --}}
                <div onclick="document.getElementById('mobile-warning-section').scrollIntoView({behavior:'smooth'})" class="p-3.5 rounded-2xl border bg-rose-50 border-rose-200 flex items-start gap-3 cursor-pointer active:bg-rose-100 transition-colors">
                    <span class="p-2 rounded-xl shrink-0 bg-rose-100 text-rose-600 mt-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>
                    </span>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-xs font-black text-rose-900 leading-tight">Cảnh báo: 1 môn học nguy cơ cấm thi!</h3>
                        <p class="text-[10px] font-semibold text-rose-600 mt-0.5 leading-relaxed">Vắng quá 20% số tiết học. Nhấn để xem chi tiết và nộp đơn giải trình.</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-rose-400 mt-1"><path d="m9 18 6-6-6-6"></path></svg>
                </div>

                {{-- OVERVIEW STATS --}}
                <div class="bg-white border border-slate-100 rounded-2xl p-4 shadow-xs">
                    <h3 class="text-[10px] font-black text-slate-600 uppercase tracking-widest mb-4">Tỷ lệ chuyên cần tổng quan</h3>
                    <div class="flex items-center gap-4">
                        {{-- Donut Ring --}}
                        <div class="relative shrink-0 w-[88px] h-[88px] flex items-center justify-center">
                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 96 96">
                                <circle cx="48" cy="48" r="38" stroke="#F1F5F9" stroke-width="10" fill="transparent"></circle>
                                <circle cx="48" cy="48" r="38" stroke="#2563EB" stroke-width="10" fill="transparent"
                                    stroke-dasharray="238.76"
                                    stroke-dashoffset="19.31"
                                    stroke-linecap="round"
                                    class="transition-all duration-700"></circle>
                            </svg>
                            <div class="absolute flex flex-col items-center">
                                <span class="text-sm font-black text-slate-900 leading-none">91.9%</span>
                                <span class="text-[7px] text-slate-400 font-bold uppercase tracking-wide mt-0.5">Tích luỹ</span>
                            </div>
                        </div>
                        {{-- Stats Grid --}}
                        <div class="flex-1 grid grid-cols-2 gap-2">
                            <div class="p-2.5 bg-blue-50/60 rounded-xl border border-blue-100 text-center">
                                <span class="block text-[7.5px] text-blue-500 font-extrabold uppercase tracking-wide">Có mặt</span>
                                <span class="text-sm font-black text-blue-700 mt-0.5 font-mono block leading-none">89</span>
                                <span class="text-[8px] text-blue-400 font-bold">tiết</span>
                            </div>
                            <div class="p-2.5 bg-rose-50/60 rounded-xl border border-rose-100 text-center">
                                <span class="block text-[7.5px] text-rose-500 font-extrabold uppercase tracking-wide">Vắng</span>
                                <span class="text-sm font-black text-rose-600 mt-0.5 font-mono block leading-none">6</span>
                                <span class="text-[8px] text-rose-400 font-bold">tiết</span>
                            </div>
                            <div class="p-2.5 bg-amber-50/60 rounded-xl border border-amber-100 text-center">
                                <span class="block text-[7.5px] text-amber-500 font-extrabold uppercase tracking-wide">Đi muộn</span>
                                <span class="text-sm font-black text-amber-600 mt-0.5 font-mono block leading-none">3</span>
                                <span class="text-[8px] text-amber-400 font-bold">lần</span>
                            </div>
                            <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 text-center">
                                <span class="block text-[7.5px] text-slate-400 font-extrabold uppercase tracking-wide">Sàn tối thiểu</span>
                                <span class="text-sm font-black text-slate-600 mt-0.5 font-mono block leading-none">80%</span>
                                <span class="text-[8px] text-slate-400 font-bold">chuẩn</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SUBJECT CARDS --}}
                <div class="space-y-2.5">
                    <h3 class="text-[10px] font-black text-slate-600 uppercase tracking-widest pl-0.5">Biến động chuyên cần theo môn học</h3>

                    {{-- DB101 --}}
                    <div onclick="window.location.href='{{ route('student.classes.detail', ['class' => 'DB101']) }}'" class="bg-white rounded-2xl border border-slate-150 p-4 shadow-xs cursor-pointer active:scale-[0.98] transition-all duration-150">
                        <div class="flex justify-between items-start mb-3">
                            <div class="space-y-0.5 flex-1 min-w-0 pr-3">
                                <span class="text-[9px] font-mono font-black bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded uppercase">DB101</span>
                                <h4 class="text-xs font-black text-slate-800 leading-tight">Thiết kế &amp; Quản trị SQL</h4>
                                <p class="text-[10px] text-slate-400 font-semibold">Thầy Lê Hoàng Đạt</p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-xl font-black text-emerald-600 font-mono leading-none block">93.8%</span>
                                <span class="text-[9px] text-slate-400 font-semibold">15/16 tiết</span>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <div class="flex justify-between text-[9px] font-bold text-slate-400 uppercase">
                                <span>Chuyên cần</span><span class="text-emerald-500">Tốt</span>
                            </div>
                            <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-500 rounded-full" style="width:93.8%"></div>
                            </div>
                        </div>
                    </div>

                    {{-- PY201 --}}
                    <div onclick="window.location.href='{{ route('student.classes.detail', ['class' => 'PY201']) }}'" class="bg-white rounded-2xl border border-slate-150 p-4 shadow-xs cursor-pointer active:scale-[0.98] transition-all duration-150">
                        <div class="flex justify-between items-start mb-3">
                            <div class="space-y-0.5 flex-1 min-w-0 pr-3">
                                <span class="text-[9px] font-mono font-black bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded uppercase">PY201</span>
                                <h4 class="text-xs font-black text-slate-800 leading-tight">Phát triển Web Python</h4>
                                <p class="text-[10px] text-slate-400 font-semibold">Cô Trần Thị Thu Thủy</p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-xl font-black text-blue-600 font-mono leading-none block">86.7%</span>
                                <span class="text-[9px] text-slate-400 font-semibold">13/15 tiết</span>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <div class="flex justify-between text-[9px] font-bold text-slate-400 uppercase">
                                <span>Chuyên cần</span><span class="text-blue-500">Bình thường</span>
                            </div>
                            <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-blue-400 to-blue-500 rounded-full" style="width:86.7%"></div>
                            </div>
                        </div>
                    </div>

                    {{-- PH102 — WARNING --}}
                    <div onclick="window.location.href='{{ route('student.classes.detail', ['class' => 'PH102']) }}'" class="bg-white rounded-2xl border-2 border-rose-300 p-4 shadow-sm cursor-pointer active:scale-[0.98] transition-all duration-150">
                        <div class="flex justify-between items-start mb-3">
                            <div class="space-y-0.5 flex-1 min-w-0 pr-3">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-[9px] font-mono font-black bg-rose-50 text-rose-600 px-1.5 py-0.5 rounded uppercase">PH102</span>
                                    <span class="text-[8px] font-black bg-rose-500 text-white px-1.5 py-0.5 rounded-full uppercase tracking-wide">⚠ Cảnh báo</span>
                                </div>
                                <h4 class="text-xs font-black text-slate-800 leading-tight">Vật lý đại cương 2</h4>
                                <p class="text-[10px] text-slate-400 font-semibold">Thầy Lâm Văn Tiến</p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-xl font-black text-rose-600 font-mono leading-none block">71.4%</span>
                                <span class="text-[9px] text-rose-400 font-semibold">10/14 tiết</span>
                            </div>
                        </div>
                        <div class="space-y-1 mb-3">
                            <div class="flex justify-between text-[9px] font-bold uppercase">
                                <span class="text-slate-400">Chuyên cần</span><span class="text-rose-500">Nguy cơ cấm thi</span>
                            </div>
                            <div class="h-1.5 w-full bg-rose-100 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-rose-400 to-rose-600 rounded-full" style="width:71.4%"></div>
                            </div>
                        </div>
                        <div class="pt-3 border-t border-rose-100 flex justify-between items-center">
                            <p class="text-[10px] text-rose-600 font-bold">Vắng 4 tiết — vượt ngưỡng cho phép</p>
                            <button onclick="event.stopPropagation(); window.location.href='{{ route('student.leaves.index') }}'" class="bg-rose-600 text-white font-black text-[9px] uppercase tracking-wider px-3 py-1.5 rounded-xl border-none shrink-0 active:scale-95 cursor-pointer">Nộp đơn</button>
                        </div>
                    </div>

                    {{-- NET301 --}}
                    <div onclick="window.location.href='{{ route('student.classes.detail', ['class' => 'NET301']) }}'" class="bg-white rounded-2xl border border-slate-150 p-4 shadow-xs cursor-pointer active:scale-[0.98] transition-all duration-150">
                        <div class="flex justify-between items-start mb-3">
                            <div class="space-y-0.5 flex-1 min-w-0 pr-3">
                                <span class="text-[9px] font-mono font-black bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded uppercase">NET301</span>
                                <h4 class="text-xs font-black text-slate-800 leading-tight">Lý thuyết Mạng Máy Tính</h4>
                                <p class="text-[10px] text-slate-400 font-semibold">TS. Lê Quang Linh</p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-xl font-black text-emerald-600 font-mono leading-none block">91.7%</span>
                                <span class="text-[9px] text-slate-400 font-semibold">11/12 tiết</span>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <div class="flex justify-between text-[9px] font-bold text-slate-400 uppercase">
                                <span>Chuyên cần</span><span class="text-emerald-500">Tốt</span>
                            </div>
                            <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-500 rounded-full" style="width:91.7%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- WARNING RISK SECTION --}}
                <div id="mobile-warning-section" class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs space-y-4 scroll-mt-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 select-none">
                        <div class="flex items-center gap-2">
                            <span class="bg-rose-50 p-1.5 rounded-xl border border-rose-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-rose-600"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>
                            </span>
                            <h3 class="text-[10px] font-black uppercase text-slate-900 tracking-wider">Cảnh báo rủi ro học phần</h3>
                        </div>
                        <span class="text-[9px] font-black text-rose-600 font-mono">Ngưỡng 80%</span>
                    </div>
                    <div class="p-3 bg-yellow-50/75 border border-yellow-200 rounded-xl text-slate-700 text-[10.5px] font-medium leading-relaxed">
                        🚨 <strong>LƯU Ý:</strong> Sinh viên cần đạt tối thiểu <strong>80%</strong> số tiết mới đủ điều kiện dự thi cuối kỳ. Nộp đơn giải trình kèm minh chứng nếu vắng có phép.
                    </div>
                    <div onclick="window.location.href='{{ route('student.classes.detail', ['class' => 'PH102']) }}'" class="p-4 bg-slate-50 border border-slate-200 hover:border-rose-300 cursor-pointer rounded-2xl transition-all space-y-3">
                        <div class="flex justify-between items-start">
                            <div class="space-y-1">
                                <span class="bg-slate-200 text-slate-700 px-2 py-0.5 rounded text-[9px] font-mono font-bold uppercase">PH102</span>
                                <h4 class="text-xs font-black text-slate-900 leading-snug">Vật lý đại cương 2</h4>
                                <span class="text-[10px] text-slate-500 block font-semibold">Thầy Lâm Văn Tiến | 2 Tín chỉ</span>
                            </div>
                            <span class="text-xl font-black font-mono text-rose-600">72%</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="p-2 bg-white rounded-xl border border-slate-200">
                                <span class="text-[8px] uppercase font-bold text-slate-400 block">Tỷ lệ</span>
                                <span class="text-xs font-black font-mono text-rose-600 block mt-0.5">72%</span>
                            </div>
                            <div class="p-2 bg-white rounded-xl border border-slate-200">
                                <span class="text-[8px] uppercase font-bold text-slate-400 block">Vắng</span>
                                <span class="text-xs font-black font-mono text-slate-800 block mt-0.5">4/14 tiết</span>
                            </div>
                            <div class="p-2 bg-white rounded-xl border border-slate-200">
                                <span class="text-[8px] uppercase font-bold text-slate-400 block">Tối đa</span>
                                <span class="text-xs font-black font-mono text-slate-500 block mt-0.5">3 tiết</span>
                            </div>
                        </div>
                        <button onclick="event.stopPropagation(); window.location.href='{{ route('student.leaves.index') }}'" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-black text-[10px] uppercase tracking-wider px-4 py-2.5 rounded-xl border-none active:scale-95 cursor-pointer transition-all">
                            Nộp minh chứng ngay
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>

