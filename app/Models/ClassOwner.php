<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Một dòng "đồng chủ lớp": gắn một user vào quản lý một lớp bên cạnh chủ chính.
 */
class ClassOwner extends Model
{
    use HasFactory;

    protected $table = 'class_owners';

    protected $fillable = [
        'class_id',    // FK -> classes: lớp mà người này được mời làm đồng chủ
        'user_id',     // FK -> users: tài khoản được cấp quyền đồng chủ lớp
        'role',        // Vai trò quản lý trong lớp (ví dụ: co_owner)
        'invited_by',  // FK -> users: chủ chính (hoặc đồng chủ) đã gửi lời mời
        'accepted_at', // Thời điểm chấp nhận lời mời; null = đang chờ chấp nhận
    ];

    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
        ];
    }

    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
