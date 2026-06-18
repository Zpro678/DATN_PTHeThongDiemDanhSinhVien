@php
    $initial = function_exists('mb_substr')
        ? mb_strtoupper(mb_substr($user->name ?? 'U', 0, 1, 'UTF-8'), 'UTF-8')
        : strtoupper(substr($user->name ?? 'U', 0, 1));

    $roleLabel = $user->is_admin ? 'Admin' : 'Người dùng';
    $statusLabel = $user->status === 'active' ? 'Đang hoạt động' : 'Bị khóa';
    $statusColor = $user->status === 'active'
        ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
        : 'bg-rose-50 text-rose-700 border-rose-100';
@endphp

<x-admin-layout title="Chi tiết người dùng">
    <div class="mx-auto max-w-[1500px] space-y-6">
        <section class="admin-card flex flex-col justify-between gap-4 overflow-hidden rounded-3xl border p-6 lg:flex-row lg:items-end lg:p-7">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-slate-400">Tài khoản</p>
                <h1 class="mt-1 text-3xl font-black tracking-tight text-slate-900">{{ $user->name }}</h1>
                <p class="mt-1 text-sm font-medium text-slate-500">{{ $user->email }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.users.index') }}" class="admin-soft-button rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50">
                    Quay lại danh sách
                </a>
                <a href="{{ route('admin.users.edit', $user) }}" class="rounded-xl bg-gradient-to-r from-blue-600 to-cyan-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-blue-500/25 transition-all hover:-translate-y-0.5 hover:from-blue-700 hover:to-cyan-700">
                    Chỉnh sửa hồ sơ
                </a>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[380px_minmax(0,1fr)]">
            <section class="admin-card admin-card-hover overflow-hidden rounded-2xl border">
                <div class="h-24 bg-gradient-to-r from-blue-600 via-cyan-500 to-emerald-500"></div>
                <div class="px-6 pb-6">
                    <div class="-mt-12 flex justify-center">
                        <div class="flex h-24 w-24 items-center justify-center rounded-3xl border-4 border-white bg-blue-100 text-3xl font-black text-blue-700 shadow-sm">
                            {{ $initial }}
                        </div>
                    </div>

                    <div class="mt-4 text-center">
                        <h2 class="text-xl font-black text-slate-900">{{ $user->name }}</h2>
                        <p class="text-sm font-medium text-slate-500">{{ $user->code ?: 'Chưa có mã định danh' }}</p>
                        <div class="mt-4 flex flex-wrap justify-center gap-2">
                            <span class="rounded-lg border px-3 py-1 text-[10px] font-bold uppercase tracking-wider {{ $user->is_admin ? 'border-blue-100 bg-blue-50 text-blue-700' : 'border-slate-100 bg-slate-50 text-slate-600' }}">
                                {{ $roleLabel }}
                            </span>
                            <span class="rounded-lg border px-3 py-1 text-[10px] font-bold uppercase tracking-wider {{ $statusColor }}">
                                {{ $statusLabel }}
                            </span>
                        </div>
                    </div>

                    <dl class="mt-6 space-y-3 border-t border-slate-100 pt-5 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="font-medium text-slate-500">Ngày tạo</dt>
                            <dd class="font-bold text-slate-900">{{ $user->created_at?->format('d/m/Y') ?? 'N/A' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="font-medium text-slate-500">Cập nhật gần nhất</dt>
                            <dd class="font-bold text-slate-900">{{ $user->updated_at?->diffForHumans() ?? 'N/A' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="font-medium text-slate-500">Lớp đã tạo</dt>
                            <dd class="font-bold text-slate-900">{{ $user->owned_classes_count ?? 0 }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="font-medium text-slate-500">Lớp đã tham gia</dt>
                            <dd class="font-bold text-slate-900">{{ $user->joined_classes_count ?? 0 }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="font-medium text-slate-500">Gói dịch vụ</dt>
                            <dd class="font-bold text-slate-900">{{ $user->subscriptions_count ?? 0 }}</dd>
                        </div>
                    </dl>
                </div>
            </section>

            <div class="space-y-6">
                <section class="admin-grid-equal grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <x-stats-card
                        title="Lớp đã tạo"
                        :value="$user->owned_classes_count ?? 0"
                        change="Tổng lớp"
                        :isPositive="true"
                        icon="book-open"
                        iconBg="bg-blue-50 border-blue-100 text-blue-600"
                        sparklineColor="stroke-blue-500"
                        sparklinePath="M 0,16 L 10,14 L 20,12 L 30,10 L 40,8 L 50,4"
                    />

                    <x-stats-card
                        title="Lớp tham gia"
                        :value="$user->joined_classes_count ?? 0"
                        change="Tổng lớp"
                        :isPositive="true"
                        icon="users"
                        iconBg="bg-emerald-50 border-emerald-100 text-emerald-600"
                        sparklineColor="stroke-emerald-500"
                        sparklinePath="M 0,18 L 10,15 L 20,14 L 30,11 L 40,7 L 50,4"
                    />

                    <x-stats-card
                        title="Phiên đăng ký"
                        :value="$user->subscriptions_count ?? 0"
                        change="Gói hoạt động"
                        :isPositive="true"
                        icon="package"
                        iconBg="bg-amber-50 border-amber-100 text-amber-600"
                        sparklineColor="stroke-amber-500"
                        sparklinePath="M 0,6 L 10,8 L 20,7 L 30,12 L 40,15 L 50,10"
                    />

                    <x-stats-card
                        title="Yêu cầu lớp"
                        :value="$user->class_join_requests_count ?? 0"
                        change="Chờ xử lý"
                        :isPositive="false"
                        icon="help-circle"
                        iconBg="bg-rose-50 border-rose-100 text-rose-600"
                        sparklineColor="stroke-rose-500"
                        sparklinePath="M 0,2 L 10,7 L 20,5 L 30,13 L 40,16 L 50,18"
                    />
                </section>

                <section class="admin-card admin-card-hover overflow-hidden rounded-2xl border">
                    <div class="flex flex-col gap-4 border-b border-slate-100 p-6 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <h2 class="text-xl font-black text-slate-900">Thông tin hồ sơ</h2>
                            <p class="mt-1 text-sm font-medium text-slate-500">Các trường chính của tài khoản đang được quản lý.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2">
                        <dl class="space-y-5 text-sm">
                            <div>
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Họ và tên</dt>
                                <dd class="mt-1 rounded-xl border border-slate-200 bg-white/75 px-4 py-3 font-bold text-slate-900">{{ $user->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Email</dt>
                                <dd class="mt-1 rounded-xl border border-slate-200 bg-white/75 px-4 py-3 font-bold text-slate-900">{{ $user->email }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Mã định danh</dt>
                                <dd class="mt-1 rounded-xl border border-slate-200 bg-white/75 px-4 py-3 font-bold text-slate-900">{{ $user->code ?: 'Chưa cập nhật' }}</dd>
                            </div>
                        </dl>

                        <dl class="space-y-5 text-sm">
                            <div>
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Trạng thái tài khoản</dt>
                                <dd class="mt-1 rounded-xl border border-slate-200 bg-white/75 px-4 py-3 font-bold text-slate-900">{{ $statusLabel }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Quyền hệ thống</dt>
                                <dd class="mt-1 rounded-xl border border-slate-200 bg-white/75 px-4 py-3 font-bold text-slate-900">{{ $roleLabel }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Email xác thực</dt>
                                <dd class="mt-1 rounded-xl border border-slate-200 bg-white/75 px-4 py-3 font-bold text-slate-900">{{ $user->email_verified_at ? $user->email_verified_at->format('d/m/Y H:i') : 'Chưa xác thực' }}</dd>
                            </div>
                        </dl>
                    </div>
                </section>

                <section class="admin-grid-equal grid grid-cols-1 gap-6 xl:grid-cols-2">
                    <div class="admin-card admin-card-hover flex h-full flex-col overflow-hidden rounded-2xl border">
                        <div class="border-b border-slate-100 p-6">
                            <h2 class="text-xl font-black text-slate-900">Lớp đã tạo gần đây</h2>
                            <p class="mt-1 text-sm font-medium text-slate-500">Danh sách lớp mà người dùng đang sở hữu.</p>
                        </div>

                        <div class="divide-y divide-slate-100">
                            @forelse ($recentClasses as $class)
                                <div class="flex items-center justify-between gap-4 p-6 transition-colors hover:bg-blue-50/40">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-black text-slate-900">{{ $class->name }}</p>
                                        <p class="mt-1 text-xs font-medium text-slate-400">{{ $class->code }} / {{ $class->subject_code ?: 'Chưa có mã môn' }}</p>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-2">
                                        <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-600">{{ $class->users_count }} SV</span>
                                        <span class="rounded-lg bg-blue-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-blue-700">{{ $class->sessions_count }} buổi</span>
                                    </div>
                                </div>
                            @empty
                                <div class="p-6 text-sm font-medium text-slate-500">Chưa có lớp nào được tạo.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="admin-card admin-card-hover flex h-full flex-col overflow-hidden rounded-2xl border">
                        <div class="border-b border-slate-100 p-6">
                            <h2 class="text-xl font-black text-slate-900">Nhật ký gần đây</h2>
                            <p class="mt-1 text-sm font-medium text-slate-500">Các thao tác gần nhất của tài khoản này.</p>
                        </div>

                        <div class="divide-y divide-slate-100">
                            @forelse ($recentLogs as $log)
                                <div class="flex flex-col gap-2 p-6 transition-colors hover:bg-blue-50/40">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-sm font-black text-slate-900">{{ $log->action }}</p>
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $log->created_at?->format('d/m/Y H:i') }}</p>
                                    </div>
                                    <p class="text-xs font-medium text-slate-500">
                                        Bảng {{ $log->table_name ?: 'n/a' }} @if($log->row_id) #{{ $log->row_id }} @endif
                                    </p>
                                </div>
                            @empty
                                <div class="p-6 text-sm font-medium text-slate-500">Chưa có nhật ký nào.</div>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-admin-layout>
