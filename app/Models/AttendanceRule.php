<?php

namespace App\Models;

use Database\Factories\AttendanceRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRule extends Model
{
    /** @use HasFactory<AttendanceRuleFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'class_id',
        'rule_type',
        'threshold_value',
        'condition_operator',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'threshold_value' => 'decimal:2',
            'is_active' => 'boolean',
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
}
