<x-app-layout variant="student" pageTitle="Hồ sơ cá nhân">
    {{-- DESKTOP PROFILE --}}
    <div class="hidden lg:block w-full h-full p-6 lg:p-8 max-w-7xl mx-auto">
        <div class="space-y-6 animate-in fade-in duration-300">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <div class="lg:col-span-8 bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-6">
                    <div class="flex items-center gap-4.5 border-b border-slate-100 pb-5">
                        <div class="w-16 h-16 rounded-2xl border border-blue-200 overflow-hidden shrink-0 shadow-sm"><img alt="Profile" class="w-full h-full object-cover" src="https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?ixlib=rb-1.2.1&amp;auto=format&amp;fit=crop&amp;w=150&amp;h=150&amp;q=80"></div>
                        <div>
                            <span class="bg-blue-50 text-blue-700 border border-blue-100 text-[9px] font-black px-2 py-0.5 rounded uppercase font-mono tracking-wider select-none leading-none">Học viên đào tạo Cao đẳng tuyển</span>
                            <h2 class="text-lg font-bold text-slate-905 mt-1.5 leading-none uppercase tracking-tight">Trần Minh Hoàng</h2>
                            <span class="text-xs font-mono font-bold text-slate-450 leading-none mt-1 shadow-none">Mã số thẻ trường: 0306231108</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs font-semibold text-slate-655 font-sans leading-relaxed">
                        <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl space-y-3 font-sans">
                            <h4 class="text-[10.5px] font-black uppercase text-slate-400 tracking-wider">Lịch trình học bạ Cao Thắng</h4>
                            <div class="flex justify-between border-b border-slate-100 pb-1.5"><span class="text-slate-450 font-medium font-sans">Lớp sinh viên:</span><span class="font-bold text-slate-805 font-sans">CĐ Kỹ thuật Phần mềm 23A</span></div>
                            <div class="flex justify-between border-b border-slate-100 pb-1.5"><span class="text-slate-450 font-medium">Khoa quản học:</span><span class="font-bold text-slate-850">Khoa Công nghệ Thông tin</span></div>
                            <div class="flex justify-between border-b border-slate-100 pb-1.5"><span class="text-slate-450 font-medium">Niên khóa khóa đào:</span><span class="font-bold text-slate-850 font-mono">2023 - 2026 (K23)</span></div>
                            <div class="flex justify-between"><span class="text-slate-450 font-medium">Cố vấn đào tạo:</span><span class="text-blue-700 font-bold">Thầy Lê Minh Triết</span></div>
                        </div>
                        <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl space-y-3 font-sans">
                            <h4 class="text-[10.5px] font-black uppercase text-slate-400 tracking-wider font-sans">Liên lạc cá nhân sinh viên</h4>
                            <div class="flex justify-between border-b border-slate-100 pb-1.5 font-sans"><span class="text-slate-450 font-sans">Email trường cấp:</span><span class="font-mono text-slate-800 font-bold">0306231108@caothang.edu.vn</span></div>
                            <div class="flex justify-between border-b border-slate-100 pb-1.5"><span class="text-slate-450">Số điện thoại di:</span><span class="font-mono text-slate-800 font-bold">0987.654.321</span></div>
                            <div class="flex justify-between border-b border-slate-100 pb-1.5"><span class="text-slate-455">Bản kết nối SAMS:</span><span class="text-emerald-700 font-extrabold uppercase">TẬP CHỮ KÝ VERIFIED</span></div>
                            <div class="flex justify-between"><span class="text-slate-455">Tổng xếp hạng:</span><strong class="text-emerald-750 font-bold">Lao học chuyên cần Tốt</strong></div>
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-4 bg-slate-900 border border-slate-850 text-white rounded-3xl p-6.5 shadow-sm space-y-5 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 border-b border-slate-805 pb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield-check w-5.5 h-5.5 text-emerald-400 shrink-0 select-none" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path><path d="m9 12 2 2 4-4"></path></svg>
                            <div><h4 class="text-xs font-black uppercase tracking-wider text-emerald-400">Thiết bị bảo mật SAMS ID điện tử</h4><span class="text-[9.5px] text-slate-500 font-bold block mt-0.5">Khóa cứng UUID chống giả trang toạ GPS</span></div>
                        </div>
                        <p class="text-[11px] text-slate-300 leading-relaxed font-semibold">Chuyên bạ học tập đào tạo SAMS của Trần Minh Hoàng được liên kết vào một UUID iPhone di động phần cứng chính hãng, loại bỏ tuyệt đối nguy cơ ghi bạ hộ hoặc chia bạ mã QR.</p>
                        <div class="p-3.5 bg-slate-950/70 border border-slate-850 rounded-2.5xl text-[10px] font-mono leading-loose font-semibold text-slate-440 space-y-1 select-text">
                            <div class="flex justify-between"><span>Thiết bị:</span><strong class="text-white">iPhone 15 Pro Max (iOS)</strong></div>
                            <div class="flex justify-between"><span>SAMS UUID:</span><strong class="text-emerald-400 font-bold">UUID:F0C2:0306:2311:08A8</strong></div>
                            <div class="flex justify-between"><span>Gói định bạ:</span><strong class="text-slate-300">RFID SECURITY CORE v2.1</strong></div>
                            <div class="flex justify-between"><span>Đồ lệch GPS tích hợp:</span><strong class="text-emerald-400">11.4 mét (Đạt chuẩn)</strong></div>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-slate-800 space-y-3 mt-6">
                        <button class="w-full h-11 bg-red-600/10 hover:bg-red-600/20 text-red-400 hover:text-red-350 font-bold text-xs cursor-pointer rounded-xl border border-red-500/20 text-center transition-all leading-none focus:outline-none">Reset mô phỏng học bạ</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- MOBILE PROFILE --}}
    <div class="lg:hidden w-full h-full font-sans">
        <div class="p-4 space-y-4 animate-in fade-in slide-in-from-bottom-2 duration-300">
            <div class="bg-white border border-slate-100 rounded-3xl p-4.5 text-center space-y-2 relative overflow-hidden shadow-xs">
                <div class="absolute top-0 inset-x-0 h-16 bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-800 rounded-t-3xl"></div>
                <div class="w-18 h-18 rounded-full border-4 border-white overflow-hidden mx-auto relative z-10 mt-5 shadow-sm shadow-black/5">
                    <img alt="Trần Minh Hoàng" class="w-full h-full object-cover" src="https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?ixlib=rb-1.2.1&amp;auto=format&amp;fit=crop&amp;w=150&amp;h=150&amp;q=80">
                </div>
                <div class="relative pt-1">
                    <h3 class="text-sm font-black text-slate-900 leading-tight uppercase tracking-tight">Trần Minh Hoàng</h3>
                    <span class="text-[10px] font-mono tracking-widest font-extrabold text-slate-400 block mt-0.5">ID: 0306231108</span>
                </div>
                <div class="grid grid-cols-2 gap-2 mt-4 pt-3.5 border-t border-slate-100 text-left text-[11px] font-semibold text-slate-500 leading-relaxed">
                    <div class="truncate"><span class="text-[8.5px] font-black text-slate-404 block uppercase tracking-widest text-slate-400">LỚP HÀNH CHÍNH</span><strong class="text-slate-800 block mt-0.5 truncate">CĐ Kỹ thuật Phần mềm 23A</strong></div>
                    <div class="truncate"><span class="text-[8.5px] font-black text-slate-404 block uppercase tracking-widest text-slate-400">KHOA QUẢN LÝ</span><strong class="text-slate-800 block mt-0.5 truncate">Công nghệ Thông tin</strong></div>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs text-xs space-y-3 font-semibold text-slate-600 font-sans">
                <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">Danh tính học viên SAMS</h4>
                <div class="flex justify-between pb-1 border-b border-slate-50"><span class="text-slate-400">Email trường:</span><span class="font-mono text-slate-800 font-extrabold">0306231108@caothang.edu.vn</span></div>
                <div class="flex justify-between pb-1 border-b border-slate-50"><span class="text-slate-400">Số di động:</span><span class="text-slate-800">0987.654.321</span></div>
                <div class="flex justify-between pb-1 border-b border-slate-50"><span class="text-slate-400">Niên khoá học:</span><span class="text-slate-800 font-mono">2023 - 2026</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Giảng viên cố vấn:</span><span class="text-blue-700 font-black">Thầy Lê Minh Triết</span></div>
            </div>
            
            <div class="bg-slate-900 rounded-2xl p-4 border border-slate-800 space-y-2.5 shadow-sm">
                <div class="flex items-center gap-1.5 font-bold">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield-check w-4.5 h-4.5 text-emerald-400 shrink-0" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path><path d="m9 12 2 2 4-4"></path></svg>
                    <span class="text-[8.5px] uppercase font-black tracking-widest text-emerald-400">Đối chiếu cấu hình thiết bị di động</span>
                </div>
                <p class="text-[10px] text-slate-300 leading-normal font-semibold">Mỗi sinh viên chỉ được phép sử dụng <strong class="text-white">1 thiết bị di động chính duy nhất</strong> để thực hiện nhiệm vụ điểm danh QR và kiểm tra GPS, tránh tình trạng điểm danh hộ.</p>
                <div class="p-3 bg-slate-950 font-mono text-[9px] leading-loose rounded-xl border border-slate-850">
                    <div class="flex justify-between text-slate-400"><span>Đã đăng ký máy:</span><strong class="text-white">iPhone 15 Pro Max (iOS)</strong></div>
                    <div class="flex justify-between text-slate-400"><span>Mã định danh UUID:</span><strong class="text-emerald-400">UUID:F0C2:0306:2311:08A8</strong></div>
                    <div class="flex justify-between text-slate-400"><span>Hạng đăng đắng kiểm:</span><strong class="text-emerald-400 font-extrabold flex items-center gap-1">🔒 AN TOÀN - CHÍNH CHỦ</strong></div>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl overflow-hidden divide-y divide-slate-100 shadow-xs border border-slate-100">
                <div class="p-3 bg-blue-50/20">
                    <a class="flex items-center justify-center gap-2 w-full py-2.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-black rounded-xl border border-blue-200 transition-all text-center leading-none cursor-pointer" href="/" data-discover="true"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-laptop w-4 h-4 text-blue-600 shrink-0" aria-hidden="true"><path d="M18 5a2 2 0 0 1 2 2v8.526a2 2 0 0 0 .212.897l1.068 2.127a1 1 0 0 1-.9 1.45H3.62a1 1 0 0 1-.9-1.45l1.068-2.127A2 2 0 0 0 4 15.526V7a2 2 0 0 1 2-2z"></path><path d="M20.054 15.987H3.946"></path></svg><span>Trở lại Giao diện GV / Khoa</span></a>
                </div>
                <button class="w-full flex items-center justify-between p-3.5 text-xs font-extrabold text-slate-700 cursor-pointer text-left border-none bg-transparent hover:bg-slate-50" id="mob-privacy-btn">
                    <span class="flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-lock w-4 h-4 text-slate-400" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>Chính sách bảo mật học vụ SAMS</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right w-4 h-4 text-slate-300" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg>
                </button>
                <button class="w-full flex items-center justify-between p-3.5 text-xs font-extrabold text-red-650 hover:bg-red-50/5 cursor-pointer text-left border-none bg-transparent" id="mob-reset-btn">
                    <span class="flex items-center gap-2 text-red-600"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-rotate-ccw w-4 h-4 text-red-500" aria-hidden="true"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path></svg>Reset Mô Phỏng ứng dụng SAMS</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right w-4 h-4 text-red-300" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg>
                </button>
            </div>
        </div>
    </div>
</x-app-layout>
