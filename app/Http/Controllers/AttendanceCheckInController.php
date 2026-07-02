<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\ClassSession;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;

class AttendanceCheckInController extends Controller
{
    public function show(string $token)
    {
        $session = ClassSession::query()
            ->with('courseClass')
            ->where('qr_token', $token)
            ->first();

        if (!$session) {
            return view('student.attendance.check-in', [
                'error' => 'Mã điểm danh không hợp lệ hoặc không tồn tại.',
                'token' => $token
            ]);
        }

        if ($session->status === 'closed') {
            return view('student.attendance.check-in', [
                'error' => 'Phiên điểm danh này đã kết thúc.',
                'token' => $token
            ]);
        }

        if ($session->token_expires_at && $session->token_expires_at->isPast()) {
            return view('student.attendance.check-in', [
                'error' => 'Mã QR này đã hết hạn. Vui lòng quét lại mã mới.',
                'token' => $token
            ]);
        }

        $user = auth()->user();
        $isGuestForm = false;
        $classMember = null;
        $record = null;
        $isSuccess = false;

        if ($user) {
            $classMember = $session->courseClass->members()
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if (!$classMember) {
                return view('student.attendance.check-in', [
                    'error' => 'Bạn không thuộc danh sách lớp học này.',
                    'token' => $token
                ]);
            }

            $record = $this->initializeRecord($session->id, $classMember->id);
            if ($record && in_array($record->status, ['present', 'late', 'excused'])) {
                $isSuccess = true;
            }
        } else {
            $isGuestForm = true;
        }

        return view('student.attendance.check-in', [
            'session' => $session,
            'token' => $token,
            'isGuestForm' => $isGuestForm,
            'classMember' => $classMember,
            'record' => $record,
            'isSuccess' => $isSuccess,
            'requireGps' => (bool) ($session->gps_radius && $session->gps_latitude && $session->gps_longitude),
            'gpsRadius' => $session->gps_radius
        ]);
    }

