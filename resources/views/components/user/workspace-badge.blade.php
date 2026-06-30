@props(['type' => 'owner'])

@php
    $isOwner = $type === 'owner';
@endphp

<span {{ $attributes->merge(['class' => 'mb-2 inline-flex w-fit items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold '.($isOwner ? 'bg-primary/10 text-primary' : 'bg-tertiary/10 text-tertiary')]) }}>
    <x-user.icon :name="$isOwner ? 'shield' : 'user'" :size="14" />
    {{ $isOwner ? 'Không gian Chủ lớp' : 'Không gian Học viên' }}
</span>
