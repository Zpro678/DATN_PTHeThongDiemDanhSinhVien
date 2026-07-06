<div>
    @if ($showModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-on-background/40 p-4 backdrop-blur-sm" wire:click.self="closeModal">
            <div class="flex w-full max-w-lg animate-in zoom-in-95 flex-col overflow-hidden rounded-2xl bg-white shadow-2xl duration-200">
                <div class="z-10 flex items-center justify-between bg-white/90 px-8 pb-4 pt-8 backdrop-blur">
                    <h3 class="flex items-center gap-2 text-xl font-bold text-on-surface">
                        <x-user.icon name="key" class="text-tertiary" />
                        Tham gia lớp học
                    </h3>
                    <button type="button" wire:click="closeModal" class="rounded-full p-2 transition-colors hover:bg-surface-container">
                        <x-user.icon name="x" :size="20" class="text-on-surface-variant" />
                    </button>
                </div>
                
                <div class="px-8 pb-8">
                    <div class="mb-6 flex items-start gap-2 rounded-xl border border-tertiary/20 bg-tertiary/5 p-3.5 text-sm text-tertiary">
                        <x-user.icon name="info" :size="16" class="mt-0.5 shrink-0" />
                        <span class="text-xs">Nếu lớp bật chế độ Yêu cầu phê duyệt, bạn sẽ cần chờ giảng viên xét duyệt để chính thức vào lớp.</span>
                    </div>

                    {{-- Flash message --}}
                    @if (session('status'))
                        <div class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-bold text-green-700">
                            <x-user.icon name="check-circle" :size="18" class="shrink-0 text-green-500" />
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif

                    @if($confirmingClass)
                        <div class="space-y-4">
                            <div class="rounded-xl border border-outline-variant/30 bg-surface-container-lowest p-4 text-center">
                                <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-tertiary/10 text-tertiary">
                                    <x-user.icon name="book-open" :size="20" />
                                </div>
                                <h4 class="mb-1 text-xl font-bold text-on-surface">{{ $confirmingClass['name'] ?? 'Tên lớp' }}</h4>
                                <p class="text-base font-medium text-on-surface-variant">GV: <span class="font-bold">{{ $confirmingClass['owner']['name'] ?? 'Chưa cập nhật' }}</span></p>
                            </div>

                            <div class="flex gap-3">
                                <button
                                    type="button"
                                    wire:click="cancelConfirm"
                                    class="flex-1 rounded-xl border border-outline-variant/50 bg-white py-2.5 font-bold text-on-surface transition-all hover:bg-surface-container active:scale-[0.98]"
                                >
                                    Hủy
                                </button>
                                <button
                                    type="button"
                                    wire:click="confirmJoin"
                                    wire:loading.attr="disabled"
                                    class="flex-1 rounded-xl bg-tertiary py-2.5 font-bold text-white shadow-md shadow-tertiary/20 transition-all hover:bg-tertiary/90 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
                                >
                                    <span wire:loading.remove wire:target="confirmJoin">Xác nhận</span>
                                    <span wire:loading wire:target="confirmJoin">Đang xử lý...</span>
                                </button>
                            </div>
                        </div>
                    @else
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

                            <div class="flex gap-3">
                                <button
                                    type="button"
                                    wire:click="closeModal"
                                    class="flex-1 rounded-xl border border-outline-variant/50 bg-white py-2.5 font-bold text-on-surface transition-all hover:bg-surface-container active:scale-[0.98]"
                                >
                                    Hủy bỏ
                                </button>
                                <button
                                    type="button"
                                    wire:click="checkCode"
                                    wire:loading.attr="disabled"
                                    class="flex-1 rounded-xl bg-tertiary py-2.5 font-bold text-white shadow-md shadow-tertiary/20 transition-all hover:bg-tertiary/90 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
                                >
                                    <span wire:loading.remove wire:target="checkCode">Vào lớp</span>
                                    <span wire:loading wire:target="checkCode">Đang xử lý...</span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
