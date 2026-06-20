<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'attendance_records';

    protected $fillable = [
        'class_session_id', // ID của phiên điểm danh.
        'class_member_id', // ID thành viên lớp được điểm danh.
        'status', // Trạng thái điểm danh pending/present/late/absent/excused/invalid.
        'is_verified', // True nếu sinh viên có tài khoản, false nếu điền form.
        'check_in_time', // Thời điểm ghi nhận có mặt.
        'ip_address', // IP mạng của thiết bị điểm danh.
        'device_fingerprint', // Mã định danh thiết bị đã băm.
        'distance_meters', // Khoảng cách GPS tính bằng mét.
        'note', // Ghi chú hoặc lý do liên quan đến bản ghi.
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean', // Ép kiểu trạng thái xác thực.
            'check_in_time' => 'datetime', // Ép kiểu thời điểm check-in.
            'distance_meters' => 'decimal:2', // Ép kiểu khoảng cách GPS.
        ];
    }

    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function classMember(): BelongsTo
    {
        return $this->belongsTo(ClassMember::class);
    }
}
