<div>
    <div class="mx-auto max-w-[1500px] space-y-6">
        <section class="flex flex-col justify-between gap-4 p-6 lg:flex-row lg:items-end lg:p-7 mb-2">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-slate-400">Người dùng</p>
                <h1 class="mt-1 text-3xl font-black tracking-tight text-slate-900">Thêm người dùng mới</h1>
                <p class="mt-1 text-sm font-medium text-slate-500">Tạo tài khoản mới cho Admin hoặc Người dùng</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.users.index') }}" class="rounded-xl bg-gradient-to-r from-blue-600 to-cyan-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-blue-500/25 transition-all hover:-translate-y-0.5 hover:from-blue-700 hover:to-cyan-700">
                    Quay lại danh sách
                </a>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[360px_minmax(0,1fr)]">
            <section class="admin-card admin-card-hover rounded-2xl border">
                <div class="p-6">
                    <div class="flex items-center gap-4">
                        @if ($avatar)
                            <img src="{{ $avatar->temporaryUrl() }}" class="h-20 w-20 rounded-3xl object-cover bg-blue-100 shadow-sm" alt="Preview">
                        @else
                            <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-blue-100 text-3xl font-black text-blue-700 shadow-sm">
                                {{ $name ? mb_strtoupper(mb_substr($name, 0, 1)) : '+' }}
                            </div>
                        @endif
                        
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Tài khoản mới</p>
                            <h2 class="truncate text-xl font-black text-slate-900">{{ $name ?: 'Chưa có tên' }}</h2>
                            <p class="truncate text-sm font-medium text-slate-500">{{ $email ?: 'Chưa có email' }}</p>
                        </div>
                    </div>

                    <dl class="mt-6 space-y-3 border-t border-slate-100 pt-5 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="font-medium text-slate-500">Vai trò</dt>
                            <dd class="font-bold text-slate-900">{{ $role === \App\Models\User::ROLE_USER ? 'Người dùng' : 'Admin' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="font-medium text-slate-500">Trạng thái</dt>
                            <dd class="font-bold text-slate-900">{{ $status === 'active' ? 'Đang hoạt động' : 'Đã khóa' }}</dd>
                        </div>
                    </dl>
                </div>
            </section>

            <section class="admin-card admin-card-hover overflow-visible rounded-2xl border">
                <div class="border-b border-slate-100 p-6">
                    <h2 class="text-xl font-black text-slate-900">Thông tin khởi tạo</h2>
                    <p class="mt-1 text-sm font-medium text-slate-500">Nhập các thông tin cơ bản để tạo tài khoản.</p>
                </div>

                <form wire:submit="save">
                    <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                        <div class="col-span-1 lg:col-span-2">
                            <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Ảnh đại diện (Tùy chọn)</label>
                            <input type="file" wire:model="avatar" accept="image/*" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('avatar') <span class="text-xs text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Họ và tên <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model.live="name" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Nhập họ và tên">
                                @error('name') <span class="text-xs text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Email <span class="text-rose-500">*</span></label>
                                <input type="email" wire:model.live="email" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Nhập email">
                                @error('email') <span class="text-xs text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Mật khẩu <span class="text-rose-500">*</span></label>
                                <input type="password" wire:model="password" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Nhập mật khẩu (tối thiểu 8 ký tự)">
                                @error('password') <span class="text-xs text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Vai trò <span class="text-rose-500">*</span></label>
                                @php
                                $roleOptions = [
                                    ['value' => \App\Models\User::ROLE_USER, 'label' => 'Người dùng', 'sub_label' => 'Giảng viên & Học viên'],
                                    ['value' => \App\Models\User::ROLE_ADMIN, 'label' => 'Admin', 'sub_label' => 'Quản trị viên hệ thống'],
                                ];
                                @endphp
                                <x-custom-select wire:model.live="role" :options="$roleOptions" placeholder="Chọn vai trò" />
                                @error('role') <span class="text-xs text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Trạng thái <span class="text-rose-500">*</span></label>
                                @php
                                $statusOptions = [
                                    ['value' => 'active', 'label' => 'Đang hoạt động', 'sub_label' => 'Tài khoản bình thường'],
                                    ['value' => 'blocked', 'label' => 'Đã khóa', 'sub_label' => 'Tài khoản bị vô hiệu hóa'],
                                ];
                                @endphp
                                <x-custom-select wire:model.live="status" :options="$statusOptions" placeholder="Chọn trạng thái" />
                                @error('status') <span class="text-xs text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="pt-4">
                                <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-blue-600 to-cyan-600 px-4 py-3 text-sm font-bold text-white shadow-sm shadow-blue-500/25 transition-all hover:-translate-y-0.5 hover:from-blue-700 hover:to-cyan-700">
                                    Lưu tài khoản
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>
