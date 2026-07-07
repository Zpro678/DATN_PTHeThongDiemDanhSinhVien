<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GpsVerification extends Model
{
    use HasFactory;

    protected $table = 'gps_verifications';

    protected $fillable = [
        'session_id',    // FK -> class_sessions: phiên điểm danh mà vé GPS này thuộc về
        'member_id',     // FK -> class_members: thành viên (SV) được cấp vé để check-in
        'token',         // Mã vé một lần (64 ký tự, unique) gắn vào link/QR để mở form điểm danh
        'check_token',   // Mã xác nhận bước 2 (unique) cấp sau khi qua kiểm tra, chống dùng lại/giả mạo
        'ip_address',    // IP của thiết bị lúc điểm danh, dùng đối chiếu và phát hiện gian lận
        'lat',           // Vĩ độ toạ độ GPS mà thiết bị gửi lên
        'lng',           // Kinh độ toạ độ GPS mà thiết bị gửi lên
        'accuracy',      // Sai số định vị (mét) do trình duyệt báo; càng nhỏ càng đáng tin
        'fraud_score',   // Điểm nghi ngờ gian lận (số nguyên) do hệ thống tính toán
        'fraud_reasons', // Chi tiết lý do bị nghi gian lận (ví dụ: sai vị trí, trùng IP...)
        'is_used',       // Đã dùng vé này để điểm danh hay chưa (true = không cho dùng lại)
        'expires_at',    // Thời điểm vé hết hạn; quá hạn thì không thể điểm danh bằng token này
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
            'accuracy' => 'float',
            'fraud_score' => 'integer',
            'is_used' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class, 'session_id');
    }

    public function classMember(): BelongsTo
    {
        return $this->belongsTo(ClassMember::class, 'member_id');
    }
}
