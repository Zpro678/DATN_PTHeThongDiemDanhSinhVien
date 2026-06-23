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
                        @if($user->avatar)
                            <img src="{{ asset('storage/'.$user->avatar) }}" alt="{{ $user->name }}" class="h-20 w-20 rounded-3xl object-cover bg-blue-100 shadow-sm">
                        @else
                            <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-blue-100 text-3xl font-black text-blue-700 shadow-sm">
                                {{ $initial }}
                            </div>
                        @endif
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
                    </dl>
                </div>
            </section>

            <section class="admin-card admin-card-hover overflow-hidden rounded-2xl border">
                <form method="POST" action="{{ route('admin.users.update', $user) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="border-b border-slate-100 p-6 flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-black text-slate-900">Thông tin chỉnh sửa</h2>
                        </div>
                        <button type="submit" class="rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm shadow-emerald-500/25 transition-all hover:-translate-y-0.5 hover:from-emerald-600 hover:to-emerald-700">
                            Lưu thay đổi
                        </button>
                    </div>

                    @if(session('success'))
                        <div class="m-6 rounded-xl border border-green-200 bg-green-50 p-4 text-green-800 text-sm font-medium">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="m-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800 text-sm font-medium">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                        <div class="space-y-5">
                            <div>
                                <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Họ và tên</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Mã số sinh viên (nếu có)</label>
                                <input type="text" name="code" value="{{ old('code', $user->code) }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                @error('code')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Email</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Vai trò</label>
                                <select disabled class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-500 cursor-not-allowed">
                                    <option value="1" {{ $user->is_admin ? 'selected' : '' }}>Admin</option>
                                    <option value="0" {{ ! $user->is_admin ? 'selected' : '' }}>Người dùng</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Trạng thái</label>
                                <select name="status" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                    <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                                    <option value="blocked" {{ $user->status === 'blocked' ? 'selected' : '' }}>Đã khóa</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-admin-layout>
