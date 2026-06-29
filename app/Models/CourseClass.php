<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseClass extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'classes';

    protected $fillable = [
        'owner_user_id', // ID của chủ lớp tạo lớp học.
        'code', // Mã lớp học duy nhất.
        'name', // Tên lớp học.
        'description', // Mô tả môn học.
        'late_threshold', // Ngưỡng thời gian trễ.
        'attendance_rules', // Cấu hình bảng điểm trừ chuyên cần.
        'subject_code', // Mã môn học.
        'semester', // Học kỳ.
        'require_approval', // Bật/tắt yêu cầu duyệt khi xin vào lớp.
        'status', // Trạng thái lớp active/archived.
        'total_sessions', // Tổng số buổi dự kiến của môn học.
        'gps_latitude', // Vĩ độ định vị GPS mặc định.
        'gps_longitude', // Kinh độ định vị GPS mặc định.
        'gps_radius', // Bán kính GPS mặc định.
    ];

    protected function casts(): array
    {
        return [
            'require_approval' => 'boolean', // Ép kiểu cờ yêu cầu duyệt.
            'attendance_rules' => 'array', // Ép kiểu mảng.
            'total_sessions' => 'integer', // Ép kiểu tổng số buổi dự kiến.
            'gps_latitude' => 'float',
            'gps_longitude' => 'float',
            'gps_radius' => 'integer',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ClassMember::class, 'class_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_members', 'class_id', 'user_id')
            ->withPivot(['id', 'student_code', 'full_name', 'status', 'deleted_at'])
            ->wherePivotNull('deleted_at')
            ->withTimestamps();
    }

    public function joinRequests(): HasMany
    {
        return $this->hasMany(ClassJoinRequest::class, 'class_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ClassSession::class, 'class_id');
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(ClassMeeting::class, 'class_id');
    }

    public function attendanceSummaries(): HasMany
    {
        return $this->hasMany(AttendanceSummary::class, 'class_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'class_id');
    }

    /**
     * Sinh mã lớp duy nhất dựa trên subject_code và semester.
     * Tái dùng ở CreateClass và ClassSettings.
     *
     * @param  string  $subjectCode  Mã môn học (có thể rỗng)
     * @param  string  $semester  Học kỳ (có thể rỗng)
     * @param  int|null  $excludeId  ID lớp cần loại trừ khi kiểm tra unique (dùng khi đổi mã)
     * @return string Mã lớp duy nhất đã được kiểm tra
     */
    public static function generateUniqueCode(string $subjectCode = '', string $semester = '', ?int $excludeId = null): string
    {
        $subPart = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $subjectCode), 0, 3));
        $prefix = ($subPart ?: 'CLS');

        $attempts = 0;
        do {
            $suffix = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $code = $prefix.$suffix;
            $query = self::withTrashed()->where('code', $code);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            $exists = $query->exists();
            $attempts++;
        } while ($exists && $attempts < 20);

        return $code;
    }

    /**
     * Lấy cấu hình điểm trừ chuyên cần.
     * Trả về giá trị mặc định nếu lớp chưa cấu hình.
     */
    public function getAttendanceRules(): array
    {
        $defaultRules = [
            'present' => 0.0,
            'late' => 0.5,
            'partial' => 0.5,
            'early_leave' => 1.0,
            'absent' => 1.0,
            'excused' => 0.0,
        ];

        return array_merge($defaultRules, $this->attendance_rules ?? []);
    }
}
