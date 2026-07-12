<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Chấm nghi vấn theo IP (chống VPN/proxy, lệch IP↔GPS)
    |--------------------------------------------------------------------------
    |
    | Phần chống fake GPS theo IP CHỈ đáng tin khi request()->ip() là IP THẬT của
    | sinh viên. Khi web chạy sau reverse-proxy/CDN mà chưa khai báo TRUSTED_PROXIES,
    | request()->ip() trả IP của proxy (giống nhau cho mọi người) -> chấm IP sẽ báo
    | nhầm hàng loạt (vd IP Cloudflare bị coi là hosting/VPN).
    |
    | Vì vậy mặc định TỰ BẬT khi đã khai báo TRUSTED_PROXIES, và TỰ TẮT khi chưa —
    | an toàn tuyệt đối: chưa cấu hình proxy thì không bao giờ báo nhầm. Có thể ép
    | bật/tắt thủ công qua GPS_IP_CHECK (vd chạy trực tiếp không proxy nhưng IP đã thật
    | thì đặt GPS_IP_CHECK=true).
    |
    | LƯU Ý: đọc env() ở đây (thời điểm nạp config) để giá trị được "bake" vào cache khi
    | chạy config:cache — service phải đọc qua config(), KHÔNG đọc env() lúc chạy.
    |
    */

    'gps_ip_check' => (bool) env('GPS_IP_CHECK', ! empty(env('TRUSTED_PROXIES'))),

];
