<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',   // Khóa định danh cấu hình (ví dụ: maintenance_mode, site_name)
        'value', // Giá trị của cấu hình dưới dạng chuỗi
    ];

    /**
     * Lấy giá trị của một setting theo key.
     *
     * An toàn khi bảng `settings` chưa tồn tại (deploy mới / chưa migrate / restore DB
     * thiếu bảng): trả về giá trị mặc định thay vì ném QueryException. Điều này quan trọng
     * vì get() được gọi ở middleware toàn cục (CheckSystemMaintenance) và nhiều view — nếu
     * ném lỗi sẽ làm sập TOÀN BỘ ứng dụng trên mọi request.
     */
    public static function get(string $key, $default = null)
    {
        try {
            $setting = self::query()->where('key', $key)->first();
        } catch (\Illuminate\Database\QueryException $e) {
            return $default;
        }

        return $setting ? $setting->value : $default;
    }

    /**
     * Cập nhật hoặc tạo mới một setting.
     */
    public static function set(string $key, $value)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Hệ thống có ĐANG trong thời gian bảo trì hay không (cờ bật + thời điểm hiện tại nằm
     * trong khung [start, end]). Đây là nguồn chân lý duy nhất cho cả middleware chặn user
     * lẫn các thao tác nguy hiểm chỉ được phép khi đang bảo trì (vd phục hồi dữ liệu).
     */
    public static function isMaintenanceActive(): bool
    {
        if (! (bool) self::get('maintenance_mode', false)) {
            return false;
        }

        $start = self::get('maintenance_start');
        $end = self::get('maintenance_end');
        if (! $start || ! $end) {
            return false;
        }

        try {
            return now()->between(\Carbon\Carbon::parse($start), \Carbon\Carbon::parse($end));
        } catch (\Throwable $e) {
            return false;
        }
    }
}
