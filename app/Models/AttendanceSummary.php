<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSummary extends Model
{
    use HasFactory;

    protected $table = 'attendance_summaries';

    public $timestamps = false;

    protected $fillable = [
        'class_id', // ID của lớp học.
        'class_member_id', // ID thành viên lớp được tổng hợp chuyên cần.
        'total_present', // Tổng số tiết có mặt.
        'total_late', // Tổng số tiết đi trễ.
        'total_absent', // Tổng số tiết vắng.
        'total_excused', // Tổng số tiết vắng có phép.
        'is_banned_from_exam', // Cờ bị cấm thi do vắng quá số tiết quy định.
        'updated_at', // Lần cập nhật tổng hợp gần nhất.
    ];

    protected function casts(): array
    {
        return [
            'total_present' => 'integer', // Ép kiểu tổng số tiết có mặt.
            'total_late' => 'integer', // Ép kiểu tổng số tiết đi trễ.
            'total_absent' => 'integer', // Ép kiểu tổng số tiết vắng.
            'total_excused' => 'integer', // Ép kiểu tổng số tiết vắng có phép.
            'is_banned_from_exam' => 'boolean', // Ép kiểu trạng thái cấm thi.
            'updated_at' => 'datetime', // Ép kiểu thời điểm cập nhật.
        ];
    }

    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }

    public function classMember(): BelongsTo
    {
        return $this->belongsTo(ClassMember::class);
    }
}
