<div>
    @php
        $initial = function_exists('mb_substr')
            ? mb_strtoupper(mb_substr($name ?? 'U', 0, 1, 'UTF-8'), 'UTF-8')
            : strtoupper(substr($name ?? 'U', 0, 1));
    @endphp
    <div class="mx-auto max-w-[1500px] space-y-6">
        <section class="flex flex-col justify-between gap-4 p-6 lg:flex-row lg:items-end lg:p-7 mb-2">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-slate-400">Người dùng</p>
                <h1 class="mt-1 text-3xl font-black tracking-tight text-slate-900">Chỉnh sửa hồ sơ</h1>
                <p class="mt-1 text-sm font-medium text-slate-500">{{ $name }} - {{ $email }}</p>
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
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-20 w-20 rounded-3xl object-cover bg-blue-100 shadow-sm">
                        @else
                            <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-blue-100 text-3xl font-black text-blue-700 shadow-sm">
                                {{ $initial }}
                            </div>
                        @endif
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Tài khoản hiện tại</p>
                            <h2 class="truncate text-xl font-black text-slate-900">{{ $name }}</h2>
                            <p class="truncate text-sm font-medium text-slate-500">{{ $email }}</p>
                        </div>
                    </div>

                    <dl class="mt-6 space-y-3 border-t border-slate-100 pt-5 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="font-medium text-slate-500">Vai trò</dt>
                            <dd class="font-bold text-slate-900">{{ $user->isAdmin() ? 'Admin' : 'Người dùng' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="font-medium text-slate-500">Trạng thái</dt>
                            <dd class="font-bold text-slate-900">
                                @if($status === 'active') Đang hoạt động @else Đã khóa @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </section>

            <section class="admin-card admin-card-hover overflow-visible rounded-2xl border">
                <form wire:submit="save">
                    
                    <div class="border-b border-slate-100 p-6 flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-black text-slate-900">Thông tin chỉnh sửa</h2>
                        </div>
                        <button type="submit" class="rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm shadow-emerald-500/25 transition-all hover:-translate-y-0.5 hover:from-emerald-600 hover:to-emerald-700">
                            Lưu thay đổi
                        </button>
                    </div>



                    <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                        <div class="space-y-5">
                            <div>
                                <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Họ và tên</label>
                                <input type="text" wire:model.live="name" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Email (Không thể thay đổi)</label>
                                <input type="email" wire:model.live="email" readonly class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-500 cursor-not-allowed focus:outline-none">
                                @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Vai trò</label>
                                @php
                                $roleOptions = [
                                    ['value' => \App\Models\User::ROLE_USER, 'label' => 'Người dùng', 'sub_label' => 'Giảng viên & Học viên'],
                                    ['value' => \App\Models\User::ROLE_ADMIN, 'label' => 'Admin', 'sub_label' => 'Quản trị viên hệ thống'],
                                ];
                                if ($user->isSuperAdmin()) {
                                    $roleOptions[] = ['value' => \App\Models\User::ROLE_SUPER_ADMIN, 'label' => 'Super Admin', 'sub_label' => 'Quản trị viên tối cao'];
                                }
                                $canEditRole = auth()->user()->isSuperAdmin();
                                // Không cho phép tự đổi quyền của chính mình
                                if ($user->id === auth()->id()) {
                                    $canEditRole = false;
                                }
                                @endphp
                                @if(!$canEditRole)
                                    <x-custom-select wire:model.live="role" :value="$role" :options="$roleOptions" placeholder="Chọn vai trò" disabled />
                                @else
                                    <x-custom-select wire:model.live="role" :value="$role" :options="$roleOptions" placeholder="Chọn vai trò" />
                                @endif
                                @error('role')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Trạng thái</label>
                                @php
                                $statusOptions = [
                                    ['value' => 'active', 'label' => 'Đang hoạt động', 'sub_label' => 'Tài khoản bình thường'],
                                    ['value' => 'blocked', 'label' => 'Đã khóa', 'sub_label' => 'Tài khoản bị vô hiệu hóa'],
                                ];
                                $canEditStatus = $user->id !== auth()->id();
                                @endphp
                                @if(!$canEditStatus)
                                    <x-custom-select wire:model.live="status" :value="$status" :options="$statusOptions" placeholder="Chọn trạng thái" disabled />
                                @else
                                    <x-custom-select wire:model.live="status" :value="$status" :options="$statusOptions" placeholder="Chọn trạng thái" />
                                @endif
                                @error('status')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>
