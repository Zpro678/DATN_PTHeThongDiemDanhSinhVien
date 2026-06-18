@php
    $classCount = $sessions->count();
    $sessionCount = $sessions->sum(fn ($class) => $class->sessions->count());
    $approvalCount = $sessions->where('require_approval', true)->count();
@endphp

<x-admin-layout title="Điểm danh">
    <div class="mx-auto max-w-[1500px] space-y-6">
        <section class="admin-card overflow-hidden rounded-3xl border p-6 lg:p-7">
            <div class="relative z-10 flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-slate-400">Attendance</p>
                <h1 class="mt-1 text-3xl font-black tracking-tight text-slate-900">Giám sát điểm danh</h1>
                <p class="mt-1 text-sm font-medium text-slate-500">Mỗi lớp một khối riêng, gọn và dễ theo dõi.</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.reports.index') }}" class="admin-soft-button rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50">
                    Mở báo cáo
                </a>
                <a href="{{ route('admin.dashboard') }}" class="admin-soft-button rounded-xl bg-gradient-to-r from-blue-600 to-cyan-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-blue-500/20">
                    Quay lại dashboard
                </a>
            </div>
            </div>
        </section>

        <section class="admin-grid-equal grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <x-stats-card title="Lớp đang giám sát" :value="$classCount" change="Tổng lớp" :isPositive="true" icon="book-open" iconBg="bg-blue-50 border-blue-100 text-blue-600" sparklineColor="stroke-blue-500" sparklinePath="M 0,15 L 10,14 L 20,12 L 30,10 L 40,8 L 50,4" />
            <x-stats-card title="Tổng buổi" :value="$sessionCount" change="Phiên điểm danh" :isPositive="true" icon="calendar-check" iconBg="bg-emerald-50 border-emerald-100 text-emerald-600" sparklineColor="stroke-emerald-500" sparklinePath="M 0,18 L 10,15 L 20,14 L 30,11 L 40,7 L 50,4" />
            <x-stats-card title="Cần duyệt" :value="$approvalCount" change="Chờ xác nhận" :isPositive="false" icon="help-circle" iconBg="bg-amber-50 border-amber-100 text-amber-600" sparklineColor="stroke-amber-500" sparklinePath="M 0,5 L 10,8 L 20,7 L 30,12 L 40,15 L 50,10" />
            <x-stats-card title="Lớp công khai" :value="$sessions->where('status', 'active')->count()" change="Trạng thái mở" :isPositive="true" icon="shield-check" iconBg="bg-rose-50 border-rose-100 text-rose-600" sparklineColor="stroke-rose-500" sparklinePath="M 0,2 L 10,7 L 20,5 L 30,13 L 40,16 L 50,18" />
        </section>

        <section class="admin-grid-equal grid grid-cols-1 gap-5 xl:grid-cols-2">
            @forelse ($sessions as $class)
                <article class="admin-card admin-card-hover flex h-full min-h-[360px] flex-col overflow-hidden rounded-2xl border">
                    <div class="relative z-10 border-b border-slate-100 p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ $class->code }}</p>
                                <h2 class="mt-1 truncate text-xl font-black text-slate-900">{{ $class->name }}</h2>
                                <p class="mt-1 text-sm font-medium text-slate-500">GV: {{ $class->owner?->name ?? 'Chưa xác định' }}</p>
                            </div>
                            <span class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-600">{{ $class->status }}</span>
                        </div>
                    </div>

                    <div class="relative z-10 grid grid-cols-3 gap-3 p-6">
                        <div class="rounded-xl border border-slate-200 bg-white/70 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Buổi</p>
                            <p class="mt-1 text-lg font-black text-slate-900">{{ $class->sessions->count() }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white/70 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Môn</p>
                            <p class="mt-1 truncate text-lg font-black text-slate-900">{{ $class->subject_code ?: 'N/A' }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white/70 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Duyệt</p>
                            <p class="mt-1 text-lg font-black text-slate-900">{{ $class->require_approval ? 'Có' : 'Không' }}</p>
                        </div>
                    </div>

                    <div class="relative z-10 mt-auto border-t border-slate-100 p-6">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Phiên gần đây</p>
                        <div class="mt-4 space-y-3">
                            @forelse ($class->sessions->sortByDesc('date')->take(3) as $session)
                                <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white/75 px-4 py-3 transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50/40">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-black text-slate-900">{{ $session->name }}</p>
                                        <p class="text-xs font-medium text-slate-400">{{ $session->date?->format('d/m/Y') ?? 'N/A' }} {{ $session->start_time ?? '' }} {{ $session->end_time ? '- ' . $session->end_time : '' }}</p>
                                    </div>
                                    <span class="rounded-lg border border-blue-100 bg-blue-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-blue-700">
                                        {{ $session->status }}
                                    </span>
                                </div>
                            @empty
                                <p class="text-sm font-medium text-slate-500">Chưa có phiên nào.</p>
                            @endforelse
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm font-medium text-slate-500 xl:col-span-2">
                    Chưa có lớp nào để giám sát.
                </div>
            @endforelse
        </section>
    </div>
</x-admin-layout>
