<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'is_admin', // Lọc nhanh quyền quản trị tối cao.
        'google_id', // ID Google phục vụ đăng nhập OAuth.
        'code', // Mã số cá nhân tùy chọn.
        'name', // Họ và tên.
        'email', // Email đăng nhập duy nhất.
        'password', // Mật khẩu đã hash.
        'avatar', // URL ảnh đại diện.
        'status', // Trạng thái tài khoản active/blocked.
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password', // Ẩn mật khẩu khi serialize.
        'remember_token', // Token ghi nhớ đăng nhập.
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime', // Thời gian xác thực email.
            'is_admin' => 'boolean', // Ép kiểu quyền quản trị.
            'password' => 'hashed', // Tự động hash mật khẩu.
        ];
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            // Check if avatar is an external URL (like Google's)
            if (str_starts_with($this->avatar, 'http')) {
                return $this->avatar;
            }
            return asset('storage/' . $this->avatar);
        }

        // Return a generated avatar with the first letter of the name if no avatar exists
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=FFFFFF&background=4285F4';
    }

    public function ownedClasses(): HasMany
    {
        return $this->hasMany(CourseClass::class, 'owner_user_id');
    }

    public function classMemberships(): HasMany
    {
        return $this->hasMany(ClassMember::class);
    }

    public function joinedClasses(): BelongsToMany
    {
        return $this->belongsToMany(CourseClass::class, 'class_members', 'user_id', 'class_id')
            ->withPivot(['id', 'student_code', 'full_name', 'status', 'deleted_at'])
            ->wherePivotNull('deleted_at')
            ->withTimestamps();
    }

    public function classJoinRequests(): HasMany
    {
        return $this->hasMany(ClassJoinRequest::class);
    }

    public function createdClassSessions(): HasMany
    {
        return $this->hasMany(ClassSession::class, 'created_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function checkInScans(): HasMany
    {
        return $this->hasMany(CheckInScan::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Gói thuê bao đang còn hiệu lực (active và chưa hết hạn), mới nhất trước.
     */
    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('end_date')->orWhere('end_date', '>=', now()))
            ->latest('start_date');
    }

    /**
     * Gói dịch vụ hiện tại của người dùng; mặc định là gói FREE nếu chưa đăng ký.
     */
    public function currentPlan(): ?Plan
    {
        return $this->activeSubscription?->plan
            ?? Plan::where('code', 'FREE')->first();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    public function reviewedLeaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'reviewed_by');
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable')->latest();
    }
}
