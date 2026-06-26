<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GpsVerification extends Model
{
    use HasFactory;

    protected $table = 'gps_verifications';

    protected $fillable = [
        'session_id',
        'member_id',
        'token',
        'check_token',
        'ip_address',
        'lat',
        'lng',
        'accuracy',
        'is_used',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
            'accuracy' => 'float',
            'is_used' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class, 'session_id');
    }

    public function classMember(): BelongsTo
    {
        return $this->belongsTo(ClassMember::class, 'member_id');
    }
}
