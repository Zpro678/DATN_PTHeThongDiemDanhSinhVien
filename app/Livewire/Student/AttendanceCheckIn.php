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
            'studentCode' => 'required|string|max:20',
        ], [
            'studentCode.required' => 'Vui lòng nhập Mã số sinh viên.',
        ]);

        $classMember = $this->session->courseClass->members()
            ->whereHas('profile', fn ($p) => $p->where('student_code', $this->studentCode))
            ->where('status', \App\Models\ClassMember::STATUS_ACTIVE)
            ->first();

        if (!$classMember) {
            $this->addError('studentCode', 'Không tìm thấy sinh viên có mã này trong danh sách lớp.');
            return;
        }
        
        $this->memberId = $classMember->id;
        $this->isGuestForm = false;
        $this->initializeRecord($classMember->id);
        $this->isAutoCheckIn = true;
    }

    public function checkIn(?string $gpsCheckToken = null): void
    {
        if (!$this->session || !$this->record) {
            return;
        }

        // Re-validate expiration just in case
        if ($this->session->status === 'closed') {
            $this->statusMessage = 'Phiên điểm danh này đã kết thúc.';
            return;
        }

        if ($this->session->token_expires_at && $this->session->token_expires_at->isPast()) {
            $this->statusMessage = 'Mã QR này đã hết hạn. Vui lòng quét lại mã mới.';
            return;
        }

        $status = 'present';
        // Check if late based on start time
        if ($this->session->start_time) {
            $startTime = \Carbon\Carbon::parse($this->session->date->format('Y-m-d') . ' ' . $this->session->start_time);
            if (now()->greaterThan($startTime->addMinutes(15))) {
                $status = 'late';
            }
        }

        $this->isGpsError = false;
        $distanceMeters = null;
        $gpsAccuracy = null;
        $gpsLatRecorded = null;
        $gpsLngRecorded = null;
        $gpsFraudFlag = null;

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
            }
        }

        $this->record->update([
            'status' => $gpsFraudFlag === 'out_of_radius' ? 'invalid' : $status,
            'check_in_time' => now(),
            'distance_meters' => $distanceMeters,
            'gps_accuracy_meters' => $gpsAccuracy,
            'gps_latitude_recorded' => $gpsLatRecorded,
            'gps_longitude_recorded' => $gpsLngRecorded,
            'gps_fraud_flag' => $gpsFraudFlag,
            'is_account' => true,
        ]);

        if ($gpsFraudFlag === 'out_of_radius') {
            $this->isSuccess = false;
            $this->statusMessage = 'Vị trí của bạn quá xa lớp học (' . round($distanceMeters) . 'm).';
            return;
        }

        $this->isSuccess = true;
        $this->statusMessage = 'Điểm danh thành công!';
        
        session()->flash('success', 'Điểm danh thành công!');
    }

    public function render(): View
    {
        return view('livewire.student.attendance-check-in')
            ->title('Điểm danh lớp học');
    }
}
