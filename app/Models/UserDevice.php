<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDevice extends Model
{
    use HasFactory;

    protected $table = 'user_devices';

    public $timestamps = false;

    protected $fillable = [
        'user_id', // ID chủ sở hữu thiết bị.
        'fcm_token', // Token Firebase định danh thiết bị.
        'device_name', // Tên thiết bị hoặc trình duyệt.
        'last_active_at', // Thời điểm thiết bị tương tác lần cuối.
        'created_at', // Thời điểm đăng ký thiết bị.
    ];

    protected function casts(): array
    {
        return [
            'last_active_at' => 'datetime', // Ép kiểu lần hoạt động cuối.
            'created_at' => 'datetime', // Ép kiểu thời điểm đăng ký.
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
