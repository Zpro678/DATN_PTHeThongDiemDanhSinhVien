<x-app-layout variant="lecturer" page-title="Quản lý điểm danh">
    <div class="mx-auto max-w-[1400px] space-y-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="flex items-center gap-3 text-[28px] font-extrabold tracking-tight text-slate-900">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <x-sams.icon name="calendar-check" class="h-6 w-6" />
                    </span>
                    Quản lý điểm danh
                </h1>
                <p class="mt-2 text-sm font-medium text-slate-500">
                    Chọn phương thức điểm danh và theo dõi nhanh các phiên trong ngày.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <button type="button" class="relative inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
                    <span class="absolute -right-2 -top-2 rounded-full bg-amber-500 px-1.5 py-0.5 text-[8px] font-black uppercase tracking-wider text-white">PRO</span>
                    <x-sams.icon name="bar-chart" class="h-4 w-4 text-slate-500" />
                    Xuất dữ liệu
                </button>

                <button type="button" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-500/30 transition hover:bg-emerald-700">
                    <x-sams.icon name="calendar-check" class="h-4 w-4 text-white" />
                    Báo cáo điểm danh
                </button>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-slate-400">Tổng số lớp</p>
                        <p class="mt-3 text-3xl font-black text-slate-900">12</p>
                    </div>
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <x-sams.icon name="book-open" class="h-5 w-5" />
                    </span>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-slate-400">Phiên đang diễn ra</p>
                        <p class="mt-3 text-3xl font-black text-slate-900">01</p>
                    </div>
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                        <x-sams.icon name="activity" class="h-5 w-5" />
                    </span>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-slate-400">Điểm danh hôm nay</p>
                        <p class="mt-3 text-3xl font-black text-slate-900">92%</p>
                    </div>
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <x-sams.icon name="shield-check" class="h-5 w-5" />
                    </span>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-slate-400">SV đã điểm danh</p>
                        <p class="mt-3 text-3xl font-black text-slate-900">348</p>
                    </div>
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                        <x-sams.icon name="users" class="h-5 w-5" />
                    </span>
                </div>
            </div>
        </div>

        <section>
            <div class="mb-5 flex items-center gap-3">
                <span class="h-7 w-1.5 rounded-full bg-blue-600"></span>
                <h2 class="text-xl font-bold text-slate-900">Chọn phương thức điểm danh</h2>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <a href="{{ route('lecturer.attendance.manual') }}" class="group relative flex flex-col gap-5 overflow-hidden rounded-2xl border-2 border-slate-200 bg-white p-6 text-left shadow-sm transition duration-300 hover:-translate-y-1 hover:border-amber-400 hover:shadow-xl sm:flex-row">
                    <span class="pointer-events-none absolute right-0 top-0 h-40 w-40 rounded-bl-full bg-amber-100/50 opacity-0 transition duration-300 group-hover:opacity-100"></span>

                    <span class="relative z-10 flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-lg shadow-amber-400/30 transition group-hover:scale-105">
                        <x-sams.icon name="calendar-check" class="h-8 w-8" />
                    </span>

                    <span class="relative z-10 min-w-0 flex-1">
                        <span class="flex items-start justify-between gap-4">
                            <span>
                                <span class="block text-lg font-bold text-slate-900 transition group-hover:text-amber-700">Điểm danh thủ công</span>
                                <span class="mt-2 block text-sm leading-6 text-slate-500">Giảng viên gọi tên và đánh dấu có mặt, vắng, muộn cho từng sinh viên.</span>
                            </span>
                            <x-sams.icon name="chevron-down" class="-rotate-90 h-5 w-5 shrink-0 text-slate-300 transition group-hover:translate-x-1 group-hover:text-amber-500" />
                        </span>

                        <span class="mt-4 flex flex-wrap gap-2">
                            <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Gọi tên trực tiếp</span>
                            <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Kiểm soát hoàn toàn</span>
                            <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Không cần thiết bị</span>
                        </span>
                    </span>
                </a>

                <a href="{{ route('attendance.qr.setup') }}" class="group relative flex flex-col gap-5 overflow-hidden rounded-2xl border-2 border-blue-200 bg-white p-6 text-left shadow-sm transition duration-300 hover:-translate-y-1 hover:border-blue-400 hover:shadow-xl sm:flex-row">
                    <span class="pointer-events-none absolute right-0 top-0 h-40 w-40 rounded-bl-full bg-blue-100/50 opacity-0 transition duration-300 group-hover:opacity-100"></span>

                    <span class="relative z-10 flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg shadow-blue-500/30 transition group-hover:scale-105">
                        <x-sams.icon name="qr-code" class="h-8 w-8" />
                    </span>

                    <span class="relative z-10 min-w-0 flex-1">
                        <span class="flex items-start justify-between gap-4">
                            <span>
                                <span class="flex flex-wrap items-center gap-2">
                                    <span class="text-lg font-bold text-slate-900 transition group-hover:text-blue-700">Link / QR + GPS</span>
                                    <span class="rounded-full bg-blue-600 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-white">Khuyên dùng</span>
                                </span>
                                <span class="mt-2 block text-sm leading-6 text-slate-500">Sinh viên quét QR hoặc truy cập link để điểm danh, kết hợp xác thực vị trí GPS.</span>
                            </span>
                            <x-sams.icon name="chevron-down" class="-rotate-90 h-5 w-5 shrink-0 text-slate-300 transition group-hover:translate-x-1 group-hover:text-blue-500" />
                        </span>

                        <span class="mt-4 flex flex-wrap gap-2">
                            <span class="rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Quét QR nhanh</span>
                            <span class="rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Xác thực GPS</span>
                            <span class="rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Chống gian lận</span>
                        </span>
                    </span>
                </a>
            </div>
        </section>
    </div>
</x-app-layout>
