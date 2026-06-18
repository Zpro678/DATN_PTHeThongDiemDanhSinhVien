<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSummary extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'class_id',
        'class_member_id',
        'total_present',
        'total_late',
        'total_absent',
        'total_excused',
        'is_banned_from_exam',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'total_present' => 'integer',
            'total_late' => 'integer',
            'total_absent' => 'integer',
            'total_excused' => 'integer',
            'is_banned_from_exam' => 'boolean',
            'updated_at' => 'datetime',
        ];
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
