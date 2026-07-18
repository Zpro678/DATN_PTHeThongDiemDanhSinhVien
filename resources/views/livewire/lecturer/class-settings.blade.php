<div class="mx-auto max-w-7xl p-4 sm:p-6 lg:p-8 animate-in fade-in slide-in-from-bottom-4 duration-500">

    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="flex items-center gap-3 text-2xl font-bold text-on-surface">
                    Cài đặt lớp học
                </h1>
                <p class="mt-1 text-sm text-on-surface-variant">Chỉnh sửa thông tin và thiết lập cho lớp <span class="font-bold text-primary">{{ $courseClass->class_code ?? $courseClass->join_key }}</span></p>
            </div>
            @can('delete', $courseClass)
                <button type="button" wire:click="confirmDelete"
                   class="inline-flex shrink-0 whitespace-nowrap items-center gap-2 rounded-xl border border-error/30 bg-white px-4 py-2 text-sm font-bold text-error transition-colors hover:bg-error/10">
                    <x-user.icon name="trash-2" :size="16" />
                    Xóa lớp học
                </button>
            @endcan
        </div>

        {{-- Đồng chủ: nói rõ ngay từ đầu những gì không làm được, tránh bấm rồi mới báo lỗi. --}}
        @cannot('manageCoOwners', $courseClass)
            <div class="mt-4 flex items-start gap-3 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3">
                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                    <x-user.icon name="info" :size="14" />
                </span>
                <p class="text-sm text-blue-900">
                    Bạn đang xem với vai trò <span class="font-bold">đồng chủ lớp</span>. Bạn sửa được cấu hình lớp,
                    nhưng việc thêm/gỡ đồng chủ và xóa lớp chỉ chủ chính
                    @if ($primaryOwner)
                        (<span class="font-bold">{{ $primaryOwner->name }}</span>)
                    @endif
                    mới thực hiện được.
                </p>
            </div>
        @endcannot
    </div>

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
                            <input type="text" wire:model="name" class="w-full rounded-xl border-2 border-outline-variant/80 hover:border-primary bg-surface-container-lowest px-4 py-3 text-base outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20" placeholder="Nhập tên môn học">
                            @error('name') <span class="text-error text-sm mt-1 block">{{ $message }}</span> @enderror
                        </label>

                        {{-- Mã lớp --}}
                        <label class="col-span-1 md:col-span-2 block">
                            <span class="mb-2 block text-base font-bold text-on-surface">Mã lớp <span class="ml-1.5 rounded-full bg-surface-container px-2 py-0.5 text-[12px] font-bold text-on-surface-variant">(Tùy chọn)</span></span>
                            <input type="text" wire:model="classCode" class="w-full rounded-xl border-2 border-outline-variant/80 hover:border-primary bg-surface-container-lowest px-4 py-3 text-base outline-none uppercase font-mono tracking-widest text-lg font-bold transition-all focus:border-primary focus:ring-2 focus:ring-primary/20" placeholder="VD: CS101, WEB-2026-01" maxlength="50">
                            <p class="mt-2 text-sm text-on-surface-variant">Mã nhận diện lớp theo môn học / học phần của trường. Nếu để trống sẽ dùng mã tham gia lớp.</p>
                            @error('classCode') <span class="text-error text-sm mt-1 block">{{ $message }}</span> @enderror
                        </label>

                        <label class="col-span-1 md:col-span-2 block">
                            <span class="mb-2 block text-base font-bold text-on-surface">Mô tả lớp học <span class="text-sm font-normal text-on-surface-variant">(Tùy chọn)</span></span>
                            <textarea wire:model="description" class="h-24 w-full resize-none rounded-xl border-2 border-outline-variant/80 hover:border-primary bg-surface-container-lowest px-4 py-3 text-base outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20" placeholder="Nhập mô tả..."></textarea>
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
                                class="w-full rounded-xl border-2 border-outline-variant/80 hover:border-primary bg-surface-container-lowest px-4 py-3 outline-none uppercase font-mono tracking-widest text-lg font-bold transition-all focus:border-primary focus:ring-2 focus:ring-primary/20"
                                placeholder="Mã lớp">
                            <button type="button" wire:click="regenerateCode"
                                class="shrink-0 inline-flex items-center gap-2 rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 text-base font-bold text-on-surface-variant hover:bg-primary hover:text-white hover:border-primary transition-colors"
                                title="Tạo mã ngẫu nhiên mới">
                                <x-user.icon name="refresh-cw" :size="16" />
                                Tạo mới
                            </button>
                        </div>
                        <p class="mt-2 text-sm text-on-surface-variant">Học viên dùng mã này để tham gia lớp. Đổi mã nếu bị lộ — mã cũ sẽ hết hiệu lực ngay.</p>
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

                            {{-- Tổng số buổi dự kiến --}}
                            <label class="block">
                                <span class="mb-2 block text-base font-bold text-on-surface">Tổng số buổi dự kiến <span class="text-error">*</span></span>
                                <input type="number" wire:model.live.debounce.300ms="totalSessions" min="1" max="200" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 text-base outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20">
                                <span class="mt-1 block text-sm text-on-surface-variant">Dùng để tính quỹ vắng cho phép và tiến độ lớp.</span>
                                @error('totalSessions') <span class="text-error text-sm mt-1 block">{{ $message }}</span> @enderror
                            </label>

                            {{-- Ngưỡng vắng cho phép --}}
                            <label class="block">
                                <span class="mb-2 block text-base font-bold text-on-surface">Ngưỡng vắng cho phép (%) <span class="text-error">*</span></span>
                                <input type="number" wire:model.live.debounce.300ms="absenceLimitPercent" min="0" max="100" step="0.5" class="w-full rounded-xl border-2 border-outline-variant/80 hover:border-primary bg-surface-container-lowest px-4 py-3 text-base outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20">
                                @php
                                    // Xem trước ngay số buổi tương ứng để giảng viên không phải tự nhẩm.
                                    $previewAllowed = \App\Services\AttendanceCalculator::allowedAbsentSessions(
                                        (int) $totalSessions,
                                        (float) $absenceLimitPercent
                                    );
                                    $previewMinAttendance = \App\Services\AttendanceCalculator::formatPercent(
                                        max(0, 100 - (float) $absenceLimitPercent)
                                    );
                                @endphp
                                <span class="mt-1 block text-sm text-on-surface-variant">
                                    = <strong>{{ $previewAllowed }} buổi</strong> trên {{ (int) $totalSessions }} buổi. Dưới {{ $previewMinAttendance }}% chuyên cần sẽ bị đánh dấu nguy cơ cấm thi.
                                </span>
                                @error('absenceLimitPercent') <span class="text-error text-sm mt-1 block">{{ $message }}</span> @enderror
                            </label>

                            {{-- Biên cảnh báo sớm --}}
                            <label class="block">
                                <span class="mb-2 block text-base font-bold text-on-surface">Biên cảnh báo sớm (%) <span class="text-error">*</span></span>
                                <input type="number" wire:model.live.debounce.300ms="warningMarginPercent" min="0" max="100" step="0.5" class="w-full rounded-xl border-2 border-outline-variant/80 hover:border-primary bg-surface-container-lowest px-4 py-3 text-base outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20">
                                <span class="mt-1 block text-sm text-on-surface-variant">
                                    Chuyên cần dưới {{ \App\Services\AttendanceCalculator::formatPercent(min(100, max(0, 100 - (float) $absenceLimitPercent) + max(0, (float) $warningMarginPercent))) }}% nhưng chưa bị cấm thi thì hiện cảnh báo.
                                </span>
                                @error('warningMarginPercent') <span class="text-error text-sm mt-1 block">{{ $message }}</span> @enderror
                            </label>

                            {{-- Số buổi còn lại thì cảnh báo --}}
                            <label class="block">
                                <span class="mb-2 block text-base font-bold text-on-surface">Cảnh báo khi quỹ vắng còn (buổi) <span class="text-error">*</span></span>
                                <input type="number" wire:model.live.debounce.300ms="nearAbsenceSessions" min="0" max="50" class="w-full rounded-xl border-2 border-outline-variant/80 hover:border-primary bg-surface-container-lowest px-4 py-3 text-base outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20">
                                <span class="mt-1 block text-sm text-on-surface-variant">Gửi thông báo cho học viên khi quỹ vắng chỉ còn từng này buổi trở xuống.</span>
                                @error('nearAbsenceSessions') <span class="text-error text-sm mt-1 block">{{ $message }}</span> @enderror
                            </label>

                        </div>

                        {{-- Bảng cấu hình điểm trừ chuyên cần --}}
                        <div class="mt-5">
                            <div class="mb-4">
                                <span class="block text-base font-bold text-on-surface">Bảng cấu hình điểm trừ chuyên cần (Quy đổi đi muộn)</span>
                                <span class="text-sm text-on-surface-variant block mt-1">Thiết lập mức điểm trừ cho từng trạng thái (ví dụ: 0.5 điểm trừ = 2 lần vi phạm thành 1 buổi vắng).</span>
                            </div>

                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-on-surface mb-1">Đi muộn</label>
                                    <input wire:model="attendanceRules.late" type="number" step="0.5" min="0" class="w-full rounded-xl border-2 border-outline-variant/80 hover:border-primary px-3 py-2 text-base outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20">
                                    @error('attendanceRules.late') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-on-surface mb-1">Vắng</label>
                                    <input wire:model="attendanceRules.absent" type="number" step="0.5" min="0" class="w-full rounded-xl border-2 border-outline-variant/80 hover:border-primary px-3 py-2 text-base outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20">
                                    @error('attendanceRules.absent') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-on-surface mb-1">Có phép</label>
                                    <input wire:model="attendanceRules.excused" type="number" step="0.5" min="0" class="w-full rounded-xl border-2 border-outline-variant/80 hover:border-primary px-3 py-2 text-base outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20">
                                    @error('attendanceRules.excused') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-on-surface-variant">Để "Có phép" = 0 nếu vắng có phép <b>không</b> bị trừ chuyên cần. Đặt > 0 để tính làm giảm %.</p>
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
                                class="flex w-full items-center justify-between gap-3 rounded-xl border-2 border-outline-variant/40 bg-surface-container-lowest px-4 py-3 text-left text-base font-bold text-on-surface outline-none transition-all hover:border-primary/50"
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

    {{-- ===== Đồng chủ lớp (chủ chính: toàn quyền · đồng chủ: chỉ xem) ===== --}}
    <div class="mt-6 rounded-2xl border border-outline-variant/20 bg-white shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 border-b border-outline-variant/10 bg-surface-container-lowest/50 px-6 py-4">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-tertiary/10 text-tertiary">
                <x-user.icon name="users" :size="16" />
            </span>
            <div>
                <h2 class="text-lg font-bold text-on-surface">Đồng chủ lớp</h2>
                <p class="text-sm text-on-surface-variant">Người được thêm sẽ cùng quản lý điểm danh, học viên, đơn nghỉ và cấu hình lớp. Không được thêm/gỡ đồng chủ hay xóa lớp.</p>
            </div>
        </div>

        <div class="p-6 space-y-5">
            {{-- Thêm đồng chủ theo email --}}
            @can('manageCoOwners', $courseClass)
            <div>
                <label class="mb-2 block text-sm font-bold text-on-surface">Thêm đồng chủ theo email</label>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <div class="relative flex-1">
                        <x-user.icon name="mail" :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-outline" />
                        <input type="email" wire:model="coOwnerEmail" wire:keydown.enter.prevent="addCoOwner"
                            placeholder="email@vidu.com"
                            class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest py-2.5 pl-10 pr-4 text-sm outline-none transition-all focus:border-tertiary focus:ring-2 focus:ring-tertiary/20">
                    </div>
                    <button type="button" wire:click="addCoOwner" wire:loading.attr="disabled" wire:target="addCoOwner"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-tertiary px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-tertiary/90 active:scale-95 disabled:opacity-60">
                        <x-user.icon name="plus" :size="16" />
                        Thêm
                    </button>
                </div>
                @error('coOwnerEmail')
                    <span class="mt-1.5 flex items-center gap-1.5 text-xs font-bold text-error">
                        <x-user.icon name="alert-circle" :size="13" /> {{ $message }}
                    </span>
                @enderror
            </div>
            @endcan

            {{-- Chủ chính — hiện cho đồng chủ biết ai đang giữ quyền cao nhất của lớp. --}}
            @cannot('manageCoOwners', $courseClass)
                @if ($primaryOwner)
                    <div class="flex items-center gap-3 rounded-xl border border-outline-variant/20 bg-surface-container-lowest/40 px-4 py-3">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-primary/10 text-sm font-bold text-primary">
                            {{ mb_strtoupper(mb_substr($primaryOwner->name, 0, 1)) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-on-surface">{{ $primaryOwner->name }}</p>
                            <p class="truncate text-xs text-on-surface-variant">{{ $primaryOwner->email }}</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-primary/10 px-2.5 py-1 text-xs font-bold text-primary">Chủ chính</span>
                    </div>
                @endif
            @endcannot

            {{-- Danh sách đồng chủ hiện tại --}}
            <div class="space-y-2" x-data="{ removing: null }">
                @forelse ($coOwners as $coOwner)
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-outline-variant/20 bg-surface-container-lowest/40 px-4 py-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-tertiary/10 text-sm font-bold text-tertiary">
                                {{ mb_strtoupper(mb_substr($coOwner->name, 0, 1)) }}
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-on-surface">{{ $coOwner->name }}</p>
                                <p class="truncate text-xs text-on-surface-variant">{{ $coOwner->email }}</p>
                            </div>
                        </div>
                        @can('manageCoOwners', $courseClass)
                            <button type="button"
                                @click="removing = { id: {{ $coOwner->id }}, name: @js($coOwner->name) }"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-error/30 px-3 py-1.5 text-xs font-bold text-error transition-colors hover:bg-error/10">
                                <x-user.icon name="x" :size="14" /> Gỡ
                            </button>
                        @else
                            <span class="shrink-0 rounded-full bg-tertiary/10 px-2.5 py-1 text-xs font-bold text-tertiary">
                                {{ $coOwner->id === auth()->id() ? 'Bạn' : 'Đồng chủ' }}
                            </span>
                        @endcan
                    </div>
                @empty
                    <p class="rounded-xl border border-dashed border-outline-variant/30 px-4 py-6 text-center text-sm text-on-surface-variant">
                        @can('manageCoOwners', $courseClass)
                            Lớp chưa có đồng chủ nào. Thêm email ở trên để mời người cùng quản lý.
                        @else
                            Lớp chưa có đồng chủ nào.
                        @endcan
                    </p>
                @endforelse

                {{-- Modal xác nhận GỠ đồng chủ (thay confirm() mặc định của trình duyệt) --}}
                @can('manageCoOwners', $courseClass)
                <template x-teleport="body">
                    <div x-show="removing" x-cloak x-transition.opacity
                        @keydown.escape.window="removing = null"
                        class="fixed inset-0 z-[110] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                        <div x-show="removing" x-transition.scale.origin.center
                            @click.outside="removing = null"
                            class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                            <div class="mb-4 flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-error/10">
                                    <x-user.icon name="alert-triangle" :size="20" class="text-error" />
                                </div>
                                <h3 class="text-lg font-bold text-on-surface">Gỡ đồng chủ lớp</h3>
                            </div>
                            <p class="text-sm text-on-surface-variant">
                                Gỡ <span class="font-bold text-on-surface" x-text="removing?.name"></span> khỏi vai trò đồng chủ lớp?
                                Người này sẽ không còn quyền quản lý điểm danh, học viên và đơn nghỉ của lớp.
                            </p>
                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button" @click="removing = null"
                                    class="rounded-xl border border-outline-variant/30 px-5 py-2.5 text-sm font-bold text-on-surface-variant hover:bg-surface-container-low transition-colors">Hủy</button>
                                <button type="button"
                                    @click="$wire.removeCoOwner(removing.id); removing = null"
                                    class="inline-flex items-center gap-2 rounded-xl bg-error px-5 py-2.5 text-sm font-bold text-white hover:bg-error/90 transition-colors">
                                    <x-user.icon name="x" :size="16" /> Gỡ đồng chủ
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
                @endcan
            </div>
        </div>
    </div>

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
