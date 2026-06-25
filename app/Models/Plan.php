<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'plans';

    public $timestamps = false;

    protected $fillable = [
        'code', // Mã gói dịch vụ duy nhất.
        'name', // Tên gói dịch vụ.
        'description', // Mô tả gói dịch vụ.
        'price', // Giá gói dịch vụ.
        'duration_days', // Thời hạn gói (ngày).
        'max_classes', // Giới hạn số lớp học được tạo.
        'max_students_per_class', // Giới hạn số SV/lớp.
        'max_gps_radius', // Giới hạn bán kính GPS (m).
        'can_export_excel', // Quyền xuất báo cáo Excel.
        'api_access', // Quyền truy cập API.
        'support_level', // Mức độ hỗ trợ.
        'is_active', // Trạng thái hoạt động.
        'features', // JSON danh sách tính năng.
        'created_at', // Thời điểm tạo gói dịch vụ.
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2', // Ép kiểu giá gói.
            'duration_days' => 'integer',
            'max_classes' => 'integer', // Ép kiểu giới hạn số lớp.
            'max_students_per_class' => 'integer',
            'max_gps_radius' => 'integer',
            'can_export_excel' => 'boolean', // Ép kiểu quyền xuất Excel.
            'api_access' => 'boolean',
            'is_active' => 'boolean',
            'features' => 'array',
            'created_at' => 'datetime', // Ép kiểu thời điểm tạo.
            'updated_at' => 'datetime',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
