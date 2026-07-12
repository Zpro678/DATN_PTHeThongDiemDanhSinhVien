<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Một email thông báo import đang chờ gửi (mẫu Outbox).
 *
 * Ghi lúc import (bulk insert, rất nhanh) và được rút dần bởi lệnh
 * import:flush-notifications để không bắn hàng nghìn mail cùng lúc.
 */
class PendingImportNotification extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    /** Số lần thử gửi tối đa trước khi đánh dấu failed. */
    public const MAX_ATTEMPTS = 3;

    protected $fillable = [
        'class_id',
        'email',
        'full_name',
        'class_name',
        'join_key',
        'status',
        'attempts',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'sent_at' => 'datetime',
        ];
    }
}
