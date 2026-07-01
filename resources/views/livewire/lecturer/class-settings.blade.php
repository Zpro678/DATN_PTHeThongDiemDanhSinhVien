<div class="mx-auto max-w-7xl p-4 sm:p-6 lg:p-8 animate-in fade-in slide-in-from-bottom-4 duration-500">

    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="flex items-center gap-3 text-2xl font-bold text-on-surface">
                    Cài đặt lớp học
                </h1>
                <p class="mt-1 text-sm text-on-surface-variant">Chỉnh sửa thông tin và thiết lập cho lớp <span class="font-bold text-primary">{{ $courseClass->join_key }}</span></p>
            </div>
            <button type="button" wire:click="confirmDelete"
               class="inline-flex shrink-0 whitespace-nowrap items-center gap-2 rounded-xl border border-error/30 bg-white px-4 py-2 text-sm font-bold text-error transition-colors hover:bg-error/10">
                <x-user.icon name="trash-2" :size="16" />
                Xóa lớp học
            </button>
        </div>
    </div>

    @if (session('status'))
        <div class="mb-6 flex items-center rounded-2xl border border-green-200 bg-green-50 p-4 font-bold text-green-700">
            <x-user.icon name="check-circle" :size="20" class="mr-3 text-green-500" />
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="save" id="class-settings-form">
        <div class="flex flex-col lg:flex-row gap-6 items-start">

            {{-- ===== CỘT TRÁI ===== --}}
            <div class="flex-1 min-w-0 space-y-6">

                {{-- Thông tin chung --}}
                <div class="rounded-2xl border border-outline-variant/20 bg-white shadow-sm overflow-hidden">
                    <div class="flex items-center gap-3 border-b border-outline-variant/10 bg-surface-container-lowest/50 px-6 py-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                            <x-user.icon name="file-text" :size="16" />
                        </span>
                        <h2 class="text-lg font-bold text-on-surface">Thông tin chung</h2>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                        <label class="col-span-1 md:col-span-2 block">
                            <span class="mb-2 block text-base font-bold text-on-surface">Tên lớp / môn học <span class="text-error">*</span></span>
                            <input type="text" wire:model="name" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 text-base outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20" placeholder="Nhập tên môn học">
                            @error('name') <span class="text-error text-sm mt-1 block">{{ $message }}</span> @enderror
                        </label>

                        <label class="col-span-1 block">
                            <span class="mb-2 block text-sm font-bold text-on-surface">Mã môn học <span class="text-xs font-normal text-on-surface-variant">(Tùy chọn)</span></span>
                            <input type="text" wire:model="subjectCode" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 outline-none uppercase transition-all focus:border-primary focus:ring-2 focus:ring-primary/20" placeholder="VD: WEB301">
                            @error('subjectCode') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                        </label>

                        <label class="col-span-1 block">
                            <span class="mb-2 block text-sm font-bold text-on-surface">Học kỳ <span class="text-xs font-normal text-on-surface-variant">(Tùy chọn)</span></span>
                            <input type="text" wire:model="semester" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20" placeholder="VD: HK1 2026-2027">
                            @error('semester') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                        </label>

                        <label class="col-span-1 md:col-span-2 block">
                            <span class="mb-2 block text-base font-bold text-on-surface">Mô tả lớp học <span class="text-sm font-normal text-on-surface-variant">(Tùy chọn)</span></span>
                            <textarea wire:model="description" class="h-24 w-full resize-none rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 text-base outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20" placeholder="Nhập mô tả..."></textarea>
                            @error('description') <span class="text-error text-sm mt-1 block">{{ $message }}</span> @enderror
                        </label>
                    </div>
                </div>

                {{-- Mã lớp học --}}
                <div class="rounded-2xl border border-outline-variant/20 bg-white shadow-sm">
                    <div class="flex items-center gap-3 rounded-t-2xl border-b border-outline-variant/10 bg-surface-container-lowest/50 px-6 py-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                            <x-user.icon name="key" :size="16" />
                        </span>
                        <h2 class="text-lg font-bold text-on-surface">Mã tham gia lớp</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-3">
                            <input type="text" wire:model="join_key"
                                class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 outline-none uppercase font-mono tracking-widest text-lg font-bold transition-all focus:border-primary focus:ring-2 focus:ring-primary/20"
                                placeholder="Mã lớp">
                            <button type="button" wire:click="regenerateCode"
                                class="shrink-0 inline-flex items-center gap-2 rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 text-base font-bold text-on-surface-variant hover:bg-primary hover:text-white hover:border-primary transition-colors"
                                title="Tạo mã ngẫu nhiên mới">
                                <x-user.icon name="refresh-cw" :size="16" />
                                Tạo mới
                            </button>
                        </div>
                        <p class="mt-2 text-sm text-on-surface-variant">Sinh viên dùng mã này để tham gia lớp. Đổi mã nếu bị lộ — mã cũ sẽ hết hiệu lực ngay.</p>
                        @error('join_key') <span class="text-error text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Cấu hình điểm danh --}}
                <div class="rounded-2xl border border-outline-variant/20 bg-white shadow-sm">
                    <div class="flex items-center gap-3 rounded-t-2xl border-b border-outline-variant/10 bg-surface-container-lowest/50 px-6 py-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                            <x-user.icon name="calendar-check" :size="16" />
                        </span>
                        <h2 class="text-lg font-bold text-on-surface">Cấu hình điểm danh</h2>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                            {{-- Ngưỡng đi muộn --}}
                            <label class="block">
                                <span class="mb-2 block text-base font-bold text-on-surface">Ngưỡng đi muộn (phút) <span class="text-error">*</span></span>
                                <input type="number" wire:model.live.debounce.300ms="lateThreshold" min="0" max="300" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 text-base outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20">
                                @error('lateThreshold') <span class="text-error text-sm mt-1 block">{{ $message }}</span> @enderror
                            </label>

                        </div>

                        {{-- Bảng cấu hình điểm trừ chuyên cần --}}
                        <div class="mt-5">
                            <div class="mb-4">
                                <span class="block text-base font-bold text-on-surface">Bảng cấu hình điểm trừ chuyên cần (Quy đổi đi muộn)</span>
                                <span class="text-sm text-on-surface-variant block mt-1">Thiết lập mức điểm trừ cho từng trạng thái (ví dụ: 0.5 điểm trừ = 2 lần vi phạm thành 1 buổi vắng).</span>
                            </div>
                            
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-on-surface mb-1">Có mặt</label>
                                    <input wire:model="attendanceRules.present" type="number" step="0.5" max="0" class="w-full rounded-xl border border-outline-variant/30 px-3 py-2 text-base focus:border-primary focus:ring-1 focus:ring-primary">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-on-surface mb-1">Đi muộn</label>
                                    <input wire:model="attendanceRules.late" type="number" step="0.5" class="w-full rounded-xl border border-outline-variant/30 px-3 py-2 text-base focus:border-primary focus:ring-1 focus:ring-primary">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-on-surface mb-1">Vắng</label>
                                    <input wire:model="attendanceRules.absent" type="number" step="0.5" class="w-full rounded-xl border border-outline-variant/30 px-3 py-2 text-base focus:border-primary focus:ring-1 focus:ring-primary">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-on-surface mb-1">Có phép</label>
                                    <input wire:model="attendanceRules.excused" type="number" step="0.5" class="w-full rounded-xl border border-outline-variant/30 px-3 py-2 text-base focus:border-primary focus:ring-1 focus:ring-primary">
                                </div>
                            </div>
                        </div>

                        {{-- Toggle: Trừ chuyên cần khi vắng có phép --}}
                        <div class="flex items-center justify-between rounded-xl border border-outline-variant/20 bg-surface-container-lowest/60 p-4 mt-5">
                            <div class="pr-4">
                                <span class="block text-base font-bold text-on-surface">Trừ chuyên cần khi vắng có phép</span>
                                <span class="text-sm text-on-surface-variant">Bật để vắng có phép vẫn bị tính làm giảm % chuyên cần (chỉ không bị cảnh báo cấm thi).</span>
                            </div>
                            <button type="button" wire:click="$toggle('deductExcusedAbsence')"
                                class="relative shrink-0 h-6 w-12 rounded-full transition-colors {{ $deductExcusedAbsence ? 'bg-primary' : 'bg-outline-variant/50' }}">
                                <span class="absolute top-1 h-4 w-4 rounded-full bg-white shadow transition-all {{ $deductExcusedAbsence ? 'right-1' : 'left-1' }}"></span>
                            </button>
                        </div>
                    </div>
                </div>


            </div>{{-- end cột trái --}}

            {{-- ===== CỘT PHẢI (sticky sidebar) ===== --}}
            <div class="w-full lg:w-72 xl:w-80 shrink-0 space-y-4 lg:sticky lg:top-[140px]">

                {{-- Trạng thái lớp - Custom Dropdown --}}
                <div class="rounded-2xl border border-outline-variant/20 bg-white shadow-sm">
                    <div class="flex items-center gap-3 rounded-t-2xl border-b border-outline-variant/10 bg-surface-container-lowest/50 px-5 py-3.5">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                            <x-user.icon name="activity" :size="15" />
                        </span>
                        <h2 class="text-base font-bold text-on-surface">Trạng thái lớp</h2>
                    </div>
                    <div class="p-4"
                        x-data="{
                            open: false,
                            value: @entangle('status'),
                            position: 'bottom',
                            options: [
                                { value: 'active', label: 'Đang hoạt động' },
                                { value: 'ended', label: 'Đã kết thúc' },
                            ],
                            get selected() { return this.options.find(o => o.value === this.value) ?? this.options[0] },
                            checkPosition() {
                                this.$nextTick(() => {
                                    let rect = this.$refs.btn.getBoundingClientRect();
                                    let menuRect = this.$refs.menu.getBoundingClientRect();
                                    let spaceBelow = window.innerHeight - rect.bottom;
                                    let spaceAbove = rect.top;
                                    this.position = (spaceBelow < menuRect.height && spaceAbove > spaceBelow) ? 'top' : 'bottom';
                                });
                            }
                        }" @click.outside="open = false" @keydown.escape.window="open = false">
                        <div class="relative">
                            <button type="button" @click="open = !open; if(open) checkPosition();" x-ref="btn"
                                class="flex w-full items-center justify-between gap-3 rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 text-left text-base font-bold text-on-surface outline-none transition-all hover:border-primary"
                                :class="open ? 'border-primary ring-2 ring-primary/20' : ''">
                                <span x-text="selected.label"></span>
                                <x-user.icon name="chevron-down" :size="16" class="shrink-0 text-on-surface-variant transition-transform duration-200" x-bind:class="open ? 'rotate-180 text-primary' : ''" />
                            </button>
                            <div x-show="open" x-cloak x-ref="menu"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 -translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 -translate-y-2"
                                class="absolute left-0 right-0 z-50 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10"
                                :class="position === 'top' ? 'bottom-full mb-1.5' : 'top-full mt-1.5'">
                                <template x-for="option in options" :key="option.value">
                                    <button type="button"
                                        @click="value = option.value; open = false"
                                        class="flex w-full items-center justify-between gap-3 px-4 py-3 text-base font-bold transition-colors hover:bg-slate-50"
                                        :class="value === option.value ? 'text-primary bg-primary/5' : 'text-slate-700'">
                                        <span x-text="option.label"></span>
                                        <span x-show="value === option.value" class="flex h-4 w-4 items-center justify-center rounded-full bg-primary">
                                            <x-user.icon name="check" :size="10" class="text-white" />
                                        </span>
                                    </button>
                                </template>
                            </div>
                        </div>
                        @error('status') <span class="text-error text-xs mt-2 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Bảo mật tham gia --}}
                <div class="rounded-2xl border border-outline-variant/20 bg-white shadow-sm overflow-hidden">
                    <div class="flex items-center gap-3 border-b border-outline-variant/10 bg-surface-container-lowest/50 px-5 py-3.5">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                            <x-user.icon name="shield" :size="15" />
                        </span>
                        <h2 class="text-base font-bold text-on-surface">Bảo mật tham gia</h2>
                    </div>
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex-1">
                                <span class="block text-base font-bold text-on-surface">Yêu cầu duyệt thành viên</span>
                                <span class="text-sm text-on-surface-variant mt-0.5 block">Học viên phải chờ phê duyệt trước khi vào lớp.</span>
                            </div>
                            <button type="button" wire:click="$toggle('requireApproval')"
                                class="relative mt-0.5 shrink-0 h-6 w-12 rounded-full transition-colors {{ $requireApproval ? 'bg-primary' : 'bg-outline-variant/50' }}">
                                <span class="absolute top-1 h-4 w-4 rounded-full bg-white shadow transition-all {{ $requireApproval ? 'right-1' : 'left-1' }}"></span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Nút lưu --}}
                <div class="rounded-2xl border border-outline-variant/20 bg-white shadow-sm p-4 flex gap-3">
                    <a href="{{ route('lecturer.classes.show', $courseClass) }}" wire:navigate
                        class="flex items-center justify-center gap-2 rounded-xl border border-outline-variant/30 bg-surface-container-low px-4 py-3 text-base font-bold text-on-surface-variant transition-all hover:bg-surface-container">
                        Hủy bỏ
                    </a>
                    <button type="submit" form="class-settings-form" wire:loading.attr="disabled" wire:target="save"
                        class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-primary px-4 py-3 text-base font-bold text-white shadow-md shadow-primary/20 transition-all hover:bg-primary/90 active:scale-95 disabled:opacity-75 disabled:cursor-not-allowed">
                        <x-user.icon name="loader" :size="18" class="hidden animate-spin" wire:loading.class.remove="hidden" wire:target="save" />
                        <x-user.icon name="save" :size="18" wire:loading.class="hidden" wire:target="save" />
                        <span wire:loading.class="hidden" wire:target="save">Lưu thay đổi</span>
                        <span class="hidden" wire:loading.class.remove="hidden" wire:target="save">Đang lưu...</span>
                    </button>
                </div>

            </div>{{-- end cột phải --}}

        </div>
    </form>

    {{-- Modal xác nhận XÓA --}}
    @if ($isConfirmingDelete)
        <template x-teleport="body">
            <div class="fixed inset-0 z-[110] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-error/10">
                            <x-user.icon name="alert-triangle" :size="20" class="text-error" />
                        </div>
                        <h3 class="text-lg font-bold text-on-surface">Xác nhận xóa lớp học</h3>
                    </div>
                    <p class="text-sm text-on-surface-variant">Bạn có chắc chắn muốn xóa lớp học này không? Mọi thông tin điểm danh có thể sẽ bị vô hiệu hóa.</p>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" wire:click="closeDeleteConfirm"
                            class="rounded-xl border border-outline-variant/30 px-5 py-2.5 text-sm font-bold text-on-surface-variant hover:bg-surface-container-low transition-colors">Hủy</button>
                        <button type="button" wire:click="deleteClass"
                            class="rounded-xl bg-error px-5 py-2.5 text-sm font-bold text-white hover:bg-error/90 transition-colors">Xóa lớp học</button>
                    </div>
                </div>
            </div>
        </template>
    @endif

</div>
