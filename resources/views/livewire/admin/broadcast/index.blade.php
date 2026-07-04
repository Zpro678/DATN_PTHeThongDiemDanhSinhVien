<div class="flex min-h-[calc(100vh-8rem)] flex-col space-y-5">
    <div class="flex-none p-5 lg:p-6 mb-2">
        <div class="relative z-10 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-slate-400">Broadcast</p>
                <h1 class="mt-1 text-2xl font-black text-slate-900">Gửi thông báo hàng loạt</h1>
                <p class="mt-1 text-sm text-slate-500">Sử dụng tính năng này để gửi email đến hàng ngàn người dùng trong hệ thống mà không làm gián đoạn máy chủ.</p>
            </div>
        </div>
    </div>

    <div class="admin-card flex-none overflow-visible rounded-2xl border bg-white p-4 lg:p-5 relative z-20">

        <form wire:submit.prevent="sendBroadcast" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Cột trái: Thiết lập -->
                <div class="md:col-span-1 space-y-6">
                    <div>
                        <label for="target" class="block text-sm font-medium text-on-surface mb-2">Nhóm đối tượng nhận</label>
                        <select wire:model="target" id="target" class="w-full rounded-lg border-outline-variant/50 bg-surface-container-lowest px-4 py-2 text-on-surface focus:border-primary focus:ring-primary/20 transition-all">
                            <option value="all">Tất cả người dùng (Active)</option>
                            <option value="free">Người dùng gói Miễn phí (Free)</option>
                            <option value="pro">Người dùng có gói Đăng ký (Pro)</option>
                        </select>
                        @error('target') <span class="text-error text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Cột phải: Nội dung -->
                <div class="md:col-span-2 space-y-6">
                    <div>
                        <label for="subject" class="block text-sm font-medium text-on-surface mb-2">Tiêu đề Email</label>
                        <input type="text" wire:model="subject" id="subject" placeholder="Nhập tiêu đề thông báo..." class="w-full rounded-lg border-outline-variant/50 bg-surface-container-lowest px-4 py-2 text-on-surface focus:border-primary focus:ring-primary/20 transition-all">
                        @error('subject') <span class="text-error text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="content" class="block text-sm font-medium text-on-surface mb-2">Nội dung (Hỗ trợ HTML cơ bản)</label>
                        <textarea wire:model="content" id="content" rows="12" placeholder="Ví dụ: <h2>Thông báo bảo trì</h2><p>Hệ thống sẽ bảo trì vào ngày mai...</p>" class="w-full rounded-lg border-outline-variant/50 bg-surface-container-lowest px-4 py-2 text-on-surface focus:border-primary focus:ring-primary/20 transition-all font-mono text-sm"></textarea>
                        @error('content') <span class="text-error text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-outline-variant/30">
                        <button type="button" wire:click="$set('content', '')" class="px-5 py-2.5 text-sm font-medium text-on-surface-variant hover:text-on-surface transition-colors">
                            Làm lại
                        </button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-medium text-on-primary bg-primary rounded-lg hover:bg-primary/90 focus:ring-4 focus:ring-primary/20 transition-all inline-flex items-center gap-2" wire:loading.attr="disabled" wire:target="sendBroadcast">
                            <svg wire:loading wire:target="sendBroadcast" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <x-user.icon name="send" :size="18" wire:loading.remove wire:target="sendBroadcast" />
                            <span wire:loading.remove wire:target="sendBroadcast">Gửi Thông Báo</span>
                            <span wire:loading wire:target="sendBroadcast">Đang xử lý...</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
