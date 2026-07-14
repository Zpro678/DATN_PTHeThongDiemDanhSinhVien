<?php

namespace App\Models;

use App\Traits\Auditable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseClass extends Model
{
    use HasFactory, Auditable, HasUuids, SoftDeletes;

    protected $table = 'classes';

    protected $fillable = [
        'owner_user_id', // ID của chủ lớp tạo lớp học.
        'join_key', // Mã tham gia lớp — mã ngẫu nhiên để học viên nhập vào lớp.
        'class_code', // Mã lớp — do giảng viên tự đặt theo trường/khoa (VD: CS101).
        'name', // Tên lớp học.
        'description', // Mô tả môn học.
        'late_threshold', // Ngưỡng phút trễ tối đa để tính đi muộn.
        'deduct_excused_absence', // Có trừ chuyên cần khi vắng có phép (mirror của deduct_excused > 0).
        'deduct_late', // Điểm trừ khi đi muộn (mặc định 0.5).
        'deduct_absent', // Điểm trừ khi vắng (mặc định 1.0).
        'deduct_excused', // Điểm trừ khi vắng có phép (mặc định 0.0).
        'require_approval', // Bật/tắt yêu cầu duyệt khi xin vào lớp.
        'status', // Trạng thái lớp active/archived.
        'total_sessions', // Tổng số buổi dự kiến của môn học.
    ];

    protected function casts(): array
    {
        return [
            'require_approval' => 'boolean', // Ép kiểu cờ yêu cầu duyệt.
            'deduct_excused_absence' => 'boolean', // Ép kiểu cờ trừ chuyên cần khi vắng có phép.
            'deduct_late' => 'float', // Điểm trừ đi muộn.
            'deduct_absent' => 'float', // Điểm trừ vắng.
            'deduct_excused' => 'float', // Điểm trừ vắng có phép.
            'late_threshold' => 'integer', // Ép kiểu ngưỡng phút trễ.
            'total_sessions' => 'integer', // Ép kiểu tổng số buổi dự kiến.
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /**
     * Các ĐỒNG CHỦ lớp (ngoài chủ chính owner_user_id) — cùng quyền quản lý nghiệp vụ dạy.
     */
    public function coOwners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_owners', 'class_id', 'user_id')
            ->withPivot(['role', 'accepted_at'])
            ->withTimestamps();
    }

    /**
     * Giới hạn truy vấn về những lớp mà $userId ĐƯỢC QUẢN LÝ = chủ chính HOẶC đồng chủ.
     * Thay cho câu where('owner_user_id', $userId) rải rác trước đây.
     */
    public function scopeManagedBy(Builder $query, int|string|null $userId): Builder
    {
        return $query->where(function (Builder $q) use ($userId) {
            $q->where('owner_user_id', $userId)
                ->orWhereHas('coOwners', fn (Builder $c) => $c->where('users.id', $userId));
        });
    }

    /** True nếu $userId được quản lý lớp này (chủ chính hoặc đồng chủ). */
    public function isManagedBy(int|string|null $userId): bool
    {
        if ($userId === null) {
            return false;
        }

        if ((string) $this->owner_user_id === (string) $userId) {
            return true;
        }

        return $this->coOwners()->where('users.id', $userId)->exists();
    }

    /** True nếu $userId là CHỦ CHÍNH (người tạo) — người duy nhất được làm thao tác xóa. */
    public function isPrimaryOwner(int|string|null $userId): bool
    {
        return $userId !== null && (string) $this->owner_user_id === (string) $userId;
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
     * Điểm trừ do GIẢNG VIÊN cấu hình theo lớp (cột deduct_late/absent/excused).
     * Nếu cột chưa có giá trị (lớp cũ / chưa cấu hình) thì dùng mặc định hợp lý.
     * "Có mặt" luôn = 0 (không lưu). Hàm này là NGUỒN DUY NHẤT cho mọi tính toán,
     * hiển thị % chuyên cần và file xuất — sửa ở đây là toàn bộ đồng bộ theo.
     *
     * @return array<string, float>
     */
    public function getAttendanceRules(): array
    {
        return [
            'present' => 0.0,
            'late' => $this->deduct_late ?? 0.5,
            'absent' => $this->deduct_absent ?? 1.0,
            'excused' => $this->deduct_excused ?? 0.0,
        ];
    }

    /**
     * Lấy danh sách tất cả những người quản lý lớp (bao gồm Chủ chính và Đồng chủ lớp đã chấp nhận lời mời).
     * Dùng chung cho việc gửi Notification.
     */
    public function getAllManagersAttribute()
    {
        $managers = collect();
        
        if ($this->owner) {
            $managers->push($this->owner);
        }

        $this->coOwners()->wherePivotNotNull('accepted_at')->get()->each(function ($coOwner) use ($managers) {
            $managers->push($coOwner);
        });

        return $managers->unique('id')->filter();
    }
}
