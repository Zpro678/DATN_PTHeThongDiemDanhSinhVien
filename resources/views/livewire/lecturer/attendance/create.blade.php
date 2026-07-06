<div class="w-full space-y-6 px-6 py-6 sm:px-10 lg:px-16 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <div class="mb-2">
        <h1 class="text-xl font-extrabold leading-tight tracking-tight text-slate-800 sm:text-2xl">Tạo buổi điểm danh</h1>
        <p class="mt-1 text-sm text-on-surface-variant">Chọn lớp, đặt tên buổi và giờ kết thúc, rồi chọn phương thức điểm danh.</p>
    </div>

    @if ($errors->any())
        <div class="rounded-2xl border border-error/30 bg-error-container/40 p-4 text-on-error-container">
            <div class="mb-2 flex items-center gap-2">
                <x-user.icon name="alert-triangle" :size="20" class="text-error" />
                <span class="text-sm font-bold">Vui lòng kiểm tra lại thông tin:</span>
            </div>
            <ul class="list-disc space-y-1 pl-5 text-[13px]">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
        <section class="space-y-6 xl:col-span-7">
            {{-- BƯỚC 1: thông tin cơ bản --}}
            <div class="space-y-5 rounded-2xl border border-outline-variant/20 bg-white p-5 shadow-sm sm:p-8">
                <h2 class="flex items-center gap-3 text-lg font-bold text-on-surface">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <x-user.icon name="calendar-plus" :size="20" />
                    </span>
                    Thông tin buổi học
                </h2>

                {{-- Lớp --}}
                <label class="block space-y-2">
                    <span class="block text-sm font-semibold text-on-surface">Lớp học <span class="text-error">*</span></span>
                    <select wire:model.live="classId" class="h-12 w-full rounded-xl border border-outline-variant/40 bg-white px-4 text-sm font-semibold text-on-surface outline-none transition-all hover:border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary/20">
                        @forelse ($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }} ({{ $class->members_count }} SV)</option>
                        @empty
                            <option value="">Chưa có lớp học</option>
                        @endforelse
                    </select>
                    @error('classId') <span class="block text-xs font-medium text-error">{{ $message }}</span> @enderror
                </label>

                {{-- Tên buổi --}}
                <label class="block space-y-2">
                    <span class="block text-sm font-semibold text-on-surface">Tên buổi <span class="text-error">*</span></span>
                    <input wire:model="name" type="text" placeholder="Ví dụ: Buổi 1 - Giới thiệu môn học"
                        class="h-12 w-full rounded-xl border border-outline-variant/40 bg-white px-4 text-sm font-semibold text-on-surface outline-none transition-all hover:border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary/20">
                    @error('name') <span class="block text-xs font-medium text-error">{{ $message }}</span> @enderror
                </label>

                {{-- Giờ kết thúc --}}
                <label class="block space-y-2">
                    <span class="block text-sm font-semibold text-on-surface">Giờ kết thúc buổi <span class="text-error">*</span></span>
                    <input wire:model="meetingEndTime" type="time" step="300"
                        class="h-12 w-full rounded-xl border border-outline-variant/40 bg-white px-4 text-sm font-semibold text-on-surface outline-none transition-all hover:border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary/20">
                    <span class="block text-xs text-on-surface-variant/70">Buổi bắt đầu ngay khi tạo (hôm nay); chỉ cần nhập giờ kết thúc.</span>
                    @error('meetingEndTime') <span class="block text-xs font-medium text-error">{{ $message }}</span> @enderror
                </label>
            </div>

            {{-- BƯỚC 2: chọn phương thức --}}
            <div>
                <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-on-surface-variant">Chọn phương thức điểm danh</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <button type="button" wire:click="createManualSession" wire:loading.attr="disabled" wire:target="createManualSession"
                        class="group flex flex-col items-start gap-2 rounded-2xl border-2 border-outline-variant/30 bg-white p-5 text-left shadow-sm transition-all hover:-translate-y-0.5 hover:border-primary hover:bg-primary/5 disabled:cursor-wait disabled:opacity-60">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <x-user.icon name="edit" :size="20" />
                        </span>
                        <span class="text-base font-bold text-on-surface">Điểm danh thủ công</span>
                        <span class="text-xs leading-5 text-on-surface-variant">Giảng viên tự đánh dấu có mặt/vắng cho từng học viên.</span>
                        <span wire:loading wire:target="createManualSession" class="text-xs font-semibold text-primary">Đang tạo...</span>
                    </button>

                    <button type="button" wire:click="createQrSession" wire:loading.attr="disabled" wire:target="createQrSession"
                        class="group flex flex-col items-start gap-2 rounded-2xl border-2 border-outline-variant/30 bg-white p-5 text-left shadow-sm transition-all hover:-translate-y-0.5 hover:border-primary hover:bg-primary/5 disabled:cursor-wait disabled:opacity-60">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <x-user.icon name="qr-code" :size="20" />
                        </span>
                        <span class="text-base font-bold text-on-surface">Điểm danh QR</span>
                        <span class="text-xs leading-5 text-on-surface-variant">Sinh viên tự quét mã QR (kèm GPS, chống điểm danh hộ).</span>
                        <span wire:loading wire:target="createQrSession" class="text-xs font-semibold text-primary">Đang tạo...</span>
                    </button>
                </div>
            </div>
        </section>

        <aside class="xl:col-span-5">
            <div class="sticky top-[140px] rounded-2xl border border-outline-variant/20 bg-white p-6 shadow-sm">
                <h3 class="flex items-center gap-2 text-[15px] font-bold text-on-surface">
                    <x-user.icon name="info" :size="18" class="text-primary" />
                    Lưu ý khi tạo buổi
                </h3>
                <ul class="mt-4 space-y-3 text-sm leading-6 text-on-surface-variant">
                    <li class="flex gap-2"><span class="mt-0.5 text-primary">•</span> Buổi diễn ra <b>hôm nay</b>; giờ bắt đầu là thời điểm bạn tạo.</li>
                    <li class="flex gap-2"><span class="mt-0.5 text-primary">•</span> Giờ kết thúc phải sau hiện tại ít nhất <b>10 phút</b>.</li>
                    <li class="flex gap-2"><span class="mt-0.5 text-primary">•</span> Lớp cần có <b>danh sách sinh viên</b> trước khi điểm danh.</li>
                    <li class="flex gap-2"><span class="mt-0.5 text-primary">•</span> Chọn <b>QR</b> nếu muốn sinh viên tự điểm danh có kiểm soát vị trí & thiết bị.</li>
                </ul>
            </div>
        </aside>
    </div>
</div>
