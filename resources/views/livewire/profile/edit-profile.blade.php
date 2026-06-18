<div class="mx-auto max-w-[1400px] space-y-6 p-4 pb-24 sm:p-8">
    <section class="flex flex-col justify-between gap-4 rounded-[2rem] border border-outline-variant/10 bg-white p-6 shadow-sm md:flex-row md:items-center">
        <div>
            <h1 class="flex items-center gap-3 text-2xl font-extrabold uppercase tracking-tight text-slate-900">
                <x-user.icon name="user" class="text-primary" />Hồ sơ cá nhân
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                Quản lý thông tin hồ sơ và bảo mật tài khoản của bạn.
            </p>
        </div>
    </section>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-stretch">
        <!-- Profile Info Column -->
        <div class="bg-white shadow-sm overflow-hidden rounded-2xl border border-slate-200 flex flex-col">
            <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-3">
                <h3 class="text-lg font-bold text-slate-900">Hồ sơ của bạn</h3>
                <p class="mt-1 text-sm text-slate-500">
                    Cập nhật thông tin tài khoản và địa chỉ email của bạn.
                </p>
            </div>
            <form wire:submit="updateProfileInformation" class="flex flex-col flex-1">
                <div class="px-6 py-4 sm:px-8 sm:py-5 flex-1 space-y-4">
                    @if (session('status'))
                        <div class="font-medium text-sm text-green-600 bg-green-50 p-4 rounded-xl border border-green-200">
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- Avatar Upload -->
                    <div class="flex flex-col items-center justify-center">
                        <label for="avatar-upload" class="relative group cursor-pointer block">
                            @if ($avatar)
                                <img src="{{ $avatar->temporaryUrl() }}" class="h-14 w-14 rounded-full object-cover ring-4 ring-primary/20 transition-all group-hover:opacity-90">
                            @elseif(auth()->user()->avatar)
                                <img src="{{ asset('storage/'.auth()->user()->avatar) }}" class="h-14 w-14 rounded-full object-cover ring-4 ring-primary/20 transition-all group-hover:opacity-90">
                            @else
                                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed={{ urlencode(auth()->user()->name) }}&backgroundColor=e5eeff" class="h-14 w-14 rounded-full object-cover ring-4 ring-primary/20 transition-all group-hover:opacity-90">
                            @endif
                            
                            <!-- Hover overlay -->
                            <div class="absolute inset-0 rounded-full bg-slate-900/40 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <x-user.icon name="edit" class="text-white" :size="18" />
                            </div>
                            
                            <input id="avatar-upload" type="file" wire:model="avatar" class="sr-only" accept="image/*">
                        </label>
                        <span class="mt-1.5 block text-sm font-bold text-slate-700">Ảnh đại diện</span>
                        @error('avatar') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-bold text-slate-700 mb-2">Họ và tên</label>
                        <input type="text" wire:model="name" id="name" autocomplete="name" class="block w-full text-base border-slate-300 focus:outline-none focus:ring-primary focus:border-primary rounded-xl transition">
                        @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-bold text-slate-700 mb-2">Địa chỉ Email</label>
                        <input type="email" wire:model="email" id="email" autocomplete="email" class="block w-full text-base border-slate-300 focus:outline-none focus:ring-primary focus:border-primary rounded-xl transition">
                        @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="px-6 py-3 bg-slate-50 border-t border-slate-200 text-right flex items-center justify-end gap-3">
                    <div wire:loading wire:target="updateProfileInformation" class="text-sm text-slate-500">
                        Đang lưu...
                    </div>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex justify-center py-2.5 px-6 border border-transparent shadow-sm text-sm font-bold rounded-xl text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition disabled:opacity-50">
                        Lưu thông tin
                    </button>
                </div>
            </form>
        </div>

        <!-- Password Column -->
        <div class="bg-white shadow-sm overflow-hidden rounded-2xl border border-slate-200 flex flex-col">
            <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-3">
                <h3 class="text-lg font-bold text-slate-900">Đổi mật khẩu</h3>
                <p class="mt-1 text-sm text-slate-500 truncate" title="Đảm bảo tài khoản của bạn đang sử dụng một mật khẩu dài, ngẫu nhiên để an toàn hơn.">
                    Sử dụng mật khẩu dài, ngẫu nhiên để bảo mật tài khoản.
                </p>
            </div>
            <form wire:submit="updatePassword" class="flex flex-col flex-1">
                <div class="px-6 py-4 sm:px-8 sm:py-5 flex-1 space-y-4">
                    @if (session('password_status'))
                        <div class="font-medium text-sm text-green-600 bg-green-50 p-4 rounded-xl border border-green-200">
                            {{ session('password_status') }}
                        </div>
                    @endif

                    <!-- Current Password -->
                    <div x-data="{ show: false }" class="pt-3">
                        <label for="current_password" class="block text-sm font-bold text-slate-700 mb-2">Mật khẩu hiện tại</label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" wire:model="current_password" id="current_password" class="block w-full text-base border-slate-300 focus:outline-none focus:ring-primary focus:border-primary rounded-xl transition pr-10">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                                <x-user.icon name="eye" x-show="!show" class="w-5 h-5" />
                                <x-user.icon name="eye-off" x-show="show" class="w-5 h-5" style="display: none;" />
                            </button>
                        </div>
                        @error('current_password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- New Password -->
                    <div x-data="{ show: false }">
                        <label for="password" class="block text-sm font-bold text-slate-700 mb-2">Mật khẩu mới</label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" wire:model="password" id="password" class="block w-full text-base border-slate-300 focus:outline-none focus:ring-primary focus:border-primary rounded-xl transition pr-10">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                                <x-user.icon name="eye" x-show="!show" class="w-5 h-5" />
                                <x-user.icon name="eye-off" x-show="show" class="w-5 h-5" style="display: none;" />
                            </button>
                        </div>
                        @error('password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div x-data="{ show: false }">
                        <label for="password_confirmation" class="block text-sm font-bold text-slate-700 mb-2">Xác nhận mật khẩu mới</label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" wire:model="password_confirmation" id="password_confirmation" class="block w-full text-base border-slate-300 focus:outline-none focus:ring-primary focus:border-primary rounded-xl transition pr-10">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                                <x-user.icon name="eye" x-show="!show" class="w-5 h-5" />
                                <x-user.icon name="eye-off" x-show="show" class="w-5 h-5" style="display: none;" />
                            </button>
                        </div>
                        @error('password_confirmation') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="px-6 py-3 bg-slate-50 border-t border-slate-200 text-right flex items-center justify-end gap-3">
                    <div wire:loading wire:target="updatePassword" class="text-sm text-slate-500">
                        Đang lưu...
                    </div>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex justify-center py-2.5 px-6 border border-transparent shadow-sm text-sm font-bold rounded-xl text-white bg-slate-800 hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition disabled:opacity-50">
                        Cập nhật mật khẩu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
