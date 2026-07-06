<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckInScan extends Model
{
    use HasFactory;

    protected $table = 'check_in_scans';

    public $timestamps = false;

    protected $fillable = [
        'class_session_id', // ID phiên điểm danh được quét.
        'user_id', // ID tài khoản nếu người quét đã đăng nhập.
        'student_code_attempt', // MSSV nhập tay nếu là khách.
        'scan_type', // Loại quét qr/gps/link.
        'payload_signature', // Chữ ký HMAC chống replay attack.
        'is_valid', // Cờ kết quả quét hợp lệ hay thất bại.
        'fail_reason', // Lý do lỗi khi quét không hợp lệ.
        'ip_address', // IP thiết bị thực hiện quét.
        'device_fingerprint', // Mã định danh thiết bị (md5 IP+UA — tín hiệu yếu, phụ).
        'device_id', // Mã định danh trình duyệt bền (client sinh) — dùng cho lịch sử thiết bị.
        'scanned_at', // Thời điểm quét.
    ];

    protected function casts(): array
    {
        return [
            'is_valid' => 'boolean', // Ép kiểu kết quả quét.
            'scanned_at' => 'datetime', // Ép kiểu thời điểm quét.
        ];
    }

    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
