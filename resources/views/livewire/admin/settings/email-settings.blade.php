<div class="space-y-8 p-6 md:p-8">
    <div>
        <div class="mb-1 flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-900">Cấu hình Mail Server (SMTP)</h2>
            <div class="flex items-center gap-3">
                <button type="button" wire:click="applyGmailPreset" class="text-sm font-semibold text-emerald-600 hover:underline">Dùng cấu hình Gmail</button>
                <button type="button" x-data @click="$dispatch('open-test-mail-modal')" class="text-sm font-semibold text-blue-600 hover:underline">Gửi mail test</button>
            </div>
        </div>
        <p class="mb-6 text-sm text-slate-500">Thiết lập kết nối để hệ thống gửi các thông báo tự động tới người dùng.</p>

        <div @class([
            'mb-6 rounded-xl border p-4 text-sm',
            'border-amber-200 bg-amber-50 text-amber-800' => $deliveryStatus['warning'] ?? false,
            'border-emerald-200 bg-emerald-50 text-emerald-800' => ! ($deliveryStatus['warning'] ?? false),
        ])>
            <p class="font-bold">{{ $deliveryStatus['title'] ?? 'Trạng thái gửi email' }}</p>
            <p class="mt-1 font-medium">{{ $deliveryStatus['message'] ?? '' }}</p>
        </div>

        @if (session()->has('email_success'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 font-medium text-emerald-700 text-sm">
                {{ session('email_success') }}
            </div>
        @endif

        <form wire:submit.prevent="save">
            <div class="mb-8 p-5 bg-slate-50 border border-slate-200 rounded-2xl space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800">Cài đặt chung cho thông báo</h3>
                    <button type="button" x-data @click="$dispatch('open-test-telegram-modal')" class="text-sm font-semibold text-blue-600 hover:underline">Gửi test Telegram</button>
                </div>

                <div class="pt-2 border-t border-slate-200">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Telegram Bot Token</label>
                    <input type="text" wire:model="telegram_bot_token" placeholder="VD: 1234567890:AAH_..." class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-slate-500 mt-1">Tạo bot qua <a href="https://t.me/BotFather" target="_blank" class="text-blue-600 hover:underline">@BotFather</a> để lấy token.</p>
                    @error('telegram_bot_token') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-700">Mail Driver</label>
                    <select wire:model="mail_driver" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        <option value="smtp">SMTP</option>
                    </select>
                    @error('mail_driver') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-700">Mail Host</label>
                    <input type="text" wire:model="mail_host" placeholder="smtp.gmail.com" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs font-medium text-slate-500">Gmail: smtp.gmail.com</p>
                    @error('mail_host') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-700">Mail Port</label>
                    <input type="text" wire:model="mail_port" placeholder="465" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs font-medium text-slate-500">Gmail khuyến nghị: 587 với TLS.</p>
                    @error('mail_port') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-700">Mã hóa (Encryption)</label>
                    <select wire:model="mail_encryption" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        <option value="">Không mã hóa</option>
                        <option value="tls">TLS</option>
                        <option value="ssl">SSL</option>
                    </select>
                    @error('mail_encryption') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-700">Mail Username</label>
                    <input type="text" wire:model="mail_username" placeholder="your_email@gmail.com" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs font-medium text-slate-500">Email Gmail dùng để gửi.</p>
                    @error('mail_username') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-2" x-data="{ show: false }">
                    <label class="block text-sm font-semibold text-slate-700">Mail Password (App Password)</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" wire:model="mail_password" placeholder="Mật khẩu ứng dụng..." class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 pr-10 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        <button type="button" @click="show = !show" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600">
                            <x-user.icon name="eye" :size="20" x-show="!show" />
                            <x-user.icon name="eye-off" :size="20" x-show="show" style="display: none;" />
                        </button>
                    </div>
                    <p class="text-xs font-medium text-slate-500">Dùng Gmail App Password 16 ký tự, không dùng mật khẩu đăng nhập Gmail thường.</p>
                    @error('mail_password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-700">Email người gửi (From Address)</label>
                    <input type="email" wire:model="mail_from_address" placeholder="noreply@sams.com" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    @error('mail_from_address') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-700">Tên người gửi (From Name)</label>
                    <input type="text" wire:model="mail_from_name" placeholder="SAMS System Notification" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    @error('mail_from_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-8 flex items-center justify-end gap-3 border-t border-slate-100 pt-6">
                <div wire:loading wire:target="save" class="text-sm font-medium text-slate-500 mr-2">Đang lưu...</div>
                <button type="button" onclick="window.location.reload()" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50">
                    Hủy
                </button>
                <button type="submit" class="rounded-xl bg-gradient-to-r from-blue-600 to-cyan-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm shadow-blue-500/25 transition-all hover:-translate-y-0.5 hover:from-blue-700 hover:to-cyan-700">
                    Lưu cấu hình Mail
                </button>
            </div>
        </form>
    </div>

    <!-- Modal Gửi Test Mail -->
    @teleport('body')
    <div x-data="{ open: false }" 
         x-on:open-test-mail-modal.window="open = true" 
         x-on:close-test-mail-modal.window="open = false" 
         x-cloak 
         x-show="open" 
         class="relative z-[9999]">
         
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="open" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <x-user.icon name="mail" class="h-6 w-6 text-blue-600" />
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-base font-semibold leading-6 text-slate-900">Gửi mail thử nghiệm</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-slate-500 mb-4">Hệ thống sẽ sử dụng cấu hình SMTP đang nhập bên ngoài để gửi thử một email.</p>
                                    
                                    @if (session()->has('test_success'))
                                        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm font-medium text-emerald-700">
                                            {{ session('test_success') }}
                                        </div>
                                    @endif

                                    @if (session()->has('test_error'))
                                        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm font-medium text-rose-700 max-h-40 overflow-y-auto">
                                            {{ session('test_error') }}
                                        </div>
                                    @endif

                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-slate-700">Email người nhận</label>
                                        <input type="email" wire:model="test_email" placeholder="Nhập email của bạn..." class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        @error('test_email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" wire:click="sendTestEmail" wire:loading.attr="disabled" class="inline-flex w-full justify-center rounded-xl bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto transition-colors disabled:opacity-50">
                            <span wire:loading.remove wire:target="sendTestEmail">Gửi thử</span>
                            <span wire:loading wire:target="sendTestEmail">Đang gửi...</span>
                        </button>
                        <button type="button" @click="open = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Đóng</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endteleport
    <!-- Modal Gửi Test Telegram -->
    @teleport('body')
    <div x-data="{ open: false }" 
         x-on:open-test-telegram-modal.window="open = true" 
         x-on:close-test-telegram-modal.window="open = false" 
         x-cloak 
         x-show="open" 
         class="relative z-[9999]">
         
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="open" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <x-user.icon name="message-circle" class="h-6 w-6 text-blue-600" />
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-base font-semibold leading-6 text-slate-900">Gửi tin nhắn Telegram thử nghiệm</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-slate-500 mb-4">Hệ thống sẽ sử dụng Token đang nhập bên ngoài để gửi thử một tin nhắn.</p>
                                    
                                    @if (session()->has('test_telegram_success'))
                                        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm font-medium text-emerald-700">
                                            {{ session('test_telegram_success') }}
                                        </div>
                                    @endif

                                    @if (session()->has('test_telegram_error'))
                                        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm font-medium text-rose-700 max-h-40 overflow-y-auto">
                                            {{ session('test_telegram_error') }}
                                        </div>
                                    @endif

                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-slate-700">Chat ID người nhận</label>
                                        <input type="text" wire:model="test_telegram_chat_id" placeholder="Nhập Chat ID của bạn..." class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        @error('test_telegram_chat_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" wire:click="sendTestTelegram" wire:loading.attr="disabled" class="inline-flex w-full justify-center rounded-xl bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto transition-colors disabled:opacity-50">
                            <span wire:loading.remove wire:target="sendTestTelegram">Gửi thử</span>
                            <span wire:loading wire:target="sendTestTelegram">Đang gửi...</span>
                        </button>
                        <button type="button" @click="open = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Đóng</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endteleport
</div>
