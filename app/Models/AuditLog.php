<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id', // ID người thực hiện hành động.
        'class_id', // ID lớp học liên quan đến hành động.
        'action', // Loại hành động được ghi log.
        'table_name', // Tên bảng vật lý bị tác động.
        'row_id', // ID bản ghi bị thay đổi.
        'old_values', // Dữ liệu cũ trước khi thay đổi.
        'new_values', // Dữ liệu mới hoặc payload chi tiết của sự kiện.
        'ip_address', // IP của người thực hiện hành động.
        'user_agent', // Thông tin trình duyệt/thiết bị thực hiện hành động.
        'created_at', // Thời điểm ghi log bất biến.
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array', // Ép kiểu dữ liệu cũ dạng JSON.
            'new_values' => 'array', // Ép kiểu dữ liệu mới dạng JSON.
            'created_at' => 'datetime', // Ép kiểu thời điểm ghi log.
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }
}
