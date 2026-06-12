<?php

namespace App\Models;

use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    // DATN NOTE: The document writes this column as `Partner_ reference_id`,
    // which is not a valid snake_case database column. It is normalized here.
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'tenant_id',
        'amount',
        'payment_method',
        'transaction_code',
        'partner_reference_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
