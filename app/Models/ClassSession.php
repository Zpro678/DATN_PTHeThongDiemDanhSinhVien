<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'class_id',
        'created_by',
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
            'gps_radius' => 'integer',
        ];
    }

    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function checkInScans(): HasMany
    {
        return $this->hasMany(CheckInScan::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }
}
