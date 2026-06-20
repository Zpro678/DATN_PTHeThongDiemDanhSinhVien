<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassJoinRequest extends Model
{
    use HasFactory;

    protected $table = 'class_join_requests';

    protected $fillable = [
        'class_id', // ID của lớp xin tham gia.
        'user_id', // ID tài khoản gửi yêu cầu.
        'student_code', // MSSV sinh viên khai báo.
        'full_name', // Họ tên sinh viên khai báo.
        'status', // Trạng thái yêu cầu pending/approved/rejected.
    ];

    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
