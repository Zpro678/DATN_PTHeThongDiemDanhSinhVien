<div class="mx-auto max-w-[1400px] space-y-6 p-4 pb-24 sm:p-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <section class="flex flex-col justify-between gap-4 rounded-[2rem] border border-outline-variant/10 bg-white p-6 shadow-sm md:flex-row md:items-center">
        <div>
            <div class="mb-2 inline-flex items-center gap-1.5 rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-rose-600">
                <x-user.icon name="alert-triangle" :size="14" />
                Hệ thống cảnh báo
            </div>
            <h1 class="text-2xl font-extrabold uppercase tracking-tight text-slate-900">Cảnh báo học tập</h1>
            <p class="mt-2 text-sm text-slate-500">
                Cập nhật các cảnh báo về điểm danh, kỷ luật và các vấn đề cần lưu ý trong quá trình học.
            </p>
        </div>
    </section>

    <!-- Hardcoded Warnings List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- Warning 1: Vắng quá số buổi -->
        <div class="group relative flex flex-col overflow-hidden rounded-[2rem] border border-rose-100 bg-gradient-to-b from-rose-50/50 to-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
            <div class="absolute -right-4 -top-4 rounded-full bg-rose-100/40 p-6">
                <x-user.icon name="alert-triangle" :size="72" class="text-rose-500 opacity-10" />
            </div>
            <div class="relative z-10 flex flex-1 flex-col">
                <div class="mb-5 inline-flex items-center justify-center self-start rounded-2xl bg-rose-100 p-3.5 text-rose-600 shadow-sm ring-1 ring-inset ring-rose-200">
                    <x-user.icon name="alert-triangle" :size="24" stroke-width="2.5" />
                </div>
                <h3 class="mb-2 text-xl font-bold text-slate-900">Nguy cơ cấm thi</h3>
                <p class="mb-6 flex-1 text-sm leading-relaxed text-slate-600">
                    Lớp <span class="font-bold text-slate-800">Lập trình Web (CT112)</span>. Bạn đã vắng 3/15 buổi học (đạt ngưỡng 20%). Nếu vắng thêm 1 buổi, bạn sẽ bị cấm thi theo quy định.
                </p>
                <div class="flex items-center justify-between border-t border-rose-100/60 pt-4">
                    <span class="text-xs font-bold text-slate-400">12/05/2026</span>
                    <a href="#" class="inline-flex items-center gap-1.5 text-sm font-bold text-rose-600 hover:text-rose-700 hover:underline">
                        Xem chi tiết <x-user.icon name="arrow-right" :size="16" stroke-width="2.5" />
                    </a>
                </div>
            </div>
        </div>

        <!-- Warning 2: Điểm danh muộn nhiều lần -->
        <div class="group relative flex flex-col overflow-hidden rounded-[2rem] border border-amber-100 bg-gradient-to-b from-amber-50/50 to-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
            <div class="absolute -right-4 -top-4 rounded-full bg-amber-100/40 p-6">
                <x-user.icon name="clock" :size="72" class="text-amber-500 opacity-10" />
            </div>
            <div class="relative z-10 flex flex-1 flex-col">
                <div class="mb-5 inline-flex items-center justify-center self-start rounded-2xl bg-amber-100 p-3.5 text-amber-600 shadow-sm ring-1 ring-inset ring-amber-200">
                    <x-user.icon name="clock" :size="24" stroke-width="2.5" />
                </div>
                <h3 class="mb-2 text-xl font-bold text-slate-900">Điểm danh muộn</h3>
                <p class="mb-6 flex-1 text-sm leading-relaxed text-slate-600">
                    Lớp <span class="font-bold text-slate-800">Mạng máy tính (CT113)</span>. Bạn đã điểm danh muộn 4 buổi liên tiếp. 3 buổi muộn sẽ bị tính là 1 buổi vắng mặt không phép.
                </p>
                <div class="flex items-center justify-between border-t border-amber-100/60 pt-4">
                    <span class="text-xs font-bold text-slate-400">08/05/2026</span>
                    <a href="{{ route('student.attendance.history') }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-amber-600 hover:text-amber-700 hover:underline">
                        Xem lịch sử <x-user.icon name="arrow-right" :size="16" stroke-width="2.5" />
                    </a>
                </div>
            </div>
        </div>

        <!-- Warning 3: Thông báo nhắc nhở từ Giảng viên -->
        <div class="group relative flex flex-col overflow-hidden rounded-[2rem] border border-blue-100 bg-gradient-to-b from-blue-50/50 to-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
            <div class="absolute -right-4 -top-4 rounded-full bg-blue-100/40 p-6">
                <x-user.icon name="info" :size="72" class="text-blue-500 opacity-10" />
            </div>
            <div class="relative z-10 flex flex-1 flex-col">
                <div class="mb-5 inline-flex items-center justify-center self-start rounded-2xl bg-blue-100 p-3.5 text-blue-600 shadow-sm ring-1 ring-inset ring-blue-200">
                    <x-user.icon name="info" :size="24" stroke-width="2.5" />
                </div>
                <h3 class="mb-2 text-xl font-bold text-slate-900">Nhắc nhở nộp minh chứng</h3>
                <p class="mb-6 flex-1 text-sm leading-relaxed text-slate-600">
                    Đơn xin nghỉ phép ngày 05/05/2026 của bạn hiện chưa có hình ảnh minh chứng y tế hợp lệ. Vui lòng bổ sung trước ngày 15/05 để được xét duyệt.
                </p>
                <div class="flex items-center justify-between border-t border-blue-100/60 pt-4">
                    <span class="text-xs font-bold text-slate-400">06/05/2026</span>
                    <a href="{{ route('student.leave-requests.history') }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-blue-600 hover:text-blue-700 hover:underline">
                        Bổ sung ngay <x-user.icon name="arrow-right" :size="16" stroke-width="2.5" />
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
