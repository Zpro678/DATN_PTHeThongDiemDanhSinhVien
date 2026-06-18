<div class="mx-auto max-w-[1100px] p-4 pb-24 sm:p-8">
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold uppercase leading-none tracking-tight text-slate-900">Thêm lớp học mới</h1>
            <p class="mt-2 text-sm text-slate-500">Khởi tạo thông tin lớp học và các cấu hình điểm danh.</p>
        </div>
        <a href="{{ route('managed-classes') }}" class="hidden items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50 sm:flex">
            <x-user.icon name="book-open" :size="18" />
            Danh sách lớp
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800">
            <div class="mb-2 flex items-center gap-2">
                <x-user.icon name="alert-triangle" :size="20" class="text-red-500" />
                <span class="text-sm font-bold">Vui lòng kiểm tra lại các thông tin bên dưới:</span>
            </div>
            <ul class="list-disc space-y-1 pl-5 text-[13px]">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <section class="rounded-[20px] border border-slate-200/80 bg-white p-6 shadow-sm sm:p-8">
            <h2 class="mb-6 flex items-center gap-2 text-lg font-bold text-slate-900">
                <x-user.icon name="book-open" :size="20" class="text-blue-500" />
                Thông tin cơ bản
            </h2>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <label class="space-y-2">
                    <span class="block text-[13px] font-semibold text-slate-700">Tên lớp <span class="text-red-500">*</span></span>
                    <input wire:model.blur="name" type="text" placeholder="Ví dụ: Công nghệ phần mềm 1" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 outline-none transition-all hover:border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" autofocus>
                    @error('name') <span class="block text-xs font-medium text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="space-y-2">
                    <span class="block text-[13px] font-semibold text-slate-700">Mã lớp <span class="text-red-500">*</span></span>
                    <input wire:model.blur="code" type="text" placeholder="Ví dụ: WEB-2026-01" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium uppercase text-slate-700 outline-none transition-all hover:border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                    @error('code') <span class="block text-xs font-medium text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="space-y-2">
                    <span class="block text-[13px] font-semibold text-slate-700">Mã môn học</span>
                    <input wire:model.blur="subjectCode" type="text" placeholder="Ví dụ: INT3110" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium uppercase text-slate-700 outline-none transition-all hover:border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                    @error('subjectCode') <span class="block text-xs font-medium text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="space-y-2">
                    <span class="block text-[13px] font-semibold text-slate-700">Học kỳ</span>
                    <input wire:model.blur="semester" type="text" placeholder="Ví dụ: HK1 2026-2027" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 outline-none transition-all hover:border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                    @error('semester') <span class="block text-xs font-medium text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="space-y-2 sm:col-span-2">
                    <span class="block text-[13px] font-semibold text-slate-700">Mô tả</span>
                    <textarea wire:model.blur="description" placeholder="Nhập mô tả thêm về lớp học..." rows="4" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 outline-none transition-all hover:border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"></textarea>
                    @error('description') <span class="block text-xs font-medium text-red-600">{{ $message }}</span> @enderror
                </label>
            </div>
        </section>

        <section class="rounded-[20px] border border-slate-200/80 bg-white p-6 shadow-sm sm:p-8">
            <h2 class="mb-6 flex items-center gap-2 text-lg font-bold text-slate-900">
                <x-user.icon name="settings" :size="20" class="text-blue-500" />
                Cấu hình lớp học
            </h2>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <label class="space-y-2">
                    <span class="block text-[13px] font-semibold text-slate-700">Tổng số buổi <span class="text-red-500">*</span></span>
                    <input wire:model.blur="totalSessions" type="number" min="1" max="100" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 outline-none transition-all hover:border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                    @error('totalSessions') <span class="block text-xs font-medium text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="space-y-2">
                    <span class="block text-[13px] font-semibold text-slate-700">Số tiết mỗi buổi <span class="text-red-500">*</span></span>
                    <input wire:model.blur="lessonsPerSession" type="number" min="1" max="20" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 outline-none transition-all hover:border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                    @error('lessonsPerSession') <span class="block text-xs font-medium text-red-600">{{ $message }}</span> @enderror
                </label>
            </div>

            <div class="mt-6 flex items-center justify-between gap-6 border-t border-slate-100 pt-6">
                <div>
                    <span class="block text-sm font-bold text-slate-900">Yêu cầu duyệt tham gia</span>
                    <p class="mt-1 text-[13px] text-slate-500">Học viên tham gia bằng mã lớp cần được duyệt trước khi vào danh sách.</p>
                </div>
                <button
                    type="button"
                    wire:click="$toggle('requireApproval')"
                    role="switch"
                    aria-label="Yêu cầu duyệt tham gia"
                    aria-checked="{{ $requireApproval ? 'true' : 'false' }}"
                    @class([
                        'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500/30',
                        'bg-blue-600' => $requireApproval,
                        'bg-slate-200' => ! $requireApproval,
                    ])
                >
                    <span @class([
                        'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200',
                        'translate-x-5' => $requireApproval,
                        'translate-x-0' => ! $requireApproval,
                    ])></span>
                </button>
            </div>
        </section>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('managed-classes') }}" class="rounded-xl border border-slate-200 px-6 py-2.5 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50">Hủy</a>
            <button type="submit" wire:loading.attr="disabled" wire:target="save" class="flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition-colors hover:bg-blue-700 disabled:cursor-wait disabled:opacity-60">
                <x-user.icon name="save" :size="18" wire:loading.remove wire:target="save" />
                <span wire:loading.remove wire:target="save">Lưu lớp học</span>
                <span wire:loading wire:target="save">Đang lưu...</span>
            </button>
        </div>
    </form>
</div>
