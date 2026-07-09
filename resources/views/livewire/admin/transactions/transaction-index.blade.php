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
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tên, email, mã GD..." class="w-64 rounded-xl border border-slate-200 bg-white py-2 pl-10 pr-4 text-sm text-slate-900 shadow-sm transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    @if($search || $statusFilter !== 'all' || $paymentMethodFilter !== 'all' || $dateFrom || $dateTo)
                        <button wire:click="clearFilters" class="text-sm font-semibold text-blue-600 hover:text-blue-700 hover:underline">Xóa lọc</button>
                    @endif
                </div>
            </div>
            
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4 lg:gap-6 relative z-10">
                <div class="relative">
                    <x-user.icon name="filter" :size="14" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                    <select wire:model.live="statusFilter" class="w-full appearance-none rounded-xl border border-slate-200 bg-white py-2 pl-9 pr-8 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="all">Tất cả trạng thái</option>
                        <option value="pending">Đang chờ</option>
                        <option value="success">Hoàn thành</option>
                        <option value="failed">Thất bại</option>
                    </select>
                </div>
                <div class="relative">
                    <x-user.icon name="credit-card" :size="14" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                    <select wire:model.live="paymentMethodFilter" class="w-full appearance-none rounded-xl border border-slate-200 bg-white py-2 pl-9 pr-8 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="all">Tất cả phương thức</option>
                        <option value="momo">MoMo</option>
                        <option value="payos">PayOS</option>
                    </select>
                </div>
                <div class="relative">
                    <x-user.icon name="calendar" :size="14" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                    <input wire:model.live="dateFrom" type="date" class="w-full rounded-xl border border-slate-200 bg-white py-2 pl-9 pr-4 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="relative">
                    <x-user.icon name="calendar" :size="14" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                    <input wire:model.live="dateTo" type="date" class="w-full rounded-xl border border-slate-200 bg-white py-2 pl-9 pr-4 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                        <div class="flex-1 rounded-2xl border border-slate-100 bg-white/75 p-4 transition-all hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50/40 hover:shadow-sm">
                            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                <span class="rounded-md border px-2.5 py-1 text-[10px] font-extrabold tracking-wider {{ $tone }}">
                                    {{ $label }}
                                </span>
                                <span class="flex items-center gap-1.5 text-xs font-medium text-slate-400">
                                    <x-user.icon name="calendar" :size="14" />
                                    {{ $tx->created_at?->format('d/m/Y H:i') ?? 'N/A' }}
                                </span>
                            </div>
                            
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-600 ring-2 ring-white">
                                    @if($tx->user?->avatar)
                                        <img src="{{ $tx->user->avatar_url }}" alt="" class="h-full w-full rounded-full object-cover">
                                    @else
                                        {{ mb_substr($tx->user?->name ?? 'U', 0, 1) }}
                                    @endif
                                </span>
                                <p class="break-words text-sm font-semibold leading-relaxed text-slate-700">
                                    <span class="font-bold text-blue-600">{{ $tx->user?->name ?? 'Người dùng' }}</span> 
                                    <span class="text-xs text-slate-500">({{ $tx->user?->email }})</span>
                                </p>
                            </div>
                            
                            <div class="mt-3 flex flex-wrap items-center gap-3 text-xs font-medium text-slate-600">
                                <span class="rounded bg-slate-100 px-2 py-1 font-bold">{{ $tx->plan?->name ?? 'Gói nâng cấp' }}</span>
                                <span class="rounded bg-slate-100 px-2 py-1 font-bold text-slate-800">{{ number_format($tx->amount, 0, ',', '.') }} {{ $tx->currency }}</span>
                                <span class="rounded border border-blue-100 bg-blue-50 px-2 py-1 font-bold text-blue-600 uppercase">{{ $tx->payment_method }}</span>
                            </div>

                            <div class="mt-4 border-t border-slate-100 pt-3">
                                <button type="button" @click="open = !open" class="flex items-center gap-1 text-xs font-bold text-slate-500 hover:text-blue-600 transition-colors">
                                    <x-user.icon name="eye" :size="12" /> Chi tiết
                                    <x-user.icon name="chevron-down" :size="12" class="transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
                                </button>
                                
                                <div x-show="open" x-collapse>
                                    <div class="mt-3 rounded-xl bg-slate-50 p-4 border border-slate-100">
                                        <dl class="space-y-2 text-xs">
                                            <div class="flex items-start justify-between">
                                                <dt class="font-medium text-slate-500">Mã giao dịch (Hệ thống):</dt>
                                                <dd class="font-bold text-slate-800 break-all text-right max-w-[60%]">{{ $tx->transaction_code }}</dd>
                                            </div>
                                            @if($tx->reference_code)
                                                <div class="flex items-start justify-between">
                                                    <dt class="font-medium text-slate-500">Mã tham chiếu (Cổng):</dt>
                                                    <dd class="font-bold text-slate-800 break-all text-right max-w-[60%]">{{ $tx->reference_code }}</dd>
                                                </div>
                                            @endif
                                            @if($tx->gateway_transaction_id)
                                                <div class="flex items-start justify-between">
                                                    <dt class="font-medium text-slate-500">Mã GD Cổng thanh toán:</dt>
                                                    <dd class="font-bold text-slate-800 break-all text-right max-w-[60%]">{{ $tx->gateway_transaction_id }}</dd>
                                                </div>
                                            @endif
                                            @if($tx->paid_at)
                                                <div class="flex items-start justify-between">
                                                    <dt class="font-medium text-slate-500">Thời gian thanh toán:</dt>
                                                    <dd class="font-bold text-slate-800 text-right">{{ $tx->paid_at->format('d/m/Y H:i:s') }}</dd>
                                                </div>
                                            @endif
                                            @if($tx->failure_reason)
                                                <div class="flex items-start justify-between">
                                                    <dt class="font-medium text-slate-500">Lý do thất bại:</dt>
                                                    <dd class="font-bold text-rose-600 text-right break-words max-w-[60%]">{{ $tx->failure_reason }}</dd>
                                                </div>
                                            @endif
                                            @if($tx->payment_response)
                                                <div class="mt-4 border-t border-slate-200 pt-3">
                                                    <dt class="mb-2 font-medium text-slate-500">Dữ liệu cổng thanh toán trả về (Raw):</dt>
                                                    <dd>
                                                        <pre class="overflow-x-auto rounded-lg bg-slate-900 p-3 text-[10px] text-emerald-400 font-mono">{{ json_encode($tx->payment_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    </dd>
                                                </div>
                                            @endif
                                        </dl>
                                    </div>
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
