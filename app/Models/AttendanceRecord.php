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
     * Khoảng cách tới tâm lớp SAU KHI trừ biên độ sai số GPS — đúng công thức mà
     * AttendanceCheckIn::checkIn dùng để phán "ngoài bán kính". Null khi buổi không đo GPS.
     */
    public function effectiveDistanceMeters(): ?float
    {
        if ($this->distance_meters === null) {
            return null;
        }

        return max(0.0, (float) $this->distance_meters - (float) ($this->gps_accuracy_meters ?? 0));
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
        if ($this->gps_fraud_flag === 'out_of_radius') {
            return true;
        }

        $effective = $this->effectiveDistanceMeters();

        return $radiusMeters !== null && $effective !== null && $effective > $radiusMeters;
    }

    /** Số mét vượt ra ngoài bán kính (đã trừ sai số), null nếu không ở ngoài bán kính. */
    public function metersOutsideRadius(?int $radiusMeters): ?int
    {
        $effective = $this->effectiveDistanceMeters();

        if ($radiusMeters === null || $effective === null || ! $this->isOutOfRadius($radiusMeters)) {
            return null;
        }

        return max(0, (int) round($effective - $radiusMeters));
    }

    /**
     * Lọc các bản ghi ngoài bán kính — bản dùng cho TRUY VẤN của điều kiện ở isOutOfRadius().
     * Giữ hai vế đồng bộ với nhau khi sửa.
     */
    public function scopeOutOfRadius(Builder $query, ?int $radiusMeters): Builder
    {
        return $query->where(function (Builder $q) use ($radiusMeters): void {
            $q->where('gps_fraud_flag', 'out_of_radius');

            if ($radiusMeters !== null) {
                $q->orWhere(fn (Builder $inner) => $inner
                    ->whereNotNull('distance_meters')
                    ->whereRaw('distance_meters - COALESCE(gps_accuracy_meters, 0) > ?', [$radiusMeters]));
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
