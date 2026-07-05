<div>
    @if ($showModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-on-background/40 p-4 backdrop-blur-sm" wire:click.self="closeModal">
            <div class="flex w-full max-w-md animate-in zoom-in-95 flex-col overflow-hidden rounded-2xl bg-white shadow-2xl duration-200">
                <div class="z-10 flex items-center justify-between border-b border-outline-variant/20 bg-white/90 p-6 backdrop-blur">
                    <h3 class="flex items-center gap-2 text-xl font-bold text-on-surface">
                        <x-user.icon name="key" class="text-tertiary" />
                        Tham gia lớp học
                    </h3>
                    <button type="button" wire:click="closeModal" class="rounded-full p-2 transition-colors hover:bg-surface-container">
                        <x-user.icon name="x" :size="20" class="text-on-surface-variant" />
                    </button>
                </div>
                
                <div class="p-6">
                    {{-- Flash message --}}
                    @if (session('status'))
                        <div class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-bold text-green-700">
                            <x-user.icon name="check-circle" :size="18" class="shrink-0 text-green-500" />
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif

                    <div class="space-y-6">
                        <label class="block">
                            <span class="mb-2 block text-sm font-bold text-on-surface">Nhập mã lớp <span class="text-error">*</span></span>
                            <span class="relative block">
                                <x-user.icon name="key" :size="20" class="absolute left-4 top-1/2 -translate-y-1/2 text-outline" />
                                <input
                                    type="text"
                                    wire:model="class_code"
                                    id="class_code"
                                    class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest py-3 pl-12 pr-4 font-mono text-base font-bold uppercase outline-none transition-all focus:border-tertiary focus:ring-2 focus:ring-tertiary/20"
                                    placeholder="VD: WEBHK14829"
                                >
                            </span>
                            @error('class_code')
                                <span class="mt-1.5 flex items-center gap-1.5 text-xs font-bold text-error">
                                    <x-user.icon name="alert-circle" :size="13" />
                                    {{ $message }}
                                </span>
                            @enderror
                        </label>


                        <label class="block">
                            <span class="mb-2 block text-sm font-bold text-on-surface">Họ và tên <span class="text-error">*</span></span>
                            <span class="relative block">
                                <x-user.icon name="user" :size="20" class="absolute left-4 top-1/2 -translate-y-1/2 text-outline" />
                                <input
                                    type="text"
                                    wire:model="full_name"
                                    id="full_name"
                                    class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest py-3 pl-12 pr-4 outline-none transition-all focus:border-tertiary focus:ring-2 focus:ring-tertiary/20"
                                    placeholder="Họ tên hiển thị trong lớp"
                                >
                            </span>
                            @error('full_name')
                                <span class="mt-1.5 flex items-center gap-1.5 text-xs font-bold text-error">
                                    <x-user.icon name="alert-circle" :size="13" />
                                    {{ $message }}
                                </span>
                            @enderror
                        </label>

                        <button
                            type="button"
                            wire:click="submit"
                            wire:loading.attr="disabled"
                            class="w-full rounded-xl bg-tertiary py-3 font-bold text-white shadow-md shadow-tertiary/20 transition-all hover:bg-tertiary/90 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="submit">Xác nhận tham gia</span>
                            <span wire:loading wire:target="submit">Đang xử lý...</span>
                        </button>
                    </div>

                    <div class="mt-6 flex items-start gap-2 rounded-xl border border-tertiary/20 bg-tertiary/5 p-3 text-sm text-tertiary">
                        <x-user.icon name="info" :size="16" class="mt-0.5 shrink-0" />
                        <span class="text-xs">Nếu lớp bật chế độ Yêu cầu phê duyệt, bạn sẽ cần chờ giảng viên xét duyệt để chính thức vào lớp.</span>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
