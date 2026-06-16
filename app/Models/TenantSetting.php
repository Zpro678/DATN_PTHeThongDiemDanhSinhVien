<?php

namespace App\Models;

use Database\Factories\TenantSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSetting extends Model
{
    /** @use HasFactory<TenantSettingFactory> */
    use HasFactory;

    public const CREATED_AT = null;

    protected $fillable = [
        'tenant_id',
        'default_gps_radius',
        'default_absence_warning',
    ];

    protected function casts(): array
    {
        return [
            'default_absence_warning' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
