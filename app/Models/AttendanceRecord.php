<?php

namespace App\Models;

use App\Traits\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceRecord extends Model
{
    use HasFactory, Auditable, SoftDeletes;

    protected $table = 'attendance_records';

    protected $fillable = [
        'class_session_id', // ID của phiên điểm danh.
        'class_member_id', // ID thành viên lớp được điểm danh.
        'status', // Trạng thái điểm danh pending/present/late/absent/excused/invalid.
        'is_account', // True nếu sinh viên có tài khoản, false nếu điền form.
        'check_in_time', // Thời điểm ghi nhận có mặt.
        'ip_address', // IP mạng của thiết bị điểm danh.
        'device_fingerprint', // Mã định danh thiết bị đã băm.
        'distance_meters', // Khoảng cách GPS tính bằng mét.
        'gps_accuracy_meters', // Độ chính xác GPS của thiết bị sinh viên.
        'gps_latitude_recorded', // Vĩ độ thực tế ghi nhận từ sinh viên.
        'gps_longitude_recorded', // Kinh độ thực tế ghi nhận từ sinh viên.
        'gps_fraud_flag', // Cờ ghi nhận gian lận ('low_accuracy', 'speed_anomaly', 'token_reuse').
        'note', // Ghi chú hoặc lý do liên quan đến bản ghi.
    ];

    protected function casts(): array
    {
        return [
            'is_account' => 'boolean', // Ép kiểu cờ có tài khoản.
            'check_in_time' => 'datetime', // Ép kiểu thời điểm check-in.
            'distance_meters' => 'decimal:2', // Ép kiểu khoảng cách GPS.
            'gps_accuracy_meters' => 'float',
            'gps_latitude_recorded' => 'float',
            'gps_longitude_recorded' => 'float',
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
