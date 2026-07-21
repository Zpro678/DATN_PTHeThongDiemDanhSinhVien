<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'attendance_records';

    protected $fillable = [
        'class_session_id', // ID của phiên điểm danh.
        'class_member_id', // ID thành viên lớp được điểm danh.
        'status', // Trạng thái điểm danh pending/present/late/absent/excused/invalid.
        'is_account', // True nếu sinh viên có tài khoản, false nếu điền form.
        'check_in_time', // Thời điểm ghi nhận có mặt.
        'ip_address', // IP mạng của thiết bị điểm danh.
        'device_fingerprint', // Mã định danh thiết bị đã băm (md5 IP+UA — tín hiệu yếu, phụ).
        'device_id', // Mã định danh trình duyệt bền (client sinh) — khóa chính chống điểm danh hộ.
        'distance_meters', // Khoảng cách GPS tính bằng mét.
        'gps_accuracy_meters', // Độ chính xác GPS của thiết bị sinh viên.
        'gps_latitude_recorded', // Vĩ độ thực tế ghi nhận từ sinh viên.
        'gps_longitude_recorded', // Kinh độ thực tế ghi nhận từ sinh viên.
        'gps_fraud_flag', // Cờ ghi nhận gian lận ('low_accuracy', 'speed_anomaly', 'token_reuse').
        'note', // Ghi chú hoặc lý do liên quan đến bản ghi.
    ];

    protected function casts(): array
    {
        return [
            'is_account' => 'boolean', // Ép kiểu cờ có tài khoản.
            'check_in_time' => 'datetime', // Ép kiểu thời điểm check-in.
            'distance_meters' => 'decimal:2', // Ép kiểu khoảng cách GPS.
            'gps_accuracy_meters' => 'float',
            'gps_latitude_recorded' => 'float',
            'gps_longitude_recorded' => 'float',
        ];
    }

    /**
     * Bản ghi này có ở NGOÀI bán kính cho phép không.
     *
     * Không thể chỉ dựa vào cột gps_fraud_flag: cột đó chỉ chứa MỘT cờ, và 'device_duplicate'
     * (nặng hơn) được gắn trước nên đè mất 'out_of_radius' khi một sinh viên dính cả hai lỗi.
     * Vì vậy tính lại từ khoảng cách đã lưu; vẫn giữ điều kiện theo cờ cho dữ liệu cũ.
     */
    public function isOutOfRadius(?int $radiusMeters): bool
    {
        if ($this->metersOutsideRadius($radiusMeters) !== null) {
            return true;
        }

        // Dữ liệu cũ không còn khoảng cách để tính lại -> đành tin vào cờ đã lưu.
        return $this->distance_meters === null && $this->gps_fraud_flag === 'out_of_radius';
    }

    /**
     * Số mét vượt ra ngoài bán kính (đã trừ sai số + biên nhiễu), null nếu vẫn trong bán kính.
     * Tính lại qua GpsValidationService để khớp đúng công thức lúc điểm danh.
     */
    public function metersOutsideRadius(?int $radiusMeters): ?int
    {
        return \App\Services\GpsValidationService::metersOutsideRadius(
            $this->distance_meters === null ? null : (float) $this->distance_meters,
            $this->gps_accuracy_meters === null ? null : (float) $this->gps_accuracy_meters,
            $radiusMeters,
        );
    }

    /**
     * Lọc các bản ghi ngoài bán kính — bản dùng cho TRUY VẤN của điều kiện ở isOutOfRadius().
     * Giữ hai vế đồng bộ với nhau khi sửa (kể cả biên nhiễu OUT_OF_RADIUS_GRACE_METERS).
     */
    public function scopeOutOfRadius(Builder $query, ?int $radiusMeters): Builder
    {
        // Ngưỡng phải bind bằng SỐ NGUYÊN: driver bind float dưới dạng CHUỖI, mà SQLite so sánh
        // số với chuỗi thì số LUÔN nhỏ hơn -> điều kiện không bao giờ đúng (đếm ra 0). Làm tròn LÊN
        // để truy vấn không bao giờ lọc rộng hơn phép tính trong PHP.
        $threshold = $radiusMeters === null
            ? null
            : (int) ceil($radiusMeters + \App\Services\GpsValidationService::OUT_OF_RADIUS_GRACE_METERS);

        return $query->where(function (Builder $q) use ($threshold): void {
            $q->where(fn (Builder $inner) => $inner
                ->whereNull('distance_meters')
                ->where('gps_fraud_flag', 'out_of_radius'));

            if ($threshold !== null) {
                $q->orWhere(fn (Builder $inner) => $inner
                    ->whereNotNull('distance_meters')
                    ->whereRaw('distance_meters - COALESCE(gps_accuracy_meters, 0) > ?', [$threshold]));
            }
        });
    }

    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function classMember(): BelongsTo
    {
        return $this->belongsTo(ClassMember::class);
    }
}
