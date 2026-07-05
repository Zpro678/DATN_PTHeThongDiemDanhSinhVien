<div>
    <div class="mx-auto max-w-[1400px]">
        <div class="mb-6 p-5 lg:p-6">
            <div class="relative z-10 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-slate-400">Coupons</p>
                    <h1 class="mb-1 text-[28px] font-bold text-slate-900">Quản lý mã giảm giá</h1>
                    <p class="text-sm text-slate-500">Tạo và quản lý các khuyến mãi áp dụng khi người dùng nâng cấp gói dịch vụ.</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('admin.packages.index') }}" class="admin-soft-button rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                        Quay lại Gói dịch vụ
                    </a>
                    <a href="{{ route('admin.packages.coupons.create', ['ma_user' => auth()->user()->id]) }}" class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-cyan-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-500/30 transition-all hover:-translate-y-0.5 hover:from-blue-700 hover:to-cyan-700">
                        <x-user.icon name="plus" :size="16" />
                        Tạo mã mới
                    </a>
                </div>
            </div>
        </div>

        <div class="admin-card overflow-hidden rounded-2xl border bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-500">
                    <thead class="bg-slate-50/50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-6 py-4 font-bold">Mã giảm giá</th>
                            <th class="px-6 py-4 font-bold">Áp dụng cho</th>
                            <th class="px-6 py-4 font-bold">Mức giảm</th>
                            <th class="px-6 py-4 font-bold">Lượt dùng</th>
                            <th class="px-6 py-4 font-bold">Thời hạn</th>
                            <th class="px-6 py-4 font-bold text-center">Trạng thái</th>
                            <th class="px-6 py-4 font-bold text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($coupons as $coupon)
                        <tr class="transition-colors hover:bg-slate-50/50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="rounded bg-blue-100 px-2.5 py-1 font-mono text-sm font-bold tracking-wider text-blue-700">
                                        {{ $coupon->code }}
                                    </span>
                                </div>
                                <div class="mt-1 text-[11px] text-slate-400">Ngày tạo: {{ $coupon->created_at->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($coupon->plan)
                                    <span class="font-medium text-slate-700">{{ $coupon->plan->name }}</span>
                                @else
                                    <span class="font-medium text-purple-600">Mọi gói dịch vụ</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($coupon->type === 'PERCENT')
                                    <span class="font-bold text-emerald-600">Giảm {{ $coupon->value }}%</span>
                                @else
                                    <span class="font-bold text-amber-600">Giảm {{ number_format($coupon->value) }}đ</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-medium text-slate-700">{{ $coupon->used_count }}</span>
                                <span class="text-slate-400">/ {{ $coupon->usage_limit ?: '∞' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($coupon->valid_until)
                                    @if($coupon->valid_until->isPast())
                                        <span class="text-red-500 font-medium">Hết hạn ({{ $coupon->valid_until->format('d/m/Y') }})</span>
                                    @else
                                        <span class="text-slate-600">Đến {{ $coupon->valid_until->format('d/m/Y') }}</span>
                                    @endif
                                @else
                                    <span class="text-emerald-500 font-medium">Vĩnh viễn</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button type="button" wire:click="toggleStatus({{ $coupon->id }})" class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 {{ $coupon->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}">
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $coupon->is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.packages.coupons.edit', ['ma_user' => auth()->user()->id, 'coupon' => $coupon->id]) }}" class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-blue-50 hover:text-blue-600" title="Chỉnh sửa">
                                        <x-user.icon name="edit" :size="18" />
                                    </a>
                                    <button wire:click="deleteCoupon({{ $coupon->id }})" wire:confirm="Bạn có chắc chắn muốn xóa mã giảm giá này?" class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-rose-50 hover:text-rose-600" title="Xóa">
                                        <x-user.icon name="trash-2" :size="18" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                        <x-user.icon name="ticket" :size="32" />
                                    </div>
                                    <p class="text-base font-medium">Chưa có mã giảm giá nào</p>
                                    <p class="mt-1 text-sm text-slate-400">Hãy tạo mã giảm giá đầu tiên cho hệ thống.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
</div>
