<?php

namespace App\Services;

use App\Models\ClassSession;
use App\Models\ClassMember;
use App\Models\GpsVerification;
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

    // Xác thực tọa độ GPS + trả về check_token
    public function verifyLocation(
        string $verificationToken,
        float $lat,
        float $lng,
        float $accuracy,
        string $ip
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
        // Nếu buổi học yêu cầu định vị GPS
        if ($session->gps_latitude && $session->gps_longitude) {
            $distance = $this->calculateDistance($lat, $lng, $session->gps_latitude, $session->gps_longitude);
            $radius = $session->gps_radius ?? 100;

            if ($distance > $radius) {
                return [
                    'success' => false,
                    'check_token' => null,
                    'error' => 'Vị trí của bạn quá xa lớp học (' . round($distance) . 'm). Bán kính cho phép là ' . $radius . 'm.',
                    'distance' => $distance,
                ];
            }
        }

        // Cập nhật thông tin định vị thành công
        $checkToken = Str::random(64);
        $verification->update([
            'check_token' => $checkToken,
            'lat' => $lat,
            'lng' => $lng,
            'accuracy' => $accuracy,
            'expires_at' => now()->addMinutes(2), // Check token chỉ có hiệu lực trong 2 phút
        ]);

        return [
            'success' => true,
            'check_token' => $checkToken,
            'error' => null,
            'distance' => $distance,
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

    // Tính khoảng cách Haversine
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
