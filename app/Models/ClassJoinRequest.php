<?php

namespace App\Models;

use App\Traits\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassJoinRequest extends Model
{
    use HasFactory, Auditable;

    // Trạng thái yêu cầu vào lớp.
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_REJECTED = 'REJECTED';

    protected $table = 'class_join_requests';

    protected $fillable = [
        'class_id', // ID của lớp xin tham gia.
        'user_id', // ID tài khoản gửi yêu cầu.
        'status', // Trạng thái yêu cầu PENDING/APPROVED/REJECTED.
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
