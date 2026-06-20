<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'class_sessions';

    protected $fillable = [
        'class_id', // ID của lớp học.
        'created_by', // ID chủ lớp tạo phiên điểm danh.
        'name', // Tên buổi học.
        'date', // Ngày diễn ra buổi học.
        'start_time', // Thời gian bắt đầu.
        'end_time', // Thời gian kết thúc.
        'qr_token', // Chuỗi token mã QR.
        'token_expires_at', // Thời điểm hết hạn của mã QR.
        'gps_latitude', // Vĩ độ vị trí điểm danh.
        'gps_longitude', // Kinh độ vị trí điểm danh.
        'gps_radius', // Bán kính GPS cho phép.
        'status', // Trạng thái phiên pending/active/closed.
        'lesson_count', // Tổng số tiết học của buổi học này.
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date', // Ép kiểu ngày diễn ra.
            'token_expires_at' => 'datetime', // Ép kiểu hạn sống mã QR.
            'gps_latitude' => 'decimal:8', // Ép kiểu vĩ độ GPS.
            'gps_longitude' => 'decimal:8', // Ép kiểu kinh độ GPS.
            'gps_radius' => 'integer', // Ép kiểu bán kính GPS.
            'lesson_count' => 'integer', // Ép kiểu tổng số tiết của buổi học.
        ];
    }

    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function checkInScans(): HasMany
    {
        return $this->hasMany(CheckInScan::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }
}
