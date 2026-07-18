<?php

namespace App\Models;

use App\Traits\Auditable;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    // Hạng gói dịch vụ.
    public const TIER_FREE = 'FREE';
    public const TIER_PRO = 'PRO';
    public const TIER_PREMIUM = 'PREMIUM';

    // Ngưỡng coi như "không giới hạn": admin nhập >= giá trị này (vd 9999) sẽ
    // hiển thị "Không giới hạn" thay vì con số thô cho người dùng.
    public const UNLIMITED_THRESHOLD = 9999;

    // Hạn mức mặc định khi gói CHƯA có bản ghi plan_configs. Xảy ra với gói FREE
    // ảo do SubscriptionService::planFor() dựng cho người dùng chưa đăng ký, và
    // cũng chặn dữ liệu lỗi (gói thật thiếu config) rơi về 0 => khoá sạch tính năng.
    public const DEFAULT_MAX_CLASSES = 2;
    public const DEFAULT_MAX_STUDENTS_PER_CLASS = 50;

    /**
     * Nhãn hiển thị cho một giới hạn số (null hoặc >= ngưỡng => "Không giới hạn").
     */
    public static function limitLabel(?int $value, string $unlimited, string $prefix, string $suffix): string
    {
        if ($value === null || $value >= self::UNLIMITED_THRESHOLD) {
            return $unlimited;
        }

        return $prefix . $value . $suffix;
    }

    protected $table = 'plans';

    protected $fillable = [
        'plan_tier', // Hạng gói dịch vụ (FREE/PRO/PREMIUM).
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
        return Attribute::get(fn () => $this->config?->max_classes ?? self::DEFAULT_MAX_CLASSES);
    }

    protected function maxStudentsPerClass(): Attribute
    {
        return Attribute::get(
            fn () => $this->config?->max_students_per_class ?? self::DEFAULT_MAX_STUDENTS_PER_CLASS
        );
    }


    protected function canExportExcel(): Attribute
    {
        return Attribute::get(fn () => (bool) $this->config?->can_export_excel);
    }

    protected function features(): Attribute
    {
        return Attribute::get(function () {
            // Giới hạn do admin cấu hình ở plan_configs — hiển thị đầu danh sách
            // để người dùng thấy ngay số lớp & số học viên/lớp mỗi gói cho phép.
            $list = [
                self::limitLabel($this->max_classes, 'Không giới hạn số lớp học', 'Tối đa ', ' lớp học'),
                self::limitLabel($this->max_students_per_class, 'Không giới hạn học viên / lớp', 'Tối đa ', ' học viên / lớp'),
                'Điểm danh bằng QR Code / Link',
                'Quản lý chuyên cần & cảnh báo',
                'Xác thực vị trí GPS',
            ];

            if ($this->config?->can_export_excel) {
                $list[] = 'Xuất báo cáo ra Excel';
            }

            return $list;
        });
    }
}
