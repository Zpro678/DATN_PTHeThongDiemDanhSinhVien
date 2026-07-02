<div class="relative min-h-screen bg-background">

    {{-- Gradient nền nhẹ --}}
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="absolute -top-32 left-1/2 h-[500px] w-[700px] -translate-x-1/2 rounded-full bg-primary/6 blur-[100px]"></div>
    </div>

    {{-- Header --}}
    <header class="relative flex items-center justify-between border-b border-outline-variant/20 bg-white/80 px-6 py-4 backdrop-blur-sm sm:px-10">
        <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2.5 text-on-surface transition-colors hover:text-primary">
            <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-primary/10 text-primary">
                <x-user.icon name="zap" :size="16" />
            </div>
            <span class="text-sm font-bold tracking-tight">{{ config('app.name', 'Attendia Tech') }}</span>
        </a>

        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}"
            wire:navigate
            class="flex h-9 w-9 items-center justify-center rounded-full border border-outline-variant/30 bg-white text-on-surface-variant shadow-sm transition-all hover:border-outline-variant hover:text-on-surface">
            <x-user.icon name="x" :size="18" />
        </a>
    </header>

    {{-- Nội dung chính --}}
    <main class="relative px-4 pb-24 pt-10 sm:px-6">

        {{-- Flash messages --}}
        @if (session('upgrade_required'))
            <div class="mx-auto mb-6 flex max-w-3xl items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-700">
                <x-user.icon name="zap" :size="18" /> {{ session('upgrade_required') }}
            </div>
        @endif
        @if (session('status'))
            <div class="mx-auto mb-6 flex max-w-3xl items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">
                <x-user.icon name="check-circle" :size="18" /> {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mx-auto mb-6 flex max-w-3xl items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700">
                <x-user.icon name="alert-triangle" :size="18" /> {{ session('error') }}
            </div>
        @endif

        {{-- Tiêu đề --}}
        <div class="mb-12 text-center">
            <div class="mb-4 inline-flex items-center gap-1.5 rounded-full border border-primary/20 bg-primary/8 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-primary">
                <x-user.icon name="sparkles" :size="12" /> Nâng cấp tài khoản
            </div>
            <h1 class="text-4xl font-extrabold tracking-tight text-on-surface sm:text-5xl">
                Mở khóa toàn bộ tính năng
            </h1>
            <p class="mx-auto mt-4 max-w-lg text-base text-on-surface-variant">
                Chọn gói phù hợp để quản lý lớp học, điểm danh và sinh viên hiệu quả hơn.
            </p>

            @if ($activeSubscription)
                <div class="mt-5 inline-flex items-center gap-2 rounded-full border border-outline-variant/30 bg-white px-4 py-1.5 text-sm text-on-surface-variant shadow-sm">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                    Đang dùng gói <span class="font-bold text-on-surface">{{ $activeSubscription->plan?->name }}</span>
                    @if ($activeSubscription->end_date)
                        &nbsp;· Hết hạn {{ $activeSubscription->end_date->format('d/m/Y') }}
                    @endif
                </div>
            @endif
        </div>

        @php
            $planStyles = [
                'FREE'       => [
                    'icon'    => 'shield',
                    'badge'   => 'bg-surface-container text-on-surface-variant',
                    'ring'    => 'ring-outline-variant/30',
                    'btnClass'=> 'border border-outline-variant/50 bg-white text-on-surface hover:bg-surface-container shadow-sm',
                    'accent'  => 'text-on-surface-variant',
                    'dot'     => 'bg-outline',
                ],
                'PRO'        => [
                    'icon'    => 'sparkles',
                    'badge'   => 'bg-primary/10 text-primary',
                    'ring'    => 'ring-primary',
                    'btnClass'=> 'bg-primary text-white hover:bg-primary/90 shadow-lg shadow-primary/25',
                    'accent'  => 'text-primary',
                    'dot'     => 'bg-primary',
                ],
                'ENTERPRISE' => [
                    'icon'    => 'zap',
                    'badge'   => 'bg-tertiary/10 text-tertiary',
                    'ring'    => 'ring-tertiary/60',
                    'btnClass'=> 'bg-tertiary text-white hover:bg-tertiary/90 shadow-lg shadow-tertiary/25',
                    'accent'  => 'text-tertiary',
                    'dot'     => 'bg-tertiary',
                ],
            ];
            $recommended = 'PRO';
        @endphp

        {{-- Bảng giá --}}
        <div class="mx-auto grid max-w-5xl grid-cols-1 gap-5 md:grid-cols-3">
            @foreach ($plans as $plan)
                @php
                    $style     = $planStyles[$plan->plan_tier] ?? $planStyles['FREE'];
                    $isCurrent = $plan->plan_tier === $currentPlanCode;
                    $isRec     = $plan->plan_tier === $recommended;
                @endphp

                <article @class([
                    'relative flex flex-col rounded-2xl bg-white p-7 shadow-sm transition-all duration-300',
                    'ring-2 ' . $style['ring'] . ' shadow-xl' => $isRec,
                    'ring-1 ring-outline-variant/30 hover:ring-outline-variant/50 hover:shadow-md' => ! $isRec,
                ])>
                    @if ($isRec)
                        <span class="absolute -top-3.5 left-1/2 -translate-x-1/2 rounded-full bg-primary px-4 py-1 text-[11px] font-bold uppercase tracking-wider text-white shadow-md shadow-primary/30">
                            Phổ biến nhất
                        </span>
                    @endif

                    {{-- Icon + trạng thái --}}
                    <div class="mb-5 flex items-center justify-between">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl {{ $style['badge'] }}">
                            <x-user.icon :name="$style['icon']" :size="22" />
                        </div>
                        @if ($isCurrent)
                            <span class="rounded-full border border-outline-variant/30 bg-surface-container-low px-3 py-0.5 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Đang dùng</span>
                        @endif
                    </div>

                    {{-- Tên & mô tả --}}
                    <h2 class="text-xl font-bold text-on-surface">{{ $plan->name }}</h2>
                    <p class="mt-1 min-h-[36px] text-sm text-on-surface-variant">{{ $plan->description }}</p>

                    {{-- Giá --}}
                    <div class="mt-5 flex items-baseline gap-1.5">
                        @if ((float) $plan->price <= 0)
                            <span class="text-4xl font-extrabold text-on-surface">Miễn phí</span>
                        @else
                            <span class="text-4xl font-extrabold text-on-surface">{{ number_format($plan->price, 0, ',', '.') }}đ</span>
                            <span class="text-sm text-on-surface-variant">/ {{ $plan->duration_days >= 365 ? round($plan->duration_days / 365).' năm' : round($plan->duration_days / 30).' tháng' }}</span>
                        @endif
                    </div>

                    {{-- Nút --}}
                    <div class="mt-6">
                        @if ($isCurrent)
                            <button disabled class="w-full cursor-default rounded-xl border border-outline-variant/30 bg-surface-container-low py-3 text-sm font-bold text-on-surface-variant">
                                Gói hiện tại
                            </button>
                        @else
                            <button type="button" wire:click="selectPlan({{ $plan->id }})"
                                class="w-full rounded-xl py-3 text-sm font-bold transition-all {{ $style['btnClass'] }}">
                                {{ $plan->plan_tier === 'FREE' ? 'Chuyển về Miễn phí' : 'Nâng cấp lên '.$plan->name }}
                            </button>
                        @endif
                    </div>

                    {{-- Tính năng --}}
                    <ul class="mt-6 space-y-3 border-t border-outline-variant/20 pt-6">
                        @foreach ($plan->features ?? [] as $feature)
                            <li class="flex items-start gap-3 text-sm text-on-surface-variant">
                                <x-user.icon name="check-circle" :size="17" class="mt-0.5 shrink-0 {{ $style['accent'] }}" />
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                </article>
            @endforeach
        </div>

        <p class="mt-10 text-center text-xs text-on-surface-variant/70">
            <x-user.icon name="shield" :size="12" class="mr-1 inline align-text-bottom" />
            Thanh toán an toàn qua ví MoMo · Kích hoạt ngay · Hủy bất kỳ lúc nào
        </p>
    </main>

    {{-- Modal xác nhận --}}
    @if ($confirmingPlan)
        @php $mStyle = $planStyles[$confirmingPlan->plan_tier] ?? $planStyles['FREE']; @endphp
        <div class="fixed inset-0 z-[100] flex items-end justify-center p-4 sm:items-center"
             wire:click.self="cancel">
            <div class="absolute inset-0 bg-on-background/40 backdrop-blur-sm"></div>

            <div class="relative w-full max-w-md animate-in slide-in-from-bottom-4 rounded-2xl bg-white p-7 shadow-2xl duration-300 sm:zoom-in-95">
                <button type="button" wire:click="cancel"
                    class="absolute right-5 top-5 flex h-8 w-8 items-center justify-center rounded-full border border-outline-variant/30 bg-surface-container-low text-on-surface-variant transition hover:bg-surface-container hover:text-on-surface">
                    <x-user.icon name="x" :size="16" />
                </button>

                <div class="mb-5 flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $mStyle['badge'] }}">
                        <x-user.icon :name="$mStyle['icon']" :size="24" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-on-surface">Xác nhận đăng ký</h3>
                        <p class="text-sm text-on-surface-variant">{{ $confirmingPlan->name }}</p>
                    </div>
                </div>

                <div class="mb-6 rounded-2xl border border-outline-variant/20 bg-surface-container-low p-4 text-sm">
                    @if ((float) $confirmingPlan->price <= 0)
                        <p class="text-on-surface-variant">Bạn sẽ chuyển về gói <strong class="text-on-surface">Miễn phí</strong>. Các quyền lợi gói trả phí hiện tại sẽ kết thúc.</p>
                    @else
                        <div class="flex items-center justify-between">
                            <span class="text-on-surface-variant">Tổng thanh toán</span>
                            <span class="text-2xl font-extrabold text-on-surface">{{ number_format($confirmingPlan->price, 0, ',', '.') }}đ</span>
                        </div>
                        <p class="mt-0.5 text-xs text-on-surface-variant/70">/ {{ $confirmingPlan->duration_days >= 365 ? round($confirmingPlan->duration_days / 365).' năm' : round($confirmingPlan->duration_days / 30).' tháng' }}</p>

                        <div class="mt-5">
                            <p class="mb-2 text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Phương thức thanh toán</p>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" wire:click="$set('paymentMethod','momo')" @class([
                                    'flex items-center justify-center gap-2 rounded-xl border-2 px-3 py-2.5 text-sm font-bold transition-all',
                                    'border-primary bg-primary/5 text-primary' => $paymentMethod === 'momo',
                                    'border-outline-variant/30 text-on-surface-variant hover:border-outline-variant' => $paymentMethod !== 'momo',
                                ])>
                                    <span class="text-[#a50064]">●</span> MoMo
                                </button>
                                <button type="button" wire:click="$set('paymentMethod','vnpay')" @class([
                                    'flex items-center justify-center gap-2 rounded-xl border-2 px-3 py-2.5 text-sm font-bold transition-all',
                                    'border-primary bg-primary/5 text-primary' => $paymentMethod === 'vnpay',
                                    'border-outline-variant/30 text-on-surface-variant hover:border-outline-variant' => $paymentMethod !== 'vnpay',
                                ])>
                                    <span class="text-[#0066b3]">●</span> VNPay
                                </button>
                            </div>
                            <p class="mt-3 text-xs text-on-surface-variant/70">
                                @if ($paymentMethod === 'vnpay')
                                    <span class="font-semibold text-amber-600">VNPay đang tích hợp</span> — vui lòng chọn MoMo.
                                @else
                                    Bạn sẽ được chuyển sang ví <span class="font-bold text-[#a50064]">MoMo</span> để hoàn tất thanh toán.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>

                <div class="flex gap-3">
                    <button type="button" wire:click="cancel"
                        class="flex-1 rounded-xl border border-outline-variant/30 bg-white py-3 text-sm font-bold text-on-surface-variant transition hover:bg-surface-container">
                        Huỷ
                    </button>
                    <button type="button" wire:click="subscribe" wire:loading.attr="disabled"
                        class="flex-1 rounded-xl py-3 text-sm font-bold transition-all disabled:opacity-50 {{ $mStyle['btnClass'] }}">
                        <span wire:loading.remove wire:target="subscribe">
                            {{ (float) $confirmingPlan->price <= 0 ? 'Xác nhận' : ($paymentMethod === 'vnpay' ? 'Thanh toán VNPay' : 'Thanh toán MoMo') }}
                        </span>
                        <span wire:loading wire:target="subscribe">Đang xử lý…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
