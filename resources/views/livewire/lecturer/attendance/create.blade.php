<div class="mx-auto max-w-full space-y-8 p-4 pb-24 sm:p-8">
    <section class="rounded-[2rem] border border-outline-variant/10 bg-white p-8 text-center shadow-sm">
        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-primary/10">
            <x-user.icon name="calendar-plus" :size="32" class="text-primary" />
        </div>
        <h1 class="text-2xl font-extrabold uppercase tracking-tight text-slate-900">Chọn hình thức điểm danh</h1>
        <p class="mt-2 text-sm text-slate-500">Vui lòng chọn cách bạn muốn tạo buổi điểm danh mới cho lớp học.</p>
    </section>

    <section class="grid gap-6 md:grid-cols-2">
        <a href="{{ route('lecturer.attendance.manual.create') }}" class="group relative flex flex-col items-center overflow-hidden rounded-[2.5rem] border-2 border-amber-100 bg-white p-8 text-center shadow-sm transition-all duration-300 hover:-translate-y-2 hover:border-amber-400 hover:shadow-xl">
            <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-amber-50 transition-transform duration-500 group-hover:scale-150"></div>
            <div class="relative z-10 mb-6 flex h-20 w-20 items-center justify-center rounded-3xl bg-amber-100 text-amber-600 shadow-inner group-hover:bg-amber-500 group-hover:text-white transition-colors duration-300">
                <x-user.icon name="check-square" :size="36" />
            </div>
            <h2 class="relative z-10 text-xl font-extrabold text-slate-900">Điểm danh thủ công</h2>
            <p class="relative z-10 mt-3 text-sm leading-relaxed text-slate-500">Phù hợp khi chủ lớp muốn gọi tên đánh dấu trực tiếp từng sinh viên hoặc chỉnh sửa trạng thái nhanh trên lớp.</p>
        </a>

        <a href="{{ route('lecturer.attendance.qr.create') }}" class="group relative flex flex-col items-center overflow-hidden rounded-[2.5rem] border-2 border-blue-100 bg-white p-8 text-center shadow-sm transition-all duration-300 hover:-translate-y-2 hover:border-blue-400 hover:shadow-xl">
            <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-blue-50 transition-transform duration-500 group-hover:scale-150"></div>
            <div class="relative z-10 mb-6 flex h-20 w-20 items-center justify-center rounded-3xl bg-blue-100 text-blue-600 shadow-inner group-hover:bg-blue-500 group-hover:text-white transition-colors duration-300">
                <x-user.icon name="qr-code" :size="36" />
            </div>
            <h2 class="relative z-10 text-xl font-extrabold text-slate-900">Điểm danh qua QR</h2>
            <p class="relative z-10 mt-3 text-sm leading-relaxed text-slate-500">Tạo mã QR có thời hạn trên màn hình để sinh viên tự động check-in bằng thiết bị cá nhân của mình.</p>
        </a>
    </section>
</div>
