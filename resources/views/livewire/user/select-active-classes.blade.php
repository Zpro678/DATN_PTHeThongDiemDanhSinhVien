<div class="w-full space-y-6 px-6 py-6 sm:px-10 lg:px-16 animate-in fade-in slide-in-from-bottom-4 duration-500">

    {{-- ===== HEADER ===== --}}
    <header class="flex flex-col gap-2">
        <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-slate-400">Quản lý gói dịch vụ</p>
        <h1 class="text-2xl font-black tracking-tight text-slate-900">Chọn lớp muốn giữ lại</h1>
        <p class="text-sm text-slate-500">
            Gói hiện tại của bạn chỉ cho phép tối đa <strong class="text-slate-800">{{ $maxAllowed }} lớp</strong>.
            Vui lòng chọn các lớp bạn muốn tiếp tục sử dụng — các lớp không được chọn sẽ được lưu trữ và không hoạt động.
        </p>
    </header>

    {{-- ===== BANNER ÂN HẠN ===== --}}
    @if ($isInGrace && $gracePeriodEnds)
        <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4">
            <div class="mt-0.5 shrink-0 grid h-9 w-9 place-items-center rounded-xl bg-amber-100 text-amber-600">
                <x-user.icon name="clock" :size="18" />
            </div>
            <div>
                <p class="text-sm font-bold text-amber-800">Bạn đang trong thời gian ân hạn</p>
                <p class="mt-0.5 text-sm text-amber-700">
                    Thời hạn chọn lớp của bạn kết thúc lúc
                    <strong>{{ $gracePeriodEnds->format('H:i, d/m/Y') }}</strong>.
                    Sau thời điểm đó, bạn bắt buộc phải chọn lớp trước khi dùng các tính năng khác.
                </p>
                <a href="{{ route('upgrade', ['ma_user' => auth()->id()]) }}" wire:navigate
                   class="mt-2 inline-flex items-center gap-1.5 text-xs font-bold text-amber-700 hover:underline">
                    <x-user.icon name="zap" :size="12" />
                    Nâng cấp gói để giữ tất cả lớp
                </a>
            </div>
        </div>
    @else
        <div class="flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4">
            <div class="mt-0.5 shrink-0 grid h-9 w-9 place-items-center rounded-xl bg-rose-100 text-rose-600">
                <x-user.icon name="alert-triangle" :size="18" />
            </div>
            <div>
                <p class="text-sm font-bold text-rose-800">Bạn đã vượt quá giới hạn số lớp của gói hiện tại</p>
                <p class="mt-0.5 text-sm text-rose-700">
                    Thời gian ân hạn đã kết thúc. Vui lòng chọn tối đa <strong>{{ $maxAllowed }} lớp</strong>
                    để tiếp tục sử dụng hệ thống, hoặc nâng cấp gói để giữ tất cả.
                </p>
                <a href="{{ route('upgrade', ['ma_user' => auth()->id()]) }}" wire:navigate
                   class="mt-2 inline-flex items-center gap-1.5 text-xs font-bold text-rose-700 hover:underline">
                    <x-user.icon name="zap" :size="12" />
                    Nâng cấp gói để giữ tất cả lớp
                </a>
            </div>
        </div>
    @endif

    {{-- ===== FORM CHỌN LỚP ===== --}}
    <form wire:submit="confirm" x-data="{
        selectedIds: $wire.entangle('selectedIds'),
        maxAllowed: {{ $maxAllowed }},
        toggle(id) {
            id = String(id);
            let idx = this.selectedIds.indexOf(id);
            if (idx > -1) {
                this.selectedIds.splice(idx, 1);
            } else {
                if (this.selectedIds.length < this.maxAllowed) {
                    this.selectedIds.push(id);
                }
            }
        },
        isChecked(id) {
            return this.selectedIds.includes(String(id));
        },
        isMaxed() {
            return this.selectedIds.length >= this.maxAllowed;
        },
        isDisabled(id) {
            return this.isMaxed() && !this.isChecked(id);
        }
    }">
        @error('selectedIds')
            <div class="flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <x-user.icon name="alert-circle" :size="16" />
                {{ $message }}
            </div>
        @enderror

        {{-- Counter --}}
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-slate-600">
                Đã chọn <span class="font-bold text-primary" x-text="selectedIds.length"></span> / {{ $maxAllowed }} lớp
            </p>
            <span x-show="isMaxed()" class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">
                <x-user.icon name="check-circle" :size="12" />
                Đủ số lượng
            </span>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($classes as $class)
                @php
                    $colorOptions = [
                        'bg-[#475569]', 'bg-[#1D4ED8]', 'bg-[#0F766E]', 'bg-[#4338CA]',
                        'bg-[#047857]', 'bg-[#0369A1]', 'bg-[#6D28D9]', 'bg-[#B45309]',
                    ];
                    $colorIndex = hexdec(substr(md5((string) $class->id), 0, 8));
                    $themeColor = $colorOptions[$colorIndex % count($colorOptions)];
                @endphp

                <div x-on:click="toggle('{{ $class->id }}')" 
                     :class="isDisabled('{{ $class->id }}') ? 'cursor-not-allowed opacity-50' : 'cursor-pointer'">
                    <article :class="{
                        'relative flex flex-col overflow-hidden rounded-2xl border-2 bg-white shadow-sm transition-all duration-200': true,
                        'border-primary ring-2 ring-primary/20 shadow-md': isChecked('{{ $class->id }}'),
                        'border-slate-200 hover:border-slate-300': !isChecked('{{ $class->id }}')
                    }">
                        {{-- Checkbox hidden --}}
                        <input
                            type="checkbox"
                            value="{{ $class->id }}"
                            :checked="isChecked('{{ $class->id }}')"
                            class="sr-only"
                            :disabled="isDisabled('{{ $class->id }}')"
                        />

                        {{-- Checkmark badge --}}
                        <div :class="{
                            'absolute right-3 top-3 z-20 grid h-6 w-6 place-items-center rounded-full border-2 transition-all': true,
                            'border-primary bg-primary text-white': isChecked('{{ $class->id }}'),
                            'border-white/80 bg-white/30': !isChecked('{{ $class->id }}')
                        }">
                            <template x-if="isChecked('{{ $class->id }}')">
                                <x-user.icon name="check" :size="13" />
                            </template>
                        </div>

                        {{-- Header --}}
                        <div class="{{ $themeColor }} h-20 px-4 py-3 relative">
                            <div class="relative z-10">
                                <h3 class="truncate font-semibold text-white text-base leading-tight" title="{{ $class->name }}">
                                    {{ $class->name }}
                                </h3>
                                <p class="mt-0.5 text-[12px] text-white/80">Mã: {{ $class->class_code ?? $class->join_key }}</p>
                            </div>
                        </div>

                        {{-- Body --}}
                        <div class="flex items-center justify-between px-4 py-3">
                            <div class="flex items-center gap-1.5 text-sm text-slate-600">
                                <x-user.icon name="users" :size="15" class="text-slate-400" />
                                <span>{{ $class->students_count }} sinh viên</span>
                            </div>
                            <span @class([
                                'rounded-full px-2 py-0.5 text-[11px] font-bold',
                                'bg-emerald-100 text-emerald-700' => $class->status === 'active',
                                'bg-slate-100 text-slate-500' => $class->status !== 'active',
                            ])>
                                {{ $class->status === 'active' ? 'Đang hoạt động' : ($class->status === 'archived' ? 'Đã lưu trữ' : 'Đã kết thúc') }}
                            </span>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>

        {{-- ACTIONS --}}
        <div class="flex flex-col gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
            <a href="{{ route('upgrade', ['ma_user' => auth()->id()]) }}" wire:navigate
               class="inline-flex items-center justify-center gap-2 rounded-xl border border-primary px-6 py-2.5 text-sm font-semibold text-primary transition hover:bg-primary/5">
                <x-user.icon name="zap" :size="16" />
                Nâng cấp gói để giữ tất cả
            </a>
            <button
                type="submit"
                wire:loading.attr="disabled"
                :class="{
                    'inline-flex items-center justify-center gap-2 rounded-xl px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition': true,
                    'bg-primary hover:bg-primary/90': selectedIds.length > 0,
                    'cursor-not-allowed bg-slate-300': selectedIds.length === 0
                }"
                :disabled="selectedIds.length === 0"
            >
                <span wire:loading.remove>
                    <x-user.icon name="save" :size="16" class="inline" />
                    Xác nhận giữ lại <span x-text="selectedIds.length"></span> lớp đã chọn
                </span>
                <span wire:loading>Đang lưu...</span>
            </button>
        </div>
    </form>
</div>
