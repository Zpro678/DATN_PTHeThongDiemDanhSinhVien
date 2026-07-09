<div>
    <div class="mx-auto max-w-[1200px]">
        <div class="mb-6 p-5 lg:p-6">
            <div class="relative z-10 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-slate-400">Transactions</p>
                    <h1 class="mb-1 text-[28px] font-bold text-slate-900">Quản lý giao dịch</h1>
                    <p class="text-sm text-slate-500">Xem tất cả các giao dịch mua gói nâng cấp trên hệ thống.</p>
                </div>

                 <div class="flex flex-col items-end gap-3 sm:flex-row sm:items-center">
                    <div class="relative">
                        <x-user.icon name="search" :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tên, email, mã GD..." class="w-64 h-11 rounded-xl border border-slate-200 bg-white pl-10 pr-4 text-sm text-slate-900 shadow-sm transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    @if($search || $statusFilter !== 'all' || $paymentMethodFilter !== 'all' || $dateFrom || $dateTo)
                        <button wire:click="clearFilters" class="text-sm font-semibold text-blue-600 hover:text-blue-700 hover:underline">Xóa lọc</button>
                    @endif
                </div>
            </div>
            
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4 lg:gap-6 relative z-20">
                <div>
                    @php
                    $statusOptions = [
                        ['value' => 'all', 'label' => 'Tất cả trạng thái', 'sub_label' => 'Hiển thị mọi giao dịch'],
                        ['value' => 'pending', 'label' => 'Đang chờ', 'sub_label' => 'Giao dịch chưa hoàn tất'],
                        ['value' => 'success', 'label' => 'Hoàn thành', 'sub_label' => 'Giao dịch thành công'],
                        ['value' => 'failed', 'label' => 'Thất bại', 'sub_label' => 'Giao dịch bị lỗi/hủy'],
                    ];
                    @endphp
                    <x-custom-select wire:model.live="statusFilter" :options="$statusOptions" placeholder="" />
                </div>
                <div>
                    @php
                    $methodOptions = [
                        ['value' => 'all', 'label' => 'Tất cả phương thức', 'sub_label' => 'Mọi cổng thanh toán'],
                        ['value' => 'momo', 'label' => 'MoMo', 'sub_label' => 'Ví điện tử MoMo'],
                        ['value' => 'payos', 'label' => 'PayOS', 'sub_label' => 'Cổng thanh toán PayOS'],
                    ];
                    @endphp
                    <x-custom-select wire:model.live="paymentMethodFilter" :options="$methodOptions" placeholder="" />
                </div>
                <div class="relative">
                    <x-user.icon name="calendar" :size="14" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                    <input wire:model.live="dateFrom" type="date" class="w-full h-11 rounded-xl border border-slate-200 bg-white pl-9 pr-4 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="relative">
                    <x-user.icon name="calendar" :size="14" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                    <input wire:model.live="dateTo" type="date" class="w-full h-11 rounded-xl border border-slate-200 bg-white pl-9 pr-4 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <div class="admin-card overflow-hidden rounded-2xl border p-6 md:p-8">
            <div class="relative z-10 space-y-8 before:absolute before:bottom-2 before:left-[17px] before:top-2 before:w-0.5 before:bg-slate-100">
                @forelse ($transactions as $tx)
                    @php
                        $statusUpper = strtoupper($tx->status);
                        $icon = match ($statusUpper) {
                            'SUCCESS', 'PAID' => 'check-circle',
                            'FAILED' => 'x-circle',
                            'PENDING' => 'clock',
                            default => 'activity',
                        };
                        $tone = match ($statusUpper) {
                            'SUCCESS', 'PAID' => 'text-emerald-600 bg-emerald-50 border-emerald-100',
                            'FAILED' => 'text-rose-600 bg-rose-50 border-rose-100',
                            'PENDING' => 'text-amber-500 bg-amber-50 border-amber-100',
                            default => 'text-slate-600 bg-slate-50 border-slate-100',
                        };
                        $label = match ($statusUpper) {
                            'PAID' => 'SUCCESS',
                            default => $statusUpper,
                        };
                    @endphp

                    <div x-data="{ open: false }" class="group relative flex items-start gap-4 md:gap-6">
                        <div class="z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 border-white bg-slate-50 shadow-sm transition-transform group-hover:scale-105">
                            <x-user.icon name="{{ $icon }}" :size="16" class="{{ explode(' ', $tone)[0] }}" />
                        </div>
                        <div class="flex-1 rounded-2xl border border-slate-100 bg-white/75 p-4 transition-all hover:border-blue-200 hover:bg-blue-50/20 hover:shadow-sm">
                            <div class="flex flex-wrap items-center gap-3.5 cursor-pointer text-slate-700 md:grid md:grid-cols-12 md:gap-4" @click="open = !open">
                                {{-- Status badge --}}
                                <div class="md:col-span-2">
                                    <span class="rounded-md border px-2.5 py-1 text-xs font-bold tracking-wider {{ $tone }}">
                                        {{ $label }}
                                    </span>
                                </div>

                                {{-- Plan Name & Expiration Date --}}
                                <div class="flex flex-col md:col-span-2 min-w-0">
                                    <span class="text-base font-bold text-slate-800">
                                        {{ $tx->plan?->name ?? 'Gói nâng cấp' }}
                                    </span>
                                    <span class="text-xs text-slate-400 mt-0.5 font-medium">
                                        @if(in_array(strtoupper($tx->status), ['SUCCESS', 'PAID']) && $tx->plan)
                                            @php
                                                $paymentDate = $tx->paid_at ?? $tx->created_at;
                                            @endphp
                                            Hạn: {{ $tx->plan->duration_days === 0 ? 'Vĩnh viễn' : $paymentDate->copy()->addDays($tx->plan->duration_days)->format('d/m/Y') }}
                                        @else
                                            Hạn: --
                                        @endif
                                    </span>
                                </div>

                                {{-- Amount --}}
                                <div class="md:col-span-1">
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-sm font-bold text-slate-600">
                                        {{ number_format($tx->amount, 0, ',', '.') }}
                                    </span>
                                </div>

                                {{-- Method --}}
                                <div class="md:col-span-1">
                                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-bold text-blue-600 uppercase">
                                        {{ $tx->payment_method }}
                                    </span>
                                </div>

                                {{-- User Info --}}
                                <div class="flex items-center gap-2 border-l border-slate-200 pl-3 md:col-span-3 min-w-0">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-600">
                                        @if($tx->user?->avatar)
                                            <img src="{{ $tx->user->avatar_url }}" alt="" class="h-full w-full rounded-full object-cover">
                                        @else
                                            {{ mb_substr($tx->user?->name ?? 'U', 0, 1) }}
                                        @endif
                                    </span>
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-xs font-bold text-blue-600 truncate" title="{{ $tx->user?->name ?? 'Người dùng' }}">
                                            {{ $tx->user?->name ?? 'Người dùng' }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 truncate" title="{{ $tx->user?->email }}">{{ $tx->user?->email }}</span>
                                    </div>
                                </div>

                                {{-- Time & Code --}}
                                <div class="flex flex-col md:col-span-2 text-sm font-medium text-slate-400 min-w-0">
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-user.icon name="clock" :size="13" class="text-slate-400 shrink-0" />
                                        {{ $tx->created_at?->format('H:i - d/m/Y') }}
                                    </span>
                                    <span class="text-[11px] text-slate-400 truncate mt-0.5" title="# {{ $tx->transaction_code }}">
                                        # {{ $tx->transaction_code }}
                                    </span>
                                </div>

                                {{-- Chevron --}}
                                <div class="ml-auto shrink-0 text-slate-400 transition-transform duration-200 md:col-span-1 md:text-right" :class="open ? 'rotate-180' : ''">
                                    <x-user.icon name="chevron-down" :size="16" />
                                </div>
                            </div>

                            <div x-show="open" x-collapse>
                                    <div class="mt-3 rounded-xl bg-slate-50 p-5 border border-slate-100">
                                        <p class="mb-4 text-xs font-bold uppercase tracking-widest text-slate-400">Chi tiết giao dịch</p>
                                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 md:grid-cols-3 mb-2">
                                            <div>
                                                <p class="text-sm font-semibold text-slate-400 mb-1">Mã giao dịch (Hệ thống)</p>
                                                <p class="text-base font-bold text-slate-700 break-all">{{ $tx->transaction_code }}</p>
                                            </div>

                                            @if($tx->coupon_code)
                                                <div>
                                                    <p class="text-sm font-semibold text-slate-400 mb-1">Mã giảm giá đã dùng</p>
                                                    <p class="text-base font-bold text-blue-600 break-all">{{ $tx->coupon_code }}</p>
                                                </div>
                                            @endif
                                            @if(in_array(strtoupper($tx->status), ['SUCCESS', 'PAID']))
                                                @php
                                                    $paymentDate = $tx->paid_at ?? $tx->created_at;
                                                @endphp
                                                <div>
                                                    <p class="text-sm font-semibold text-slate-400 mb-1">Thời gian thanh toán</p>
                                                    <p class="text-base font-bold text-slate-700">{{ $paymentDate->format('d/m/Y H:i:s') }}</p>
                                                </div>
                                                @if($tx->plan)
                                                    <div>
                                                        <p class="text-sm font-semibold text-slate-400 mb-1">Hết hạn gói dịch vụ</p>
                                                        <p class="text-base font-bold text-indigo-600">
                                                            @if($tx->plan->duration_days === 0)
                                                                Vĩnh viễn
                                                            @else
                                                                {{ $paymentDate->copy()->addDays($tx->plan->duration_days)->format('d/m/Y H:i:s') }}
                                                            @endif
                                                        </p>
                                                    </div>
                                                @endif
                                            @endif
                                            @if($tx->failure_reason)
                                                <div>
                                                    <p class="text-sm font-semibold text-slate-400 mb-1">Lý do thất bại</p>
                                                    <p class="text-base font-bold text-rose-600 break-all">{{ $tx->failure_reason }}</p>
                                                </div>
                                            @endif
                                            @if(strtolower($tx->status) === 'pending' && $tx->expired_at)
                                                <div>
                                                    <p class="text-sm font-semibold text-slate-400 mb-1">Hết hạn thanh toán</p>
                                                    <p class="text-base font-bold text-amber-600">
                                                        {{ $tx->expired_at->format('d/m/Y H:i:s') }}
                                                        @if($tx->expired_at->isPast())
                                                            <span class="text-amber-500 font-semibold">(Đã hết hạn)</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                        @if($tx->payment_response)
                                            <div class="mt-4 border-t border-slate-200 pt-3">
                                                <p class="mb-2 text-xs font-semibold text-slate-400">Dữ liệu cổng thanh toán trả về (Raw)</p>
                                                <pre class="overflow-x-auto rounded-lg bg-slate-900 p-3 text-[10px] text-emerald-400 font-mono">{{ json_encode(json_decode($tx->payment_response), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                @empty
                    <div class="relative flex items-start gap-4 md:gap-6">
                        <div class="z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 border-white bg-slate-50 shadow-sm">
                            <x-user.icon name="receipt" :size="16" class="text-slate-400" />
                        </div>
                        <div class="flex-1 rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 p-6 text-sm font-medium text-slate-500">
                            @if($search || $statusFilter !== 'all' || $paymentMethodFilter !== 'all' || $dateFrom || $dateTo)
                                Không tìm thấy giao dịch nào phù hợp với bộ lọc.
                            @else
                                Chưa có giao dịch nào trên hệ thống.
                            @endif
                        </div>
                    </div>
                @endforelse
            </div>

            @if($transactions->hasPages())
                <div class="relative z-10 mt-8 border-t border-slate-200 pt-6">
                    {{ $transactions->links('vendor.livewire.tailwind') }}
                </div>
            @endif
        </div>
    </div>
</div>
