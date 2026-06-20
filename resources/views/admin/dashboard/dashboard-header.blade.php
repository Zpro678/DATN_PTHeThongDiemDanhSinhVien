@php
    \Carbon\Carbon::setLocale('vi');
    $formattedDate = ucfirst(\Carbon\Carbon::now()->isoFormat('dddd, D MMMM, YYYY'));
@endphp

<div class="admin-card overflow-hidden rounded-3xl border p-6 lg:p-7">
    <div class="relative z-10 flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
        <div>
            <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-[10px] font-extrabold uppercase tracking-widest text-blue-700">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                Trung tâm quản trị
            </div>
            <h1 class="text-3xl font-black tracking-tight text-slate-900 lg:text-4xl">
                Hệ thống Quản lý hành chính
            </h1>
            <p class="mt-1 text-sm font-medium text-slate-500">
                Tổng quan hoạt động lớp học, chuyên cần, giảng dạy và giám sát hạ tầng thời gian thực.
            </p>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-3 self-end lg:self-auto ml-auto">
            <div class="admin-soft-button flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-600">
                <x-user.icon name="calendar" :size="16" class="text-blue-600" />
                <span>{{ $formattedDate }}</span>
            </div>

            <button type="button" onclick="window.location.reload();" class="admin-soft-button inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-cyan-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-blue-500/20">
                <x-user.icon name="refresh-cw" :size="14" />
                <span>Tải lại dữ liệu</span>
            </button>
        </div>
    </div>
</div>
