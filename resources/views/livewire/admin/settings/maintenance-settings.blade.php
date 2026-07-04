<div>
    <h2 class="mb-1 text-lg font-bold text-rose-600">Khu vực nguy hiểm</h2>
    <p class="mb-6 text-sm text-slate-500">Các thiết lập ảnh hưởng trực tiếp đến trạng thái hoạt động của toàn bộ ứng dụng.</p>

    <form wire:submit.prevent="save">
        <div class="flex flex-col items-start justify-between gap-6 rounded-2xl border border-rose-200 bg-rose-50 p-6 md:flex-row md:items-center">
            <div>
                <h3 class="text-base font-bold text-rose-900">Chế độ bảo trì hệ thống</h3>
                <p class="mt-1 text-sm text-rose-700">Khi bật chế độ này, hệ thống sẽ hiện thông báo bảo trì đỏ trên toàn bộ trang. (Chức năng khóa truy cập chưa được kích hoạt, chỉ hiển thị thông báo cảnh báo).</p>
            </div>

            <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                <input type="checkbox" wire:model="maintenance_mode" class="peer sr-only">
                <div class="peer h-7 w-14 rounded-full bg-slate-300 after:absolute after:left-[2px] after:top-[2px] after:h-6 after:w-6 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-rose-600 peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
            </label>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-slate-700">Thời gian dự kiến Bắt đầu</label>
                <input type="datetime-local" wire:model="start_time" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 transition-colors focus:border-rose-500 focus:ring-2 focus:ring-rose-500">
                @error('start_time') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
            </div>
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-slate-700">Thời gian dự kiến Kết thúc</label>
                <input type="datetime-local" wire:model="end_time" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 transition-colors focus:border-rose-500 focus:ring-2 focus:ring-rose-500">
                @error('end_time') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="mt-8 flex justify-end gap-3 border-t border-slate-100 pt-6">
            <button type="submit" class="rounded-xl bg-rose-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm shadow-rose-500/25 transition-all hover:-translate-y-0.5 hover:bg-rose-700 disabled:opacity-50" wire:loading.attr="disabled">
                <span wire:loading.remove>Lưu & Thông báo</span>
                <span wire:loading>Đang lưu...</span>
            </button>
        </div>
    </form>
</div>
