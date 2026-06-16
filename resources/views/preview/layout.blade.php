@php
    $titles = [
        'admin' => 'Admin Center',
        'lecturer' => 'Bảng điều khiển giảng viên',
        'student' => 'Tổng quan sinh viên',
    ];
@endphp

<x-app-layout :variant="$variant" :page-title="$titles[$variant]">
    <div class="space-y-6">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-blue-600">Preview Layout</p>
            <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                {{ $titles[$variant] }}
            </h1>
            <p class="mt-2 max-w-2xl text-sm font-medium text-slate-500 dark:text-slate-400">
                Trang này dùng để xem nhanh layout migrate từ source React sang Blade/Tailwind.
            </p>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Header</p>
                <p class="mt-2 text-sm font-semibold text-slate-700 dark:text-slate-200">Topbar và vùng tìm kiếm đã được migrate.</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Navigation</p>
                <p class="mt-2 text-sm font-semibold text-slate-700 dark:text-slate-200">Sidebar hoặc tabbar mobile giữ style Tailwind từ React.</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Footer</p>
                <p class="mt-2 text-sm font-semibold text-slate-700 dark:text-slate-200">Footer chung được thêm vào shell layout.</p>
            </div>
        </div>
    </div>
</x-app-layout>
