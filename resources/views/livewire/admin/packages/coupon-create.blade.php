<div>
    <div class="mx-auto max-w-[800px]">
        <div class="mb-6 p-5 lg:p-6">
            <div class="relative z-10 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-slate-400">Coupons</p>
                    <h1 class="mb-1 text-[28px] font-bold text-slate-900">Tạo mã giảm giá mới</h1>
                    <p class="text-sm text-slate-500">Thiết lập thông số cho mã giảm giá mới.</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('admin.packages.coupons.index', ['ma_user' => auth()->user()->id]) }}" class="admin-soft-button rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                        Hủy bỏ
                    </a>
                </div>
            </div>
        </div>

        <div class="admin-card overflow-hidden rounded-2xl border bg-white shadow-sm p-6">
            <form wire:submit="save" class="space-y-6">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Mã (Code) <span class="text-red-500">*</span></label>
                    <div class="flex gap-2">
                        <input type="text" wire:model="code" placeholder="VD: SUMMER2026" class="w-full uppercase rounded-xl border border-slate-300 bg-transparent px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        <button type="button" wire:click="generateRandomCode" class="shrink-0 rounded-xl bg-slate-100 px-4 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-200">
                            Tạo ngẫu nhiên
                        </button>
                    </div>
                    @error('code') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Áp dụng cho gói</label>
                    <select wire:model="applicable_plan_id" class="w-full rounded-xl border border-slate-300 bg-transparent px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Tất cả gói dịch vụ --</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }} ({{ number_format($plan->price) }}đ)</option>
                        @endforeach
                    </select>
                    @error('applicable_plan_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Loại giảm giá <span class="text-red-500">*</span></label>
                        <select wire:model.live="type" class="w-full rounded-xl border border-slate-300 bg-transparent px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            <option value="PERCENT">Theo phần trăm (%)</option>
                            <option value="FIXED">Số tiền cố định (VND)</option>
                        </select>
                        @error('type') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Giá trị giảm <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="value" placeholder="{{ $type === 'PERCENT' ? 'VD: 20' : 'VD: 50000' }}" class="w-full rounded-xl border border-slate-300 bg-transparent px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        @error('value') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Giới hạn số lượt dùng</label>
                    <input type="number" wire:model="usage_limit" placeholder="Để trống nếu không giới hạn" class="w-full rounded-xl border border-slate-300 bg-transparent px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    @error('usage_limit') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Từ ngày</label>
                        <input type="datetime-local" wire:model="valid_from" class="w-full rounded-xl border border-slate-300 bg-transparent px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        @error('valid_from') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Đến ngày</label>
                        <input type="datetime-local" wire:model="valid_until" class="w-full rounded-xl border border-slate-300 bg-transparent px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        @error('valid_until') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-end gap-3 pt-5 border-t border-slate-100">
                    <button type="submit" class="rounded-xl bg-blue-600 px-8 py-3 text-sm font-bold text-white shadow-sm shadow-blue-500/30 transition-all hover:-translate-y-0.5 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Tạo mã giảm giá
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
