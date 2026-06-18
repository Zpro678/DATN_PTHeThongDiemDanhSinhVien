<x-admin-layout title="Nhật ký hệ thống">
    <div class="mx-auto max-w-[1200px]">
        <div class="admin-card mb-6 overflow-hidden rounded-3xl border p-5 lg:p-6">
            <div class="relative z-10 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-slate-400">Logs</p>
                <h1 class="mb-1 text-[28px] font-bold text-slate-900">Nhật ký hệ thống</h1>
                <p class="text-sm text-slate-500">Theo dõi dòng sự kiện và các hoạt động thay đổi trên hệ thống.</p>
            </div>

            <div class="flex items-center gap-3">
                <div class="relative">
                    <x-user.icon name="search" :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                    <input type="text" placeholder="Tìm kiếm nhật ký..." class="w-64 rounded-xl border border-slate-200 bg-white py-2 pl-10 pr-4 text-sm text-slate-900 shadow-sm transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="button" class="admin-soft-button rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                    Hôm nay
                </button>
            </div>
            </div>
        </div>

        <div class="admin-card overflow-hidden rounded-2xl border p-6 md:p-8">
            <div class="relative z-10 space-y-8 before:absolute before:bottom-2 before:left-[17px] before:top-2 before:w-0.5 before:bg-slate-100">
                @forelse ($logs as $log)
                    @php
                        $action = strtolower($log->action ?? '');
                        $icon = match (true) {
                            str_contains($action, 'create') || str_contains($action, 'add') => 'plus-circle',
                            str_contains($action, 'attendance') || str_contains($action, 'check') => 'calendar-check',
                            str_contains($action, 'update') || str_contains($action, 'setting') => 'settings',
                            str_contains($action, 'delete') || str_contains($action, 'blocked') => 'alert-triangle',
                            default => 'activity',
                        };
                        $tone = match ($icon) {
                            'plus-circle' => 'text-blue-600 bg-blue-50 border-blue-100',
                            'calendar-check' => 'text-emerald-600 bg-emerald-50 border-emerald-100',
                            'settings' => 'text-amber-500 bg-amber-50 border-amber-100',
                            'alert-triangle' => 'text-rose-600 bg-rose-50 border-rose-100',
                            default => 'text-indigo-600 bg-indigo-50 border-indigo-100',
                        };
                    @endphp

                    <div class="group relative flex items-start gap-4 md:gap-6">
                        <div class="z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 border-white bg-slate-50 shadow-sm transition-transform group-hover:scale-105">
                            <x-user.icon name="{{ $icon }}" :size="16" class="{{ explode(' ', $tone)[0] }}" />
                        </div>
                        <div class="flex-1 rounded-2xl border border-slate-100 bg-white/75 p-4 transition-all hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50/40 hover:shadow-sm">
                            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                                <span class="rounded-md border px-2.5 py-1 text-[10px] font-extrabold tracking-wider {{ $tone }}">
                                    {{ strtoupper($log->table_name ?: 'HỆ THỐNG') }}
                                </span>
                                <span class="flex items-center gap-1.5 text-xs font-medium text-slate-400">
                                    <x-user.icon name="calendar" :size="14" />
                                    {{ $log->created_at?->format('d/m/Y H:i') ?? 'N/A' }}
                                </span>
                            </div>
                            <p class="break-words text-sm font-semibold leading-relaxed text-slate-700">
                                <span class="font-bold text-blue-600">{{ $log->user?->name ?? 'Hệ thống' }}</span>
                                {{ $log->action }}
                                @if($log->courseClass)
                                    lớp <span class="font-bold text-slate-900">{{ $log->courseClass->name }}</span>
                                @endif
                                @if($log->row_id)
                                    <span class="text-slate-400">#{{ $log->row_id }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="relative flex items-start gap-4 md:gap-6">
                        <div class="z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 border-white bg-slate-50 shadow-sm">
                            <x-user.icon name="activity" :size="16" class="text-slate-400" />
                        </div>
                        <div class="flex-1 rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 p-6 text-sm font-medium text-slate-500">
                            Chưa có nhật ký hệ thống.
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="relative z-10 mt-8 flex items-center justify-between border-t border-slate-200 pt-6">
                <p class="text-xs font-medium text-slate-500">
                    Hiển thị <span class="font-bold text-slate-900">{{ $logs->count() }}</span> bản ghi mới nhất
                </p>
                <div class="flex items-center gap-1">
                    <button type="button" class="cursor-not-allowed rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-400">Trước</button>
                    <button type="button" class="rounded-lg border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700">1</button>
                    <button type="button" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition-colors hover:bg-slate-50">Sau</button>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
