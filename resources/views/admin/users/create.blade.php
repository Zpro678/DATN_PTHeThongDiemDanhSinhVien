<x-admin-layout title="Thêm người dùng mới">
    <div class="mx-auto max-w-[1500px] space-y-6">
        <section class="admin-card flex flex-col justify-between gap-4 overflow-hidden rounded-3xl border p-6 lg:flex-row lg:items-end lg:p-7">
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

        <div x-data="{
            name: '{{ old('name', '') }}',
            email: '{{ old('email', '') }}',
            role: '{{ old('is_admin', '0') }}',
            status: '{{ old('status', 'active') }}',
            avatarPreview: null,
            handleAvatarChange(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.avatarPreview = e.target.result;
                    };
                    reader.readAsDataURL(file);
                } else {
                    this.avatarPreview = null;
                }
            }
        }" class="grid grid-cols-1 gap-6 xl:grid-cols-[360px_minmax(0,1fr)]">
            <section class="admin-card admin-card-hover rounded-2xl border">
                <div class="p-6">
                    <div class="flex items-center gap-4">
                        <template x-if="avatarPreview">
                            <img :src="avatarPreview" class="h-20 w-20 rounded-3xl object-cover bg-blue-100 shadow-sm" alt="Preview">
                        </template>
                        <template x-if="!avatarPreview">
                            <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-blue-100 text-3xl font-black text-blue-700 shadow-sm" x-text="name ? name.charAt(0).toUpperCase() : '+'">
                            </div>
                        </template>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Tài khoản mới</p>
                            <h2 class="truncate text-xl font-black text-slate-900" x-text="name || 'Chưa có tên'">Chưa có tên</h2>
                            <p class="truncate text-sm font-medium text-slate-500" x-text="email || 'Chưa có email'">Chưa có email</p>
                        </div>
                    </div>

                    <dl class="mt-6 space-y-3 border-t border-slate-100 pt-5 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="font-medium text-slate-500">Vai trò</dt>
                            <dd class="font-bold text-slate-900" x-text="role === '1' ? 'Admin' : 'Người dùng'">Người dùng</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="font-medium text-slate-500">Trạng thái</dt>
                            <dd class="font-bold text-slate-900" x-text="status === 'active' ? 'Đang hoạt động' : 'Đã khóa'">Đang hoạt động</dd>
                        </div>
                    </dl>
                </div>
            </section>

            <section class="admin-card admin-card-hover overflow-hidden rounded-2xl border">
                <div class="border-b border-slate-100 p-6">
                    <h2 class="text-xl font-black text-slate-900">Thông tin khởi tạo</h2>
                    <p class="mt-1 text-sm font-medium text-slate-500">Nhập các thông tin cơ bản để tạo tài khoản.</p>
                </div>

                <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                        <div class="col-span-1 lg:col-span-2">
                            <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Ảnh đại diện (Tùy chọn)</label>
                            <input type="file" name="avatar" accept="image/*" @change="handleAvatarChange" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('avatar') <span class="text-xs text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Họ và tên <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" x-model="name" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Nhập họ và tên">
                                @error('name') <span class="text-xs text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Email <span class="text-rose-500">*</span></label>
                                <input type="email" name="email" x-model="email" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Nhập email">
                                @error('email') <span class="text-xs text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Mật khẩu <span class="text-rose-500">*</span></label>
                                <input type="password" name="password" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Nhập mật khẩu (tối thiểu 8 ký tự)">
                                @error('password') <span class="text-xs text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Vai trò <span class="text-rose-500">*</span></label>
                                <select name="is_admin" x-model="role" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="0">Người dùng</option>
                                    <option value="1">Admin</option>
                                </select>
                                @error('is_admin') <span class="text-xs text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Trạng thái <span class="text-rose-500">*</span></label>
                                <select name="status" x-model="status" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="active">Đang hoạt động</option>
                                    <option value="blocked">Đã khóa</option>
                                </select>
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
</x-admin-layout>
