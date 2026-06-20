<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'plans';

    public $timestamps = false;

    protected $fillable = [
        'code', // Mã gói dịch vụ duy nhất.
        'name', // Tên gói dịch vụ.
        'price', // Giá gói dịch vụ.
        'max_classes', // Giới hạn số lớp học được tạo.
        'can_export_excel', // Quyền xuất báo cáo Excel.
        'created_at', // Thời điểm tạo gói dịch vụ.
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2', // Ép kiểu giá gói.
            'max_classes' => 'integer', // Ép kiểu giới hạn số lớp.
            'can_export_excel' => 'boolean', // Ép kiểu quyền xuất Excel.
            'created_at' => 'datetime', // Ép kiểu thời điểm tạo.
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
