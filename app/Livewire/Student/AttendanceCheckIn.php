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
    public string $fullName = '';
    public string $email = '';
    public bool $isAutoCheckIn = false;
    public bool $isGuestForm = false;
    public bool $isGpsError = false;
    public ?int $memberId = null;

    // Điểm danh THÀNH CÔNG nhưng Ở NGOÀI bán kính cho phép: dùng để hiện cảnh báo vàng cho sinh viên.
    public bool $isOutOfRadius = false;
    public ?int $outOfRadiusDistance = null; // khoảng cách tới lớp (m)
    public ?int $metersOutside = null;       // số mét vượt ra ngoài bán kính

    // Điểm danh THÀNH CÔNG nhưng tín hiệu GPS BẤT THƯỜNG (VPN/proxy hoặc nghi giả lập vị trí):
    // vẫn ghi có mặt nhưng đánh dấu "Sai GPS" và hiện cảnh báo đỏ cho sinh viên + ghi chú cho GV.
    public bool $isSuspectedFake = false;
    public ?string $fakeReason = null;       // lý do nghi ngờ (hiện cho SV & ghi vào note)

    public function mount(string $token): void
    {
        $this->token = $token;
        // Nhận CẢ HAI dạng token: qr_token (quét ảnh QR, xoay) HOẶC share_token (link chia sẻ ổn định).
        $this->session = ClassSession::query()
            ->with('courseClass')
            ->where(function ($query) {
                $query->where('qr_token', $this->token)
                    ->orWhere('share_token', $this->token);
            })
            ->first();

        if (!$this->session) {
            $this->statusMessage = 'Mã điểm danh không hợp lệ hoặc không tồn tại.';
            return;
        }

        if ($this->session->status === 'closed') {
            $this->statusMessage = 'Phiên điểm danh này đã kết thúc.';
            return;
        }

        // Hạn token NGẮN chỉ áp cho luồng QUÉT ẢNH QR (qr_token xoay, chống chụp lại). Link chia sẻ
        // ổn định (khớp share_token) KHÔNG bị chặn bởi hạn này — nó sống suốt lúc phiên còn mở.
        $matchedByShareToken = $this->session->share_token && $this->session->share_token === $this->token;
        if (!$matchedByShareToken && $this->session->token_expires_at && $this->session->token_expires_at->isPast()) {
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
                'is_account' => auth()->check(),
            ]);

        // Chống điểm danh 2 lần trong cùng một phiên: chỉ chặn khi đã ghi nhận có mặt/muộn/có phép.
        // Lưu ý: điểm danh ngoài bán kính GPS nay VẪN là 'present' (kèm cờ vàng) nên cũng bị chặn
        // quét lại như một lần có mặt hợp lệ — đúng ý đồ (đã được ghi nhận rồi).
        if ($this->record && in_array($this->record->status, ['present', 'late', 'excused'], true)) {
            $this->isSuccess = true;
            $this->isAutoCheckIn = false; // Chặn blade tự động bấm điểm danh lại.
            $this->statusMessage = 'Bạn đã điểm danh cho phiên này rồi. Mỗi phiên chỉ được điểm danh một lần.';
        }
    }

    public function submitGuestForm(): void
    {
        if ($this->session->status === 'closed') {
            $this->statusMessage = 'Phiên điểm danh này đã kết thúc.';
            return;
        }

        $this->validate([
            'fullName' => 'required|string|max:100',
            'email' => 'required|email|max:100',
        ], [
            'fullName.required' => 'Vui lòng nhập họ và tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
        ]);

        $classMember = $this->session->courseClass->members()
            ->where('status', \App\Models\ClassMember::STATUS_ACTIVE)
            ->whereHas('profile', fn ($p) => $p->where('email', $this->email))
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

        // Chặn điểm danh lần 2 trong cùng phiên (kể cả khi client cố gọi lại checkIn).
        // Chỉ chặn khi đã ghi nhận có mặt/đi muộn/có phép. Ngoài bán kính nay cũng là 'present'
        // (kèm cờ vàng) nên đã điểm danh rồi thì không ghi lại.
        $this->record->refresh();
        if (in_array($this->record->status, ['present', 'late', 'excused'], true)) {
            $this->isSuccess = true;
            $this->statusMessage = 'Bạn đã điểm danh cho phiên này rồi. Mỗi phiên chỉ được điểm danh một lần.';
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

        // Điểm danh QR trong PHIÊN chỉ ghi CÓ MẶT — KHÔNG tự tính "đi muộn" ở mức phiên.
        // "Đi muộn" chỉ được xác định ở TỔNG KẾT BUỔI (AttendanceCalculator::consolidateMeeting):
        // vắng phiên đầu nhưng có mặt phiên sau -> đi muộn; hoặc giảng viên chỉnh tay ở trang tổng kết.
        $status = 'present';

        $this->isGpsError = false;
        $this->isOutOfRadius = false;
        $this->isSuspectedFake = false;
        $distanceMeters = null;
        $gpsAccuracy = null;
        $gpsLatRecorded = null;
        $gpsLngRecorded = null;
        $gpsFraudFlag = null;
        // Gom NHIỀU lý do nghi vấn (trùng máy + ngoài bán kính + sai GPS...) để không đè lên nhau;
        // nối lại bằng ' | ' khi ghi vào note của bản ghi.
        $fraudNotes = [];

        $ipAddress = request()->ip();
        $userAgent = request()->userAgent();
        $deviceFingerprint = md5($ipAddress . $userAgent);
        // Mã định danh trình duyệt bền do client gửi (localStorage + cookie). Khóa CHÍNH.
        $persistentDeviceId = $this->sanitizeDeviceId($deviceId);

        // Chỉ kiểm tra thiết bị khi phiên bật toggle device_check (tôn trọng cấu hình giảng viên).
        $deviceCheckEnabled = (bool) ($this->session->device_check ?? true);

        if ($deviceCheckEnabled) {
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

                $this->record->loadMissing('classMember');
                $currentName = $this->record->classMember->full_name ?? 'Sinh viên';
                $duplicateName = $duplicateRecord->classMember->full_name ?? 'Sinh viên';

                // Ghi rõ AI trùng với AI vào ghi chú của bản ghi HIỆN TẠI để giảng viên đối chiếu nhanh.
                $fraudNotes[] = "Trùng thiết bị với {$duplicateName}.";

                // Gắn cờ + ghi chú cho CẢ bản ghi trùng TRƯỚC ĐÓ (để thấy đủ 2 SV cùng một máy, và
                // biết nó trùng với chính SV vừa quét). Không đè note cũ, chỉ nối thêm phần chưa có.
                $previousFraudFlag = $duplicateRecord->gps_fraud_flag;
                $duplicateUpdate = ['gps_fraud_flag' => 'device_duplicate'];
                $duplicateNoteAdditions = [];

                $duplicateWithNote = "Trùng thiết bị với {$currentName}.";
                if (! str_contains($duplicateRecord->note ?? '', $duplicateWithNote)) {
                    $duplicateNoteAdditions[] = $duplicateWithNote;
                }
                if ($previousFraudFlag !== null && $previousFraudFlag !== 'device_duplicate') {
                    $previousFlagNote = "Cảnh báo trước đó: {$previousFraudFlag}.";
                    if (! str_contains($duplicateRecord->note ?? '', $previousFlagNote)) {
                        $duplicateNoteAdditions[] = $previousFlagNote;
                    }
                }
                if ($duplicateNoteAdditions !== []) {
                    $duplicateUpdate['note'] = trim(($duplicateRecord->note ? $duplicateRecord->note . ' | ' : '') . implode(' | ', $duplicateNoteAdditions));
                }
                $duplicateRecord->forceFill($duplicateUpdate)->save();

                app(\App\Services\NotificationService::class)->notifyDeviceDuplicate(
                    $this->session->courseClass->owner_user_id,
                    $this->record->classMember->user_id ?? null,
                    $duplicateRecord->classMember->user_id ?? null,
                    $this->session,
                    $currentName,
                    $duplicateName
                );
            }
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

            // Trừ BIÊN ĐỘ SAI SỐ GPS trước khi phán "ngoài bán kính": trình duyệt báo accuracy tới
            // 150m nên đứng ĐÚNG chỗ vẫn có thể đo lệch vài chục mét (đây là nguyên nhân "ở gần mà
            // báo xa"). Chỉ gắn cờ khi CHẮC CHẮN ở ngoài kể cả đã cho hưởng trọn sai số đo được.
            $accuracyMargin = (float) ($gpsAccuracy ?? 0);
            $effectiveDistance = max(0.0, $distanceMeters - $accuracyMargin);

            if ($effectiveDistance > $this->session->gps_radius) {
                // NGHIỆP VỤ MỚI: ngoài bán kính VẪN cho điểm danh (không chặn). Chỉ GẮN CỜ VÀNG
                // 'out_of_radius' và BÁO CHỦ LỚP kèm số mét vượt ra ngoài để chủ lớp rà soát.
                $metersOutside = (int) round($effectiveDistance - $this->session->gps_radius);

                // Ghi lại để hiện CẢNH BÁO VÀNG cho chính sinh viên ở màn hình kết quả.
                $this->isOutOfRadius = true;
                $this->outOfRadiusDistance = (int) round($distanceMeters);
                $this->metersOutside = $metersOutside;

                // Cờ vàng: chỉ gắn khi chưa dính cờ nặng hơn (vd trùng máy = đỏ) để không che cảnh báo nặng.
                if ($gpsFraudFlag === null) {
                    $gpsFraudFlag = 'out_of_radius';
                }

                $fraudNotes[] = 'Ngoài bán kính cho phép: cách lớp ' . round($distanceMeters) . 'm (vượt '
                    . $metersOutside . 'm, đã trừ sai số ±' . round($accuracyMargin) . 'm).';

                // Báo CHỦ LỚP (mức warning = vàng). Mỗi SV chỉ điểm danh thành công một lần nên không spam.
                $this->record->loadMissing('classMember');
                app(\App\Services\NotificationService::class)->notifyGpsOutOfRadius(
                    (int) ($this->session->courseClass->owner_user_id ?? 0),
                    $this->session,
                    $this->record->classMember->full_name ?? 'Sinh viên',
                    $distanceMeters,
                    $metersOutside,
                );
            }

            // Di chuyển bất khả thi (impossible travel): cùng một người vừa điểm danh ở nơi
            // khác cách quá xa trong thời gian quá ngắn -> nghi vấn giả mạo vị trí/điểm danh hộ.
            // Cờ mềm: vẫn cho điểm danh, chỉ gắn nếu chưa dính cờ nặng hơn.
            if ($gpsFraudFlag === null) {
                $impossibleReason = $this->detectImpossibleTravel($gpsLatRecorded, $gpsLngRecorded);
                if ($impossibleReason !== null) {
                    $gpsFraudFlag = 'impossible_travel';
                    $fraudNotes[] = $impossibleReason;
                }
            }

            // Nghi ngờ giả lập vị trí (fake GPS): dựa trên ĐIỂM tổng hợp đa tín hiệu đã chấm ở
            // bước /gps/verify (độ chính xác, jitter toạ độ, thiếu độ cao, lệch IP↔GPS). Cờ mềm —
            // vẫn cho điểm danh, chỉ để dấu vết cho GV rà soát; chỉ gắn nếu chưa dính cờ nặng hơn.
            if ($gpsFraudFlag === null
                && (int) ($verification->fraud_score ?? 0) >= \App\Services\GpsValidationService::FAKE_GPS_SUSPICION_THRESHOLD) {
                $gpsFraudFlag = 'suspected_mock';
                $reason = $verification->fraud_reasons ?: 'nhiều dấu hiệu bất thường';
                // Tiền tố "Sai GPS" để bảng của giảng viên tô màu cảnh báo và lọc nhanh (xem
                // student-list.blade.php). Kèm lý do chi tiết (vd: kết nối qua VPN/proxy) để GV biết vì sao.
                $mockNote = 'Sai GPS (nghi giả lập vị trí / VPN): ' . $reason . '.';
                // Kèm KHOẢNG CÁCH tới lớp (nếu buổi có định vị) để GV có thêm ngữ cảnh khi rà soát.
                if ($distanceMeters !== null) {
                    $mockNote .= ' Cách lớp ~' . round($distanceMeters) . 'm.';
                }
                $fraudNotes[] = $mockNote;

                // Bật cảnh báo hiển thị cho chính sinh viên ở màn hình kết quả.
                $this->isSuspectedFake = true;
                $this->fakeReason = $reason;
            }
        }

        $updateData = [
            // Ngoài bán kính VẪN ghi CÓ MẶT (chỉ gắn cờ vàng để chủ lớp rà soát), nên status/giờ
            // check-in ghi bình thường như mọi lần điểm danh hợp lệ.
            'status' => $status,
            'check_in_time' => now('Asia/Ho_Chi_Minh'),
            'distance_meters' => $distanceMeters,
            'gps_accuracy_meters' => $gpsAccuracy,
            'gps_latitude_recorded' => $gpsLatRecorded,
            'gps_longitude_recorded' => $gpsLngRecorded,
            'gps_fraud_flag' => $gpsFraudFlag,
            'ip_address' => $ipAddress,
            'device_fingerprint' => $deviceFingerprint,
            'device_id' => $persistentDeviceId,
            'is_account' => auth()->check(),
        ];

        // Nối các lý do nghi ngờ (trùng máy / ngoài bán kính / sai GPS...) vào note hiện có
        // (không ghi đè) để giảng viên xem được.
        if ($fraudNotes !== []) {
            $updateData['note'] = trim(($this->record->note ? $this->record->note . ' | ' : '') . implode(' | ', $fraudNotes));
        }

        $this->record->update($updateData);

        // Ghi nhật ký quét (bảng check_in_scans) — phục vụ lịch sử thiết bị xuyên phiên & rà soát.
        $this->logCheckInScan(
            // Ngoài bán kính nay VẪN là một lần điểm danh hợp lệ (đã ghi có mặt) — chỉ kèm
            // fail_reason như một DẤU VẾT cảnh báo cho nhật ký, giống các cờ mềm khác.
            isValid: true,
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
                'status' => $status,
                'distance_meters' => $distanceMeters,
                'gps_accuracy_meters' => $gpsAccuracy,
                'gps_latitude_recorded' => $gpsLatRecorded,
                'gps_longitude_recorded' => $gpsLngRecorded,
                'gps_fraud_flag' => $gpsFraudFlag,
                'device_fingerprint' => $deviceFingerprint,
            ])
        ]);

        // Leo thang: nếu cùng một device_id đã điểm danh cho nhiều SV khác nhau trong lớp
        // (xuyên nhiều buổi) -> nghi vấn "máy điểm danh hộ chuyên nghiệp", báo giảng viên.
        if ($deviceCheckEnabled && $persistentDeviceId !== null) {
            $this->escalateProxyDevice($persistentDeviceId);
        }

        $this->isSuccess = true;

        if ($this->isOutOfRadius) {
            // Vẫn ghi nhận có mặt, nhưng CẢNH BÁO cho sinh viên rằng họ điểm danh ngoài bán kính
            // (kèm số mét vượt). Không flash "success" xanh để màn hình hiện thẻ cảnh báo vàng.
            $this->statusMessage = 'Đã ghi nhận điểm danh, nhưng bạn đang ở NGOÀI bán kính cho phép: cách lớp '
                . $this->outOfRadiusDistance . 'm (vượt ' . $this->metersOutside . 'm). Chủ lớp đã được thông báo để rà soát.';
        } elseif ($this->isSuspectedFake) {
            // Vẫn ghi nhận có mặt, nhưng hệ thống thấy tín hiệu GPS bất thường (VPN/proxy hoặc nghi
            // giả lập vị trí). Bản ghi đã bị đánh dấu "Sai GPS" -> hiện thẻ cảnh báo đỏ, yêu cầu tắt.
            $this->statusMessage = 'Đã ghi nhận điểm danh, nhưng hệ thống phát hiện TÍN HIỆU GPS BẤT THƯỜNG ('
                . $this->fakeReason . '). Bản ghi đã bị đánh dấu SAI GPS để giảng viên rà soát. '
                . 'Nếu bạn đang bật VPN/proxy hoặc ứng dụng định vị giả, vui lòng TẮT rồi điểm danh lại.';
        } else {
            $this->statusMessage = 'Điểm danh thành công!';
            session()->flash('success', 'Điểm danh thành công!');
        }

        // Cập nhật realtime cho bảng của giảng viên — CHỈ là best-effort. StudentCheckedIn là
        // ShouldBroadcastNow (đẩy Redis ĐỒNG BỘ), nên nếu Redis/broadcast lỗi mà không bọc thì
        // sẽ ném 500 SAU KHI điểm danh đã lưu -> phía SV kẹt mãi ở "Đang xác thực...". Nuốt lỗi
        // ở đây: điểm danh đã ghi nhận thành công là điều quan trọng, realtime hỏng thì báo log.
        try {
            event(new \App\Events\StudentCheckedIn($this->session->id));
        } catch (\Throwable $e) {
            report($e);
        }
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

    /** Số sinh viên KHÁC NHAU tối đa một thiết bị được phép điểm danh trong lớp trước khi bị leo thang. */
    private const PROXY_DEVICE_STUDENT_THRESHOLD = 3;

    /** Tốc độ di chuyển tối đa hợp lý giữa 2 lần điểm danh (km/h). */
    private const IMPOSSIBLE_TRAVEL_MAX_KMH = 300;

    /** Khoảng cách tối thiểu (m) mới xét di chuyển bất khả thi — tránh nhiễu GPS gây báo nhầm. */
    private const IMPOSSIBLE_TRAVEL_MIN_METERS = 1000;

    /**
     * Phát hiện "di chuyển bất khả thi": so vị trí lần này với lần điểm danh GẦN NHẤT của cùng
     * người (theo user_id, xuyên lớp/buổi). Nếu quãng đường/thời gian ra tốc độ vô lý -> nghi vấn.
     *
     * Dùng updated_at (app timezone) làm mốc thời gian để tránh lệch múi giờ của check_in_time.
     * Trả về câu mô tả nếu nghi ngờ, null nếu bình thường.
     */
    private function detectImpossibleTravel(?float $lat, ?float $lng): ?string
    {
        if ($lat === null || $lng === null) {
            return null;
        }

        $this->record->loadMissing('classMember');
        $userId = $this->record->classMember->user_id ?? null;
        if (! $userId) {
            return null; // Khách không có tài khoản -> không thể nối lịch sử xuyên buổi.
        }

        $prior = \App\Models\AttendanceRecord::query()
            ->join('class_members as cm', 'cm.id', '=', 'attendance_records.class_member_id')
            ->where('cm.user_id', $userId)
            ->where('attendance_records.id', '!=', $this->record->id)
            ->whereNotNull('attendance_records.check_in_time')
            ->whereNotNull('attendance_records.gps_latitude_recorded')
            ->whereNotNull('attendance_records.gps_longitude_recorded')
            ->orderByDesc('attendance_records.updated_at')
            ->first([
                'attendance_records.gps_latitude_recorded as lat',
                'attendance_records.gps_longitude_recorded as lng',
                'attendance_records.updated_at as ts',
            ]);

        if (! $prior) {
            return null;
        }

        $service = app(\App\Services\GpsValidationService::class);
        $distanceM = $service->calculateDistance((float) $prior->lat, (float) $prior->lng, $lat, $lng);
        if ($distanceM < self::IMPOSSIBLE_TRAVEL_MIN_METERS) {
            return null;
        }

        $seconds = max((int) abs(now()->diffInSeconds(\Illuminate\Support\Carbon::parse($prior->ts))), 1);
        $speedKmh = ($distanceM / 1000) / ($seconds / 3600);
        if ($speedKmh <= self::IMPOSSIBLE_TRAVEL_MAX_KMH) {
            return null;
        }

        return 'Nghi ngờ di chuyển bất khả thi: cách lần điểm danh trước ' . round($distanceM) . 'm trong ' . $seconds . 's (~' . round($speedKmh) . ' km/h).';
    }

    /**
     * Leo thang khi một thiết bị (device_id) đã điểm danh cho quá nhiều SV KHÁC NHAU trong lớp
     * (tính xuyên tất cả buổi) — dấu hiệu "máy điểm danh hộ chuyên nghiệp". Báo giảng viên (chống trùng).
     */
    private function escalateProxyDevice(string $deviceId): void
    {
        $classId = $this->session->class_id;

        $distinctStudents = \App\Models\AttendanceRecord::query()
            ->join('class_sessions as cs', 'cs.id', '=', 'attendance_records.class_session_id')
            ->where('cs.class_id', $classId)
            ->where('attendance_records.device_id', $deviceId)
            ->whereNotNull('attendance_records.check_in_time')
            ->distinct()
            ->count('attendance_records.class_member_id');

        if ($distinctStudents < self::PROXY_DEVICE_STUDENT_THRESHOLD) {
            return;
        }

        app(\App\Services\NotificationService::class)->notifyProxyDeviceAbuse(
            (int) $this->session->courseClass->owner_user_id,
            $this->session,
            $distinctStudents,
        );
    }

    public function render(): View
    {
        return view('livewire.student.attendance-check-in')
            ->title('Điểm danh lớp học');
    }
}
