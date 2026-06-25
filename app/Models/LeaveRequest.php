<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'leave_requests';

    public $timestamps = false;

    protected $fillable = [
        'class_member_id', // ID thành viên lớp xin nghỉ.
        'class_session_id', // ID buổi học xin nghỉ.
        'reason', // Lý do vắng mặt.
        'proof_image', // Hình ảnh minh chứng.
        'status', // Trạng thái đơn pending/approved/rejected.
        'rejected_reason', // Lý do từ chối đơn.
        'reviewed_by', // ID chủ lớp hoặc người duyệt.
        'reviewed_at', // Thời điểm duyệt đơn.
        'created_at', // Thời điểm tạo đơn xin nghỉ.
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime', // Ép kiểu thời điểm duyệt.
            'created_at' => 'datetime', // Ép kiểu thời điểm tạo.
        ];
    }

    protected function proofImage(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (empty($value)) {
                    return [];
                }
                $decoded = json_decode($value, true);

                return (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [$value];
            },
            set: fn ($value) => json_encode(is_array($value) ? array_values($value) : [$value])
        );
    }

    public function classMember(): BelongsTo
    {
        return $this->belongsTo(ClassMember::class);
    }

    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
