<?php

namespace App\Models;

use Database\Factories\CheckInScanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckInScan extends Model
{
    /** @use HasFactory<CheckInScanFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'class_session_id',
        'user_id',
        'student_code_attempt',
        'scan_type',
        'payload_signature',
        'is_valid',
        'fail_reason',
        'ip_address',
        'device_info',
        'scanned_at',
    ];

    protected function casts(): array
    {
        return [
            'is_valid' => 'boolean',
            'scanned_at' => 'datetime',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
