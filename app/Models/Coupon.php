<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    use HasFactory;

    // Loại giảm giá.
    public const TYPE_PERCENT = 'PERCENT';
    public const TYPE_FIXED = 'FIXED';

    public const UPDATED_AT = null; // Chỉ có created_at.

    protected $table = 'coupons';

    protected $fillable = [
        'code', // Mã nhập vào.
        'applicable_plan_id', // null = mọi gói; có ID = gói cụ thể.
        'type', // PERCENT | FIXED.
        'value', // Giá trị giảm.
        'usage_limit', // Tổng số lần dùng (null = vô hạn).
        'used_count', // Đã dùng bao nhiêu lần.
        'valid_from', // Bắt đầu có hiệu lực.
        'valid_until', // Ngày hết hạn.
        'is_active', // Công tắc bật/tắt mã.
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'applicable_plan_id');
    }

    /**
     * Mã còn hiệu lực để áp dụng hay không.
     */
    public function isUsable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();
        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }
        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }
}
