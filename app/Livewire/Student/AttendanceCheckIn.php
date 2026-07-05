<?php

namespace App\Livewire\Student;

use App\Models\AttendanceRecord;
use App\Models\ClassMember;
use App\Models\ClassSession;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.empty')]
class AttendanceCheckIn extends Component
{
    public string $token;
    public ?ClassSession $session = null;
    public ?AttendanceRecord $record = null;
    public string $statusMessage = '';
    public bool $isSuccess = false;

    // For guest mode
    public string $studentCode = '';
    public string $fullName = '';
    public string $email = '';
    public bool $isAutoCheckIn = false;
    public bool $isGuestForm = false;
    public bool $isGpsError = false;
    public ?int $memberId = null;

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->session = ClassSession::query()
            ->with('courseClass')
            ->where('qr_token', $this->token)
            ->first();

        if (!$this->session) {
            $this->statusMessage = 'Mã điểm danh không hợp lệ hoặc không tồn tại.';
            return;
        }

        if ($this->session->status === 'closed') {
            $this->statusMessage = 'Phiên điểm danh này đã kết thúc.';
            return;
        }

        if ($this->session->token_expires_at && $this->session->token_expires_at->isPast()) {
            $this->statusMessage = 'Mã QR này đã hết hạn. Vui lòng làm mới trang hoặc quét lại mã mới.';
            return;
        }

