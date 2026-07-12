<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ClassSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'class_sessions';

    /**
     * Số giây ân hạn cộng thêm vào tuổi thọ token QR.
     * Che jitter của vòng lặp làm mới và cho phép cú quét rơi sát mép ảnh vẫn kịp mở trang.
     */
    public const QR_TOKEN_GRACE_SECONDS = 5;

    protected $fillable = [
        'class_id', // ID của lớp học.
        'meeting_id', // ID buổi học chứa phiên này.
        'created_by', // ID chủ lớp tạo phiên điểm danh.
        'name', // Tên buổi học.
        'date', // Ngày diễn ra buổi học.
        'start_time', // Thời gian bắt đầu.
        'end_time', // Thời gian kết thúc.
        'qr_token', // Chuỗi token mã QR (xoay theo nhịp làm mới).
        'token_expires_at', // Thời điểm hết hạn của mã QR.
        'share_token', // Token ỔN ĐỊNH cho link chia sẻ (không xoay); sống suốt lúc phiên mở.
        'qr_refresh_rate', // Tốc độ làm mới mã QR.
        'gps_latitude', // Vĩ độ vị trí điểm danh.
        'gps_longitude', // Kinh độ vị trí điểm danh.
        'gps_radius', // Bán kính GPS cho phép.
        'device_check', // Bật/tắt kiểm tra thiết bị (chống điểm danh hộ) cho phiên.
        'status', // Trạng thái phiên pending/active/closed.
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date', // Ép kiểu ngày diễn ra.
            'token_expires_at' => 'datetime', // Ép kiểu hạn sống mã QR.
            'gps_latitude' => 'decimal:8', // Ép kiểu vĩ độ GPS.
            'gps_longitude' => 'decimal:8', // Ép kiểu kinh độ GPS.
            'gps_radius' => 'integer', // Ép kiểu bán kính GPS.
            'device_check' => 'boolean', // Ép kiểu cờ kiểm tra thiết bị.
        ];
    }

    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(ClassMeeting::class, 'meeting_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function checkInScans(): HasMany
    {
        return $this->hasMany(CheckInScan::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /* ====================================================================
     * TOKEN QR — xoay token để chống dùng lại ảnh chụp
     * ==================================================================== */

    /** Tự cấp share_token ổn định khi tạo phiên mới (nếu chưa có). */
    protected static function booted(): void
    {
        static::creating(function (self $session): void {
            if (empty($session->share_token)) {
                $session->share_token = self::generateShareToken();
            }
        });
    }

    /** Sinh một token QR mới (ngẫu nhiên, in hoa). */
    public static function generateQrToken(): string
    {
        return Str::upper(Str::random(24));
    }

    /** Sinh token ổn định cho link chia sẻ (khác dạng qr_token: chữ thường 32 ký tự). */
    public static function generateShareToken(): string
    {
        return Str::lower(Str::random(32));
    }

    /** Tuổi thọ token QR (giây) = nhịp làm mới + ân hạn. */
    public static function qrTokenTtlSecondsFor(?int $refreshRate): int
    {
        return max(1, (int) ($refreshRate ?: 10)) + self::QR_TOKEN_GRACE_SECONDS;
    }

    /** Mốc hết hạn token QR tính từ hiện tại theo nhịp làm mới. */
    public static function qrTokenExpiryFor(?int $refreshRate): Carbon
    {
        return now()->addSeconds(self::qrTokenTtlSecondsFor($refreshRate));
    }

    /**
     * Xoay token QR: sinh token mới + đặt hạn ngắn (nhịp làm mới + ân hạn) rồi lưu.
     *
     * Vì token cũ bị thay ngay trong DB, ảnh chụp mã QR gửi đi sẽ không còn khớp khi
     * quét (tra cứu theo qr_token thất bại), nên chỉ sống tối đa ~1 nhịp làm mới.
     */
    public function rotateQrToken(): void
    {
        $this->forceFill([
            'qr_token' => self::generateQrToken(),
            'token_expires_at' => self::qrTokenExpiryFor($this->qr_refresh_rate),
        ])->save();
    }
}
