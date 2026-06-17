<x-app-layout variant="student" pageTitle="Bảng điều khiển">
    {{-- DESKTOP DASHBOARD --}}
    <div class="hidden lg:block w-full h-full p-6 lg:p-8 max-w-7xl mx-auto">
        <div class="space-y-6 animate-in fade-in duration-350">
            {{-- Alert --}}
            <div class="bg-amber-50 border border-amber-200 rounded-3xl p-5 flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-500 shrink-0 select-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-triangle-alert w-5.5 h-5.5 text-amber-500" aria-hidden="true"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>
                </div>
                <div class="space-y-1.5 flex-1">
                    <span class="text-[10px] font-black uppercase text-amber-800 tracking-wider">Cảnh báo chuyên cần yếu tích lũy</span>
                    <h4 class="text-sm font-bold text-slate-900 leading-tight">Bạn có học phần <span class="font-extrabold text-red-650 font-mono">PH102: Vật lý đại cương 2</span> đang ở tỉ lệ chuyên cần <strong class="text-red-500 font-bold">72%</strong> (dưới mức sàn tối thiểu 80%).</h4>
                    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 pt-1.5 text-xs text-slate-550 font-medium">
                        <span>Mức cảnh báo: <strong class="text-red-500 font-bold">Mức 2 (Nguy cơ đình chỉ thi)</strong></span>
                        <span>Số buổi đã vắng: <strong class="text-slate-800 font-bold">4 Buổi</strong></span>
                        <span>Số tiết đã vắng: <strong class="text-slate-800 font-bold">8 Tiết học</strong></span>
                    </div>
                </div>
                <button class="shrink-0 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs px-4 py-2.5 rounded-xl border-none cursor-pointer transition-all active:scale-95 select-none">Gửi đơn giải trình phép</button>
            </div>

            {{-- Welcome & Stats --}}
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
                <div class="xl:col-span-8 bg-white border border-slate-200/90 rounded-3xl p-6 shadow-sm flex flex-col justify-between min-h-[220px] relative overflow-hidden transition-colors">
                    <div class="absolute right-0 bottom-0 w-32 h-32 bg-blue-50/60 rounded-full blur-2xl pointer-events-none"></div>
                    <div class="space-y-2">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-700/90 rounded-full text-[9px] font-extrabold uppercase tracking-widest border border-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-graduation-cap w-3.5 h-3.5 text-blue-600" aria-hidden="true"><path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"></path><path d="M22 10v6"></path><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"></path></svg> 
                            Trường Cao đẳng kỹ thuật Cao Thắng
                        </span>
                        <h1 class="text-xl font-bold tracking-tight text-slate-900 pt-1">Xin chào, Trần Minh Hoàng! 👋</h1>
                        <p class="text-xs text-slate-500 font-medium leading-relaxed max-w-xl">Chào mừng trở lại bảng chuyên bạ. Hệ thống ghi nhận hồ sơ học bạ hiện diện của bạn đạt tiêu chuẩn cao. Duy trì hiện diện của bạn để giữ vững điều kiện thi cuối môn khoa Công nghệ thông tin.</p>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-5 border-t border-slate-100">
                        <div class="p-2.5 bg-slate-50/55 rounded-2xl border border-slate-201/50">
                            <span class="block text-[8px] font-black text-slate-400 uppercase tracking-wider mb-1">Môn đang học</span>
                            <strong class="text-slate-800 text-sm font-mono font-black">5 Lớp học</strong>
                        </div>
                        <div class="p-2.5 bg-slate-50/55 rounded-2xl border border-slate-201/50">
                            <span class="block text-[8px] font-black text-slate-400 uppercase tracking-wider mb-1">Đã tham gia</span>
                            <strong class="text-slate-800 text-sm font-mono font-black">68 Tiết học</strong>
                        </div>
                        <div class="p-2.5 bg-slate-50/55 rounded-2xl border border-slate-201/50">
                            <span class="block text-[8px] font-black text-slate-400 uppercase tracking-wider mb-1">Tỷ lệ chuyên cần</span>
                            <strong class="text-blue-700 text-sm font-mono font-black">92.4%</strong>
                        </div>
                        <div class="p-2.5 bg-slate-50/55 rounded-2xl border border-slate-201/50">
                            <span class="block text-[8px] font-black text-slate-400 uppercase tracking-wider mb-1">Cảnh báo</span>
                            <strong class="text-sm font-mono font-black text-red-500">1 Cảnh báo</strong>
                        </div>
                    </div>
                </div>
                
                <div class="xl:col-span-4 bg-gradient-to-br from-blue-700 to-indigo-850 text-white rounded-3xl p-6 shadow-md relative overflow-hidden flex flex-col justify-between min-h-[220px] border border-blue-620/20">
                    <div class="absolute top-0 right-0 bg-white/10 text-white font-bold text-[9px] uppercase tracking-wider px-3.5 py-1.5 rounded-bl-2xl">SAMS ACTIVE PORTAL</div>
                    <div class="space-y-1">
                        <div class="flex items-center gap-1.5 text-blue-200">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full animate-ping shrink-0"></span>
                            <span class="text-[10px] uppercase font-bold tracking-wider">Phiên đang diễn ra</span>
                        </div>
                        <h3 class="text-base font-extrabold text-white pt-1">NET301: Lý thuyết Mạng Máy Tính</h3>
                        <p class="text-[11px] text-blue-100 font-semibold">Giảng viên: TS. Lê Quang Linh</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-white/10 flex items-center justify-between">
                        <div class="space-y-0.5">
                            <span class="text-[9px] text-blue-200 font-bold block uppercase leading-none">Thời gian còn lại</span>
                            <span class="text-xs font-mono font-bold text-emerald-400">00:00</span>
                        </div>
                        <button class="justify-center focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none focus:ring-blue-500 bg-white hover:bg-slate-100 text-blue-750 font-bold text-xs px-4 py-2.5 rounded-xl cursor-pointer shadow-md select-none border-none flex items-center gap-1.5 active:scale-95 transition-all outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-qr-code w-4.5 h-4.5 text-blue-600" aria-hidden="true"><rect width="5" height="5" x="3" y="3" rx="1"></rect><rect width="5" height="5" x="16" y="3" rx="1"></rect><rect width="5" height="5" x="3" y="16" rx="1"></rect><path d="M21 16h-3a2 2 0 0 0-2 2v3"></path><path d="M21 21v.01"></path><path d="M12 7v3a2 2 0 0 1-2 2H7"></path><path d="M3 12h.01"></path><path d="M12 3h.01"></path><path d="M12 16v.01"></path><path d="M16 12h1"></path><path d="M21 12v.01"></path><path d="M12 21v-1"></path></svg>
                            Điểm danh ngay
                        </button>
                    </div>
                </div>
            </div>


            {{-- Recent Classes --}}
            <div class="space-y-3.5">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-black text-slate-700 uppercase tracking-widest pl-0.5 select-none">Lớp học của tôi gần nhất (Tối ưu 4 lớp)</h3>
                    <a href="{{ route('student.classes.list') }}" class="text-xs text-blue-600 hover:underline border-none bg-transparent font-bold cursor-pointer transition-all">Xem tất cả học phần</a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white rounded-3xl p-5 border shadow-sm transition-all flex flex-col justify-between min-h-[200px] border-slate-200 hover:border-blue-200"><div class="space-y-1"><div class="flex justify-between items-center text-[10px] font-mono font-bold text-slate-400 select-none"><span>CS402</span><span class="text-[9px] px-1.5 py-0.5 bg-emerald-50 text-emerald-700 rounded-md font-bold uppercase select-none">Đạt chuẩn</span></div><h3 class="text-sm font-bold text-slate-800 leading-tight pt-1 truncate">CS402: Thuật toán Nâng cao</h3><p class="text-[11px] text-slate-500 truncate">Lý thuyết &amp; Thực hành Thuật toán Nâng Cao</p></div><div class="py-3 border-t border-b border-slate-100/70 my-3.5 space-y-2 text-xs"><div class="flex justify-between font-medium"><span class="text-slate-450">Giảng viên:</span><span class="font-semibold text-slate-800 truncate max-w-[120px]">TS. Nguyễn Mạnh Hùng</span></div><div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden mt-1.5"><div class="h-full rounded-full bg-blue-600" style="width: 96.8%;"></div></div><div class="flex justify-between pt-0.5 font-bold text-[10px] text-slate-500"><span>Chương trình: 96.8%</span><span>14/15 tiết</span></div></div><button class="w-full py-2 bg-blue-50/50 hover:bg-blue-100/70 text-blue-700 font-bold text-xs rounded-xl border-none cursor-pointer transition-all leading-none active:scale-95 flex items-center justify-center gap-1"><span>Xem chi tiết</span><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right w-3.5 h-3.5 shrink-0" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg></button></div>
                    <div class="bg-white rounded-3xl p-5 border shadow-sm transition-all flex flex-col justify-between min-h-[200px] border-slate-200 hover:border-blue-200"><div class="space-y-1"><div class="flex justify-between items-center text-[10px] font-mono font-bold text-slate-400 select-none"><span>DB101</span><span class="text-[9px] px-1.5 py-0.5 bg-emerald-50 text-emerald-700 rounded-md font-bold uppercase select-none">Đạt chuẩn</span></div><h3 class="text-sm font-bold text-slate-800 leading-tight pt-1 truncate">DB101: Thiết kế &amp; Quản trị SQL</h3><p class="text-[11px] text-slate-500 truncate">Cơ sở dữ liệu Quan Hệ &amp; Tối Ưu Hóa</p></div><div class="py-3 border-t border-b border-slate-100/70 my-3.5 space-y-2 text-xs"><div class="flex justify-between font-medium"><span class="text-slate-450">Giảng viên:</span><span class="font-semibold text-slate-800 truncate max-w-[120px]">Thầy Lê Hoàng Đạt</span></div><div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden mt-1.5"><div class="h-full rounded-full bg-blue-600" style="width: 92.5%;"></div></div><div class="flex justify-between pt-0.5 font-bold text-[10px] text-slate-500"><span>Chương trình: 92.5%</span><span>15/16 tiết</span></div></div><button class="w-full py-2 bg-blue-50/50 hover:bg-blue-100/70 text-blue-700 font-bold text-xs rounded-xl border-none cursor-pointer transition-all leading-none active:scale-95 flex items-center justify-center gap-1"><span>Xem chi tiết</span><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right w-3.5 h-3.5 shrink-0" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg></button></div>
                    <div class="bg-white rounded-3xl p-5 border shadow-sm transition-all flex flex-col justify-between min-h-[200px] border-slate-200 hover:border-blue-200"><div class="space-y-1"><div class="flex justify-between items-center text-[10px] font-mono font-bold text-slate-400 select-none"><span>PY201</span><span class="text-[9px] px-1.5 py-0.5 bg-emerald-50 text-emerald-700 rounded-md font-bold uppercase select-none">Đạt chuẩn</span></div><h3 class="text-sm font-bold text-slate-800 leading-tight pt-1 truncate">PY201: Phát triển Web Python</h3><p class="text-[11px] text-slate-500 truncate">Lập trình Fullstack với Django &amp; FastAPI</p></div><div class="py-3 border-t border-b border-slate-100/70 my-3.5 space-y-2 text-xs"><div class="flex justify-between font-medium"><span class="text-slate-450">Giảng viên:</span><span class="font-semibold text-slate-800 truncate max-w-[120px]">Cô Trần Thị Thu Thủy</span></div><div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden mt-1.5"><div class="h-full rounded-full bg-blue-600" style="width: 94%;"></div></div><div class="flex justify-between pt-0.5 font-bold text-[10px] text-slate-500"><span>Chương trình: 94%</span><span>13/15 tiết</span></div></div><button class="w-full py-2 bg-blue-50/50 hover:bg-blue-100/70 text-blue-700 font-bold text-xs rounded-xl border-none cursor-pointer transition-all leading-none active:scale-95 flex items-center justify-center gap-1"><span>Xem chi tiết</span><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right w-3.5 h-3.5 shrink-0" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg></button></div>
                    <div class="bg-white rounded-3xl p-5 border shadow-sm transition-all flex flex-col justify-between min-h-[200px] border-rose-220 bg-rose-50/5"><div class="space-y-1"><div class="flex justify-between items-center text-[10px] font-mono font-bold text-slate-400 select-none"><span>PH102</span><span class="text-[9px] px-1.5 py-0.5 bg-rose-100 text-rose-650 rounded-md font-bold uppercase select-none">Cảnh cáo vắng</span></div><h3 class="text-sm font-bold text-slate-800 leading-tight pt-1 truncate">PH102: Vật lý đại cương 2</h3><p class="text-[11px] text-slate-500 truncate">Vật lý Ứng dụng Điện học &amp; Từ học</p></div><div class="py-3 border-t border-b border-slate-100/70 my-3.5 space-y-2 text-xs"><div class="flex justify-between font-medium"><span class="text-slate-450">Giảng viên:</span><span class="font-semibold text-slate-800 truncate max-w-[120px]">Thầy Lâm Văn Tiến</span></div><div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden mt-1.5"><div class="h-full rounded-full bg-red-500" style="width: 72%;"></div></div><div class="flex justify-between pt-0.5 font-bold text-[10px] text-slate-500"><span>Chương trình: 72%</span><span>10/14 tiết</span></div></div><button class="w-full py-2 bg-blue-50/50 hover:bg-blue-100/70 text-blue-700 font-bold text-xs rounded-xl border-none cursor-pointer transition-all leading-none active:scale-95 flex items-center justify-center gap-1"><span>Xem chi tiết</span><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right w-3.5 h-3.5 shrink-0" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg></button></div>
                </div>
            </div>

            {{-- History & Chart --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 pt-3">
                <div class="lg:col-span-8 bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-4 flex flex-col justify-between">
                    <div class="space-y-1">
                        <h3 class="text-[13px] font-black text-slate-800 uppercase tracking-wider select-none">Lịch sử điểm danh (10 lần gần nhất)</h3>
                        <p class="text-xs text-slate-400 font-medium">Đối soát kiểm bạ lưu động SAMS</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs font-semibold text-slate-650 border-collapse">
                            <thead>
                                <tr class="border-b border-indigo-50 text-[10px] uppercase tracking-wider text-slate-400 font-bold select-none">
                                    <th class="py-2.5 pr-4 pl-1">Ngày</th>
                                    <th class="py-2.5 px-4 font-sans">Mã Môn</th>
                                    <th class="py-2.5 px-4">Tên môn học</th>
                                    <th class="py-2.5 px-4 text-center">Giờ</th>
                                    <th class="py-2.5 px-4 text-right">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-[11px] font-medium text-slate-600">
                                <tr class="hover:bg-slate-50/40 transition-colors"><td class="py-3 px-1 font-mono text-slate-800 font-bold">04/06/2026</td><td class="py-3 px-4 font-mono text-slate-450 leading-none">DB101</td><td class="py-3 px-4 text-slate-800 font-bold truncate max-w-[170px]">DB101: Thiết kế &amp; Quản trị SQL</td><td class="py-3 px-4 font-mono text-center text-slate-500 font-semibold">08:15</td><td class="py-3 px-4 text-right"><span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 font-extrabold px-2 py-0.5 rounded-full uppercase text-[9.5px] border border-emerald-100">Có mặt</span></td></tr>
                                <tr class="hover:bg-slate-50/40 transition-colors"><td class="py-3 px-1 font-mono text-slate-800 font-bold">03/06/2026</td><td class="py-3 px-4 font-mono text-slate-450 leading-none">PY201</td><td class="py-3 px-4 text-slate-800 font-bold truncate max-w-[170px]">PY201: Phát triển Web Python</td><td class="py-3 px-4 font-mono text-center text-slate-500 font-semibold">13:35</td><td class="py-3 px-4 text-right"><span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 font-extrabold px-2 py-0.5 rounded-full uppercase text-[9.5px] border border-amber-100">Muộn</span></td></tr>
                                <tr class="hover:bg-slate-50/40 transition-colors"><td class="py-3 px-1 font-mono text-slate-800 font-bold">02/06/2026</td><td class="py-3 px-4 font-mono text-slate-450 leading-none">PH102</td><td class="py-3 px-4 text-slate-800 font-bold truncate max-w-[170px]">PH102: Vật lý đại cương 2</td><td class="py-3 px-4 font-mono text-center text-slate-500 font-semibold">15:10</td><td class="py-3 px-4 text-right"><span class="inline-flex items-center gap-1 bg-rose-50 text-rose-700 font-extrabold px-2 py-0.5 rounded-full uppercase text-[9.5px] border border-rose-100">Vắng</span></td></tr>
                                <tr class="hover:bg-slate-50/40 transition-colors"><td class="py-3 px-1 font-mono text-slate-800 font-bold">29/05/2026</td><td class="py-3 px-4 font-mono text-slate-450 leading-none">NET301</td><td class="py-3 px-4 text-slate-800 font-bold truncate max-w-[170px]">NET301: Lý thuyết Mạng Máy Tính</td><td class="py-3 px-4 font-mono text-center text-slate-500 font-semibold">08:05</td><td class="py-3 px-4 text-right"><span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 font-extrabold px-2 py-0.5 rounded-full uppercase text-[9.5px] border border-blue-105">Có phép</span></td></tr>
                                <tr class="hover:bg-slate-50/40 transition-colors"><td class="py-3 px-1 font-mono text-slate-800 font-bold">28/05/2026</td><td class="py-3 px-4 font-mono text-slate-450 leading-none">CS402</td><td class="py-3 px-4 text-slate-800 font-bold truncate max-w-[170px]">CS402: Thuật toán Nâng cao</td><td class="py-3 px-4 font-mono text-center text-slate-500 font-semibold">07:35</td><td class="py-3 px-4 text-right"><span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 font-extrabold px-2 py-0.5 rounded-full uppercase text-[9.5px] border border-emerald-100">Có mặt</span></td></tr>
                                <tr class="hover:bg-slate-50/40 transition-colors"><td class="py-3 px-1 font-mono text-slate-800 font-bold">27/05/2026</td><td class="py-3 px-4 font-mono text-slate-450 leading-none">DB101</td><td class="py-3 px-4 text-slate-800 font-bold truncate max-w-[170px]">DB101: Thiết kế &amp; Quản trị SQL</td><td class="py-3 px-4 font-mono text-center text-slate-500 font-semibold">08:02</td><td class="py-3 px-4 text-right"><span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 font-extrabold px-2 py-0.5 rounded-full uppercase text-[9.5px] border border-emerald-100">Có mặt</span></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="lg:col-span-4 bg-white border border-slate-200 rounded-3xl p-6 shadow-sm flex flex-col justify-between min-h-[300px]">
                    <div class="space-y-1">
                        <h3 class="text-[13px] font-black text-slate-800 uppercase tracking-wider select-none">Biểu đồ chuyên cần</h3>
                        <p class="text-xs text-slate-400 font-medium">Tổng quan cơ cấu 72 tiết học</p>
                    </div>
                    <div class="relative shrink-0 w-32 h-32 mx-auto flex items-center justify-center select-none my-4">
                        <svg class="w-full h-full transform -rotate-90">
                            <circle cx="64" cy="64" r="50" stroke="#F1F5F9" stroke-width="10" fill="transparent"></circle>
                            <circle cx="64" cy="64" r="50" stroke="#2563EB" stroke-width="10" fill="transparent" stroke-dasharray="314.1592653589793" stroke-dashoffset="23.876104167282413" stroke-linecap="round" class="transition-all duration-500"></circle>
                        </svg>
                        <div class="absolute flex flex-col items-center">
                            <span class="text-xl font-mono font-extrabold text-slate-800 leading-none">92.4%</span>
                            <span class="text-[9px] text-slate-400 font-black uppercase tracking-widest mt-1">Hiện diện</span>
                        </div>
                    </div>
                    <div class="space-y-3 pt-2 text-xs">
                        <div class="space-y-1"><div class="flex justify-between font-bold text-slate-600"><span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-blue-600 inline-block shrink-0"></span> Có mặt</span><span class="font-mono font-extrabold text-slate-800">68 Tiết</span></div><div class="w-full h-1 bg-slate-150 rounded overflow-hidden"><div class="h-full bg-blue-600 rounded" style="width: 94.4444%;"></div></div></div>
                        <div class="space-y-1"><div class="flex justify-between font-bold text-slate-600"><span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-red-500 inline-block shrink-0"></span> Vắng không phép</span><span class="font-mono font-extrabold text-red-600">4 Tiết</span></div><div class="w-full h-1 bg-slate-150 rounded overflow-hidden"><div class="h-full bg-red-500 rounded" style="width: 5.5%;"></div></div></div>
                        <div class="space-y-1"><div class="flex justify-between font-bold text-slate-600"><span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-amber-500 inline-block shrink-0"></span> Đi muộn / trễ</span><span class="font-mono font-extrabold text-amber-600">3 Tiết</span></div><div class="w-full h-1 bg-slate-150 rounded overflow-hidden"><div class="h-full bg-amber-500 rounded" style="width: 4.1%;"></div></div></div>
                        <div class="space-y-1"><div class="flex justify-between font-bold text-slate-600"><span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-indigo-400 inline-block shrink-0"></span> Có đơn phép</span><span class="font-mono font-extrabold text-indigo-600">1 Tiết</span></div><div class="w-full h-1 bg-slate-150 rounded overflow-hidden"><div class="h-full bg-indigo-400 rounded" style="width: 1.4%;"></div></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MOBILE DASHBOARD --}}
    <div class="lg:hidden w-full h-full font-sans">
        <div class="p-4 space-y-4 animate-in fade-in slide-in-from-bottom-2 duration-300">
            {{-- Warning --}}
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 flex items-start gap-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-triangle-alert w-5 h-5 text-amber-500 shrink-0 mt-0.5" aria-hidden="true"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>
                <div class="space-y-1 flex-1">
                    <h4 class="text-[10px] font-black text-amber-800 uppercase tracking-widest leading-none">Rủi ro chuyên cần học phần</h4>
                    <p class="text-xs text-amber-800 font-semibold leading-relaxed">Môn <strong class="text-amber-950">PH102: Vật lý đại cương 2</strong> của bạn vắng mặt 4 buổi, tỉ lệ còn <span class="text-red-650 font-black">72%</span>.</p>
                    <button class="mt-2 text-[10px] font-black bg-amber-500 text-white rounded-lg px-2.5 py-1 hover:bg-amber-600 cursor-pointer border-none" id="warning-class-action-btn">Gửi chứng từ giải trình xin nghỉ phép</button>
                </div>
            </div>

            {{-- Welcome --}}
            <div class="bg-gradient-to-br from-blue-600 via-indigo-650 to-indigo-800 text-white rounded-2xl p-6 relative overflow-hidden shadow-md border border-blue-500/30">
                <div class="absolute right-[-10px] bottom-[-10px] w-36 h-36 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute right-4 top-4 flex flex-col items-end opacity-40 font-mono text-[8px] select-none text-white/85"><div class="w-8 h-1 bg-white/60 mb-0.5 rounded-xs"></div><div class="w-6 h-1 bg-white/60 mb-0.5 rounded-xs"></div><div class="w-7 h-1 bg-white/60 rounded-xs"></div></div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white/10 backdrop-blur-md rounded-full text-[9px] font-extrabold uppercase tracking-widest mb-3.5 border border-white/15">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-graduation-cap w-3.5 h-3.5 text-blue-200" aria-hidden="true"><path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"></path><path d="M22 10v6"></path><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"></path></svg> 
                    Cao đẳng Kỹ thuật Cao Thắng
                </span>
                <h1 class="text-lg font-black text-white leading-tight tracking-tight">Chào Trần Minh Hoàng! 👋</h1>
                <p class="text-[10.5px] text-blue-100/90 font-medium mt-1.5 leading-relaxed">Lớp hành chính: <strong class="text-white font-black">CĐ Kỹ thuật Phần mềm 23A</strong>. Duy trì tỷ lệ chuyên cần trên <strong class="text-amber-300 font-black">80%</strong> để bảo toàn điều kiện thi học phần khoa CNTT.</p>
                <div class="mt-4 pt-3 border-t border-white/10 flex items-center justify-between">
                    <div class="flex gap-0.5 opacity-30 select-none">
                        <div class="bg-white h-4" style="width: 1px;"></div><div class="bg-white h-4" style="width: 2.5px;"></div><div class="bg-white h-4" style="width: 1px;"></div><div class="bg-white h-4" style="width: 1.5px;"></div><div class="bg-white h-4" style="width: 4px;"></div><div class="bg-white h-4" style="width: 1px;"></div><div class="bg-white h-4" style="width: 2px;"></div><div class="bg-white h-4" style="width: 1.5px;"></div><div class="bg-white h-4" style="width: 1px;"></div><div class="bg-white h-4" style="width: 3px;"></div><div class="bg-white h-4" style="width: 1.5px;"></div><div class="bg-white h-4" style="width: 1px;"></div>
                    </div>
                    <span class="text-[9px] font-mono text-white/40 tracking-widest leading-none font-bold">RFID ENCRYPTED</span>
                </div>
            </div>

            {{-- Active Class --}}
            <div class="bg-white border border-blue-100 rounded-2xl p-4 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 bg-blue-600 text-white font-black text-[9px] uppercase tracking-widest px-3 py-1.5 rounded-bl-xl flex items-center gap-1.5 shadow-sm leading-none"><span class="w-2 h-2 bg-white rounded-full animate-ping"></span>CỔNG ĐIỂM DANH MỞ</div>
                <div class="flex items-center gap-1.5 text-blue-600 select-none"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock w-4 h-4 shrink-0" aria-hidden="true"><path d="M12 6v6l4 2"></path><circle cx="12" cy="12" r="10"></circle></svg><span class="text-[10px] font-bold uppercase tracking-widest leading-none">Cơ chế địa định vị SAMS</span></div>
                <div class="space-y-2 mt-4">
                    <h4 class="text-[8.5px] font-black text-slate-400 uppercase tracking-widest leading-none">Lớp học hiện tại</h4>
                    <h3 class="text-base font-black text-slate-900 leading-snug">NET301: Lý thuyết Mạng Máy Tính</h3>
                    <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-100 mt-2.5 space-y-2 text-[11px] text-slate-600 font-semibold">
                        <div class="flex items-center gap-2.5"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user w-3.5 h-3.5 text-slate-400 shrink-0" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg><span>Giảng viên học phần: <span class="font-bold text-slate-800">TS. Lê Quang Linh</span></span></div>
                        <div class="flex items-center gap-2.5"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pin w-3.5 h-3.5 text-slate-400 shrink-0" aria-hidden="true"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg><span>Phòng học lý thuyết: <span class="font-bold text-slate-800">Cơ sở A.205 (Lab A lầu 2)</span></span></div>
                        <div class="flex items-center gap-2.5"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock w-3.5 h-3.5 text-slate-400 shrink-0" aria-hidden="true"><path d="M12 6v6l4 2"></path><circle cx="12" cy="12" r="10"></circle></svg><span>Thời gian hết hạn khóa: <span class="text-red-500 font-bold font-mono bg-red-50 border border-red-100 rounded px-1.5 py-0.5">00:00</span></span></div>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <button class="focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none focus:ring-blue-500 px-4 py-2 w-full h-12 bg-blue-600 hover:bg-blue-750 text-white font-extrabold cursor-pointer shadow-lg shadow-blue-500/10 rounded-xl text-xs gap-2 border-none flex items-center justify-center active:scale-[0.98] transition-all animate-pulse" id="mob-dash-btn"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-qr-code w-4.5 h-4.5 shrink-0 text-white stroke-[2.5]" aria-hidden="true"><rect width="5" height="5" x="3" y="3" rx="1"></rect><rect width="5" height="5" x="16" y="3" rx="1"></rect><rect width="5" height="5" x="3" y="16" rx="1"></rect><path d="M21 16h-3a2 2 0 0 0-2 2v3"></path><path d="M21 21v.01"></path><path d="M12 7v3a2 2 0 0 1-2 2H7"></path><path d="M3 12h.01"></path><path d="M12 3h.01"></path><path d="M12 16v.01"></path><path d="M16 12h1"></path><path d="M21 12v.01"></path><path d="M12 21v-1"></path></svg>ĐIỂM DANH GPS/QR NGAY</button>
                </div>
            </div>

            {{-- Mobile Stats --}}
            <div class="bg-white border border-slate-100 rounded-2xl p-4 shadow-xs">
                <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest mb-3">Tỷ Lệ Chuyên Cần Tổng Quan</h3>
                <div class="flex items-center justify-between gap-3">
                    <div class="relative shrink-0 w-24 h-24 flex items-center justify-center">
                        <svg class="w-full h-full transform -rotate-90">
                            <circle cx="48" cy="48" r="38" stroke="#F1F5F9" stroke-width="8" fill="transparent"></circle>
                            <circle cx="48" cy="48" r="38" stroke="#2563EB" stroke-width="8" fill="transparent" stroke-dasharray="238.76104167282426" stroke-dashoffset="18.145839167134632" stroke-linecap="round" class="transition-all duration-500"></circle>
                        </svg>
                        <div class="absolute flex flex-col items-center">
                            <span class="text-base font-black text-slate-850 leading-none">92.4%</span>
                            <span class="text-[8px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">Tích luỹ</span>
                        </div>
                    </div>
                    <div class="flex-1 grid grid-cols-2 gap-2">
                        <div class="p-2 bg-slate-50/50 rounded-xl border border-slate-100 text-center flex flex-col justify-center"><span class="block text-[8px] text-slate-400 font-extrabold uppercase">Có mặt</span><span class="text-xs font-black text-slate-800 mt-1 font-mono">68 / 72 tiết</span></div>
                        <div class="p-2 bg-slate-50/50 rounded-xl border border-slate-100 text-center flex flex-col justify-center"><span class="block text-[8px] text-slate-400 font-extrabold uppercase">Vắng</span><span class="text-xs font-black text-red-600 mt-1 font-mono">4 tiết vắng</span></div>
                        <div class="p-2 bg-slate-50/50 rounded-xl border border-slate-100 text-center flex flex-col justify-center"><span class="block text-[8px] text-slate-400 font-extrabold uppercase">Đi muộn</span><span class="text-xs font-black text-amber-600 mt-1 font-mono">3 tiết trễ</span></div>
                        <div class="p-2 bg-slate-50/50 rounded-xl border border-slate-100 text-center flex flex-col justify-center"><span class="block text-[8px] text-slate-400 font-extrabold uppercase">Có phép</span><span class="text-xs font-black text-blue-600 mt-1 font-mono">1 tiết phép</span></div>
                    </div>
                </div>
            </div>

            {{-- Schedule --}}
            <div class="space-y-2.5">
                <div class="flex items-center justify-between"><h3 class="text-xs font-black text-slate-800 uppercase tracking-widest">Thời Khóa Biểu Hôm Nay</h3><span class="text-[10px] font-bold text-slate-400">Thứ sáu, 05/06</span></div>
                <div class="space-y-2">
                    <div class="bg-white border-l-4 border-blue-500 rounded-xl p-3 border-r border-y border-slate-100 flex items-center justify-between gap-2 shadow-xs"><div class="space-y-1"><h4 class="text-xs font-black text-slate-800">NET301: Lý thuyết Mạng Máy Tính</h4><p class="text-[10px] text-slate-500 flex items-center gap-1 font-medium"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock w-3 h-3 text-slate-400" aria-hidden="true"><path d="M12 6v6l4 2"></path><circle cx="12" cy="12" r="10"></circle></svg> Tiết 4-6 (08:00) • TS. Lê Quang Linh • A.205</p></div><span class="shrink-0 text-[10px] font-black text-blue-600 px-2 py-1 rounded-md bg-blue-50">Đang học</span></div>
                    <div class="bg-white border-l-4 border-slate-300 rounded-xl p-3 border-r border-y border-slate-100 flex items-center justify-between gap-2 shadow-xs"><div class="space-y-1"><h4 class="text-xs font-black text-slate-800">DB101: Thiết kế &amp; Quản trị SQL</h4><p class="text-[10px] text-slate-500 flex items-center gap-1 font-medium"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock w-3 h-3 text-slate-400" aria-hidden="true"><path d="M12 6v6l4 2"></path><circle cx="12" cy="12" r="10"></circle></svg> Tiết 7-9 (13:15) • Thầy Lê Hoàng Đạt • A.201</p></div><span class="shrink-0 text-[10px] font-black text-slate-400 px-2 py-1 rounded-md bg-slate-50">Chưa học</span></div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
