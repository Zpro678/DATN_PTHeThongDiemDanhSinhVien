<x-admin-layout title="Báo cáo">
    <div class="mx-auto max-w-[1500px] space-y-6">
        <section class="admin-card overflow-hidden rounded-3xl border p-6 lg:p-7">
            <div class="relative z-10 flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-slate-400">Reports</p>
                <h1 class="mt-1 text-3xl font-black tracking-tight text-slate-900">Báo cáo tổng hợp</h1>
                <p class="mt-1 text-sm font-medium text-slate-500">Số liệu hành chính được gom thành từng mảng rõ ràng.</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.logs.index') }}" class="admin-soft-button rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50">
                    Nhật ký hệ thống
                </a>
                <a href="{{ route('admin.dashboard') }}" class="admin-soft-button rounded-xl bg-gradient-to-r from-blue-600 to-cyan-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-blue-500/20">
                    Quay lại dashboard
                </a>
            </div>
            </div>
        </section>

        <section class="admin-grid-equal grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-5">
            <x-stats-card title="Người dùng" :value="$overview['users']" change="Tài khoản" :isPositive="true" icon="users" iconBg="bg-blue-50 border-blue-100 text-blue-600" sparklineColor="stroke-blue-500" sparklinePath="M 0,15 L 10,13 L 20,11 L 30,9 L 40,7 L 50,4" />
            <x-stats-card title="Lớp học" :value="$overview['classes']" change="Đang quản lý" :isPositive="true" icon="book-open" iconBg="bg-emerald-50 border-emerald-100 text-emerald-600" sparklineColor="stroke-emerald-500" sparklinePath="M 0,18 L 10,16 L 20,14 L 30,11 L 40,7 L 50,4" />
            <x-stats-card title="Gói dịch vụ" :value="$overview['plans']" change="Cấu hình" :isPositive="true" icon="package" iconBg="bg-amber-50 border-amber-100 text-amber-600" sparklineColor="stroke-amber-500" sparklinePath="M 0,6 L 10,8 L 20,7 L 30,12 L 40,15 L 50,10" />
            <x-stats-card title="Đăng ký" :value="$overview['subscriptions']" change="Billing" :isPositive="true" icon="download" iconBg="bg-cyan-50 border-cyan-100 text-cyan-600" sparklineColor="stroke-cyan-500" sparklinePath="M 0,5 L 10,7 L 20,6 L 30,11 L 40,14 L 50,9" />
            <x-stats-card title="Nhật ký" :value="$overview['logs']" change="Sự kiện" :isPositive="true" icon="activity" iconBg="bg-rose-50 border-rose-100 text-rose-600" sparklineColor="stroke-rose-500" sparklinePath="M 0,2 L 10,7 L 20,5 L 30,13 L 40,16 L 50,18" />
        </section>

        <div class="admin-grid-equal grid grid-cols-1 gap-6 xl:grid-cols-[360px_minmax(0,1fr)]">
            <section class="admin-card admin-card-hover overflow-hidden rounded-2xl border p-6">
                <div class="relative z-10">
                <h2 class="text-xl font-black text-slate-900">Chỉ số nhanh</h2>
                <div class="mt-4 space-y-3">
                    <div class="rounded-xl border border-slate-200 bg-white/75 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Sức khỏe dữ liệu</p>
                        <p class="mt-2 text-sm font-semibold text-slate-700">Mọi nhóm tính năng được tách ra thành một trang riêng để thao tác nhanh hơn.</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-white/75 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Xuất báo cáo</p>
                        <p class="mt-2 text-sm font-semibold text-slate-700">Chuẩn bị sẵn khối dữ liệu cho các bước xuất Excel hoặc dashboard đồ họa sau này.</p>
                    </div>
                </div>
                </div>
            </section>

            <section class="admin-card admin-card-hover overflow-hidden rounded-2xl border">
                <div class="relative z-10 border-b border-slate-100 p-6">
                    <h2 class="text-xl font-black text-slate-900">Hoạt động gần đây</h2>
                    <p class="mt-1 text-sm font-medium text-slate-500">Tổng hợp các sự kiện hệ thống gần nhất.</p>
                </div>

                <div class="relative z-10 divide-y divide-slate-100">
                    @forelse ($recentActivity as $log)
                        <div class="flex flex-col gap-2 p-6 transition hover:bg-blue-50/40">
                            <div class="flex min-w-0 items-center justify-between gap-3">
                                <p class="min-w-0 truncate text-sm font-black text-slate-900">{{ $log->action }}</p>
                                <p class="shrink-0 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $log->created_at?->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
                                <span class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 uppercase tracking-wider text-slate-600">{{ $log->table_name ?: 'n/a' }}</span>
                                <span class="rounded-lg border border-blue-100 bg-blue-50 px-2.5 py-1 uppercase tracking-wider text-blue-700">#{{ $log->row_id ?: '0' }}</span>
                                <span class="rounded-lg border border-emerald-100 bg-emerald-50 px-2.5 py-1 uppercase tracking-wider text-emerald-700">{{ $log->user?->name ?? 'Hệ thống' }}</span>
                            </div>
                            <p class="truncate text-sm font-medium text-slate-500">
                                {{ $log->courseClass?->name ?? 'Không gắn lớp' }}
                            </p>
                        </div>
                    @empty
                        <div class="p-6 text-sm font-medium text-slate-500">Chưa có hoạt động nào.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-admin-layout>
