<?php

namespace App\Services;

use App\Models\ClassSession;
use App\Models\ClassMember;
use App\Models\GpsVerification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GpsValidationService
{
    // Tạo GPS verification token (1 lần dùng, hết hạn sau 5 phút)
    public function issueVerificationToken(ClassSession $session, ClassMember $member, string $ip): GpsVerification
    {
        // Xóa các token cũ của member trong session này
        GpsVerification::where('session_id', $session->id)
            ->where('member_id', $member->id)
            ->delete();

        return GpsVerification::create([
            'session_id' => $session->id,
            'member_id' => $member->id,
            'token' => Str::random(64),
            'ip_address' => $ip,
            'is_used' => false,
            'expires_at' => now()->addMinutes(5),
        ]);
    }

    /**
     * Xác thực tọa độ GPS + trả về check_token.
     *
     * @param array $signals Tín hiệu bổ sung để chấm nghi vấn fake GPS:
     *   altitude, altitude_accuracy, speed, heading (float|null) và samples (mảng {lat,lng}).
     */
    public function verifyLocation(
        string $verificationToken,
        float $lat,
        float $lng,
        float $accuracy,
        string $ip,
        array $signals = []
    ): array {
        $verification = GpsVerification::where('token', $verificationToken)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification) {
            return [
                'success' => false,
                'check_token' => null,
                'error' => 'Token xác thực không hợp lệ hoặc đã hết hạn.',
                'distance' => null,
            ];
        }

        // Kiểm tra độ chính xác GPS (tối đa 150m)
        if ($accuracy > 150) {
            \Illuminate\Support\Facades\Log::warning('Attendance check-in failed', [
                'session_id' => $verification->session_id,
                'member_id' => $verification->member_id,
                'reason' => 'low_accuracy',
                'accuracy' => $accuracy,
                'ip' => $ip,
            ]);

            return [
                'success' => false,
                'check_token' => null,
                'error' => 'Độ chính xác GPS quá thấp (' . round($accuracy) . 'm). Vui lòng di chuyển ra nơi thoáng hơn.',
                'distance' => null,
            ];
        }

        $session = $verification->classSession;
        if (!$session) {
            return [
                'success' => false,
                'check_token' => null,
                'error' => 'Phiên điểm danh không tồn tại.',
                'distance' => null,
            ];
        }

        $distance = 0;
        // Nếu buổi học yêu cầu định vị GPS: chỉ TÍNH khoảng cách để bước check-in quyết định.
        // NGHIỆP VỤ MỚI: ở NGOÀI bán kính KHÔNG còn chặn điểm danh tại đây nữa — vẫn cấp check_token
        // để sinh viên được ghi nhận CÓ MẶT. Phần "quá xa" sẽ được GẮN CỜ VÀNG + BÁO CHỦ LỚP (kèm số
        // mét vượt) ở bước checkIn (xem App\Livewire\Student\AttendanceCheckIn::checkIn). Bước này chỉ
        // còn xác thực toạ độ hợp lệ (token, độ chính xác) và chấm nghi vấn fake GPS.
        if ($session->gps_latitude && $session->gps_longitude) {
            $distance = $this->calculateDistance($lat, $lng, $session->gps_latitude, $session->gps_longitude);
        }

        // Chấm điểm nghi vấn fake GPS (đa tín hiệu) và lưu để bước check-in đọc lại.
        $fraud = $this->computeFakeGpsScore($lat, $lng, $accuracy, $signals, $ip);

        // Cập nhật thông tin định vị thành công
        $checkToken = Str::random(64);
        $verification->update([
            'check_token' => $checkToken,
            'lat' => $lat,
            'lng' => $lng,
            'accuracy' => $accuracy,
            'fraud_score' => $fraud['score'],
            'fraud_reasons' => $fraud['reasons'] !== [] ? implode('; ', $fraud['reasons']) : null,
            'expires_at' => now()->addMinutes(2), // Check token chỉ có hiệu lực trong 2 phút
        ]);

        // Cảnh báo mềm gửi về client để YÊU CẦU sinh viên tắt VPN/proxy trước khi điểm danh
        // (vị trí mạng bị che khiến hệ thống không xác thực được). Nếu sinh viên vẫn cố điểm danh,
        // bước check-in sẽ ĐÁNH DẤU bản ghi + ghi lý do vào ghi chú cho giảng viên rà soát.
        $warnings = [];
        if (! empty($fraud['vpn'])) {
            $warnings[] = 'vpn';
        }

        return [
            'success' => true,
            'check_token' => $checkToken,
            'error' => null,
            'distance' => $distance,
            'fraud_score' => $fraud['score'],
            'warnings' => $warnings,
        ];
    }

    // Xác thực check_token trước khi lưu attendance
    public function consumeCheckToken(string $checkToken, ClassSession $session): ?GpsVerification
    {
        $verification = GpsVerification::where('check_token', $checkToken)
            ->where('session_id', $session->id)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($verification) {
            $verification->update(['is_used' => true]);
            return $verification;
        }

        return null;
    }

    // Ngưỡng dưới của độ chính xác GPS được coi là "bất thường".
    // GPS điện thoại qua trình duyệt gần như không bao giờ đạt độ chính xác dưới 1m;
    // các app Fake GPS lại thường gán cứng accuracy = 0/1. Đây là tín hiệu 1-lần-đọc
    // đáng tin nhất để suy đoán mock location (thay cho heuristic "accuracy là số
    // nguyên" cũ — vốn báo nhầm rất nhiều vì thiết bị thật thường trả accuracy nguyên).
    public const IMPLAUSIBLE_ACCURACY_METERS = 1.0;

    /**
     * Suy đoán vị trí bị giả lập (mock/fake GPS) từ một lần đọc.
     *
     * Trình duyệt KHÔNG cho biết cờ "mock provider" của Android (chỉ app native mới
     * đọc được), nên ở phía server ta chỉ có thể suy đoán từ giá trị bất thường về
     * mặt vật lý. Ở đây dùng độ chính xác quá nhỏ để coi là đáng ngờ.
     *
     * Trả về câu mô tả lý do nếu nghi ngờ, hoặc null nếu bình thường.
     * Lưu ý: hàm chỉ GẮN CỜ để giảng viên rà soát, KHÔNG tự chặn điểm danh —
     * tránh khóa nhầm sinh viên thật khi thiết bị vô tình báo accuracy nhỏ.
     */
    public function detectSuspiciousGps(?float $accuracy): ?string
    {
        if ($accuracy !== null && $accuracy <= self::IMPLAUSIBLE_ACCURACY_METERS) {
            return 'Nghi ngờ giả lập vị trí: độ chính xác bất thường (' . round($accuracy, 2) . 'm).';
        }

        return null;
    }

    /** Ngưỡng điểm nghi vấn để coi là nghi giả lập vị trí (cờ mềm). */
    public const FAKE_GPS_SUSPICION_THRESHOLD = 2;

    /** Khoảng cách tối đa (km) giữa vị trí theo IP và GPS trước khi coi là bất thường. */
    public const IP_GPS_MAX_DISTANCE_KM = 150;

    /**
     * Chấm điểm nghi vấn fake GPS từ nhiều tín hiệu độc lập.
     *
     * @param  array  $signals  altitude/altitude_accuracy/speed/heading (float|null) + samples (mảng {lat,lng}).
     * @return array{score:int, reasons:array<int,string>, vpn:bool}
     */
    public function computeFakeGpsScore(float $lat, float $lng, float $accuracy, array $signals, ?string $ip): array
    {
        $score = 0;
        $reasons = [];
        $isVpn = false;

        // (1) Độ chính xác quá đẹp — app fake thường gán cứng ~0/1m. Tín hiệu MẠNH: một mình đủ nghi.
        if ($accuracy <= self::IMPLAUSIBLE_ACCURACY_METERS) {
            $score += 2;
            $reasons[] = 'độ chính xác bất thường (' . round($accuracy, 2) . 'm)';
        }

        // Tín hiệu YẾU (#2, #3): điện thoại THẬT đứng yên và định vị bằng WiFi/cell — rất phổ biến
        // với iPhone trong nhà — CŨNG cho toạ độ trùng khít qua các lần đọc và thường THIẾU độ cao.
        // Đây KHÔNG phải bằng chứng giả lập. Vì vậy hai tín hiệu này gộp lại chỉ tính TỐI ĐA 1 điểm
        // và KHÔNG tự vượt ngưỡng (=2); chúng chỉ CỘNG THÊM khi đã có tín hiệu mạnh (độ chính xác
        // bất thường / VPN / lệch IP↔GPS) để tránh dán nhãn "Sai GPS" oan cho sinh viên có mặt thật.
        $weakScore = 0;

        // (2) Toạ độ đứng yên tuyệt đối qua nhiều mẫu.
        if ($this->samplesLookStatic($signals['samples'] ?? [])) {
            $weakScore = 1;
            $reasons[] = 'toạ độ không đổi qua nhiều lần đọc';
        }

        // (3) Thiếu độ cao — nhiều app fake để altitude null (nhưng iPhone dùng WiFi cũng vậy).
        if (array_key_exists('altitude', $signals) && $signals['altitude'] === null) {
            $weakScore = 1;
            $reasons[] = 'thiết bị không cung cấp độ cao';
        }

        // (4) VPN/proxy hoặc lệch IP↔GPS. CHỈ chấm khi request()->ip() đáng tin (đã khai báo
        // TRUSTED_PROXIES / bật GPS_IP_CHECK). Chưa cấu hình -> bỏ qua, tránh báo nhầm hàng loạt
        // khi web chạy sau proxy mà mọi request đều mang IP của proxy.
        $ipLoc = $this->ipCheckEnabled() ? $this->lookupIpLocation($ip) : null;
        if ($ipLoc !== null) {
            if (! empty($ipLoc['proxy']) || ! empty($ipLoc['hosting'])) {
                // Đang qua VPN/proxy: IP là exit-node nên khoảng cách IP↔GPS VÔ NGHĨA (không so nữa,
                // tránh phạt oan sinh viên có mặt thật mà bật VPN). Thay bằng một CỜ VPN riêng: bước
                // check-in sẽ yêu cầu tắt VPN, và đánh dấu nếu vẫn cố điểm danh.
                $isVpn = true;
                $score += 3;
                $reasons[] = 'kết nối qua VPN/proxy (vị trí mạng bị che giấu)';
            } else {
                // Không VPN: lệch IP↔GPS là dấu hiệu fake toạ độ trình duyệt (không đổi IP thật).
                $ipDistanceKm = $this->calculateDistance($ipLoc['lat'], $ipLoc['lng'], $lat, $lng) / 1000;
                if ($ipDistanceKm > self::IP_GPS_MAX_DISTANCE_KM) {
                    $score += 3;
                    $reasons[] = 'vị trí mạng (IP) cách GPS ~' . round($ipDistanceKm) . 'km';
                }
            }
        }

        // Cộng phần tín hiệu yếu SAU CÙNG (tối đa 1). Riêng chúng không đủ vượt ngưỡng, chỉ nâng
        // điểm khi đã có tín hiệu mạnh ở trên — nên máy thật đứng yên (toạ độ tĩnh + thiếu độ cao)
        // chỉ đạt 1 điểm và KHÔNG bị gắn cờ "Sai GPS".
        $score += $weakScore;

        return ['score' => $score, 'reasons' => $reasons, 'vpn' => $isVpn];
    }

    /**
     * Các mẫu toạ độ có "đứng yên tuyệt đối" không (≥2 mẫu trùng tới 6 chữ số thập phân).
     *
     * @param  array<int, array{lat?:mixed, lng?:mixed}>  $samples
     */
    private function samplesLookStatic(array $samples): bool
    {
        if (count($samples) < 2) {
            return false;
        }

        $firstKey = null;
        foreach ($samples as $sample) {
            $key = round((float) ($sample['lat'] ?? 0), 6) . ',' . round((float) ($sample['lng'] ?? 0), 6);
            if ($firstKey === null) {
                $firstKey = $key;
            } elseif ($key !== $firstKey) {
                return false;
            }
        }

        return true;
    }

    /**
     * Có bật chấm nghi vấn theo IP không (chỉ đáng tin khi request()->ip() là IP thật của SV).
     * Mặc định bật khi đã khai báo TRUSTED_PROXIES — xem config/attendance.php.
     */
    public function ipCheckEnabled(): bool
    {
        return (bool) config('attendance.gps_ip_check', false);
    }

    /**
     * Tra vị trí thô + cờ VPN/proxy/hosting theo IP (ip-api.com), cache 24h, fail-open
     * (trả null khi lỗi hoặc IP nội bộ). Các field proxy/hosting/mobile có sẵn ở endpoint free.
     *
     * @return array{lat:float, lng:float, proxy:bool, hosting:bool, mobile:bool}|null
     */
    public function lookupIpLocation(?string $ip): ?array
    {
        if (! $ip || $this->isPrivateIp($ip)) {
            return null;
        }

        return Cache::remember('ipgeo:' . $ip, now()->addHours(24), function () use ($ip): ?array {
            try {
                $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}", ['fields' => 'status,lat,lon,proxy,hosting,mobile']);
                $data = $response->json();
                if (($data['status'] ?? null) === 'success' && isset($data['lat'], $data['lon'])) {
                    return [
                        'lat' => (float) $data['lat'],
                        'lng' => (float) $data['lon'],
                        'proxy' => (bool) ($data['proxy'] ?? false),
                        'hosting' => (bool) ($data['hosting'] ?? false),
                        'mobile' => (bool) ($data['mobile'] ?? false),
                    ];
                }
            } catch (\Throwable $e) {
                // fail-open: không có tín hiệu IP -> không gắn cờ nhầm.
            }

            return null;
        });
    }

    /** IP có thuộc dải nội bộ/không định tuyến được không (không thể tra vị trí). */
    private function isPrivateIp(string $ip): bool
    {
        return ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    /**
     * Tính khoảng cách "đường chim bay" (great-circle) giữa 2 điểm GPS theo mét
     * bằng công thức Haversine. Đây là bản dùng chung duy nhất — controller và
     * Livewire component đều gọi qua service này thay vì tự tính.
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // in meters
        
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);
            
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }
}
