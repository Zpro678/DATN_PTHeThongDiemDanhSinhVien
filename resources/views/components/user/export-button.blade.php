@props([
    'action' => null,   // Tên method wire:click để kích hoạt xuất (nút trong cùng component Livewire).
    'href' => null,     // Hoặc dùng liên kết điều hướng thay cho wire:click (vd chuyển sang trang xuất).
    'label' => 'Xuất Excel',
    'can' => false,     // Có được xuất không (gói Pro + bật tính năng xuất Excel).
    'target' => null,   // wire:target cho trạng thái loading (mặc định = action).
])

@php
    // Kiểu chung cho MỌI nút xuất trong hệ thống — đồng bộ ở tất cả các trang.
    $enabledClass = 'group inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 shadow-sm transition-all hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900';
    $lockedClass = 'group inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 text-sm font-bold text-amber-700 shadow-sm transition-all hover:border-amber-300 hover:bg-amber-100';
    $wireTarget = $target ?? $action;
@endphp

@if(! $can)
    {{-- Chưa đủ điều kiện: hiển thị nút khóa có vương miện, dẫn tới trang nâng cấp. --}}
    <a href="{{ route('upgrade') }}" title="Xuất Excel là tính năng của gói Pro trở lên" {{ $attributes->merge(['class' => $lockedClass]) }}>
        <x-user.icon name="crown" :size="16" class="text-amber-500" />
        <span class="leading-none">{{ $label }}</span>
        <span class="rounded-md bg-amber-200/70 px-1.5 py-0.5 text-[10px] font-black uppercase tracking-wide text-amber-800">Pro</span>
    </a>
@elseif($href)
    <a href="{{ $href }}" wire:navigate {{ $attributes->merge(['class' => $enabledClass]) }}>
        <x-user.icon name="download" :size="16" />
        <span class="leading-none">{{ $label }}</span>
    </a>
@else
    <button type="button" wire:click="{{ $action }}" {{ $attributes->merge(['class' => $enabledClass]) }}>
        <span wire:loading.remove wire:target="{{ $wireTarget }}" class="flex items-center gap-2">
            <x-user.icon name="download" :size="16" />
            <span class="leading-none">{{ $label }}</span>
        </span>
        <span wire:loading wire:target="{{ $wireTarget }}" class="flex items-center gap-2">
            <x-user.icon name="loader" :size="16" class="animate-spin" />
            <span class="leading-none">Đang xuất...</span>
        </span>
    </button>
@endif
