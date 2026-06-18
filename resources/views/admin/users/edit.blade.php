@php
    $initial = function_exists('mb_substr')
        ? mb_strtoupper(mb_substr($user->name ?? 'U', 0, 1, 'UTF-8'), 'UTF-8')
        : strtoupper(substr($user->name ?? 'U', 0, 1));
@endphp

<x-admin-layout title="Chỉnh sửa người dùng">
    <div class="mx-auto max-w-[1500px] space-y-6">
        <section class="admin-card flex flex-col justify-between gap-4 overflow-hidden rounded-3xl border p-6 lg:flex-row lg:items-end lg:p-7">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-slate-400">Người dùng</p>
                <h1 class="mt-1 text-3xl font-black tracking-tight text-slate-900">Chỉnh sửa hồ sơ</h1>
                <p class="mt-1 text-sm font-medium text-slate-500">{{ $user->name }} - {{ $user->email }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.users.show', $user) }}" class="admin-soft-button rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50">
                    Xem chi tiết
                </a>
                <a href="{{ route('admin.users.index') }}" class="rounded-xl bg-gradient-to-r from-blue-600 to-cyan-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-blue-500/25 transition-all hover:-translate-y-0.5 hover:from-blue-700 hover:to-cyan-700">
                    Quay lại danh sách
                </a>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[360px_minmax(0,1fr)]">
            <section class="admin-card admin-card-hover rounded-2xl border">
                <div class="p-6">
                    <div class="flex items-center gap-4">
                        <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-blue-100 text-3xl font-black text-blue-700">
                            {{ $initial }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Tài khoản hiện tại</p>
                            <h2 class="truncate text-xl font-black text-slate-900">{{ $user->name }}</h2>
                            <p class="truncate text-sm font-medium text-slate-500">{{ $user->email }}</p>
                        </div>
                    </div>

                    <dl class="mt-6 space-y-3 border-t border-slate-100 pt-5 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="font-medium text-slate-500">Vai trò</dt>
                            <dd class="font-bold text-slate-900">{{ $user->is_admin ? 'Admin' : 'Người dùng' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="font-medium text-slate-500">Trạng thái</dt>
                            <dd class="font-bold text-slate-900">{{ ucfirst($user->status ?? 'active') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="font-medium text-slate-500">Mã</dt>
                            <dd class="font-bold text-slate-900">{{ $user->code ?: 'Chưa có' }}</dd>
                        </div>
                    </dl>
                </div>
            </section>

            <section class="admin-card admin-card-hover overflow-hidden rounded-2xl border">
                <div class="border-b border-slate-100 p-6">
                    <h2 class="text-xl font-black text-slate-900">Thông tin chỉnh sửa</h2>
                    <p class="mt-1 text-sm font-medium text-slate-500">Các trường bên dưới đang được hiển thị đúng theo hồ sơ hiện tại.</p>
                </div>

                <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                    <div class="space-y-5">
                        <div>
                            <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Họ và tên</label>
                            <input type="text" value="{{ $user->name }}" disabled class="w-full rounded-xl border border-slate-200 bg-white/75 px-4 py-3 text-sm font-bold text-slate-900">
                        </div>

                        <div>
                            <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Email</label>
                            <input type="email" value="{{ $user->email }}" disabled class="w-full rounded-xl border border-slate-200 bg-white/75 px-4 py-3 text-sm font-bold text-slate-900">
                        </div>

                        <div>
                            <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Mã định danh</label>
                            <input type="text" value="{{ $user->code }}" disabled class="w-full rounded-xl border border-slate-200 bg-white/75 px-4 py-3 text-sm font-bold text-slate-900 placeholder:text-slate-400">
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Vai trò</label>
                            <select disabled class="w-full rounded-xl border border-slate-200 bg-white/75 px-4 py-3 text-sm font-bold text-slate-900">
                                <option {{ $user->is_admin ? 'selected' : '' }}>Admin</option>
                                <option {{ ! $user->is_admin ? 'selected' : '' }}>Người dùng</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Trạng thái</label>
                            <select disabled class="w-full rounded-xl border border-slate-200 bg-white/75 px-4 py-3 text-sm font-bold text-slate-900">
                                <option {{ $user->status === 'active' ? 'selected' : '' }}>active</option>
                                <option {{ $user->status === 'blocked' ? 'selected' : '' }}>blocked</option>
                            </select>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white/75 p-4">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Gợi ý layout</p>
                            <p class="mt-2 text-sm font-medium leading-6 text-slate-600">Màn hình này đang được giữ ở dạng chỉnh sửa hồ sơ để đồng bộ với luồng quản trị nhiều trang.</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-admin-layout>
