<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    public $timestamps = false;

    protected $fillable = [
        'user_id', // ID người thực hiện giao dịch.
        'plan_id', // Gói được thanh toán trong giao dịch này.
        'amount', // Số tiền thanh toán.
        'payment_method', // Phương thức thanh toán.
        'transaction_code', // Mã giao dịch nội bộ duy nhất.
        'partner_reference_id', // Mã tham chiếu từ cổng thanh toán.
        'status', // Trạng thái giao dịch pending/success/failed/canceled.
        'created_at', // Thời điểm tạo giao dịch.
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2', // Ép kiểu số tiền thanh toán.
            'created_at' => 'datetime', // Ép kiểu thời điểm tạo.
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
