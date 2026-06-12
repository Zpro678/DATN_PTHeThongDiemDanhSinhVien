<?php

namespace App\Models;

use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'code',
        'name',
        'price',
        'max_classes',
        'can_export_excel',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'can_export_excel' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
