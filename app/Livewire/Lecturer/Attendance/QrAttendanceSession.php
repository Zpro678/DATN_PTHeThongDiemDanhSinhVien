<?php

namespace App\Livewire\Lecturer\Attendance;

use App\Exports\ClassSessionExport;
use App\Livewire\Lecturer\Attendance\Concerns\OwnsAttendanceSessions;
use App\Models\AttendanceRecord;
use App\Services\AuditLogService;
use App\Services\AttendanceCalculator;
use App\Services\NotificationService;
use App\Services\SubscriptionService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrAttendanceSession extends Component
{
    use OwnsAttendanceSessions, WithPagination;

    public int $sessionId;

    public string $search = '';

    public string $statusFilter = 'all';

    public array $draftStatuses = [];
    public array $draftNotes = [];

    public bool $isClosed = false;

    public function mount(int $session): void
    {
        $model = $this->ownedSession($session);
        // Buổi hết giờ thì tự động chốt phiên này.
        $model->meeting?->closeIfExpired();
        $model->refresh();

        $this->sessionId = $model->id;
        $this->isClosed = $model->status === 'closed';

        // Mở thẳng bộ lọc "cùng 1 máy" khi giảng viên bấm "xem" từ thông báo điểm danh hộ.
        if (request()->query('filter') === 'same_device') {
            $this->statusFilter = 'same_device';
        }

        $this->initDrafts();
    }

    private function initDrafts(): void
    {
        $session = $this->ownedSession($this->sessionId);

        // Đảm bảo tất cả sinh viên đang hoạt động đều có bản ghi điểm danh trong phiên này
        $activeMembers = $session->courseClass->members()->where('status', \App\Models\ClassMember::STATUS_ACTIVE)->pluck('id');
        $existingRecordMemberIds = AttendanceRecord::query()
            ->where('class_session_id', $this->sessionId)
            ->pluck('class_member_id');
            
        $missingMemberIds = $activeMembers->diff($existingRecordMemberIds);
        if ($missingMemberIds->isNotEmpty()) {
            $newRecords = $missingMemberIds->map(fn ($memberId) => [
                'class_session_id' => $this->sessionId,
                'class_member_id' => $memberId,
                'status' => 'pending',
                'is_account' => \App\Models\ClassMember::find($memberId)->user_id !== null,
                'created_at' => now(),
                'updated_at' => now(),
            ])->toArray();
            
            AttendanceRecord::insert($newRecords);
        }

        $records = AttendanceRecord::query()
            ->where('class_session_id', $this->sessionId)
            ->whereHas('classMember')
            ->get(['id', 'status', 'note']);

        foreach ($records as $record) {
            $this->draftStatuses[$record->id] = $record->status;
            $this->draftNotes[$record->id] = $record->note ?? '';
        }
    }

    public function setStatusFilter(string $status): void
    {
        abort_unless(in_array($status, ['all', 'pending', 'present', 'late', 'absent', 'excused', 'invalid', 'same_device', 'out_of_radius'], true), 422);

        $this->statusFilter = $status;
    }

    /**
     * Các device_id được ≥2 sinh viên (đã điểm danh) dùng chung trong phiên này.
     * Là nền cho bộ lọc "cùng 1 máy" và số đếm hiển thị.
     *
     * @return array<int, string>
     */
    private function sharedDeviceIds(): array
    {
        return AttendanceRecord::query()
            ->where('class_session_id', $this->sessionId)
            ->whereNotNull('device_id')
            ->whereNotNull('check_in_time')
            ->groupBy('device_id')
            ->havingRaw('COUNT(DISTINCT class_member_id) > 1')
            ->pluck('device_id')
            ->all();
    }

    /**
     * Tên các học viên đã điểm danh trên CÙNG một thiết bị, gom theo device_id.
     *
     * Dùng để chỉ đích danh "trùng với ai" ở cột Ghi chú — biết số lượng thôi thì giảng viên
     * vẫn phải tự dò lại cả danh sách.
     *
     * @param  array<int, string>  $sharedDeviceIds
     * @return array<string, array<int, string>>  device_id => [tên học viên]
     */
    private function sharedDeviceMemberNames(array $sharedDeviceIds): array
    {
        if ($sharedDeviceIds === []) {
            return [];
        }

        return AttendanceRecord::query()
            ->where('class_session_id', $this->sessionId)
            ->whereIn('device_id', $sharedDeviceIds)
            ->whereNotNull('check_in_time')
            ->whereHas('classMember')
            ->with('classMember.profile')
            ->get(['id', 'device_id', 'class_member_id'])
            ->groupBy('device_id')
            ->map(fn ($rows) => $rows
                ->map(fn ($row) => $row->classMember?->full_name)
                ->filter()
                ->unique()
                ->values()
                ->all())
            ->all();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->statusFilter = 'all';
    }

    /**
     * Làm mới mã QR: XOAY token thật (không chỉ đổi ảnh).
     *
     * Được vòng lặp Alpine gọi mỗi qr_refresh_rate giây (và nút "Làm mới QR"). Mỗi lần
     * sinh token mới + đặt hạn ngắn, nên ảnh chụp mã cũ gửi đi sẽ hết hiệu lực ngay.
     * Nếu buổi đã quá giờ kết thúc thì đóng phiên và dừng xoay.
     */
    public function refreshToken(): void
    {
        $session = $this->ownedSession($this->sessionId);

        if ($session->status === 'closed') {
            $this->isClosed = true;
            return;
        }

        // Buổi hết giờ -> đóng phiên (chốt sổ), không xoay token nữa.
        if ($session->meeting && $session->meeting->closeIfExpired()) {
            $this->isClosed = true;
            return;
        }

        $session->rotateQrToken();
    }

    public function setStatus(int $recordId, string $status): void
    {
        abort_unless(in_array($status, ['present', 'late', 'absent', 'excused'], true), 422);
        $this->ensureSessionIsOpen();

        $record = AttendanceRecord::query()
            ->where('class_session_id', $this->sessionId)
            ->whereHas('classSession.courseClass', fn ($query) => $query->managedBy(auth()->id()))
            ->findOrFail($recordId);

        $record->update([
            'status' => $status,
            'check_in_time' => in_array($status, ['present', 'late'], true) ? now('Asia/Ho_Chi_Minh') : null,
            'is_account' => $record->classMember->user_id !== null,
        ]);

        $this->draftStatuses[$recordId] = $status;
        $this->syncCurrentMeetingSummaries();
    }

    /**
     * Tên kênh socket.io mà bảng điểm danh cần lắng nghe cho phiên hiện tại.
     *
     * StudentCheckedIn broadcast qua Redis (kèm prefix), server.cjs relay sang socket.io;
     * client (Alpine x-init) nghe đúng kênh này rồi gọi $wire.$refresh() để cập nhật realtime.
     * (Dùng socket.io thô thay vì Echo vì server.cjs không nói giao thức của laravel-echo.)
     */
    public function realtimeChannel(): string
    {
        return (string) config('database.redis.options.prefix') . 'attendance.' . $this->sessionId;
    }

    public function markAllPresent(): void
    {
        $this->ensureSessionIsOpen();

        $records = AttendanceRecord::query()
            ->where('class_session_id', $this->sessionId)
            ->whereHas('classSession.courseClass', fn ($query) => $query->managedBy(auth()->id()))
            ->where('status', 'pending')
            ->get();

        foreach ($records as $record) {
            $record->update([
                'status' => 'present',
                'check_in_time' => now('Asia/Ho_Chi_Minh'),
            ]);
            $this->draftStatuses[$record->id] = 'present';
        }

        $this->syncCurrentMeetingSummaries();

        session()->flash('success', 'Đã đánh dấu tất cả học viên chưa điểm danh là có mặt.');
    }

    public function closeSession(): void
    {
        $session = $this->ownedSession($this->sessionId);

        // Idempotent: phiên đã chốt thì không xử lý/gửi thông báo lại.
        if ($session->status === 'closed') {
            $this->isClosed = true;
            return;
        }

        $session->update(['status' => 'closed']);

        // Mặc định những ai chưa điểm danh (pending) khi khóa phiên QR sẽ thành vắng (absent)
        $pendingIds = $session->attendanceRecords()->where('status', 'pending')->pluck('id');
        $session->attendanceRecords()->where('status', 'pending')->update(['status' => 'absent']);

        // Đồng bộ draft để các nút trạng thái đổi sang "Vắng" ngay (realtime), không lệch với DB.
        foreach ($pendingIds as $recordId) {
            $this->draftStatuses[$recordId] = 'absent';
        }

        $this->isClosed = true;
        $this->syncCurrentMeetingSummaries();

        $notifier = app(NotificationService::class);
        $notifier->attendanceSessionClosed((int) auth()->id(), $session, isQr: true);
        // Báo trạng thái điểm danh của phiên QR cho từng học viên.
        $notifier->notifyQrSessionResults($session);

        app(AuditLogService::class)->log('session_closed', [
            'class_id'   => $session->class_id,
            'table_name' => 'class_sessions',
            'row_id'     => $session->id,
            'new_values' => ['name' => $session->name, 'type' => 'qr'],
        ]);

        session()->flash('success', 'Phiên QR đã được chốt.');

        // Chốt xong quay về trang danh sách các phiên của buổi.
        $this->redirectRoute('lecturer.attendance.meeting.sessions', [
            'ma_user' => auth()->id(),
            'meeting' => $session->meeting_id,
        ], navigate: true);
    }

    /**
     * Lưu phiên QR rồi quay về trang chi tiết buổi.
     * Điểm danh QR đã được ghi trực tiếp khi sinh viên quét, nên ở đây chỉ điều hướng về buổi.
     * Không chốt sổ: phiên vẫn mở, chuyên cần sẽ tổng hợp khi buổi kết thúc.
     */
    public function saveSession(): void
    {
        $this->ensureSessionIsOpen();

        $records = AttendanceRecord::query()
            ->where('class_session_id', $this->sessionId)
            ->whereHas('classSession.courseClass', fn ($query) => $query->managedBy(auth()->id()))
            ->get();

        foreach ($records as $record) {
            if (isset($this->draftNotes[$record->id])) {
                $note = trim((string) $this->draftNotes[$record->id]);
                if ($note !== ($record->note ?? '')) {
                    $record->update(['note' => $note !== '' ? $note : null]);
                }
            }
        }

        $session = $this->ownedSession($this->sessionId)->load('meeting');
        if ($session->meeting) {
            AttendanceCalculator::syncSummaries($session->meeting);
        }

        $meetingId = $session->meeting_id;

        session()->flash('status', 'Đã lưu phiên điểm danh.');

        $this->redirectRoute('lecturer.attendance.meeting.sessions', [
            'ma_user' => auth()->id(),
            'meeting' => $meetingId,
        ], navigate: true);
    }

    public function deleteSession()
    {
        $session = $this->ownedSession($this->sessionId);

        abort_if($session->status === 'closed', 403, 'Không thể xóa phiên điểm danh đã chốt.');

        $classId = $session->class_id;
        $session->delete();

        session()->flash('success', 'Phiên điểm danh đã được xóa thành công.');

        return redirect()->route('lecturer.attendance.index', ['ma_user' => auth()->id()]);
    }

    public function exportExcel()
    {
        // Kiểm tra gói: xuất Excel là tính năng từ gói Pro trở lên.
        if (! app(SubscriptionService::class)->canExportExcel(auth()->user())) {
            session()->flash('upgrade_required', 'Xuất báo cáo Excel là tính năng của gói Pro trở lên. Vui lòng nâng cấp để sử dụng.');

            return $this->redirectRoute('upgrade', navigate: true);
        }

        $session = $this->ownedSession($this->sessionId)->load(['courseClass', 'meeting']);

        $date = $session->date->format('Y-m-d');
        $className = Str::slug($session->courseClass->name);
        $fileName = "{$date}_{$className}.xlsx";

        return Excel::download(
            new ClassSessionExport($this->sessionId),
            $fileName
        );
    }

    public function render(): View
    {
        $session = $this->ownedSession($this->sessionId)->load('courseClass');
        
        // ẢNH QR dựng từ qr_token XOAY (chống chụp màn hình gửi cho bạn vắng) — đổi mỗi nhịp làm mới.
        $qrLink = route('attendance.check-in.guest', ['token' => $session->qr_token]);
        // LINK CHIA SẺ dựng từ share_token ỔN ĐỊNH — không đổi suốt lúc phiên mở (copy/gửi được).
        // Fallback về qr_token cho phiên cũ chưa có share_token (đề phòng chưa migrate).
        $attendanceLink = route('attendance.check-in.guest', ['token' => $session->share_token ?: $session->qr_token]);

        // Các device_id được từ 2 sinh viên trở lên dùng chung trong phiên (điểm danh hộ nghi vấn).
        $sharedDeviceIds = $this->sharedDeviceIds();
        $sharedDeviceNames = $this->sharedDeviceMemberNames($sharedDeviceIds);

        $records = $session->attendanceRecords()
            ->whereHas('classMember')
            ->with('classMember.user')
            // Lọc "cùng 1 máy" theo device_id; "ngoài bán kính" tính lại theo khoảng cách đã lưu
            // (scope outOfRadius) chứ không theo cờ — cột gps_fraud_flag chỉ giữ được MỘT cờ nên
            // trùng thiết bị sẽ đè mất out_of_radius khi một học viên dính cả hai lỗi.
            ->when($this->statusFilter === 'same_device', fn (Builder $q) => $q->whereIn('device_id', $sharedDeviceIds ?: ['__none__']))
            ->when($this->statusFilter === 'out_of_radius', fn (Builder $q) => $q->outOfRadius($session->gps_radius))
            ->when(
                ! in_array($this->statusFilter, ['all', 'same_device', 'out_of_radius'], true),
                fn (Builder $q) => $q->where('status', $this->statusFilter),
            )
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query->whereHas('classMember.profile', fn (Builder $p) => $p->where('email', 'like', '%'.$this->search.'%')->orWhere('full_name', 'like', '%'.$this->search.'%'))
                        ->orWhereHas('classMember.user', fn (Builder $u) => $u->where('name', 'like', '%'.$this->search.'%'));
                });
            })
            // Ở chế độ "cùng 1 máy": gom các SV cùng device_id đứng cạnh nhau cho dễ đối chiếu.
            ->when($this->statusFilter === 'same_device', fn (Builder $query) => $query->orderBy('device_id'))
            ->orderByRaw("CASE WHEN gps_fraud_flag IN ('device_duplicate', 'out_of_radius', 'impossible_travel', 'suspected_mock') OR note LIKE '%Cảnh báo:%' OR note LIKE '%Nghi ngờ%' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->get();

        // Đồng bộ trạng thái nút chọn (radio) với DB mỗi lần render. Khi SV tự quét QR điểm danh,
        // status trong DB đổi nhưng $draftStatuses (state Livewire) vẫn giữ giá trị lúc mount ->
        // sau $wire.$refresh() realtime, wire:model morph lại radio về giá trị cũ (nhãn chữ thì
        // đã đúng vì đọc thẳng $record->status). Resync ở đây để radio khớp ngay, khỏi phải F5.
        // Chỉ đồng bộ status (luôn được ghi ngay), KHÔNG đụng draftNotes vì ghi chú lưu trễ (saveSession),
        // resync sẽ xóa ghi chú giảng viên đang gõ dở.
        foreach ($records as $record) {
            $this->draftStatuses[$record->id] = $record->status;
        }

        // Toạ độ từng lần quét, bơm xuống console F12 của trang phiên để đối chiếu với tâm lớp.
        // Vừa truyền cho view (lần tải đầu) vừa dispatch (các lần morph sau — x-init KHÔNG chạy
        // lại khi Livewire refresh nên chỉ dựa vào view thì người quét sau sẽ không bao giờ hiện).
        $gpsScanLog = $this->gpsScanLog($session);
        $this->dispatch('gps-scan-log', scans: $gpsScanLog);

        // Số SV thuộc nhóm dùng chung máy (cho chip bộ lọc).
        $sameDeviceCount = $sharedDeviceIds === []
            ? 0
            : $session->attendanceRecords()->whereHas('classMember')->whereIn('device_id', $sharedDeviceIds)->count();

        $stats = $session->attendanceRecords()
            ->whereHas('classMember')
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $fraudStats = $session->attendanceRecords()
            ->whereHas('classMember')
            ->selectRaw('gps_fraud_flag, COUNT(*) as aggregate')
            ->whereNotNull('gps_fraud_flag')
            ->groupBy('gps_fraud_flag')
            ->pluck('aggregate', 'gps_fraud_flag');

        // Đếm lại "ngoài bán kính" theo khoảng cách thay vì theo cờ: học viên vừa trùng thiết bị
        // vừa ở ngoài bán kính chỉ mang cờ 'device_duplicate' nên nhóm theo cờ sẽ đếm thiếu.
        $fraudStats->put('out_of_radius', $session->attendanceRecords()
            ->whereHas('classMember')
            ->outOfRadius($session->gps_radius)
            ->count());

        $summary = [
            'present' => (int) ($stats['present'] ?? 0),
            'late' => (int) ($stats['late'] ?? 0),
            'excused' => (int) ($stats['excused'] ?? 0),
            'absent' => (int) ($stats['absent'] ?? 0),
            'pending' => (int) ($stats['pending'] ?? 0),
            'total' => $session->attendanceRecords()->whereHas('classMember')->count(),
        ];

        $summary['checked_in'] = $summary['present'] + $summary['late'];
        $summary['checked_in_percent'] = $summary['total'] > 0
            ? (int) round(($summary['checked_in'] / $summary['total']) * 100)
            : 0;

        $qrSvg = $this->qrSvg($qrLink);
        $qrCells = $this->fallbackQrCells($qrLink);

        $canExportExcel = app(SubscriptionService::class)->canExportExcel(auth()->user()); // Quyền xuất Excel theo gói (Pro trở lên).

        return view('livewire.lecturer.attendance.qr-session', compact('session', 'records', 'attendanceLink', 'summary', 'fraudStats', 'qrSvg', 'qrCells', 'canExportExcel', 'sameDeviceCount', 'sharedDeviceIds', 'sharedDeviceNames', 'gpsScanLog'))
            ->layout('layouts.user', ['title' => 'Điểm danh QR']);
    }

    /**
     * Toạ độ của TỪNG lần quét trong phiên, để trang phiên in ra console F12 cho giảng viên
     * đối chiếu với tâm lớp. Truy vấn RIÊNG, KHÔNG theo bộ lọc/tìm kiếm đang bật — bật lọc mà
     * vẫn phải thấy đủ người vừa quét thì log mới dùng để soi được.
     *
     * @return array<int, array<string, mixed>>
     */
    private function gpsScanLog(\App\Models\ClassSession $session): array
    {
        return $session->attendanceRecords()
            ->whereHas('classMember')
            ->with('classMember.profile', 'classMember.user')
            ->whereNotNull('check_in_time')
            ->whereNotNull('gps_latitude_recorded')
            ->orderBy('check_in_time')
            ->get()
            ->map(function (AttendanceRecord $record) use ($session): array {
                $distance = $record->distance_meters === null ? null : (float) $record->distance_meters;
                $accuracy = $record->gps_accuracy_meters === null ? null : (float) $record->gps_accuracy_meters;

                return [
                    'id' => $record->id,
                    'ten' => $record->classMember->full_name ?? 'Không xác định',
                    'lat' => (float) $record->gps_latitude_recorded,
                    'lng' => (float) $record->gps_longitude_recorded,
                    'accuracy_m' => $accuracy,
                    'khoang_cach_m' => $distance === null ? null : round($distance, 1),
                    // Đúng con số hệ thống dùng để phán "ngoài bán kính" (đã trừ sai số đo).
                    'khoang_cach_hieu_dung_m' => $distance === null ? null : round(max(0.0, $distance - (float) ($accuracy ?? 0)), 1),
                    'ban_kinh_m' => $session->gps_radius === null ? null : (int) $session->gps_radius,
                    'vuot_m' => $record->metersOutsideRadius($session->gps_radius),
                    'co' => $record->gps_fraud_flag,
                    'luc' => $record->check_in_time?->format('H:i:s'),
                    'google_maps' => 'https://maps.google.com/?q='.$record->gps_latitude_recorded.','.$record->gps_longitude_recorded,
                ];
            })
            ->values()
            ->all();
    }

    private function ensureSessionIsOpen(): void
    {
        $session = $this->ownedSession($this->sessionId)->load('meeting');
        $meeting = $session->meeting;

        if (! $meeting) {
            abort_if($session->status === 'closed', 403);
            return;
        }

        $meeting->closeIfExpired();
        $meeting->refresh();

        abort_if($meeting->status === 'closed' || $meeting->isExpired(), 403);
    }

    private function syncCurrentMeetingSummaries(): void
    {
        $meeting = $this->ownedSession($this->sessionId)->load('meeting')->meeting;

        if ($meeting) {
            AttendanceCalculator::syncSummaries($meeting);
        }
    }

    private function qrSvg(string $attendanceLink): ?string
    {
        if (! class_exists(QrCode::class)) {
            return null;
        }

        try {
            return (string) QrCode::format('svg')
                ->size(280)
                ->margin(1)
                ->generate($attendanceLink);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<int, bool>
     */
    private function fallbackQrCells(string $seed): array
    {
        $hash = hash('sha256', $seed, true);
        $cells = [];

        for ($index = 0; $index < 29 * 29; $index++) {
            $x = $index % 29;
            $y = intdiv($index, 29);
            $inFinder = $this->inFinderPattern($x, $y);

            if ($inFinder !== null) {
                $cells[] = $inFinder;

                continue;
            }

            $byte = ord($hash[$index % strlen($hash)]);
            $cells[] = (($byte + $x * 7 + $y * 13) % 5) < 2;
        }

        return $cells;
    }

    private function inFinderPattern(int $x, int $y): ?bool
    {
        foreach ([[0, 0], [22, 0], [0, 22]] as [$originX, $originY]) {
            if ($x < $originX || $x > $originX + 6 || $y < $originY || $y > $originY + 6) {
                continue;
            }

            $edge = $x === $originX || $x === $originX + 6 || $y === $originY || $y === $originY + 6;
            $center = $x >= $originX + 2 && $x <= $originX + 4 && $y >= $originY + 2 && $y <= $originY + 4;

            return $edge || $center;
        }

        return null;
    }
}
