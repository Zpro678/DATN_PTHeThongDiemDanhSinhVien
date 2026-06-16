@php
    \Carbon\Carbon::setLocale('vi');
    // Định dạng ngày giống ví dụ: Thứ Hai, 15 tháng 6, 2026
    $formattedDate = ucfirst(\Carbon\Carbon::now()->isoFormat('dddd, D MMMM, YYYY'));
@endphp

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
            Hệ thống Quản lý hành chính
        </h1>
        <p class="text-sm font-medium text-slate-500 mt-1 dark:text-slate-400">
            Tổng quan hoạt động lớp học, chuyên cần, giảng dạy và giám sát hạ tầng thời gian thực.
        </p>
    </div>

    <div class="flex items-center gap-3 self-start md:self-auto">
        <div class="flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs text-xs font-bold text-slate-600 dark:text-slate-350">
            <x-sams.icon name="calendar" class="w-4 h-4 text-blue-600 shrink-0" />
            <span>{{ $formattedDate }}</span>
        </div>

        <button onclick="window.location.reload();" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-xl shadow-sm text-xs font-bold flex items-center gap-2 cursor-pointer transition-all shrink-0">
            <x-sams.icon name="refresh-cw" class="w-3.5 h-3.5" />
            <span>Tải lại dữ liệu</span>
        </button>
    </div>
</div>
