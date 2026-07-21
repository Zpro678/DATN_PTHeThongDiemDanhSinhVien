<?php

namespace App\Livewire\Concerns;

trait DeniesAccess
{
    /**
     * Guard quyền truy cập ở mount(): thay vì bắn 403/404 ra trang lỗi trắng,
     * đưa người dùng về trang danh sách tương ứng kèm toast báo lỗi.
     *
     * LƯU Ý: nơi gọi PHẢI `return;` ngay sau khi gọi hàm này — redirectRoute()
     * chỉ ghi nhận redirect chứ không dừng hàm đang chạy.
     */
    protected function denyAccess(string $route, string $message): void
    {
        session()->flash('error', $message);
        $this->redirectRoute($route, navigate: true);
    }
}
