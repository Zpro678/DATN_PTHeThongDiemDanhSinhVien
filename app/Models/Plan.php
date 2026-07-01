<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, SoftDeletes;

    // Hạng gói dịch vụ.
    public const TIER_FREE = 'FREE';
    public const TIER_PRO = 'PRO';
    public const TIER_ENTERPRISE = 'ENTERPRISE';

    protected $table = 'plans';

    protected $fillable = [
        'plan_tier', // Hạng gói dịch vụ (FREE/PRO/ENTERPRISE).
        'name', // Tên gói dịch vụ.
        'description', // Mô tả gói dịch vụ.
        'price', // Giá gói dịch vụ.
        'duration_days', // Thời hạn gói (ngày), 0 = vĩnh viễn.
        'is_active', // Trạng thái hoạt động.
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2', // Ép kiểu giá gói.
            'duration_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Cấu hình giới hạn 1-1 của gói.
     */
    public function config(): HasOne
    {
        return $this->hasOne(PlanConfig::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class, 'applicable_plan_id');
    }

    // --- Proxy tiện ích sang plan_configs để code cũ đọc $plan->max_classes vẫn chạy ---

    protected function maxClasses(): Attribute
    {
        return Attribute::get(fn () => $this->config?->max_classes);
    }

    protected function maxStudentsPerClass(): Attribute
    {
        return Attribute::get(fn () => $this->config?->max_students_per_class);
    }

    protected function maxGpsRadius(): Attribute
    {
        return Attribute::get(fn () => $this->config?->max_gps_radius);
    }

    protected function canExportExcel(): Attribute
    {
        return Attribute::get(fn () => (bool) $this->config?->can_export_excel);
    }
}
