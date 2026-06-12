<?php

namespace App\Models;

use Database\Factories\ClassJoinRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassJoinRequest extends Model
{
    /** @use HasFactory<ClassJoinRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'class_id',
        'user_id',
        'student_code',
        'full_name',
        'status',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
