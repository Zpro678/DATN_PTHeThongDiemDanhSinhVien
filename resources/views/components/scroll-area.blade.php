{{--
    Vùng cuộn với thanh cuộn tùy biến (mảnh, bo tròn, theo tông màu thương hiệu).

    Cách dùng:
        <x-scroll-area class="max-h-96">
            ... nội dung dài ...
        </x-scroll-area>

    Mọi class/thuộc tính truyền vào đều được gộp thêm (id, x-data, wire:key, style...).
    Mặc định cuộn cả 2 chiều; muốn chỉ cuộn dọc thì thêm class "overflow-x-hidden".
--}}
<div {{ $attributes->merge(['class' => 'scrollbar-custom overflow-auto']) }}>
    {{ $slot }}
</div>
