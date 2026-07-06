<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $table = 'subscriptions';

    protected $fillable = [
        'user_id', // ID người dùng đăng ký thuê bao.
        'plan_id', // ID gói đang dùng hiện tại (có thể là FREE khi tạm hạ cấp).
        'paid_plan_id', // ID gói ĐÃ TRẢ TIỀN — gói duy nhất được đổi qua lại miễn phí cùng FREE.
        'start_date', // Ngày kích hoạt gói cước.
        'end_date', // Ngày hết hạn gói cước.
        'status', // Trạng thái thuê bao active/expired/canceled.
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime', // Ép kiểu ngày bắt đầu.
            'end_date' => 'datetime', // Ép kiểu ngày hết hạn.
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