        if (auth()->check()) {
            $this->isAutoCheckIn = true;
            $user = auth()->user();
            
            $classMember = $this->session->courseClass->members()
                ->where('user_id', $user->id)
                ->where('status', ClassMember::STATUS_ACTIVE)
                ->first();

            if (!$classMember) {
                $this->statusMessage = 'Bạn không thuộc danh sách lớp học này.';
                return;
            }
            
            $this->memberId = $classMember->id;
            $this->initializeRecord($classMember->id);
        } else {
            $this->isGuestForm = true;
        }
    }

    private function initializeRecord(int $memberId): void
    {
        $this->record = AttendanceRecord::query()
            ->firstOrCreate([
                'class_session_id' => $this->session->id,
                'class_member_id' => $memberId,
            ], [
                'status' => 'pending',
                'is_account' => true,
            ]);

        if ($this->record && in_array($this->record->status, ['present', 'late', 'excused'])) {
            $this->isSuccess = true;
            $this->statusMessage = 'Bạn đã điểm danh thành công trước đó.';
        }
    }

    public function submitGuestForm(): void
    {
        if ($this->session->status === 'closed') {
            $this->statusMessage = 'Phiên điểm danh này đã kết thúc.';
            return;
        }

        $this->validate([
            'studentCode' => 'nullable|string|max:20',
            'fullName' => 'required|string|max:100',
            'email' => 'required|email|max:100',
        ], [
            'fullName.required' => 'Vui lòng nhập họ và tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
        ]);

        $classMember = $this->session->courseClass->members()
            ->where('status', \App\Models\ClassMember::STATUS_ACTIVE)
            ->whereHas('profile', function ($p) {
                $p->where(function ($q) {
                    $q->where('email', $this->email);
                    if (!empty($this->studentCode)) {
                        $q->orWhere('student_code', $this->studentCode);
                    }
                });
            })
            ->first();

        if (!$classMember) {
            $this->addError('email', 'Không tìm thấy sinh viên có thông tin này trong danh sách lớp.');
            return;
        }
        
        $this->memberId = $classMember->id;
        $this->isGuestForm = false;
        $this->initializeRecord($classMember->id);
        $this->isAutoCheckIn = true;
    }

    public function checkIn(?string $gpsCheckToken = null, ?string $deviceId = null): void
    {
        if (!$this->session || !$this->record) {
            return;
        }

        // Re-validate expiration just in case
        if ($this->session->status === 'closed') {
            $this->statusMessage = 'Phiên điểm danh này đã kết thúc.';
            return;
        }

        // Đã mở được trang này nghĩa là cú quét hợp lệ (token khớp lúc mount). Việc HOÀN TẤT
        // điểm danh không phụ thuộc hạn token QR ngắn (vốn để chống dùng lại ảnh chụp), mà dựa
        // vào "phiên còn mở" theo giờ kết thúc buổi — nên bấm chậm vẫn điểm danh được, không phải quét lại.
        $this->session->meeting?->closeIfExpired();
        if ($this->session->fresh()?->status === 'closed') {
            $this->statusMessage = 'Phiên điểm danh này đã kết thúc.';
            return;
        }

        // "Đi muộn" = quá GIỜ MỞ BUỔI cộng ngưỡng phút cấu hình ở lớp (late_threshold).
        // - Mốc: thời điểm tạo buổi = lúc mở phiên điểm danh ĐẦU TIÊN (meeting->created_at),
        //   ổn định cho mọi phiên trong buổi (phiên thêm sau vẫn tính theo mốc buổi, không theo
        //   thời điểm tạo từng phiên).
        // - Ngưỡng: lấy từ cấu hình lớp, KHÔNG hardcode 15 nữa.
        $status = 'present';
        $lateThresholdMinutes = (int) ($this->session->courseClass->late_threshold ?? 15);
        $meetingStartedAt = $this->session->meeting?->created_at ?? $this->session->created_at;
        if ($meetingStartedAt && now()->greaterThan($meetingStartedAt->copy()->addMinutes($lateThresholdMinutes))) {
            $status = 'late';
        }

        $this->isGpsError = false;
        $distanceMeters = null;
        $gpsAccuracy = null;
        $gpsLatRecorded = null;
        $gpsLngRecorded = null;
        $gpsFraudFlag = null;
        $fraudNote = null;

        $ipAddress = request()->ip();
        $userAgent = request()->userAgent();
        $deviceFingerprint = md5($ipAddress . $userAgent);
        // Mã định danh trình duyệt bền do client gửi (localStorage + cookie). Khóa CHÍNH.
        $persistentDeviceId = $this->sanitizeDeviceId($deviceId);

        // Phát hiện "một máy điểm danh cho nhiều SV": ưu tiên device_id bền (không đụng nhau
        // giữa 2 máy khác dù cùng NAT/cùng model); chỉ fallback về md5(IP+UA) cho client cũ.
        $duplicateQuery = \App\Models\AttendanceRecord::query()
            ->with(['classMember.user'])
            ->where('class_session_id', $this->session->id)
            ->where('class_member_id', '!=', $this->record->class_member_id)
            ->whereNotNull('check_in_time');

        if ($persistentDeviceId !== null) {
            $duplicateQuery->where('device_id', $persistentDeviceId);
        } else {
            $duplicateQuery->whereNotNull('device_fingerprint')->where('device_fingerprint', $deviceFingerprint);
        }

        $duplicateRecord = $duplicateQuery->first();

        if ($duplicateRecord) {
            $gpsFraudFlag = 'device_duplicate';

            // Gắn cờ cho CẢ bản ghi trùng trước đó để giảng viên thấy đủ 2 SV cùng một máy.
            if ($duplicateRecord->gps_fraud_flag === null) {
                $duplicateRecord->forceFill(['gps_fraud_flag' => 'device_duplicate'])->save();
            }

            $this->record->loadMissing('classMember');

            app(\App\Services\NotificationService::class)->notifyDeviceDuplicate(
                $this->session->courseClass->owner_user_id,
                $this->record->classMember->user_id ?? null,
                $duplicateRecord->classMember->user_id ?? null,
                $this->session,
                $this->record->classMember->full_name ?? 'Sinh viên',
                $duplicateRecord->classMember->full_name ?? 'Sinh viên'
            );
        }

        if ($this->session->gps_radius && $this->session->gps_latitude && $this->session->gps_longitude) {
            if (!$gpsCheckToken) {
                $this->statusMessage = 'Phiên điểm danh yêu cầu xác minh vị trí GPS. Vui lòng cấp quyền và bật vị trí trên trình duyệt.';
                $this->isGpsError = true;
                return;
            }

            $service = app(\App\Services\GpsValidationService::class);
            $verification = $service->consumeCheckToken($gpsCheckToken, $this->session);

            if (!$verification) {
                $this->statusMessage = 'Xác thực vị trí thất bại hoặc token hết hạn. Vui lòng load lại trang và thử lại.';
                $this->isGpsError = true;
                return;
            }

            $distanceMeters = $service->calculateDistance(
                $verification->lat,
                $verification->lng,
                $this->session->gps_latitude,
                $this->session->gps_longitude
            );
            $gpsAccuracy = $verification->accuracy;
            $gpsLatRecorded = $verification->lat;
            $gpsLngRecorded = $verification->lng;

            if ($distanceMeters > $this->session->gps_radius) {
                $this->statusMessage = 'Vị trí của bạn quá xa lớp học (' . round($distanceMeters) . 'm). Bán kính cho phép là ' . $this->session->gps_radius . 'm.';
                $this->isGpsError = true;
                
                // Vẫn ghi nhận nhật ký gian lận
                $gpsFraudFlag = 'out_of_radius';
                
                $this->record->loadMissing('classMember');
                app(\App\Services\NotificationService::class)->notifyGpsFraud(
                    $this->session->courseClass->owner_user_id,
                    $this->record->classMember->user_id ?? null,
                    $this->session,
                    $this->record->classMember->full_name ?? 'Sinh viên',
                    $distanceMeters
                );
            }

            // Nghi ngờ giả lập vị trí (mock GPS): chỉ gắn cờ nếu chưa dính cờ nặng hơn
            // (out_of_radius/device_duplicate). Đây là cờ mềm — vẫn cho điểm danh,
            // chỉ để lại dấu vết cho giảng viên rà soát.
            if ($gpsFraudFlag === null) {
                $suspiciousReason = $service->detectSuspiciousGps($gpsAccuracy);
                if ($suspiciousReason !== null) {
                    $gpsFraudFlag = 'suspected_mock';
                    $fraudNote = $suspiciousReason;
                }
            }
        }

        $updateData = [
            'status' => $gpsFraudFlag === 'out_of_radius' ? 'invalid' : $status,
            'check_in_time' => now('Asia/Ho_Chi_Minh'),
            'distance_meters' => $distanceMeters,
            'gps_accuracy_meters' => $gpsAccuracy,
            'gps_latitude_recorded' => $gpsLatRecorded,
            'gps_longitude_recorded' => $gpsLngRecorded,
            'gps_fraud_flag' => $gpsFraudFlag,
            'ip_address' => $ipAddress,
            'device_fingerprint' => $deviceFingerprint,
            'device_id' => $persistentDeviceId,
            'is_account' => true,
        ];

        // Nối lý do nghi ngờ vào note hiện có (không ghi đè) để giảng viên xem được.
        if ($fraudNote !== null) {
            $updateData['note'] = trim(($this->record->note ? $this->record->note . ' | ' : '') . $fraudNote);
        }

        $this->record->update($updateData);

        // Ghi nhật ký quét (bảng check_in_scans) — phục vụ lịch sử thiết bị xuyên phiên & rà soát.
        $this->logCheckInScan(
            isValid: $gpsFraudFlag !== 'out_of_radius',
            failReason: match ($gpsFraudFlag) {
                'out_of_radius' => 'out_of_range',
                'device_duplicate' => 'device_duplicate',
                'suspected_mock' => 'suspected_mock',
                default => null,
            },
            ip: $ipAddress,
            deviceFingerprint: $deviceFingerprint,
            deviceId: $persistentDeviceId,
        );

        \App\Jobs\SaveAuditLogJob::dispatch([
            'user_id' => auth()->id() ?? null,
            'class_id' => $this->session->class_id,
            'action' => 'attendance_check_in',
            'table_name' => 'attendance_records',
            'row_id' => $this->record->id,
            'ip_address' => substr($ipAddress, 0, 45),
            'user_agent' => $userAgent,
            'new_values' => json_encode([
                'status' => $gpsFraudFlag === 'out_of_radius' ? 'invalid' : $status,
                'distance_meters' => $distanceMeters,
                'gps_accuracy_meters' => $gpsAccuracy,
                'gps_latitude_recorded' => $gpsLatRecorded,
                'gps_longitude_recorded' => $gpsLngRecorded,
                'gps_fraud_flag' => $gpsFraudFlag,
                'device_fingerprint' => $deviceFingerprint,
            ])
        ]);

        if ($gpsFraudFlag === 'out_of_radius') {
            $this->isSuccess = false;
            $this->statusMessage = 'Vị trí của bạn quá xa lớp học (' . round($distanceMeters) . 'm).';
            return;
        }

        $this->isSuccess = true;
        $this->statusMessage = 'Điểm danh thành công!';
        
        session()->flash('success', 'Điểm danh thành công!');

        // Trigger real-time update
        event(new \App\Events\StudentCheckedIn($this->session->id));
    }

    /**
     * Làm sạch device_id do client gửi: chỉ nhận chuỗi token hợp lệ (UUID/base tự sinh),
     * tránh nhồi dữ liệu rác/độc. Trả null nếu không hợp lệ (để fallback fingerprint).
     */
    private function sanitizeDeviceId(?string $deviceId): ?string
    {
        $deviceId = is_string($deviceId) ? trim($deviceId) : '';

        return preg_match('/^[A-Za-z0-9\-_]{8,191}$/', $deviceId) ? $deviceId : null;
    }

    /**
     * Chữ ký HMAC của một lần quét (chống replay + phục vụ đối chiếu nhật ký).
     */
    private function scanSignature(int $memberId, ?string $deviceId): string
    {
        return hash_hmac('sha256', implode('|', [
            $this->session->id,
            $memberId,
            $deviceId ?? '',
            now()->timestamp,
        ]), (string) config('app.key'));
    }

    /**
     * Ghi một dòng nhật ký quét vào bảng check_in_scans (immutable) để dựng lịch sử thiết bị.
     */
    private function logCheckInScan(bool $isValid, ?string $failReason, string $ip, ?string $deviceFingerprint, ?string $deviceId): void
    {
        \App\Models\CheckInScan::query()->create([
            'class_session_id' => $this->session->id,
            'user_id' => auth()->id(),
            'student_code_attempt' => $this->studentCode ?: null,
            'scan_type' => 'qr',
            'payload_signature' => $this->scanSignature((int) $this->record->class_member_id, $deviceId),
            'is_valid' => $isValid,
            'fail_reason' => $failReason,
            'ip_address' => substr($ip, 0, 45),
            'device_fingerprint' => $deviceFingerprint,
            'device_id' => $deviceId,
            'scanned_at' => now(),
        ]);
    }

    public function render(): View
    {
        return view('livewire.student.attendance-check-in')
            ->title('Điểm danh lớp học');
    }
}
