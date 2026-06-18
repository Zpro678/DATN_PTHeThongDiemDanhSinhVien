<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'class_session_id',
        'class_member_id',
        'status',
        'is_verified',
        'check_in_time',
        'ip_address',
        'device_fingerprint',
        'distance_meters',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'check_in_time' => 'datetime',
            'distance_meters' => 'decimal:2',
        ];
    }

    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function classMember(): BelongsTo
    {
        return $this->belongsTo(ClassMember::class);
    }
}
