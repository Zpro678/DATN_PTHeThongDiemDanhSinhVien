<?php

namespace App\Models;

use Database\Factories\AttendanceSummaryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSummary extends Model
{
    /** @use HasFactory<AttendanceSummaryFactory> */
    use HasFactory;

    // DATN NOTE: The document calls this a materialized view. It is implemented
    // as a normal table so Laravel queues/workers can update it safely.
    public const CREATED_AT = null;

    protected $fillable = [
        'tenant_id',
        'class_id',
        'class_member_id',
        'total_present',
        'total_late',
        'total_absent',
        'total_excused',
        'is_banned_from_exam',
    ];

    protected function casts(): array
    {
        return [
            'is_banned_from_exam' => 'boolean',
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

    public function classMember(): BelongsTo
    {
        return $this->belongsTo(ClassMember::class);
    }
}
