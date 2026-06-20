<div class="mx-auto max-w-2xl p-4 py-10 sm:p-8 sm:py-12">

    {{-- Header --}}
    <div class="mb-8 text-center">
        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10">
            <x-user.icon name="log-in" :size="32" class="text-primary" />
        </div>
        <h1 class="text-2xl font-extrabold tracking-tight text-on-surface sm:text-3xl">Tham gia lớp học</h1>
        <p class="mt-2 text-sm text-on-surface-variant">Nhập mã lớp do giảng viên cung cấp để đăng ký tham gia.</p>
    </div>

    {{-- Flash message --}}
    @if (session('status'))
        <div class="mb-6 flex items-center gap-3 rounded-2xl border border-green-200 bg-green-50 p-4 font-bold text-green-700">
            <x-user.icon name="check-circle" :size="20" class="shrink-0 text-green-500" />
            <span>{{ session('status') }}</span>
        </div>
    @endif

    {{-- Form card --}}
    <div class="overflow-hidden rounded-[2rem] border border-outline-variant/10 bg-white shadow-sm">

        {{-- Nhập mã lớp --}}
        <div class="border-b border-outline-variant/10 bg-primary/3 px-6 py-5 sm:px-8">
            <p class="mb-3 text-xs font-bold uppercase tracking-widest text-primary/70">Bước 1</p>
            <label class="block">
                <span class="mb-2 block text-sm font-bold text-on-surface">Mã lớp học <span class="text-error">*</span></span>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-4 flex items-center">
                        <x-user.icon name="hash" :size="18" class="text-primary/60" />
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.400ms="class_code"
                        id="class_code"
                        autocomplete="off"
                        class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest py-3 pl-10 pr-4 font-mono text-base font-bold uppercase tracking-wider outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20 placeholder:font-normal placeholder:tracking-normal placeholder:normal-case"
                        placeholder="VD: WEBHK14829"
                    >
                </div>
                @error('class_code')
                    <span class="mt-1.5 flex items-center gap-1.5 text-xs font-bold text-error">
                        <x-user.icon name="alert-circle" :size="13" />
                        {{ $message }}
                    </span>
                @enderror
            </label>
        </div>

        {{-- Thông tin sinh viên --}}
        <div class="space-y-5 px-6 py-5 sm:px-8">
            <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Bước 2 — Thông tin của bạn</p>

            <label class="block">
                <span class="mb-2 block text-sm font-bold text-on-surface">Mã sinh viên <span class="text-error">*</span></span>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-4 flex items-center">
                        <x-user.icon name="credit-card" :size="18" class="text-on-surface-variant/50" />
                    </div>
                    <input
                        type="text"
                        wire:model="student_code"
                        id="student_code"
                        class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest py-3 pl-10 pr-4 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20"
                        placeholder="Nhập mã sinh viên của bạn (VD: SV001)"
                    >
                </div>
                @error('student_code')
                    <span class="mt-1.5 flex items-center gap-1.5 text-xs font-bold text-error">
                        <x-user.icon name="alert-circle" :size="13" />
                        {{ $message }}
                    </span>
                @enderror
            </label>

            <label class="block">
                <span class="mb-2 block text-sm font-bold text-on-surface">Họ và tên <span class="text-error">*</span></span>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-4 flex items-center">
                        <x-user.icon name="user" :size="18" class="text-on-surface-variant/50" />
                    </div>
                    <input
                        type="text"
                        wire:model="full_name"
                        id="full_name"
                        class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest py-3 pl-10 pr-4 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20"
                        placeholder="Họ tên hiển thị trong danh sách lớp"
                    >
                </div>
                @error('full_name')
                    <span class="mt-1.5 flex items-center gap-1.5 text-xs font-bold text-error">
                        <x-user.icon name="alert-circle" :size="13" />
                        {{ $message }}
                    </span>
                @enderror
            </label>
        </div>

        {{-- Footer actions --}}
        <div class="flex items-center justify-between border-t border-outline-variant/10 bg-surface-container-lowest/50 px-6 py-4 sm:px-8">
            <a href="{{ route('joined-classes') }}" class="flex items-center gap-2 text-sm font-bold text-on-surface-variant transition-colors hover:text-on-surface">
                <x-user.icon name="arrow-left" :size="16" />
                Quay lại
            </a>
            <button
                type="button"
                wire:click="submit"
                wire:loading.attr="disabled"
                class="flex items-center gap-2 rounded-xl bg-primary px-6 py-3 font-bold text-white shadow-md shadow-primary/20 transition-all hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-60"
            >
                <span wire:loading.remove wire:target="submit">
                    <x-user.icon name="log-in" :size="18" class="inline" />
                </span>
                <span wire:loading wire:target="submit">
                    <x-user.icon name="loader" :size="18" class="inline animate-spin" />
                </span>
                <span wire:loading.remove wire:target="submit">Tham gia lớp</span>
                <span wire:loading wire:target="submit">Đang xử lý...</span>
            </button>
        </div>
    </div>

    {{-- Info note --}}
    <div class="mt-6 flex gap-3 rounded-2xl border border-outline-variant/10 bg-surface-container-lowest p-4">
        <x-user.icon name="info" :size="18" class="mt-0.5 shrink-0 text-primary/70" />
        <div class="text-xs text-on-surface-variant leading-relaxed">
            <strong class="text-on-surface">Lưu ý:</strong> Nếu lớp học bật chế độ <em>Yêu cầu phê duyệt</em>, yêu cầu của bạn sẽ được gửi đến giảng viên và cần chờ xét duyệt trước khi chính thức vào lớp.
        </div>
    </div>
</div>
