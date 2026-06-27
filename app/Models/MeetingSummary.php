<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingSummary extends Model
{
    use HasFactory;

    protected $table = 'meeting_summaries';

    protected $fillable = [
        'meeting_id', // ID buổi học được tổng kết.
        'class_member_id', // ID thành viên lớp.
        'status', // Trạng thái tổng kết: present/late/absent/excused.
        'deduction', // Điểm trừ chuyên cần: 0 / 0.5 / 1.
        'auto_status', // Trạng thái hệ thống tự tính.
        'is_overridden', // Giảng viên đã chỉnh sửa thủ công hay chưa.
        'note', // Ghi chú tổng kết.
    ];

    protected function casts(): array
    {
        return [
            'deduction' => 'decimal:1',
            'is_overridden' => 'boolean',
        ];
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(ClassMeeting::class, 'meeting_id');
    }

    public function classMember(): BelongsTo
    {
        return $this->belongsTo(ClassMember::class);
    }
}