    public function process(Request $request, string $token)
    {
        $session = ClassSession::query()
            ->with('courseClass')
            ->where('qr_token', $token)
            ->first();

        if (!$session || $session->status === 'closed' || ($session->token_expires_at && $session->token_expires_at->isPast())) {
            return back()->with('error', 'Phiên điểm danh không hợp lệ, đã kết thúc hoặc hết hạn.');
        }

        $user = auth()->user();
        $classMember = null;

        if ($user) {
            $classMember = $session->courseClass->members()
                ->where('user_id', $user->id)
                ->where('status', \App\Models\ClassMember::STATUS_ACTIVE)
                ->first();
        } else {
            $request->validate([
                'studentCode' => 'required|string|max:20',
            ], [
                'studentCode.required' => 'Vui lòng nhập Mã số sinh viên.',
            ]);

            $classMember = $session->courseClass->members()
                ->whereHas('profile', fn ($p) => $p->where('student_code', $request->studentCode))
                ->where('status', \App\Models\ClassMember::STATUS_ACTIVE)
                ->first();
        }

        if (!$classMember) {
            return back()->with('error', 'Không tìm thấy sinh viên trong danh sách lớp.');
        }

        $record = $this->initializeRecord($session->id, $classMember->id);

        if (in_array($record->status, ['present', 'late', 'excused'])) {
            return back()->with('success', 'Bạn đã điểm danh thành công trước đó.');
        }

        // Lớp 3: Fingerprinting - Kiểm tra chống gian lận (Điểm danh hộ)
        $deviceId = $request->cookie('device_fingerprint');
        $isNewDevice = false;
        
        if (!$deviceId) {
            $deviceId = Str::uuid()->toString();
            $isNewDevice = true;
        } else {
            $originalCheaterRecord = AttendanceRecord::query()
                ->with('classMember')
                ->where('class_session_id', $session->id)
                ->where('class_member_id', '!=', $classMember->id)
                ->whereIn('status', ['present', 'late', 'excused'])
                ->where('device_fingerprint', $deviceId)
                ->first();

            if ($originalCheaterRecord) {
                $originalMember = $originalCheaterRecord->classMember;
                
                $originalNote = 'Cảnh báo: Dùng thiết bị này điểm danh hộ cho MSSV ' . $classMember->student_code;
                if (!str_contains($originalCheaterRecord->note ?? '', $originalNote)) {
                    $originalCheaterRecord->update([
                        'note' => ($originalCheaterRecord->note ? $originalCheaterRecord->note . ' | ' : '') . $originalNote,
                    ]);
                }

                $currentNote = 'Cảnh báo: Nhờ sinh viên MSSV ' . ($originalMember->student_code ?? 'Khác') . ' điểm danh hộ';
                if (!str_contains($record->note ?? '', $currentNote)) {
                    $record->update([
                        'note' => ($record->note ? $record->note . ' | ' : '') . $currentNote,
                        'ip_address' => $request->ip(),
                        'device_fingerprint' => $deviceId,
                        'gps_fraud_flag' => 'device_duplicate',
                    ]);
                }

                app(\App\Services\NotificationService::class)->notifyDeviceDuplicate(
                    $session->courseClass->owner_user_id,
                    $classMember->user_id ?? null,
                    $originalMember->user_id ?? null,
                    $session,
                    $classMember->full_name ?? 'Sinh viên',
                    $originalMember->full_name ?? 'Sinh viên'
                );

                return back()->with('error', 'LỖI: Thiết bị này đã được sử dụng để điểm danh. Hệ thống đã lưu vết gian lận của cả người điểm danh hộ và người nhờ!');
            }
        }

        // Lấy tọa độ và Metadata từ request
        $lat = $request->input('latitude');
        $lng = $request->input('longitude');
        $accuracy = $request->input('accuracy'); // Metadata Lớp 2
        $altitude = $request->input('altitude'); // Metadata Lớp 2
        $distanceMeters = 0;
        
        // Khởi tạo cờ kiểm tra Lớp 2
        $isSuspiciousGps = false;
        $suspiciousNote = '';

        if ($session->gps_radius && $session->gps_latitude && $session->gps_longitude) {
            if (empty($lat) || empty($lng)) {
                return back()->with('error', 'Phiên điểm danh yêu cầu xác minh vị trí GPS. Vui lòng cấp quyền và bật vị trí trên trình duyệt.');
            }

            $distanceMeters = $this->calculateDistance((float) $lat, (float) $lng, (float) $session->gps_latitude, (float) $session->gps_longitude);
            if ($distanceMeters > $session->gps_radius) {
                return back()->with('error', 'Vị trí của bạn quá xa lớp học (' . round($distanceMeters) . 'm). Bán kính cho phép là ' . $session->gps_radius . 'm.');
            }
            
            // Logic Lớp 2: Phân tích Metadata bắt Fake GPS
            if ($accuracy !== null && fmod((float) $accuracy, 1) === 0.0) {
                $isSuspiciousGps = true;
                $suspiciousNote .= 'Nghi ngờ Fake GPS (Sai số cố định ' . $accuracy . 'm). ';
            }

            if ($altitude !== null && (float) $altitude === 0.0) {
                $isSuspiciousGps = true;
                $suspiciousNote .= 'Nghi ngờ Fake GPS (Độ cao = 0). ';
            }
        }

        // Xác định trạng thái Đúng giờ / Đi muộn
        $status = 'present';
        if ($session->start_time) {
            $startTime = Carbon::parse($session->date->format('Y-m-d') . ' ' . $session->start_time);
            if (now()->greaterThan($startTime->addMinutes(15))) {
                $status = 'late';
            }
        }

        // Cập nhật note nếu phát hiện gian lận Lớp 2
        $finalNote = $record->note;
        if ($isSuspiciousGps) {
            $finalNote = ($finalNote ? $finalNote . ' | ' : '') . trim($suspiciousNote);
        }

        // Lưu toàn bộ thông tin (bao gồm cả dữ liệu Lớp 2) vào database
        $record->update([
            'status' => $status,
            'check_in_time' => now('Asia/Ho_Chi_Minh'),
            'distance_meters' => $distanceMeters,
            'accuracy' => $accuracy,      // Lưu vào DB
            'altitude' => $altitude,      // Lưu vào DB
            'note' => $finalNote,         // Cập nhật lưu vết cảnh báo
            'is_account' => true,
            'ip_address' => $request->ip(),
            'device_fingerprint' => $deviceId,
        ]);

        $response = back()->with('success', 'Điểm danh thành công!');
        
        if ($isNewDevice) {
            Cookie::queue(cookie('device_fingerprint', $deviceId, 60 * 24 * 365));
        }

        return back()->with('success', 'Điểm danh thành công!');
    }

    private function initializeRecord(int $sessionId, int $memberId): AttendanceRecord
    {
        return AttendanceRecord::query()
            ->firstOrCreate([
                'class_session_id' => $sessionId,
                'class_member_id' => $memberId,
            ], [
                'status' => 'pending',
                'is_account' => true,
            ]);
    }

    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;
        
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);
            
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }
}