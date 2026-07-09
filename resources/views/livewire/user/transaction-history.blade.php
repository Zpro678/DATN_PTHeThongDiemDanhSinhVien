@php
    $statusMeta = [
        'PENDING'  => ['icon' => 'clock',        'bg' => 'bg-amber-100',   'text' => 'text-amber-700',   'dot' => 'bg-amber-500',  'label' => 'Đang chờ'],
        'SUCCESS'  => ['icon' => 'check-circle', 'bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-500','label' => 'Hoàn thành'],
        'PAID'     => ['icon' => 'check-circle', 'bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-500','label' => 'Hoàn thành'],
        'FAILED'   => ['icon' => 'x-circle',     'bg' => 'bg-rose-100',    'text' => 'text-rose-700',    'dot' => 'bg-rose-500',   'label' => 'Thất bại'],
    ];

    $defaultMeta = ['icon' => 'credit-card', 'bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'dot' => 'bg-gray-400', 'label' => 'Không rõ'];
@endphp

<div class="w-full space-y-6 px-4 py-6 sm:px-8 lg:px-14 animate-in fade-in slide-in-from-bottom-4 duration-500">

    {{-- ===== HEADER ===== --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-slate-400">Tài khoản</p>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900">Lịch sử giao dịch</h1>
            <p class="mt-0.5 text-sm font-medium text-slate-500">Danh sách các giao dịch mua gói nâng cấp của bạn.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-3 py-1.5 text-xs font-bold text-primary">
                <x-user.icon name="receipt" :size="13" />
                {{ $transactions->total() }} giao dịch
            </span>
            @if($statusFilter !== 'all' || $dateFrom || $dateTo)
                <button wire:click="clearFilters" type="button"
                    class="flex items-center gap-1.5 rounded-full border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-600 transition hover:bg-rose-100">
                    <x-user.icon name="x" :size="12" />
                    Xóa bộ lọc
                </button>
            @endif
        </div>
    </div>

    {{-- ===== FILTER BAR ===== --}}
    <div class="rounded-2xl border border-outline-variant/10 bg-white p-4 shadow-sm relative z-20">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

            {{-- Lọc theo trạng thái --}}
            <div>
                @php
                $statusOptions = [
                    ['value' => 'all', 'label' => 'Tất cả trạng thái', 'sub_label' => 'Hiển thị mọi giao dịch'],
                    ['value' => 'pending', 'label' => 'Đang chờ', 'sub_label' => 'Giao dịch chưa thanh toán'],
                    ['value' => 'success', 'label' => 'Hoàn thành', 'sub_label' => 'Thanh toán thành công'],
                    ['value' => 'failed', 'label' => 'Thất bại', 'sub_label' => 'Giao dịch lỗi hoặc bị hủy'],
                ];
                @endphp
                <x-custom-select id="transaction-status-filter" wire:model.live="statusFilter" :options="$statusOptions" placeholder="" />
            </div>

            {{-- Từ ngày --}}
            <div class="relative">
                <x-user.icon name="calendar" :size="15" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                <input id="transaction-date-from" wire:model.live="dateFrom" type="date"
                    class="w-full h-11 rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm font-medium text-slate-700 focus:border-primary focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/20 transition">
            </div>

            {{-- Đến ngày --}}
            <div class="relative">
                <x-user.icon name="calendar" :size="15" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                <input id="transaction-date-to" wire:model.live="dateTo" type="date"
                    class="w-full h-11 rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm font-medium text-slate-700 focus:border-primary focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/20 transition">
            </div>
        </div>
    </div>

    {{-- ===== TIMELINE ===== --}}
    <div class="rounded-2xl border border-outline-variant/10 bg-white shadow-sm overflow-hidden">
        @forelse($transactions as $transaction)
            @php
                $meta = $statusMeta[strtoupper($transaction->status)] ?? $defaultMeta;
            @endphp
            <div wire:key="transaction-{{ $transaction->id }}"
                 x-data="{ open: false }"
                 class="border-b border-outline-variant/10 last:border-b-0">
                <div class="flex flex-wrap items-center gap-3.5 px-5 py-4 transition-colors hover:bg-slate-50/70 cursor-pointer text-slate-700 md:grid md:grid-cols-12 md:gap-4"
                     @click="open = !open">

                    {{-- Icon & Status Badge --}}
                    <div class="flex items-center gap-3 md:col-span-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $meta['bg'] }} {{ $meta['text'] }}">
                            <x-user.icon :name="$meta['icon']" :size="17" />
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full {{ $meta['bg'] }} px-3 py-1 text-sm font-bold {{ $meta['text'] }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $meta['dot'] }}"></span>
                            {{ strtoupper($meta['label']) }}
                        </span>
                    </div>

                    {{-- Plan Name & Expiration Date --}}
                    <div class="flex flex-col md:col-span-2 min-w-0">
                        <span class="text-base font-bold text-slate-800">
                            {{ $transaction->plan ? $transaction->plan->name : 'Gói nâng cấp' }}
                        </span>
                        <span class="text-xs text-slate-400 mt-0.5 font-medium">
                            @if(in_array(strtoupper($transaction->status), ['SUCCESS', 'PAID']) && $transaction->plan)
                                @php
                                    $paymentDate = $transaction->paid_at ?? $transaction->created_at;
                                @endphp
                                Hạn: {{ $transaction->plan->duration_days === 0 ? 'Vĩnh viễn' : $paymentDate->copy()->addDays($transaction->plan->duration_days)->format('d/m/Y') }}
                            @else
                                Hạn: --
                            @endif
                        </span>
                    </div>

                    {{-- Amount --}}
                    <div class="md:col-span-1">
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-sm font-bold text-slate-600">
                            {{ number_format($transaction->amount, 0, ',', '.') }} {{ $transaction->currency }}
                        </span>
                    </div>

                    {{-- Method --}}
                    <div class="md:col-span-1">
                        <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-bold text-blue-600 uppercase">
                            {{ $transaction->payment_method }}
                        </span>
                    </div>

                    {{-- Time --}}
                    <span class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-400 md:col-span-2">
                        <x-user.icon name="clock" :size="14" class="text-slate-400" />
                        {{ $transaction->created_at?->format('H:i - d/m/Y') }}
                    </span>

                    {{-- Code --}}
                    <span class="text-sm font-medium text-slate-400 md:col-span-2 break-all">
                        # {{ $transaction->transaction_code }}
                    </span>

                    {{-- Chevron --}}
                    <div class="ml-auto shrink-0 text-slate-400 transition-transform duration-200 md:col-span-1 md:text-right" :class="open ? 'rotate-180' : ''">
                        <x-user.icon name="chevron-down" :size="18" />
                    </div>
                </div>

                {{-- Chi tiết (expand) --}}
                <div x-show="open"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-1"
                     style="display: none;"
                     x-cloak>
                    <div class="mx-5 mb-4 rounded-xl bg-slate-50 border border-slate-100 p-5">
                        <p class="mb-4 text-xs font-bold uppercase tracking-widest text-slate-400">Chi tiết giao dịch</p>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 md:grid-cols-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-400 mb-1">Mã giao dịch nội bộ</p>
                                <p class="text-base font-bold text-slate-700 break-all">{{ $transaction->transaction_code }}</p>
                            </div>

                            @if($transaction->coupon_code)
                                <div>
                                    <p class="text-sm font-semibold text-slate-400 mb-1">Mã giảm giá đã dùng</p>
                                    <p class="text-base font-bold text-blue-600 break-all">{{ $transaction->coupon_code }}</p>
                                </div>
                            @endif
                            @if(in_array(strtoupper($transaction->status), ['SUCCESS', 'PAID']))
                                @php
                                    $paymentDate = $transaction->paid_at ?? $transaction->created_at;
                                @endphp
                                <div>
                                    <p class="text-sm font-semibold text-slate-400 mb-1">Thời gian thanh toán</p>
                                    <p class="text-base font-bold text-slate-700">{{ $paymentDate->format('d/m/Y H:i:s') }}</p>
                                </div>
                                @if($transaction->plan)
                                    <div>
                                        <p class="text-sm font-semibold text-slate-400 mb-1">Hết hạn gói dịch vụ</p>
                                        <p class="text-base font-bold text-indigo-600">
                                            @if($transaction->plan->duration_days === 0)
                                                Vĩnh viễn
                                            @else
                                                {{ $paymentDate->copy()->addDays($transaction->plan->duration_days)->format('d/m/Y H:i:s') }}
                                            @endif
                                        </p>
                                    </div>
                                @endif
                            @endif
                            @if($transaction->failure_reason)
                                <div>
                                    <p class="text-sm font-semibold text-slate-400 mb-1">Lý do thất bại</p>
                                    <p class="text-base font-bold text-rose-600 break-all">{{ $transaction->failure_reason }}</p>
                                </div>
                            @endif
                            @if(strtolower($transaction->status) === 'pending' && $transaction->expired_at)
                                <div>
                                    <p class="text-sm font-semibold text-slate-400 mb-1">Hết hạn thanh toán</p>
                                    <p class="text-base font-bold text-amber-600">
                                        {{ $transaction->expired_at->format('d/m/Y H:i:s') }}
                                        @if($transaction->expired_at->isPast())
                                            <span class="text-amber-500 font-semibold">(Đã hết hạn)</span>
                                        @endif
                                    </p>
                                </div>
                            @endif
                        </div>
                        @if(strtolower($transaction->status) === 'pending' && $transaction->expired_at && !$transaction->expired_at->isPast() && $transaction->payment_url)
                            <div class="mt-5 border-t border-slate-100 pt-4">
                                <button wire:click="continuePayment({{ $transaction->id }})" wire:loading.attr="disabled" wire:target="continuePayment({{ $transaction->id }})" class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-primary/90 disabled:opacity-70 disabled:cursor-not-allowed">
                                    <x-user.icon name="external-link" :size="14" /> 
                                    <span wire:loading.remove wire:target="continuePayment({{ $transaction->id }})">Tiếp tục thanh toán</span>
                                    <span wire:loading wire:target="continuePayment({{ $transaction->id }})">Đang xử lý...</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <x-user.icon name="receipt" :size="30" />
                </div>
                <h3 class="text-base font-bold text-slate-800">Chưa có giao dịch nào</h3>
                <p class="mt-1 text-sm text-slate-400">
                    @if($statusFilter !== 'all' || $dateFrom || $dateTo)
                        Không tìm thấy giao dịch nào phù hợp với bộ lọc.
                    @else
                        Lịch sử mua gói nâng cấp của bạn sẽ xuất hiện tại đây.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    {{-- Phân trang --}}
    @if($transactions->hasPages())
        <div class="px-1">
            {{ $transactions->links() }}
        </div>
    @endif

</div>
