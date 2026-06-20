<div class="mx-auto max-w-4xl p-4 sm:p-8">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="flex items-center gap-2 text-2xl font-bold text-on-surface md:text-3xl">
                <x-user.icon name="settings" class="text-primary" :size="28" />
                Cài đặt lớp học
            </h2>
            <p class="mt-2 text-body-lg text-on-surface-variant">Chỉnh sửa thông tin và thiết lập cho lớp {{ $courseClass->code }}</p>
        </div>
        <a href="{{ route('managed-classes') }}" class="flex items-center gap-2 rounded-full border border-outline-variant/30 bg-white px-4 py-2 font-bold text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-on-surface">
            <x-user.icon name="arrow-left" :size="18" />
            Quay lại
        </a>
    </div>

    @if (session('status'))
        <div class="mb-6 flex items-center rounded-2xl border border-green-200 bg-green-50 p-4 font-bold text-green-700">
            <x-user.icon name="check-circle" :size="20" class="mr-3 text-green-500" />
            {{ session('status') }}
        </div>
    @endif

    <div class="rounded-[2rem] border border-outline-variant/10 bg-white shadow-sm overflow-hidden">
        <form wire:submit="save">
            <div class="p-6 md:p-10 space-y-8">
                <!-- Thông tin chung -->
                <div>
                    <h3 class="mb-4 text-lg font-bold text-on-surface border-b border-outline-variant/10 pb-2">Thông tin chung</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <label class="col-span-1 md:col-span-2 block">
                            <span class="mb-2 block text-sm font-bold text-on-surface">Tên lớp / môn học <span class="text-error">*</span></span>
                            <input type="text" wire:model="name" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20" placeholder="Nhập tên môn học">
                            @error('name') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                        </label>
                        
                        <label class="block">
                            <span class="mb-2 block text-sm font-bold text-on-surface">Mã lớp</span>
                            <div class="flex items-center gap-3">
                                <input type="text" wire:model="code" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 outline-none uppercase opacity-60 cursor-not-allowed" disabled>
                                <span class="shrink-0 rounded-full bg-surface-container px-3 py-1 text-xs font-bold text-on-surface-variant">Tự động sinh</span>
                            </div>
                        </label>
                        
                        <label class="block">
                            <span class="mb-2 block text-sm font-bold text-on-surface">Mã môn học (Tùy chọn)</span>
                            <input type="text" wire:model="subjectCode" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 outline-none uppercase transition-all focus:border-primary focus:ring-2 focus:ring-primary/20" placeholder="VD: WEB301">
                            @error('subjectCode') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                        </label>
                        
                        <label class="block">
                            <span class="mb-2 block text-sm font-bold text-on-surface">Học kỳ (Tùy chọn)</span>
                            <input type="text" wire:model="semester" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20" placeholder="VD: HK2 2025-2026">
                            @error('semester') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                        </label>
                        
                        <label class="block col-span-1 md:col-span-2">
                            <span class="mb-2 block text-sm font-bold text-on-surface">Trạng thái lớp</span>
                            <select wire:model="status" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20">
                                <option value="active">Đang hoạt động</option>
                                <option value="ended">Đã kết thúc</option>
                                <option value="archived">Lưu trữ</option>
                            </select>
                            @error('status') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                        </label>
                        
                        <label class="col-span-1 md:col-span-2 block">
                            <span class="mb-2 block text-sm font-bold text-on-surface">Mô tả lớp học (Tùy chọn)</span>
                            <textarea wire:model="description" class="h-24 w-full resize-none rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20" placeholder="Nhập mô tả..."></textarea>
                            @error('description') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                        </label>
                    </div>
                </div>

                <!-- Cấu hình điểm danh -->
                <div>
                    <h3 class="mb-4 text-lg font-bold text-on-surface border-b border-outline-variant/10 pb-2">Cấu hình điểm danh</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <label class="block">
                            <span class="mb-2 block text-sm font-bold text-on-surface">Tổng số buổi <span class="text-error">*</span></span>
                            <input type="number" wire:model="totalSessions" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20">
                            @error('totalSessions') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                        </label>
                        
                        <label class="block">
                            <span class="mb-2 block text-sm font-bold text-on-surface">Số tiết mỗi buổi <span class="text-error">*</span></span>
                            <input type="number" wire:model="lessonsPerSession" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20">
                            @error('lessonsPerSession') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                        </label>
                    </div>
                </div>

                <!-- Cấu hình bảo mật -->
                <div>
                    <h3 class="mb-4 text-lg font-bold text-on-surface border-b border-outline-variant/10 pb-2">Bảo mật tham gia</h3>
                    <div class="flex items-center justify-between rounded-xl border border-outline-variant/20 bg-surface-container-lowest p-4">
                        <div>
                            <span class="block text-sm font-bold text-on-surface">Yêu cầu duyệt khi tham gia lớp</span>
                            <span class="text-xs text-on-surface-variant">Sinh viên sẽ phải chờ bạn phê duyệt thay vì được thêm ngay vào danh sách.</span>
                        </div>
                        <button 
                            type="button" 
                            wire:click="$toggle('requireApproval')"
                            class="relative h-6 w-12 rounded-full transition-colors {{ $requireApproval ? 'bg-primary' : 'bg-outline-variant/50' }}"
                        >
                            <span class="absolute top-1 h-4 w-4 rounded-full bg-white transition-all {{ $requireApproval ? 'right-1' : 'left-1' }}"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Nút lưu -->
            <div class="flex items-center justify-between border-t border-outline-variant/20 bg-surface-container-lowest p-6">
                <div class="text-sm text-on-surface-variant">
                    <span wire:loading wire:target="save">Đang lưu thay đổi...</span>
                </div>
                <div class="flex gap-4">
                    <button type="button" wire:click="save" class="flex items-center gap-2 rounded-xl bg-primary px-6 py-3 font-bold text-white shadow-md shadow-primary/20 transition-all hover:bg-primary-container disabled:opacity-50">
                        <x-user.icon name="save" :size="18" />
                        Lưu thay đổi
                    </button>
                </div>
            </div>
        </form>
    </div>
    <div class="mt-8 rounded-[2rem] border border-error/20 bg-error/5 p-6 md:p-10 shadow-sm">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-2xl border border-error/10 bg-white p-5">
            <div>
                <h4 class="font-bold text-on-surface">Xóa lớp học</h4>
                <p class="text-xs text-on-surface-variant mt-1">Xóa lớp học khỏi hệ thống quản lý của bạn.</p>
            </div>
            <button 
                type="button" 
                wire:click="confirmDelete"
                class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-error/20 bg-error/10 px-5 py-2.5 font-bold text-error transition-colors hover:bg-error hover:text-white"
            >
                <x-user.icon name="trash-2" :size="18" />
                Xóa lớp học
            </button>
        </div>
    </div>

    @if ($isConfirmingDelete)
        <template x-teleport="body">
            <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                    <div class="mb-4 flex items-center gap-3 text-error">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-error/10">
                            <x-user.icon name="alert-triangle" :size="20" />
                        </div>
                        <h3 class="text-lg font-bold text-on-surface">Xác nhận xóa lớp học</h3>
                    </div>
                    <p class="text-sm text-on-surface-variant">Bạn có chắc chắn muốn xóa lớp học này không? Mọi thông tin điểm danh có thể sẽ bị vô hiệu hóa.</p>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" wire:click="closeDeleteConfirm" class="rounded-xl px-5 py-2.5 text-sm font-bold text-on-surface-variant hover:bg-surface-container-low transition-colors">Hủy</button>
                        <button type="button" wire:click="deleteClass" class="rounded-xl bg-error px-5 py-2.5 text-sm font-bold text-white hover:bg-error/90 transition-colors">Xóa lớp học</button>
                    </div>
                </div>
            </div>
        </template>
    @endif
</div>
