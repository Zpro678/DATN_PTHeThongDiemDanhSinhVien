@php
    $user = auth()->user();
    $initial = function_exists('mb_substr')
        ? mb_strtoupper(mb_substr($user->name ?? 'U', 0, 1, 'UTF-8'), 'UTF-8')
        : strtoupper(substr($user->name ?? 'U', 0, 1));
    $roleLabel = $user->isAdmin() ? 'Admin' : 'Người dùng';
    $statusLabel = $user->status === 'active' ? 'Đang hoạt động' : 'Bị khóa';
    $statusColor = $user->status === 'active'
        ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
        : 'bg-rose-50 text-rose-700 border-rose-100';
@endphp

<div x-data="{ view: 'profile' }" class="w-full p-4 pb-24 md:p-8 md:pb-12 space-y-6">
    <section class="flex flex-col justify-between gap-4 p-6 lg:flex-row lg:items-end lg:p-7 mb-2">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-slate-400">Tài khoản cá nhân</p>
            <h1 class="mt-1 text-3xl font-black tracking-tight text-slate-900">Hồ sơ của bạn</h1>
            <p class="mt-1 text-sm font-medium text-slate-500">Quản lý thông tin hồ sơ và bảo mật tài khoản của bạn.</p>
        </div>
    </section>

    <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-[380px_minmax(0,1fr)]">
        <section class="admin-card admin-card-hover flex h-full flex-col overflow-hidden rounded-2xl border bg-white">
            <div class="h-24 bg-gradient-to-r from-blue-600 via-cyan-500 to-emerald-500"></div>
            <div class="px-6 pb-6">
                <div class="-mt-12 flex justify-center relative">
                    <label for="avatar-upload" class="group relative cursor-pointer block">
                        @if ($avatar)
                            <img src="{{ $avatar->temporaryUrl() }}" class="flex h-24 w-24 items-center justify-center rounded-3xl border-4 border-white object-cover bg-blue-100 shadow-sm transition-all group-hover:opacity-90">
                        @elseif(auth()->user()->avatar)
                            <img src="{{ auth()->user()->avatar_url }}" class="flex h-24 w-24 items-center justify-center rounded-3xl border-4 border-white object-cover bg-blue-100 shadow-sm transition-all group-hover:opacity-90">
                        @else
                            <div class="flex h-24 w-24 items-center justify-center rounded-3xl border-4 border-white bg-blue-100 text-3xl font-black text-blue-700 shadow-sm transition-all group-hover:opacity-90">
                                {{ $initial }}
                            </div>
                        @endif
                        
                        <!-- Hover overlay -->
                        <div class="absolute inset-0 rounded-3xl bg-slate-900/40 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <x-user.icon name="edit" class="text-white" :size="24" />
                        </div>
                        
                        <input id="avatar-upload" type="file" wire:model.live="avatar" class="sr-only" accept="image/*">
                    </label>
                </div>
                <div class="text-center mt-2">
                    <div wire:loading wire:target="avatar" class="text-xs text-blue-600 font-bold mb-1">Đang tải ảnh lên...</div>
                    @error('avatar') <span class="text-red-500 text-xs font-bold block">{{ $message }}</span> @enderror
                </div>

                <div class="mt-4 text-center">
                    <h2 class="text-xl font-black text-slate-900">{{ $user->name }}</h2>
                    <p class="text-sm font-medium text-slate-500">{{ $user->email }}</p>
                    <div class="mt-4 flex flex-wrap justify-center gap-2">
                        <span class="rounded-lg border px-3 py-1 text-[10px] font-bold uppercase tracking-wider {{ $user->isAdmin() ? 'border-blue-100 bg-blue-50 text-blue-700' : 'border-slate-100 bg-slate-50 text-slate-600' }}">
                            {{ $roleLabel }}
                        </span>
                        <span class="rounded-lg border px-3 py-1 text-[10px] font-bold uppercase tracking-wider {{ $statusColor }}">
                            {{ $statusLabel }}
                        </span>
                    </div>
                </div>

                <dl class="mt-6 space-y-3 border-t border-slate-100 pt-5 text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="font-medium text-slate-500">Ngày tham gia</dt>
                        <dd class="font-bold text-slate-900">{{ $user->created_at?->format('d/m/Y') ?? 'N/A' }}</dd>
                    </div>
                </dl>
            </div>
        </section>

        <div class="grid min-h-[400px]" x-cloak>
            <div x-show="view === 'profile'" class="col-start-1 row-start-1 w-full h-full" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 -translate-x-4">
                <section class="admin-card admin-card-hover flex h-full flex-col overflow-hidden rounded-2xl border bg-white">
                    <div class="flex flex-col gap-4 border-b border-slate-100 p-6 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <h2 class="text-xl font-black text-slate-900">Chỉnh sửa hồ sơ</h2>
                            <p class="mt-1 text-sm font-medium text-slate-500">Cập nhật thông tin tài khoản của bạn.</p>
                        </div>
                        <div class="flex gap-2">
                            @if(!$user->isAdmin())
                            <a href="{{ route('activity-log', ['ma_user' => auth()->id()]) }}" wire:navigate
                                class="admin-soft-button inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                                <x-user.icon name="history" :size="14" />
                                Lịch sử thao tác
                            </a>
                            @endif
                            <button type="button" @click="view = 'password'" class="admin-soft-button rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50">
                                {{ auth()->user()->password ? 'Đổi mật khẩu' : 'Thiết lập mật khẩu' }}
                            </button>
                        </div>
                    </div>

                    <form wire:submit.prevent="updateProfileInformation" class="flex flex-1 flex-col">
                        <div class="flex-1 p-6">


                            <div class="space-y-5">
                                <div>
                                    <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Họ và tên</label>
                                    <input type="text" wire:model="name" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>


                                <div>
                                    <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Email (Không thể thay đổi)</label>
                                    <input type="email" wire:model="email" readonly class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-500 cursor-not-allowed focus:outline-none">
                                </div>

                                <div>
                                    <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Telegram Chat ID</label>
                                    <input type="text" wire:model="telegram_chat_id" placeholder="VD: 123456789" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <p class="mt-1.5 text-xs text-slate-500">Nhắn tin cho <a href="https://t.me/userinfobot" target="_blank" class="text-blue-600 hover:underline">@userinfobot</a> để lấy ID của bạn.</p>
                                    @error('telegram_chat_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mt-auto flex justify-end gap-3 border-t border-slate-100 bg-slate-50 p-6">
                            <div wire:loading wire:target="updateProfileInformation" class="text-sm text-slate-500 self-center">
                                Đang lưu...
                            </div>
                            <button type="submit" wire:loading.attr="disabled" class="rounded-xl bg-gradient-to-r from-blue-600 to-cyan-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm shadow-blue-500/25 transition-all hover:-translate-y-0.5 hover:from-blue-700 hover:to-cyan-700">
                                Lưu thông tin
                            </button>
                        </div>
                    </form>
                </section>
            </div>

            <!-- Password Form -->
            <div x-cloak x-show="view === 'password'" class="col-start-1 row-start-1 w-full h-full" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 -translate-x-4">
                <section class="admin-card admin-card-hover flex h-full flex-col overflow-hidden rounded-2xl border bg-white">
                    <div class="border-b border-slate-100 px-6 py-6 lg:p-6 lg:pb-4 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between bg-white relative z-10">
                        <div>
                            <h2 class="text-xl font-black text-slate-900">{{ auth()->user()->password ? 'Đổi mật khẩu' : 'Thiết lập mật khẩu' }}</h2>
                            <p class="mt-1 text-sm font-medium text-slate-500">Đảm bảo tài khoản của bạn sử dụng mật khẩu dài, ngẫu nhiên để an toàn.</p>
                        </div>
                    </div>
                    
                    <form wire:submit.prevent="updatePassword" class="relative z-10 flex flex-1 flex-col bg-white">
                        <div class="flex-1 space-y-4 px-6 py-5">


                            @if(auth()->user()->password)
                            <div x-data="{ showPass1: false }" wire:key="current-password-field">
                                <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Mật khẩu hiện tại</label>
                                <div class="relative">
                                    <input :type="showPass1 ? 'text' : 'password'" wire:model="current_password" autocomplete="new-password" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 pr-10">
                                    <button type="button" @click="showPass1 = !showPass1" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                                        <x-user.icon name="eye" x-show="!showPass1" class="w-5 h-5" />
                                        <x-user.icon name="eye-off" x-show="showPass1" class="w-5 h-5" style="display: none;" />
                                    </button>
                                </div>
                                @error('current_password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            @endif

                            <div x-data="{ showPass2: false }">
                                <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Mật khẩu mới</label>
                                <div class="relative">
                                    <input :type="showPass2 ? 'text' : 'password'" wire:model="password" autocomplete="new-password" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 pr-10">
                                    <button type="button" @click="showPass2 = !showPass2" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                                        <x-user.icon name="eye" x-show="!showPass2" class="w-5 h-5" />
                                        <x-user.icon name="eye-off" x-show="showPass2" class="w-5 h-5" style="display: none;" />
                                    </button>
                                </div>
                                @error('password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div x-data="{ showPass3: false }">
                                <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Xác nhận mật khẩu mới</label>
                                <div class="relative">
                                    <input :type="showPass3 ? 'text' : 'password'" wire:model="password_confirmation" autocomplete="new-password" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 pr-10">
                                    <button type="button" @click="showPass3 = !showPass3" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                                        <x-user.icon name="eye" x-show="!showPass3" class="w-5 h-5" />
                                        <x-user.icon name="eye-off" x-show="showPass3" class="w-5 h-5" style="display: none;" />
                                    </button>
                                </div>
                                @error('password_confirmation') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="relative z-10 mt-auto flex justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                            <button type="button" @click="view = 'profile'" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50">
                                Quay lại
                            </button>
                            <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-slate-800">
                                {{ auth()->user()->password ? 'Xác nhận đổi' : 'Xác nhận thiết lập' }}
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
</div>
