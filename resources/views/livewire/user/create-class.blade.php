<div class="min-h-full px-4 py-6 pb-24 sm:px-6 xl:px-8">
    <script>
    function removeDiacritics(str) {
        return str
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/\u0111/g, 'd').replace(/\u0110/g, 'D')
            .replace(/[^A-Za-z0-9\s\-_]/g, '');
    }
    function cleanInput(el) {
        const pos = el.selectionStart;
        el.value = removeDiacritics(el.value).toUpperCase();
        el.setSelectionRange(pos, pos);
    }
    </script>
    @php
        $previewName    = filled($name) ? $name : 'Tên lớp học';
        $previewSubject = filled($subjectCode) ? strtoupper($subjectCode) : 'Mã môn';

    @endphp

    <div class="mx-auto w-full max-w-[1400px] space-y-6">
        <div class="flex flex-col gap-4 rounded-2xl border border-outline-variant/20 bg-white p-5 shadow-sm sm:p-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary text-white shadow-sm shadow-primary/20">
                    <x-user.icon name="book-open" :size="24" />
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.28em] text-primary">Quản lý lớp học</p>
                    <h1 class="mt-2 text-2xl font-bold leading-tight tracking-tight text-on-surface sm:text-3xl">Thêm lớp học mới</h1>
                    <p class="mt-2 max-w-3xl text-sm text-on-surface-variant">Khởi tạo thông tin lớp học và các cấu hình điểm danh. Bố cục được mở rộng để dùng trọn không gian làm việc của hệ thống.</p>
                </div>
            </div>

            <a href="{{ route('managed-classes') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-outline-variant/40 bg-white px-5 py-3 text-sm font-bold text-on-surface-variant shadow-sm transition-colors hover:bg-surface-container">
                <x-user.icon name="book-open" :size="18" />
                Danh sách lớp
            </a>
        </div>

        @if ($errors->any())
            <div class="rounded-2xl border border-error/30 bg-error-container/40 p-4 text-on-error-container">
                <div class="mb-2 flex items-center gap-2">
                    <x-user.icon name="alert-triangle" :size="20" class="text-error" />
                    <span class="text-sm font-bold">Vui lòng kiểm tra lại các thông tin bên dưới:</span>
                </div>
                <ul class="list-disc space-y-1 pl-5 text-[13px]">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form wire:submit="save" class="grid grid-cols-1 gap-6 xl:grid-cols-12">
            <div class="space-y-6 xl:col-span-7 2xl:col-span-7">
                <section class="rounded-2xl border border-outline-variant/20 bg-white p-5 shadow-sm sm:p-8">
                    <div class="mb-7 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="flex items-center gap-3 text-xl font-bold text-on-surface">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                <x-user.icon name="book-open" :size="20" />
                            </span>
                            Thông tin cơ bản
                        </h2>
                        <span class="rounded-full bg-surface-container px-3 py-1 text-xs font-bold uppercase tracking-wide text-on-surface-variant">không được để trống các trường</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        {{-- Tên lớp --}}
                        <label class="space-y-2 sm:col-span-2">
                            <span class="block text-[13px] font-semibold text-on-surface">Tên lớp <span class="text-error">*</span></span>
                            <input wire:model.live.debounce.300ms="name" type="text" placeholder="Ví dụ: Công nghệ phần mềm 1" class="h-12 w-full rounded-xl border border-outline-variant/40 bg-white px-4 text-sm font-semibold text-on-surface outline-none transition-all placeholder:text-on-surface-variant/50 hover:border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary/20" autofocus>
                            @error('name') <span class="block text-xs font-medium text-error">{{ $message }}</span> @enderror
                        </label>

                        {{-- Mã môn học --}}
                        <label class="space-y-2 sm:col-span-2">
                            <span class="block text-[13px] font-semibold text-on-surface">Mã môn học</span>
                            <input
                                wire:model.live.debounce.300ms="subjectCode"
                                type="text"
                                placeholder="Ví dụ: INT3110"
                                oninput="cleanInput(this)"
                                class="h-12 w-full rounded-xl border border-outline-variant/40 bg-white px-4 text-sm font-semibold uppercase text-on-surface outline-none transition-all placeholder:text-on-surface-variant/50 hover:border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary/20"
                            >
                            @error('subjectCode') <span class="block text-xs font-medium text-error">{{ $message }}</span> @enderror
                        </label>



                        {{-- Mã lớp: ẩn input, hiển thị preview sinh tự động --}}
                        <div class="space-y-2 sm:col-span-2">
                            <span class="block text-[13px] font-semibold text-on-surface">Mã lớp học <span class="ml-1.5 rounded-full bg-primary/10 px-2 py-0.5 text-[11px] font-bold text-primary">Tự động sinh</span></span>
                            <div class="flex h-12 w-full items-center gap-3 rounded-xl border border-dashed border-outline-variant/50 bg-surface-container-low px-4">
                                <x-user.icon name="key" :size="16" class="shrink-0 text-on-surface-variant/60" />
                                @if($generatedCode)
                                    <span class="font-mono text-base font-black tracking-widest text-primary truncate">{{ $generatedCode }}</span>
                                    <span class="ml-auto text-xs text-on-surface-variant/70 shrink-0">Mã sẽ được xác nhận khi lưu</span>
                                @else
                                    <span class="text-sm text-on-surface-variant/70 truncate">Nhập mã môn học để xem trước mã lớp...</span>
                                @endif
                            </div>
                        </div>

                        {{-- Mô tả --}}
                        <label class="space-y-2 sm:col-span-2">
                            <span class="block text-[13px] font-semibold text-on-surface">Mô tả</span>
                            <textarea wire:model.blur="description" placeholder="Nhập mô tả thêm về lớp học..." rows="4" class="w-full rounded-xl border border-outline-variant/40 bg-white px-4 py-3 text-sm font-semibold text-on-surface outline-none transition-all placeholder:text-on-surface-variant/50 hover:border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary/20"></textarea>
                            @error('description') <span class="block text-xs font-medium text-error">{{ $message }}</span> @enderror
                        </label>
                    </div>
                </section>

                <section class="rounded-2xl border border-outline-variant/20 bg-white p-5 shadow-sm sm:p-8">
                    <div class="mb-7 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="flex items-center gap-3 text-xl font-bold text-on-surface">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                <x-user.icon name="settings" :size="20" />
                            </span>
                            Cấu hình lớp học
                        </h2>
                        <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-bold uppercase tracking-wide text-primary">Theo dữ liệu hiện tại</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <label class="space-y-2 sm:col-span-2 md:col-span-1">
                            <span class="block text-[13px] font-semibold text-on-surface">Ngưỡng đi muộn (phút) <span class="text-error">*</span></span>
                            <input wire:model.live.debounce.300ms="lateThreshold" type="number" min="0" max="300" class="h-12 w-full rounded-xl border border-outline-variant/40 bg-white px-4 text-sm font-semibold text-on-surface outline-none transition-all hover:border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary/20">
                            @error('lateThreshold') <span class="block text-xs font-medium text-error">{{ $message }}</span> @enderror
                        </label>
                    </div>

                    <div class="mt-7 space-y-4">
                        {{-- Cấu hình điểm trừ chuyên cần --}}
                        <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-low p-5">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-primary shadow-sm">
                                    <x-user.icon name="check-square" :size="20" />
                                </div>
                                <div>
                                    <span class="block text-sm font-bold text-on-surface">Bảng cấu hình điểm trừ chuyên cần</span>
                                    <p class="mt-1 max-w-2xl text-[13px] leading-5 text-on-surface-variant">Thiết lập mức điểm trừ cho từng trạng thái (ví dụ: -0.5, -1, 0).</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-on-surface mb-1">Có mặt</label>
                                    <input wire:model="attendanceRules.present" type="number" step="0.5" max="0" class="w-full rounded-lg border border-outline-variant/40 bg-white px-3 py-2 text-sm text-on-surface outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-on-surface mb-1">Đi muộn</label>
                                    <input wire:model="attendanceRules.late" type="number" step="0.5" class="w-full rounded-lg border border-outline-variant/40 bg-white px-3 py-2 text-sm text-on-surface outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-on-surface mb-1">Vắng giữa giờ</label>
                                    <input wire:model="attendanceRules.partial" type="number" step="0.5" class="w-full rounded-lg border border-outline-variant/40 bg-white px-3 py-2 text-sm text-on-surface outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-on-surface mb-1">Về sớm</label>
                                    <input wire:model="attendanceRules.early_leave" type="number" step="0.5" class="w-full rounded-lg border border-outline-variant/40 bg-white px-3 py-2 text-sm text-on-surface outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-on-surface mb-1">Vắng</label>
                                    <input wire:model="attendanceRules.absent" type="number" step="0.5" class="w-full rounded-lg border border-outline-variant/40 bg-white px-3 py-2 text-sm text-on-surface outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-on-surface mb-1">Có phép</label>
                                    <input wire:model="attendanceRules.excused" type="number" step="0.5" class="w-full rounded-lg border border-outline-variant/40 bg-white px-3 py-2 text-sm text-on-surface outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20">
                                </div>
                            </div>
                        </div>

                        {{-- Toggle: Yêu cầu duyệt tham gia --}}
                        <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-low p-5">
                            <div class="flex flex-row items-center justify-between gap-5">
                                <div class="flex gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-primary shadow-sm">
                                        <x-user.icon name="shield-check" :size="20" />
                                    </div>
                                    <div>
                                        <span class="block text-sm font-bold text-on-surface">Yêu cầu duyệt tham gia</span>
                                        <p class="mt-1 max-w-2xl text-[13px] leading-5 text-on-surface-variant">Học viên tham gia bằng mã lớp cần được chủ lớp duyệt trước khi xuất hiện trong danh sách chính thức.</p>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    wire:click="$toggle('requireApproval')"
                                    role="switch"
                                    aria-label="Yêu cầu duyệt tham gia"
                                    aria-checked="{{ $requireApproval ? 'true' : 'false' }}"
                                    @class([
                                        'relative inline-flex h-8 w-14 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary/20',
                                        'bg-primary' => $requireApproval,
                                        'bg-outline-variant' => ! $requireApproval,
                                    ])
                                >
                                    <span @class([
                                        'pointer-events-none inline-block h-7 w-7 transform rounded-full bg-white shadow ring-0 transition duration-200',
                                        'translate-x-6' => $requireApproval,
                                        'translate-x-0' => ! $requireApproval,
                                    ])></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="grid grid-cols-2 gap-3 rounded-2xl border border-outline-variant/20 bg-white p-4 shadow-sm sm:flex sm:flex-row sm:items-center sm:justify-end">
                    <a href="{{ route('managed-classes') }}" class="inline-flex w-full sm:w-auto items-center justify-center rounded-xl border border-outline-variant/40 px-6 py-3 text-sm font-bold text-on-surface-variant transition-colors hover:bg-surface-container">Hủy</a>
                    <button type="submit" wire:loading.attr="disabled" wire:target="save" class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-bold text-white shadow-sm shadow-primary/20 transition-colors hover:bg-primary/90 disabled:cursor-wait disabled:opacity-60">
                        <x-user.icon name="save" :size="18" wire:loading.remove wire:target="save" />
                        <span wire:loading.remove wire:target="save">Lưu lớp học</span>
                        <span wire:loading wire:target="save">Đang lưu...</span>
                    </button>
                </div>
            </div>

            <aside class="space-y-6 xl:col-span-5 2xl:col-span-5">
                <div class="sticky top-6 space-y-6">
                    <section class="overflow-hidden rounded-2xl border border-outline-variant/20 bg-white shadow-sm">
                        <div class="bg-gradient-to-br from-primary to-primary-container p-6 text-white">
                            <div class="flex items-center justify-between gap-4">
                                <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-wide">Xem trước</span>
                                <x-user.icon name="eye" :size="22" class="text-white/90" />
                            </div>
                            <h3 class="mt-5 text-2xl font-bold leading-tight">{{ $previewName }}</h3>
                            <p class="mt-2 text-sm font-medium text-primary-fixed">{{ $previewSubject }}</p>
                            @if($generatedCode)
                                <div class="mt-3 inline-flex items-center gap-2 rounded-xl bg-white/15 px-3 py-1.5">
                                    <x-user.icon name="key" :size="14" class="text-white/80" />
                                    <span class="font-mono text-sm font-black tracking-widest text-white">{{ $generatedCode }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="space-y-4 p-6">
                            <div class="grid grid-cols-1 gap-3">
                                <!-- Đã bỏ Tổng số buổi dự kiến -->
                            </div>

                            <div class="rounded-2xl border border-outline-variant/20 p-4">
                                <div class="flex items-center gap-3">
                                    <div @class([
                                        'flex h-10 w-10 items-center justify-center rounded-xl',
                                        'bg-primary/10 text-primary' => $requireApproval,
                                        'bg-surface-container text-on-surface-variant' => ! $requireApproval,
                                    ])>
                                        <x-user.icon name="shield-check" :size="20" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-on-surface">{{ $requireApproval ? 'Có duyệt tham gia' : 'Tham gia trực tiếp' }}</p>
                                        <p class="mt-0.5 text-xs text-on-surface-variant">{{ $requireApproval ? 'Học viên cần được chủ lớp xác nhận.' : 'Học viên vào lớp ngay sau khi dùng mã lớp.' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-outline-variant/20 bg-white p-6 shadow-sm">
                        <h3 class="flex items-center gap-2 text-base font-bold text-on-surface">
                            <x-user.icon name="sparkles" :size="19" class="text-primary" />
                            Gợi ý nhập liệu
                        </h3>
                        <div class="mt-5 space-y-4">
                            <div class="flex gap-3">
                                <span class="mt-0.5 flex h-6 w-6 items-center justify-center rounded-full bg-primary/10 text-xs font-extrabold text-primary">1</span>
                                <p class="text-sm leading-6 text-on-surface-variant">Mã lớp nên ngắn, dễ nhận diện và không trùng với lớp đã có.</p>
                            </div>
                            <div class="flex gap-3">
                                <span class="mt-0.5 flex h-6 w-6 items-center justify-center rounded-full bg-primary/10 text-xs font-extrabold text-primary">2</span>
                                <p class="text-sm leading-6 text-on-surface-variant">Bật duyệt tham gia nếu lớp cần kiểm soát danh sách học viên trước.</p>
                            </div>
                        </div>
                    </section>
                </div>
            </aside>
        </form>
    </div>
</div>
