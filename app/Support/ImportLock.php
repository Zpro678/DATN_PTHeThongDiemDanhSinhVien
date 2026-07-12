<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Khoá "mỗi tài khoản chỉ 1 lượt import đang chạy".
 *
 * Chống việc một người bấm import nhiều lần liên tiếp làm ngập hàng đợi. Dùng
 * Cache::add (atomic SETNX) làm khoá; giải phóng ở batch finally() khi import
 * xong. TTL là lưới an toàn: nếu worker chết giữa chừng, khoá tự hết hạn.
 */
class ImportLock
{
    /** Thời hạn tối đa giữ khoá (phút) — lưới an toàn nếu finally() không chạy. */
    private const TTL_MINUTES = 60;

    public static function key(int $userId): string
    {
        return 'student-import:user:'.$userId;
    }

    /**
     * Thử chiếm khoá. Trả về false nếu tài khoản đang có một lượt import chạy dở.
     */
    public static function acquire(int $userId): bool
    {
        return Cache::add(self::key($userId), true, now()->addMinutes(self::TTL_MINUTES));
    }

    public static function release(int $userId): void
    {
        Cache::forget(self::key($userId));
    }
}
