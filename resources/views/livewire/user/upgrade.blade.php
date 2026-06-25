<div class="mx-auto max-w-[1200px] p-4 pb-24 sm:p-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
    @php
        // Style theo từng mã gói (đặt tại view để Tailwind quét được class).
        $planStyles = [
            'FREE' => ['icon' => 'shield', 'accent' => 'text-on-surface-variant', 'badge' => 'bg-surface-container text-on-surface-variant', 'btn' => 'border border-outline-variant text-on-surface-variant hover:bg-surface-container'],
            'PRO' => ['icon' => 'sparkles', 'accent' => 'text-primary', 'badge' => 'bg-primary/10 text-primary', 'btn' => 'bg-primary text-white hover:bg-primary/90 shadow-md shadow-primary/20'],
            'ENTERPRISE' => ['icon' => 'zap', 'accent' => 'text-tertiary', 'badge' => 'bg-tertiary/10 text-tertiary', 'btn' => 'bg-tertiary text-white hover:bg-tertiary/90 shadow-md shadow-tertiary/20'],
        ];
        $recommended = 'PRO';
    @endphp

    {{-- Tiêu đề --}}
    <div class="mb-8 text-center">
        <div class="mb-3 inline-flex items-center gap-1.5 rounded-full border border-primary/20 bg-primary/5 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-primary">
            <x-user.icon name="sparkles" :size="14" />
            Gói dịch vụ
        </div>
        <h1 class="text-3xl font-extrabold tracking-tight text-on-surface md:text-4xl">Nâng cấp gói của bạn</h1>
        <p class="mx-auto mt-3 max-w-xl text-body-lg text-on-surface-variant">
            Chọn gói phù hợp để mở thêm lớp học, sinh viên và các tính năng nâng cao.
        </p>
    </div>

    @if (session('upgrade_required'))
        <div class="mx-auto mb-6 flex max-w-2xl items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-700">
            <x-user.icon name="zap" :size="20" />
            {{ session('upgrade_required') }}
        </div>
    @endif

    @if (session('status'))
        <div class="mx-auto mb-6 flex max-w-2xl items-center gap-3 rounded-2xl border border-tertiary/20 bg-tertiary/10 px-4 py-3 text-sm font-bold text-tertiary">
            <x-user.icon name="check-circle" :size="20" />
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mx-auto mb-6 flex max-w-2xl items-center gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">
            <x-user.icon name="zap" :size="20" />
            {{ session('error') }}
        </div>
    @endif

    @if ($activeSubscription)
        <div class="mx-auto mb-8 flex max-w-2xl flex-col items-center justify-between gap-2 rounded-2xl border border-outline-variant/20 bg-white px-5 py-3 text-sm shadow-sm sm:flex-row">
            <span class="font-bold text-on-surface">
                Bạn đang dùng gói <span class="text-primary">{{ $activeSubscription->plan?->name }}</span>
            </span>
            @if ($activeSubscription->end_date)
                <span class="text-on-surface-variant">Hết hạn: {{ $activeSubscription->end_date->format('d/m/Y') }}</span>
            @endif
        </div>
    @endif

    {{-- Bảng giá --}}
    <div class="mx-auto grid max-w-4xl grid-cols-1 gap-6 md:grid-cols-2">
        @foreach ($plans as $plan)
            @php
                $style = $planStyles[$plan->code] ?? $planStyles['FREE'];
                $isCurrent = $plan->code === $currentPlanCode;
                $isRecommended = $plan->code === $recommended;
            @endphp

            <article @class([
                'relative flex flex-col rounded-[2rem] bg-white p-6 shadow-sm transition-all duration-300',
                'ring-2 ring-primary' => $isRecommended,
                'ring-1 ring-outline-variant/20 hover:ring-outline-variant/40' => ! $isRecommended,
            ])>
                @if ($isRecommended)
                    <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-primary px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-white shadow-sm">
                        Phổ biến nhất
                    </span>
                @endif

                <div class="mb-4 flex items-center justify-between">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $style['badge'] }}">
                        <x-user.icon :name="$style['icon']" :size="24" />
                    </div>
                    @if ($isCurrent)
                        <span class="rounded-full bg-surface-container px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Đang dùng</span>
                    @endif
                </div>

                <h2 class="text-xl font-bold text-on-surface">{{ $plan->name }}</h2>
                <p class="mt-1 min-h-[40px] text-sm text-on-surface-variant">{{ $plan->description }}</p>

                <div class="mt-4 flex items-baseline gap-1">
                    @if ((float) $plan->price <= 0)
                        <span class="text-3xl font-extrabold text-on-surface">Miễn phí</span>
                    @else
                        <span class="text-3xl font-extrabold text-on-surface">{{ number_format($plan->price, 0, ',', '.') }}đ</span>
                        <span class="text-sm font-medium text-on-surface-variant">/ {{ $plan->duration_days >= 365 ? round($plan->duration_days / 365) . ' năm' : round($plan->duration_days / 30) . ' tháng' }}</span>
                    @endif
                </div>

                <div class="mt-6">
                    @if ($isCurrent)
                        <button type="button" disabled
                            class="w-full cursor-default rounded-xl border border-outline-variant/40 bg-surface-container-low py-3 text-sm font-bold text-on-surface-variant">
                            Gói hiện tại
                        </button>
                    @else
                        <button type="button" wire:click="selectPlan({{ $plan->id }})"
                            class="w-full rounded-xl py-3 text-sm font-bold transition-colors {{ $style['btn'] }}">
                            {{ $plan->code === 'FREE' ? 'Chuyển về Miễn phí' : 'Nâng cấp lên '.$plan->name }}
                        </button>
                    @endif
                </div>

                <ul class="mt-6 space-y-3 border-t border-outline-variant/15 pt-6">
                    @foreach ($plan->features ?? [] as $feature)
                        <li class="flex items-start gap-3 text-sm text-on-surface">
                            <x-user.icon name="check-circle" :size="18" class="mt-0.5 shrink-0 {{ $style['accent'] }}" />
                            <span>{{ $feature }}</span>
                        </li>
                    @endforeach
                </ul>
            </article>
        @endforeach
    </div>

    <p class="mt-8 text-center text-xs text-on-surface-variant">
        <x-user.icon name="shield" :size="14" class="mr-1 inline align-text-bottom" />
        Thanh toán an toàn qua ví MoMo. Gói được kích hoạt ngay sau khi thanh toán thành công.
    </p>

    {{-- Modal xác nhận --}}
    @if ($confirmingPlan)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-on-background/40 p-4 backdrop-blur-sm" wire:click.self="cancel">
            <div class="w-full max-w-md animate-in zoom-in-95 rounded-[2rem] bg-white p-6 shadow-2xl duration-200">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl {{ ($planStyles[$confirmingPlan->code] ?? $planStyles['FREE'])['badge'] }}">
                        <x-user.icon :name="($planStyles[$confirmingPlan->code] ?? $planStyles['FREE'])['icon']" :size="24" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-on-surface">Xác nhận đăng ký</h3>
                        <p class="text-sm text-on-surface-variant">Gói {{ $confirmingPlan->name }}</p>
                    </div>
                </div>

                <div class="mb-6 rounded-2xl border border-outline-variant/20 bg-surface-container-low p-4 text-sm">
                    @if ((float) $confirmingPlan->price <= 0)
                        <p class="text-on-surface">Bạn sẽ chuyển về gói <strong>Miễn phí</strong>. Các quyền lợi của gói trả phí hiện tại sẽ kết thúc.</p>
                    @else
                        <div class="flex items-center justify-between">
                            <span class="text-on-surface-variant">Giá</span>
                            <span class="font-bold text-on-surface">{{ number_format($confirmingPlan->price, 0, ',', '.') }}đ / {{ $confirmingPlan->duration_days >= 365 ? round($confirmingPlan->duration_days / 365) . ' năm' : round($confirmingPlan->duration_days / 30) . ' tháng' }}</span>
                        </div>

                        {{-- Chọn phương thức thanh toán --}}
                        <div class="mt-4">
                            <p class="mb-2 text-xs font-bold uppercase tracking-wide text-on-surface-variant">Phương thức thanh toán</p>
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" wire:click="$set('paymentMethod', 'momo')" @class([
                                    'flex items-center justify-center gap-2 rounded-xl border-2 px-3 py-2.5 text-sm font-bold transition-colors',
                                    'border-primary bg-primary/5 text-primary' => $paymentMethod === 'momo',
                                    'border-outline-variant/30 text-on-surface-variant hover:border-outline-variant' => $paymentMethod !== 'momo',
                                ])>
                                    <span class="text-base text-[#a50064]">●</span> MoMo
                                </button>
                                <button type="button" wire:click="$set('paymentMethod', 'vnpay')" @class([
                                    'flex items-center justify-center gap-2 rounded-xl border-2 px-3 py-2.5 text-sm font-bold transition-colors',
                                    'border-primary bg-primary/5 text-primary' => $paymentMethod === 'vnpay',
                                    'border-outline-variant/30 text-on-surface-variant hover:border-outline-variant' => $paymentMethod !== 'vnpay',
                                ])>
                                    <span class="text-base text-[#0066b3]">●</span> VNPay
                                </button>
                            </div>
                        </div>

                        <p class="mt-3 text-xs text-on-surface-variant">
                            @if ($paymentMethod === 'vnpay')
                                <span class="font-bold text-amber-600">VNPay đang được tích hợp</span> — hiện chưa thanh toán được, vui lòng chọn MoMo.
                            @else
                                Bạn sẽ được chuyển sang ví <strong class="text-[#a50064]">MoMo</strong> để thanh toán. Gói kích hoạt ngay khi thanh toán thành công.
                            @endif
                        </p>
                    @endif
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="cancel" class="rounded-xl px-5 py-2.5 text-sm font-bold text-on-surface-variant transition-colors hover:bg-surface-container">
                        Huỷ
                    </button>
                    <button type="button" wire:click="subscribe" wire:loading.attr="disabled"
                        class="rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-primary/20 transition-colors hover:bg-primary/90 disabled:opacity-60">
                        <span wire:loading.remove wire:target="subscribe">{{ (float) $confirmingPlan->price <= 0 ? 'Xác nhận' : ($paymentMethod === 'vnpay' ? 'Thanh toán qua VNPay' : 'Thanh toán qua MoMo') }}</span>
                        <span wire:loading wire:target="subscribe">Đang xử lý...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
