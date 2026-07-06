<div class="space-y-8 p-6 md:p-8">
    <div>
        <h2 class="mb-1 text-lg font-bold text-slate-900">Giao diện & Tải lên</h2>
        <p class="mb-6 text-sm text-slate-500">Thay đổi thông tin nhận diện hệ thống và các giới hạn dung lượng.</p>

    @if (session()->has('general_success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 font-medium text-emerald-700 text-sm">
            {{ session('general_success') }}
        </div>
    @endif

    <form wire:submit.prevent="save">
        <div class="space-y-6">
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-slate-700">Tên hệ thống</label>
                <input type="text" wire:model="app_name" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500 md:w-2/3">
                @error('app_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-semibold text-slate-700">Logo hệ thống</label>
                <div class="mt-2 flex items-center gap-6">
                    <div class="flex h-20 w-20 items-center justify-center rounded-2xl border border-slate-200 shadow-sm overflow-hidden relative bg-white">
                        @if ($new_logo)
                            <img src="{{ $new_logo->temporaryUrl() }}" class="h-full w-full object-cover">
                        @elseif ($app_logo_path)
                            <img src="{{ asset('storage/' . $app_logo_path) }}" class="h-full w-full object-cover">
                        @else
                            <img src="{{ asset('favicon.svg') }}" class="h-full w-full object-contain p-2">
                        @endif
                    </div>
                    
                    <div class="space-y-2 relative">
                        <input type="file" wire:model="new_logo" id="new_logo" title=" " class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*">
                        <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50 pointer-events-none">
                            Thay đổi Logo
                        </button>
                        <p class="text-[11px] text-slate-500">Khuyên dùng định dạng PNG, SVG hoặc JPG. Kích thước tối đa 2MB.</p>
                        @error('new_logo') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <hr class="border-slate-100">

            <div class="space-y-2">
                <label class="block text-sm font-semibold text-slate-700">Giới hạn dung lượng Import Excel (MB)</label>
                <input type="number" wire:model="max_upload_size" min="1" max="100" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500 md:w-1/3">
                <p class="mt-1 text-xs text-slate-500">Dung lượng tối đa cho phép tải lên khi Import danh sách học viên.</p>
                @error('max_upload_size') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="mt-8 flex items-center justify-end gap-3 border-t border-slate-100 pt-6">
            <div wire:loading wire:target="save" class="text-sm font-medium text-slate-500 mr-2">Đang lưu...</div>
            <button type="button" onclick="window.location.reload()" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50">
                Hủy
            </button>
            <button type="submit" class="rounded-xl bg-gradient-to-r from-blue-600 to-cyan-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm shadow-blue-500/25 transition-all hover:-translate-y-0.5 hover:from-blue-700 hover:to-cyan-700">
                Lưu cấu hình giao diện
            </button>
        </div>
    </form>
    </div>
</div>
