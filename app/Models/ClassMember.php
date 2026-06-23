<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'class_members';

    protected $fillable = [
        'class_id', // ID của lớp học.
        'student_code', // MSSV thực tế do chủ lớp import.
        'full_name', // Họ tên sinh viên trong lớp.
        'email', // Email sinh viên
        'user_id', // ID tài khoản liên kết khi sinh viên đăng nhập.
        'status', // Trạng thái thành viên active/dropped.
    ];

    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function attendanceSummary(): HasOne
    {
        return $this->hasOne(AttendanceSummary::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }
}
