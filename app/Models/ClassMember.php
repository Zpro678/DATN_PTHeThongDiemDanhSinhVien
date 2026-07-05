<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassMember extends Model
{
    use HasFactory, SoftDeletes;

    // Trạng thái thành viên: đang học / tự thoát / bị đá khỏi lớp.
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_LEFT = 'LEFT';
    public const STATUS_REMOVED = 'REMOVED';

    protected $table = 'class_members';

    protected $fillable = [
        'class_id', // ID của lớp học.
        'user_id', // ID tài khoản liên kết khi sinh viên đăng nhập (late binding).
        'status', // Trạng thái thành viên ACTIVE/LEFT/REMOVED.
        'status_changed_at', // Thời điểm bị đá/tự out.
    ];

    protected function casts(): array
    {
        return [
            'status_changed_at' => 'datetime', // Ép kiểu thời điểm đổi trạng thái.
        ];
    }

    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Hồ sơ danh tính (MSSV/tên/email) — đặc biệt cho SV nhập form/import.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(ClassMemberProfile::class);
    }

    /**
     * Tạo/cập nhật hồ sơ danh tính của thành viên.
     *
     * @param  array<string, mixed>  $data  student_code|full_name|email.
     */
    public function syncProfile(array $data): ClassMemberProfile
    {
        return $this->profile()->updateOrCreate([], $data);
    }

    // --- Accessor danh tính: đọc từ profile, fallback sang tài khoản liên kết ---

    protected function studentCode(): Attribute
    {
        return Attribute::get(fn () => $this->profile?->student_code);
    }

    protected function fullName(): Attribute
    {
        return Attribute::get(function () {
            $profileName = $this->profile?->full_name;
            $userName = $this->user?->name;
            
            if ($profileName) {
                return $profileName;
            }
            return $userName;
        });
    }

    protected function email(): Attribute
    {
        return Attribute::get(fn () => $this->profile?->email ?? $this->user?->email);
    }

    /**
     * Tên hiển thị ưu tiên tài khoản, sau đó hồ sơ khai báo.
     */
    protected function displayName(): Attribute
    {
        return Attribute::get(function () {
            $profileName = $this->profile?->full_name;
            $userName = $this->user?->name;
            
            if ($profileName) {
                return $profileName;
            }
            if ($userName && !filter_var($userName, FILTER_VALIDATE_EMAIL)) {
                return $userName;
            }
            return $userName ?? '—';
        });
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
