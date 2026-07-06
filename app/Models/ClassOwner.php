<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Một dòng "đồng chủ lớp": gắn một user vào quản lý một lớp bên cạnh chủ chính.
 */
class ClassOwner extends Model
{
    use HasFactory;

    protected $table = 'class_owners';

    protected $fillable = [
        'class_id',
        'user_id',
        'role',
        'invited_by',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
        ];
    }

    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
