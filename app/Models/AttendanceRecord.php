<?php

namespace App\Models;

use Database\Factories\AttendanceRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceRecord extends Model
{
    /** @use HasFactory<AttendanceRecordFactory> */
    use HasFactory, SoftDeletes;

    // DATN NOTE: The index section says `(session_id, student_id)`, but the
    // dictionary columns are `class_session_id` and `class_member_id`.
    protected $fillable = [
        'tenant_id',
        'class_session_id',
        'class_member_id',
        'status',
        'method_id',
        'is_verified',
        'check_in_time',
        'ip_address',
        'device_info',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'check_in_time' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function classMember(): BelongsTo
    {
        return $this->belongsTo(ClassMember::class);
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(AttendanceMethod::class, 'method_id');
    }
}
