<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckInScan extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'class_session_id',
        'user_id',
        'student_code_attempt',
        'scan_type',
        'payload_signature',
        'is_valid',
        'fail_reason',
        'ip_address',
        'device_fingerprint',
        'scanned_at',
    ];

    protected function casts(): array
    {
        return [
            'is_valid' => 'boolean',
            'scanned_at' => 'datetime',
        ];
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
