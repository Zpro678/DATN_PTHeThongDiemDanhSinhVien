<?php

namespace App\Models;

use Database\Factories\ClassSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassSession extends Model
{
    /** @use HasFactory<ClassSessionFactory> */
    use HasFactory, SoftDeletes;

    // DATN NOTE: Earlier sections and index notes mention `attendance_sessions`,
    // while the data dictionary defines `class_sessions`. The implementation follows the dictionary.
    protected $table = 'class_sessions';

    protected $fillable = [
        'tenant_id',
        'class_id',
        'name',
        'date',
        'start_time',
        'end_time',
        'qr_token',
        'token_expires_at',
        'gps_latitude',
        'gps_longitude',
        'gps_radius',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'token_expires_at' => 'datetime',
            'gps_latitude' => 'decimal:8',
            'gps_longitude' => 'decimal:8',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function scans(): HasMany
    {
        return $this->hasMany(CheckInScan::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }
}
