<?php

namespace App\Models;

use Database\Factories\CourseClassFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseClass extends Model
{
    /** @use HasFactory<CourseClassFactory> */
    use HasFactory, SoftDeletes;

    // DATN NOTE: Section 2.1 names this model AttendanceClass, but the data dictionary
    // uses table `classes`. The PHP model is CourseClass to avoid the reserved word "class".
    protected $table = 'classes';

    protected $fillable = [
        'tenant_id',
        'owner_id',
        'code',
        'name',
        'description',
        'subject_code',
        'semester',
        'require_approval',
        'status',
        'total_sessions',
        'lessons_per_session',
    ];

    protected function casts(): array
    {
        return [
            'require_approval' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ClassMember::class, 'class_id');
    }

    public function joinRequests(): HasMany
    {
        return $this->hasMany(ClassJoinRequest::class, 'class_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ClassSession::class, 'class_id');
    }

    public function attendanceRules(): HasMany
    {
        return $this->hasMany(AttendanceRule::class, 'class_id');
    }

    public function summaries(): HasMany
    {
        return $this->hasMany(AttendanceSummary::class, 'class_id');
    }
}
