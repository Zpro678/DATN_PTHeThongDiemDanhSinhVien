<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'user_id', // ID người thực hiện giao dịch.
        'plan_id', // Gói được thanh toán trong giao dịch này.
        'amount', // Số tiền thanh toán.
        'currency', // Đơn vị tiền tệ.
        'payment_method', // Cổng thanh toán: MOMO/PAYOS/VNPAY/STRIPE.
        'transaction_code', // Mã giao dịch nội bộ duy nhất.
        'reference_code', // Mã tham chiếu gửi sang cổng thanh toán.
        'gateway_transaction_id', // Mã giao dịch do cổng trả về.
        'status', // Trạng thái giao dịch.
        'payment_url', // Link thanh toán do gateway tạo.
        'payment_response', // Dữ liệu phản hồi từ cổng.
        'failure_reason', // Lý do thất bại nếu lỗi.
        'paid_at', // Thời điểm thanh toán thành công.
        'expired_at', // Thời gian hết hạn thanh toán.
        'coupon_id', // ID mã giảm giá.
        'coupon_code', // Mã giảm giá.
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2', // Ép kiểu số tiền thanh toán.
            'payment_response' => 'array', // Ép kiểu phản hồi cổng dạng JSON.
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
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

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }
}
