<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseClass extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'classes';

    protected $fillable = [
        'owner_user_id', // ID của chủ lớp tạo lớp học.
        'join_key', // Mã lớp (SV nhập để vào lớp).
        'name', // Tên lớp học.
        'description', // Mô tả môn học.
        'late_threshold', // Ngưỡng phút trễ tối đa để tính đi muộn.
        'deduct_excused_absence', // Có trừ chuyên cần khi vắng có phép.
        'require_approval', // Bật/tắt yêu cầu duyệt khi xin vào lớp.
        'status', // Trạng thái lớp active/archived.
        'total_sessions', // Tổng số buổi dự kiến của môn học.
    ];

    protected function casts(): array
    {
        return [
            'require_approval' => 'boolean', // Ép kiểu cờ yêu cầu duyệt.
            'deduct_excused_absence' => 'boolean', // Ép kiểu cờ trừ chuyên cần khi vắng có phép.
            'late_threshold' => 'integer', // Ép kiểu ngưỡng phút trễ.
            'total_sessions' => 'integer', // Ép kiểu tổng số buổi dự kiến.
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
            ->withPivot(['id', 'status', 'status_changed_at', 'deleted_at'])
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
     * Sinh mã lớp (join_key) duy nhất toàn cục.
     *
     * @param  string  $prefixHint  Gợi ý tiền tố (vd mã môn); mặc định CLS.
     * @param  int|null  $excludeId  ID lớp cần loại trừ khi kiểm tra unique (dùng khi đổi mã).
     * @return string Mã lớp duy nhất đã được kiểm tra.
     */
    public static function generateUniqueCode(string $prefixHint = '', ?string $excludeId = null): string
    {
        $subPart = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $prefixHint), 0, 3));
        $prefix = ($subPart ?: 'CLS');

        $attempts = 0;
        do {
            $suffix = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $code = $prefix.$suffix;
            $query = self::withTrashed()->where('join_key', $code);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            $exists = $query->exists();
            $attempts++;
        } while ($exists && $attempts < 20);

        return $code;
    }

    /**
     * Bảng điểm trừ chuyên cần của lớp.
     *
     * Cấu hình chi tiết theo từng trạng thái đã được lược bỏ khỏi bảng classes;
     * lớp chỉ còn cờ deduct_excused_absence. Hàm này trả về bảng điểm trừ mặc định
     * (đã điều chỉnh theo cờ vắng có phép) để các phần tính chuyên cần dùng chung.
     *
     * @return array<string, float>
     */
    public function getAttendanceRules(): array
    {
        return [
            'present' => 0.0,
            'late' => 0.5,
            'absent' => 1.0,
            'excused' => $this->deduct_excused_absence ? 1.0 : 0.0,
        ];
    }
}
