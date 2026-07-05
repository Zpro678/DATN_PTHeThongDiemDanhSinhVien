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
    <div class="rounded-2xl border border-outline-variant/10 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

            {{-- Lọc theo trạng thái --}}
            <div class="relative">
                <x-user.icon name="filter" :size="15" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                <select id="transaction-status-filter" wire:model.live="statusFilter"
                    class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-8 text-sm font-medium text-slate-700 focus:border-primary focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/20 transition">
                    @foreach($statusLabels as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Từ ngày --}}
            <div class="relative">
                <x-user.icon name="calendar" :size="15" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                <input id="transaction-date-from" wire:model.live="dateFrom" type="date"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm font-medium text-slate-700 focus:border-primary focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/20 transition">
            </div>

            {{-- Đến ngày --}}
            <div class="relative">
                <x-user.icon name="calendar" :size="15" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                <input id="transaction-date-to" wire:model.live="dateTo" type="date"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm font-medium text-slate-700 focus:border-primary focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/20 transition">
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
                <div class="flex items-start gap-4 px-5 py-4 transition-colors hover:bg-slate-50/70 cursor-pointer"
                     @click="open = !open">

                    {{-- Icon --}}
                    <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $meta['bg'] }} {{ $meta['text'] }}">
                        <x-user.icon :name="$meta['icon']" :size="17" />
                    </div>

                    {{-- Nội dung chính --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            {{-- Badge status --}}
                            <span class="inline-flex items-center gap-1.5 rounded-lg border px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide
                                {{ $meta['bg'] }} {{ $meta['text'] }} border-transparent">
                                <span class="h-1.5 w-1.5 rounded-full {{ $meta['dot'] }}"></span>
                                {{ $meta['label'] }}
                            </span>

                            <span class="text-[13px] font-bold text-slate-800">
                                {{ $transaction->plan ? $transaction->plan->name : 'Gói nâng cấp' }}
                            </span>
                            
                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600">
                                {{ number_format($transaction->amount, 0, ',', '.') }} {{ $transaction->currency }}
                            </span>

                            <span class="inline-flex items-center gap-1 rounded-full border border-blue-100 bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-600 uppercase">
                                {{ $transaction->payment_method }}
                            </span>
                        </div>

                        <p class="mt-1 text-[13px] text-slate-500 font-medium flex flex-wrap items-center gap-x-1.5 gap-y-0.5">
                            <x-user.icon name="clock" :size="12" class="text-slate-400" />
                            <span>{{ $transaction->created_at?->format('H:i') }}</span>
                            <span class="text-slate-300">·</span>
                            <span>{{ $transaction->created_at?->format('d/m/Y') }}</span>
                            <span class="text-slate-300">·</span>
                            <x-user.icon name="hash" :size="12" class="text-slate-400" />
                            <span>{{ $transaction->transaction_code }}</span>
                        </p>
                    </div>

                    {{-- Chevron --}}
                    <div class="shrink-0 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''">
                        <x-user.icon name="chevron-down" :size="16" />
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
                    <div class="mx-5 mb-4 rounded-xl bg-slate-50 border border-slate-100 p-4">
                        <p class="mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">Chi tiết giao dịch</p>
                        <dl class="space-y-1.5">
                            <div class="flex items-start gap-3 text-[13px]">
                                <dt class="w-36 shrink-0 font-semibold text-slate-500">Mã giao dịch nội bộ</dt>
                                <dd class="font-medium text-slate-800 break-all">{{ $transaction->transaction_code }}</dd>
                            </div>
                            @if($transaction->reference_code)
                                <div class="flex items-start gap-3 text-[13px]">
                                    <dt class="w-36 shrink-0 font-semibold text-slate-500">Mã tham chiếu cổng</dt>
                                    <dd class="font-medium text-slate-800 break-all">{{ $transaction->reference_code }}</dd>
                                </div>
                            @endif
                            @if($transaction->gateway_transaction_id)
                                <div class="flex items-start gap-3 text-[13px]">
                                    <dt class="w-36 shrink-0 font-semibold text-slate-500">Mã GD cổng thanh toán</dt>
                                    <dd class="font-medium text-slate-800 break-all">{{ $transaction->gateway_transaction_id }}</dd>
                                </div>
                            @endif
                            @if($transaction->paid_at)
                                <div class="flex items-start gap-3 text-[13px]">
                                    <dt class="w-36 shrink-0 font-semibold text-slate-500">Thời gian thanh toán</dt>
                                    <dd class="font-medium text-slate-800">{{ $transaction->paid_at->format('d/m/Y H:i:s') }}</dd>
                                </div>
                            @endif
                            @if($transaction->failure_reason)
                                <div class="flex items-start gap-3 text-[13px]">
                                    <dt class="w-36 shrink-0 font-semibold text-slate-500">Lý do thất bại</dt>
                                    <dd class="font-medium text-rose-600 break-all">{{ $transaction->failure_reason }}</dd>
                                </div>
                            @endif
                            @if(strtolower($transaction->status) === 'pending' && $transaction->expired_at)
                                <div class="flex items-start gap-3 text-[13px]">
                                    <dt class="w-36 shrink-0 font-semibold text-slate-500">Hết hạn thanh toán</dt>
                                    <dd class="font-medium text-amber-600">
                                        {{ $transaction->expired_at->format('d/m/Y H:i:s') }}
                                        @if($transaction->expired_at->isPast())
                                            (Đã hết hạn)
                                        @endif
                                    </dd>
                                </div>
                                @if(!$transaction->expired_at->isPast() && $transaction->payment_url)
                                    <div class="mt-4">
                                        <button wire:click="continuePayment({{ $transaction->id }})" wire:loading.attr="disabled" wire:target="continuePayment({{ $transaction->id }})" class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-primary/90 disabled:opacity-70 disabled:cursor-not-allowed">
                                            <x-user.icon name="external-link" :size="14" /> 
                                            <span wire:loading.remove wire:target="continuePayment({{ $transaction->id }})">Tiếp tục thanh toán</span>
                                            <span wire:loading wire:target="continuePayment({{ $transaction->id }})">Đang xử lý...</span>
                                        </button>
                                    </div>
                                @endif
                            @endif
                        </dl>
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
