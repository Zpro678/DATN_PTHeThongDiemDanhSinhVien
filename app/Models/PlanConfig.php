<?php

namespace App\Models;

use App\Traits\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanConfig extends Model
{
    use HasFactory, Auditable;

    public $timestamps = false;

    protected $table = 'plan_configs';

    protected $fillable = [
        'plan_id', // ID gói dịch vụ (1-1).
        'max_classes', // Giới hạn số lớp học được tạo.
        'max_students_per_class', // Giới hạn số SV/lớp.
        'can_export_excel', // Quyền xuất báo cáo Excel.
    ];

    protected function casts(): array
    {
        return [
            'max_classes' => 'integer',
            'max_students_per_class' => 'integer',
            'can_export_excel' => 'boolean',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
