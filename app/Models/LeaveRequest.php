<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'class_member_id',
        'class_session_id',
        'reason',
        'proof_image',
        'status',
        'rejected_reason',
        'reviewed_by',
        'reviewed_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function classMember(): BelongsTo
    {
        return $this->belongsTo(ClassMember::class);
    }

    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
