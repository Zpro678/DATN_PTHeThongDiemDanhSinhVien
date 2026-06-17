<x-app-layout variant="student" pageTitle="Thông báo">
    {{-- DESKTOP UI --}}
    <div class="hidden lg:block w-full h-full p-6 lg:p-8 max-w-7xl mx-auto">
        <div class="space-y-6 animate-in fade-in duration-300">
            <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-sm font-black text-slate-900 uppercase pl-0.5">Hộp thư thông báo học vụ &amp; Chuyên bạ</h2>
                        <span class="px-2.5 py-0.5 text-[9.5px] font-black bg-blue-100 text-blue-700 rounded-full font-mono select-none">2 Mới</span>
                    </div>
                    <button class="text-xs font-bold text-blue-600 hover:underline border-none bg-transparent cursor-pointer">Đánh dấu đã đọc tất cả thông báo</button>
                </div>
                <div class="divide-y divide-slate-105">
                    <div class="py-5 pl-2 pr-4 transition-colors flex gap-4.5 bg-blue-50/5">
                        <div class="relative shrink-0 select-none">
                            <span class="absolute top-0 right-0 w-2.5 h-2.5 bg-blue-600 rounded-full border border-white"></span>
                            <div class="w-11 h-11 rounded-full flex items-center justify-center border bg-blue-50 border-blue-105 text-blue-600">
                                <x-sams.icon name="bell" class="w-5 h-5 text-blue-600" />
                            </div>
                        </div>
                        <div class="flex-1 space-y-1.5 text-xs text-slate-655 font-sans leading-relaxed">
                            <div class="flex justify-between items-center text-xs font-semibold">
                                <h4 class="font-bold text-slate-900 text-sm leading-tight">Điểm danh thành công</h4>
                                <span class="text-[10px] font-mono text-slate-400 font-extrabold">Vừa xong</span>
                            </div>
                            <p class="text-slate-500 font-medium leading-relaxed max-w-4xl">Hệ thống ghi nhận bạn đã điểm danh thành công lớp "NET301: Lý thuyết Mạng Máy Tính".</p>
                        </div>
                    </div>
                    <div class="py-5 pl-2 pr-4 transition-colors flex gap-4.5 bg-blue-50/5">
                        <div class="relative shrink-0 select-none">
                            <span class="absolute top-0 right-0 w-2.5 h-2.5 bg-blue-600 rounded-full border border-white"></span>
                            <div class="w-11 h-11 rounded-full flex items-center justify-center border bg-red-50 border-red-150 text-red-650">
                                <x-sams.icon name="bell" class="w-5 h-5 text-red-500" />
                            </div>
                        </div>
                        <div class="flex-1 space-y-1.5 text-xs text-slate-655 font-sans leading-relaxed">
                            <div class="flex justify-between items-center text-xs font-semibold">
                                <h4 class="font-bold text-slate-900 text-sm leading-tight">Cảnh báo rủi ro chuyên cần</h4>
                                <span class="text-[10px] font-mono text-slate-400 font-extrabold">1 ngày trước</span>
                            </div>
                            <p class="text-slate-500 font-medium leading-relaxed max-w-4xl">Cảnh báo: Tỷ lệ chuyên cần môn Vật lý đại cương 2 của bạn đã giảm còn 72% (Ngưỡng an toàn là 80%).</p>
                            <div class="pt-2 text-left">
                                <button class="bg-red-50 hover:bg-red-100 text-red-700 font-extrabold border border-red-200 rounded-xl px-4 py-1.5 cursor-pointer text-xs">Nộp minh chứng giải trình phép</button>
                            </div>
                        </div>
                    </div>
                    <div class="py-5 pl-2 pr-4 transition-colors flex gap-4.5 ">
                        <div class="relative shrink-0 select-none">
                            <div class="w-11 h-11 rounded-full flex items-center justify-center border bg-blue-50 border-blue-105 text-blue-600">
                                <x-sams.icon name="bell" class="w-5 h-5 text-blue-600" />
                            </div>
                        </div>
                        <div class="flex-1 space-y-1.5 text-xs text-slate-655 font-sans leading-relaxed">
                            <div class="flex justify-between items-center text-xs font-semibold">
                                <h4 class="font-bold text-slate-900 text-sm leading-tight">Lịch bổ sung Mạng máy tính</h4>
                                <span class="text-[10px] font-mono text-slate-400 font-extrabold">2 ngày trước</span>
                            </div>
                            <p class="text-slate-500 font-medium leading-relaxed max-w-4xl">TS. Lê Quang Linh thông báo lịch bù môn Mạng máy tính vào sáng Chủ nhật (Tiết 1-3).</p>
                        </div>
                    </div>
                    <div class="py-5 pl-2 pr-4 transition-colors flex gap-4.5 ">
                        <div class="relative shrink-0 select-none">
                            <div class="w-11 h-11 rounded-full flex items-center justify-center border bg-blue-50 border-blue-105 text-blue-600">
                                <x-sams.icon name="bell" class="w-5 h-5 text-blue-600" />
                            </div>
                        </div>
                        <div class="flex-1 space-y-1.5 text-xs text-slate-655 font-sans leading-relaxed">
                            <div class="flex justify-between items-center text-xs font-semibold">
                                <h4 class="font-bold text-slate-900 text-sm leading-tight">Duyệt đơn xin phép vắng học</h4>
                                <span class="text-[10px] font-mono text-slate-400 font-extrabold">5 ngày trước</span>
                            </div>
                            <p class="text-slate-500 font-medium leading-relaxed max-w-4xl">Đơn xin nghỉ phép điện tử ngày 29/05 môn NET301 của bạn đã được Thầy Linh phê duyệt.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MOBILE UI --}}
    <div class="lg:hidden w-full h-full font-sans pb-24">
        <div class="p-4 space-y-4 animate-in fade-in slide-in-from-bottom-2 duration-300">
            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                <h2 class="text-xs font-black text-slate-800 uppercase tracking-widest pl-0.5">Trung Tâm Thông Báo</h2>
                <button class="text-[10px] font-black text-blue-600 hover:underline border-none bg-transparent cursor-pointer" id="mob-mark-all-read-btn">Đọc tất cả</button>
            </div>
            <div class="space-y-3">
                <div class="p-3.5 bg-white border rounded-xl shadow-xs relative transition-all flex gap-3 border-blue-105 bg-blue-50/5">
                    <span class="absolute top-4.5 left-1.5 w-1.5 h-1.5 bg-blue-600 rounded-full animate-pulse"></span>
                    <div class="w-8.5 h-8.5 rounded-xl shrink-0 flex items-center justify-center bg-blue-50 text-blue-600">
                        <x-sams.icon name="bell" class="w-4.5 h-4.5 hover:scale-105 transition-transform" />
                    </div>
                    <div class="flex-1 space-y-1">
                        <div class="flex justify-between items-center text-[11px] leading-none">
                            <h4 class="font-extrabold text-slate-900 pr-2">Điểm danh thành công</h4>
                            <span class="text-[8.5px] font-mono text-slate-400 font-extrabold shrink-0">Vừa xong</span>
                        </div>
                        <p class="text-[11px] text-slate-500 font-medium leading-normal">Hệ thống ghi nhận bạn đã điểm danh thành công lớp "NET301: Lý thuyết Mạng Máy Tính".</p>
                    </div>
                </div>
                <div class="p-3.5 bg-white border rounded-xl shadow-xs relative transition-all flex gap-3 border-blue-105 bg-blue-50/5">
                    <span class="absolute top-4.5 left-1.5 w-1.5 h-1.5 bg-blue-600 rounded-full animate-pulse"></span>
                    <div class="w-8.5 h-8.5 rounded-xl shrink-0 flex items-center justify-center bg-red-50 text-red-600">
                        <x-sams.icon name="bell" class="w-4.5 h-4.5 hover:scale-105 transition-transform" />
                    </div>
                    <div class="flex-1 space-y-1">
                        <div class="flex justify-between items-center text-[11px] leading-none">
                            <h4 class="font-extrabold text-slate-900 pr-2">Cảnh báo rủi ro chuyên cần</h4>
                            <span class="text-[8.5px] font-mono text-slate-400 font-extrabold shrink-0">1 ngày trước</span>
                        </div>
                        <p class="text-[11px] text-slate-500 font-medium leading-normal">Cảnh báo: Tỷ lệ chuyên cần môn Vật lý đại cương 2 của bạn đã giảm còn 72% (Ngưỡng an toàn là 80%).</p>
                        <div class="pt-2 text-right">
                            <button class="text-[9px] font-bold bg-red-50 text-red-750 border border-red-150 rounded px-2 py-1 cursor-pointer leading-none hover:bg-red-100" id="notif-excuse-btn">Gửi văn bản minh chứng</button>
                        </div>
                    </div>
                </div>
                <div class="p-3.5 bg-white border rounded-xl shadow-xs relative transition-all flex gap-3 border-slate-100">
                    <div class="w-8.5 h-8.5 rounded-xl shrink-0 flex items-center justify-center bg-blue-50 text-blue-600">
                        <x-sams.icon name="bell" class="w-4.5 h-4.5 hover:scale-105 transition-transform" />
                    </div>
                    <div class="flex-1 space-y-1">
                        <div class="flex justify-between items-center text-[11px] leading-none">
                            <h4 class="font-extrabold text-slate-900 pr-2">Lịch bổ sung Mạng máy tính</h4>
                            <span class="text-[8.5px] font-mono text-slate-400 font-extrabold shrink-0">2 ngày trước</span>
                        </div>
                        <p class="text-[11px] text-slate-500 font-medium leading-normal">TS. Lê Quang Linh thông báo lịch bù môn Mạng máy tính vào sáng Chủ nhật (Tiết 1-3).</p>
                    </div>
                </div>
                <div class="p-3.5 bg-white border rounded-xl shadow-xs relative transition-all flex gap-3 border-slate-100">
                    <div class="w-8.5 h-8.5 rounded-xl shrink-0 flex items-center justify-center bg-blue-50 text-blue-600">
                        <x-sams.icon name="bell" class="w-4.5 h-4.5 hover:scale-105 transition-transform" />
                    </div>
                    <div class="flex-1 space-y-1">
                        <div class="flex justify-between items-center text-[11px] leading-none">
                            <h4 class="font-extrabold text-slate-900 pr-2">Duyệt đơn xin phép vắng học</h4>
                            <span class="text-[8.5px] font-mono text-slate-400 font-extrabold shrink-0">5 ngày trước</span>
                        </div>
                        <p class="text-[11px] text-slate-500 font-medium leading-normal">Đơn xin nghỉ phép điện tử ngày 29/05 môn NET301 của bạn đã được Thầy Linh phê duyệt.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
