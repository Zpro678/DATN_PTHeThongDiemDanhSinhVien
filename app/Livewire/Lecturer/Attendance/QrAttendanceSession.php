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

/**
 * MÀN HÌNH ĐIỂM DANH BẰNG QR (phía giảng viên).
 *
 * Đây là một Livewire Component: một class PHP nhưng hoạt động như một trang động —
 * mỗi lần người dùng bấm nút, Livewire gửi AJAX về server, chạy đúng method tương ứng
 * rồi render lại HTML và "vá" (morph) vào DOM, không cần viết JavaScript.
 *
 * Trang này lo 4 việc:
 *  1. Sinh và XOAY mã QR (token đổi liên tục để chống chụp màn hình gửi cho bạn vắng).
 *  2. Hiển thị bảng danh sách học viên + trạng thái điểm danh, cập nhật realtime khi SV quét.
 *  3. Cho giảng viên sửa tay trạng thái / ghi chú, đánh dấu tất cả có mặt, chốt phiên.
 *  4. Soi gian lận: quét trùng thiết bị (điểm danh hộ) và quét ngoài bán kính GPS.
 *
 * Các thư viện / thành phần ngoài được dùng trong file:
 *  - Livewire\Component        : lớp cha của mọi component Livewire (có mount, render, dispatch...).
 *  - Livewire\WithPagination   : trait thêm khả năng phân trang giữ state qua các request Livewire.
 *  - Eloquent (Builder)        : ORM của Laravel — viết truy vấn SQL bằng cú pháp PHP.
 *  - Maatwebsite\Excel         : thư viện xuất/nhập file Excel (dùng ở exportExcel()).
 *  - SimpleSoftwareIO\QrCode   : thư viện sinh ảnh mã QR (SVG) từ một chuỗi/URL.
 *  - Illuminate\Support\Str    : bộ tiện ích xử lý chuỗi của Laravel (ở đây dùng Str::slug).
 *
 * Trait nội bộ OwnsAttendanceSessions cung cấp ownedSession(): lấy phiên theo id NHƯNG
 * chỉ khi người đang đăng nhập là chủ/đồng chủ lớp — nếu không sẽ abort 403/404.
 */
class QrAttendanceSession extends Component
{
    use OwnsAttendanceSessions, WithPagination;

    /** ID phiên điểm danh (class_sessions.id) đang mở trên màn hình. */
    public int $sessionId;

    /** Từ khoá tìm kiếm học viên (tên / email), bind từ ô search bằng wire:model. */
    public string $search = '';

    /** Bộ lọc đang bật: all|pending|present|late|absent|excused|invalid|same_device|out_of_radius. */
    public string $statusFilter = 'all';

    /**
     * State tạm của bảng, key = attendance_records.id:
     *  - $draftStatuses: trạng thái đang chọn ở nút radio (ghi xuống DB NGAY khi bấm).
     *  - $draftNotes   : ghi chú giảng viên đang gõ (ghi xuống DB TRỄ, lúc bấm Lưu).
     * Tách ra để radio và ô ghi chú giữ đúng giá trị sau mỗi lần Livewire render lại.
     */
    public array $draftStatuses = [];
    public array $draftNotes = [];

    /** Cờ phiên đã chốt — view dùng để khoá nút và dừng vòng lặp xoay QR. */
    public bool $isClosed = false;

    /**
     * Khởi tạo component — Livewire gọi MỘT LẦN khi trang được mở lần đầu.
     * ($session lấy từ tham số route, ví dụ /lecturer/attendance/qr/{session}.)
     */
    public function mount(int $session): void
    {
        // ownedSession(): lấy phiên + kiểm tra quyền sở hữu lớp (chặn xem phiên của lớp người khác).
        $model = $this->ownedSession($session);
        // Buổi hết giờ thì tự động chốt phiên này.
        // ?-> : nullsafe operator của PHP — nếu meeting là null thì bỏ qua, không lỗi.
        $model->meeting?->closeIfExpired();
        // refresh(): nạp lại dữ liệu model từ DB (vì closeIfExpired() vừa cập nhật status).
        $model->refresh();

        $this->sessionId = $model->id;
        $this->isClosed = $model->status === 'closed';

        // Mở thẳng bộ lọc "cùng 1 máy" khi giảng viên bấm "xem" từ thông báo điểm danh hộ.
        // request()->query('filter'): đọc tham số ?filter=... trên URL.
        if (request()->query('filter') === 'same_device') {
            $this->statusFilter = 'same_device';
        }

        $this->initDrafts();
    }

    /**
     * Chuẩn bị dữ liệu ban đầu cho bảng điểm danh:
     *  1. Tạo bù bản ghi 'pending' cho những học viên đang hoạt động mà chưa có dòng trong phiên
     *     (ví dụ SV được thêm vào lớp sau khi phiên đã mở) — để ai cũng xuất hiện trong bảng.
     *  2. Nạp trạng thái + ghi chú hiện có vào $draftStatuses / $draftNotes.
     */
    private function initDrafts(): void
    {
        $session = $this->ownedSession($this->sessionId);

        // Đảm bảo tất cả sinh viên đang hoạt động đều có bản ghi điểm danh trong phiên này
        // pluck('id'): lấy ra một Collection chỉ gồm cột id (thay vì cả object) cho nhẹ.
        $activeMembers = $session->courseClass->members()->where('status', \App\Models\ClassMember::STATUS_ACTIVE)->pluck('id');
        $existingRecordMemberIds = AttendanceRecord::query()
            ->where('class_session_id', $this->sessionId)
            ->pluck('class_member_id');

        // diff(): phép trừ tập hợp của Collection — ai có trong lớp mà chưa có bản ghi điểm danh.
        $missingMemberIds = $activeMembers->diff($existingRecordMemberIds);
        if ($missingMemberIds->isNotEmpty()) {
            // map()->toArray(): biến danh sách id thành mảng các dòng sẵn sàng insert.
            $newRecords = $missingMemberIds->map(fn ($memberId) => [
                'class_session_id' => $this->sessionId,
                'class_member_id' => $memberId,
                'status' => 'pending',
                // is_account: đánh dấu SV này có tài khoản trong hệ thống hay chỉ là tên trong danh sách.
                'is_account' => \App\Models\ClassMember::find($memberId)->user_id !== null,
                // now(): helper Laravel trả về thời điểm hiện tại (đối tượng Carbon).
                'created_at' => now(),
                'updated_at' => now(),
            ])->toArray();

            // insert(): ghi thẳng nhiều dòng bằng 1 câu SQL (nhanh, nhưng KHÔNG chạy event/mutator của model).
            AttendanceRecord::insert($newRecords);
        }

        $records = AttendanceRecord::query()
            ->where('class_session_id', $this->sessionId)
            // whereHas('classMember'): chỉ lấy bản ghi còn gắn với học viên tồn tại (bỏ dữ liệu mồ côi).
            ->whereHas('classMember')
            ->get(['id', 'status', 'note']);

        foreach ($records as $record) {
            $this->draftStatuses[$record->id] = $record->status;
            $this->draftNotes[$record->id] = $record->note ?? '';
        }
    }

    /**
     * Đổi bộ lọc trạng thái khi giảng viên bấm các chip lọc phía trên bảng.
     * Whitelist để người dùng không tự sửa request truyền giá trị lạ vào câu WHERE.
     */
    public function setStatusFilter(string $status): void
    {
        // abort_unless(): nếu điều kiện SAI thì dừng request với mã HTTP 422 (dữ liệu không hợp lệ).
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
            // whereNotNull(): chỉ xét bản ghi thực sự có thiết bị và đã quét (có giờ check-in).
            ->whereNotNull('device_id')
            ->whereNotNull('check_in_time')
            // groupBy + havingRaw: gom theo thiết bị rồi chỉ giữ nhóm có từ 2 học viên KHÁC NHAU trở lên.
            // havingRaw cho phép viết SQL thô vì Eloquent không có sẵn cú pháp COUNT(DISTINCT ...).
            ->groupBy('device_id')
            ->havingRaw('COUNT(DISTINCT class_member_id) > 1')
            ->pluck('device_id')
            // all(): đổi Collection thành mảng PHP thuần.
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
            // with(): eager-load quan hệ để tránh N+1 query khi lấy tên học viên bên dưới.
            ->with('classMember.profile')
            ->get(['id', 'device_id', 'class_member_id'])
            // Từ đây là xử lý trên Collection (bộ nhớ), không còn là SQL:
            ->groupBy('device_id')                       // gom các dòng theo thiết bị
            ->map(fn ($rows) => $rows
                ->map(fn ($row) => $row->classMember?->full_name) // lấy tên
                ->filter()                                        // bỏ null/rỗng
                ->unique()                                        // bỏ trùng tên
                ->values()                                        // đánh lại số thứ tự 0,1,2...
                ->all())
            ->all();
    }

    /** Xoá từ khoá tìm kiếm và đưa bộ lọc về "tất cả" (nút X trên ô tìm kiếm). */
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

        // rotateQrToken(): method của model ClassSession — sinh token mới + hạn dùng ngắn.
        $session->rotateQrToken();
    }

    /**
     * Giảng viên sửa TAY trạng thái của một học viên (nút Có mặt / Muộn / Vắng / Có phép).
     * Ghi thẳng xuống DB rồi tính lại tổng hợp chuyên cần của buổi.
     */
    public function setStatus(int $recordId, string $status): void
    {
        abort_unless(in_array($status, ['present', 'late', 'absent', 'excused'], true), 422);
        $this->ensureSessionIsOpen();

        $record = AttendanceRecord::query()
            ->where('class_session_id', $this->sessionId)
            // Chặn sửa chéo: bản ghi phải thuộc lớp do người đang đăng nhập quản lý.
            // managedBy(): scope trong CourseClass, phủ cả chủ lớp lẫn đồng chủ.
            ->whereHas('classSession.courseClass', fn ($query) => $query->managedBy(auth()->id()))
            // findOrFail(): tìm theo id, không thấy thì ném 404.
            ->findOrFail($recordId);

        $record->update([
            'status' => $status,
            // Có mặt/Muộn thì đóng dấu giờ điểm danh; Vắng/Có phép thì xoá giờ đi.
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
        // config(): đọc giá trị trong thư mục config/ — ở đây là prefix Redis tự thêm vào tên kênh.
        return (string) config('database.redis.options.prefix') . 'attendance.' . $this->sessionId;
    }

    /**
     * Nút "Đánh dấu tất cả có mặt": chuyển mọi bản ghi còn 'pending' sang 'present'.
     * Dùng khi mạng/QR trục trặc, giảng viên điểm danh miệng rồi chốt nhanh.
     */
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

        // session()->flash(): nhắn một thông báo chỉ sống qua 1 request kế tiếp (hiện toast rồi mất).
        session()->flash('success', 'Đã đánh dấu tất cả học viên chưa điểm danh là có mặt.');
    }

    /**
     * CHỐT phiên QR (không nhận điểm danh nữa):
     *  - đổi status phiên sang 'closed';
     *  - ai còn 'pending' bị tính 'absent';
     *  - tính lại chuyên cần, gửi thông báo cho giảng viên + từng học viên, ghi audit log;
     *  - quay về danh sách phiên của buổi.
     */
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

        // app(Class::class): lấy đối tượng service từ Service Container của Laravel (tự new + tiêm phụ thuộc).
        $notifier = app(NotificationService::class);
        $notifier->attendanceSessionClosed((int) auth()->id(), $session, isQr: true);
        // Báo trạng thái điểm danh của phiên QR cho từng học viên.
        $notifier->notifyQrSessionResults($session);

        // Ghi nhật ký hệ thống: ai chốt phiên nào, lúc nào (phục vụ tra soát/khiếu nại).
        app(AuditLogService::class)->log('session_closed', [
            'class_id'   => $session->class_id,
            'table_name' => 'class_sessions',
            'row_id'     => $session->id,
            'new_values' => ['name' => $session->name, 'type' => 'qr'],
        ]);

        session()->flash('success', 'Phiên QR đã được chốt.');

        // Chốt xong quay về trang danh sách các phiên của buổi.
        // redirectRoute(..., navigate: true): điều hướng kiểu SPA của Livewire — đổi trang
        // không tải lại toàn bộ tài nguyên, giữ cảm giác mượt.
        $this->redirectRoute('lecturer.attendance.meeting.sessions', [
            'ma_user' => auth()->id(),
            'meeting' => $session->meeting_id,
        ], navigate: true);
    }

    /**
     * Lưu phiên QR rồi quay về trang chi tiết buổi.
     * Điểm danh QR đã được ghi trực tiếp khi sinh viên quét, nên ở đây chỉ điều hướng về buổi.
     * Không chốt sổ: phiên vẫn mở, chuyên cần sẽ tổng hợp khi buổi kết thúc.
     *
     * Thứ duy nhất thực sự được ghi ở đây là GHI CHÚ ($draftNotes) — vì ghi chú lưu trễ.
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
                // trim(): cắt khoảng trắng thừa hai đầu; chỉ UPDATE khi ghi chú thực sự đổi.
                $note = trim((string) $this->draftNotes[$record->id]);
                if ($note !== ($record->note ?? '')) {
                    $record->update(['note' => $note !== '' ? $note : null]);
                }
            }
        }

        // load('meeting'): nạp quan hệ buổi học vào model đã có sẵn (lazy eager loading).
        $session = $this->ownedSession($this->sessionId)->load('meeting');
        if ($session->meeting) {
            // Tính lại bảng tổng hợp chuyên cần của cả buổi từ các phiên con.
            AttendanceCalculator::syncSummaries($session->meeting);
        }

        $meetingId = $session->meeting_id;

        session()->flash('status', 'Đã lưu phiên điểm danh.');

        $this->redirectRoute('lecturer.attendance.meeting.sessions', [
            'ma_user' => auth()->id(),
            'meeting' => $meetingId,
        ], navigate: true);
    }

    /**
     * Xoá hẳn phiên điểm danh (kèm các bản ghi con theo ràng buộc khoá ngoại).
     * Chỉ cho xoá khi phiên CHƯA chốt — đã chốt là dữ liệu chuyên cần chính thức.
     */
    public function deleteSession()
    {
        $session = $this->ownedSession($this->sessionId);

        // abort_if(): ngược với abort_unless — điều kiện ĐÚNG thì dừng request (403 = cấm).
        abort_if($session->status === 'closed', 403, 'Không thể xóa phiên điểm danh đã chốt.');

        $classId = $session->class_id;
        $session->delete();

        session()->flash('success', 'Phiên điểm danh đã được xóa thành công.');

        // Trả về redirect thường (không navigate) vì trang phiên hiện tại đã biến mất.
        return redirect()->route('lecturer.attendance.index', ['ma_user' => auth()->id()]);
    }

    /**
     * Xuất danh sách điểm danh của phiên ra file Excel (.xlsx).
     * Là tính năng trả phí: gói dưới Pro sẽ bị đẩy sang trang nâng cấp.
     */
    public function exportExcel()
    {
        // Kiểm tra gói: xuất Excel là tính năng từ gói Pro trở lên.
        if (! app(SubscriptionService::class)->canExportExcel(auth()->user())) {
            session()->flash('upgrade_required', 'Xuất báo cáo Excel là tính năng của gói Pro trở lên. Vui lòng nâng cấp để sử dụng.');

            return $this->redirectRoute('upgrade', navigate: true);
        }

        $session = $this->ownedSession($this->sessionId)->load(['courseClass', 'meeting']);

        // format('Y-m-d'): định dạng ngày của Carbon; Str::slug(): bỏ dấu tiếng Việt + thay
        // khoảng trắng bằng "-" để tên file an toàn trên mọi hệ điều hành.
        $date = $session->date->format('Y-m-d');
        $className = Str::slug($session->courseClass->name);
        $fileName = "{$date}_{$className}.xlsx";

        // Excel::download(): thư viện Maatwebsite dựng file từ lớp Export rồi trả response tải về.
        return Excel::download(
            new ClassSessionExport($this->sessionId),
            $fileName
        );
    }

    /**
     * Dựng toàn bộ dữ liệu cho giao diện và trả view.
     * Livewire gọi render() sau MỖI tương tác (bấm nút, gõ tìm kiếm, refresh realtime),
     * nên mọi con số ở đây luôn là số mới nhất trong DB.
     */
    public function render(): View
    {
        $session = $this->ownedSession($this->sessionId)->load('courseClass');

        // ẢNH QR dựng từ qr_token XOAY (chống chụp màn hình gửi cho bạn vắng) — đổi mỗi nhịp làm mới.
        // route(): sinh URL đầy đủ từ TÊN route đã khai báo trong routes/web.php.
        $qrLink = route('attendance.check-in.guest', ['token' => $session->qr_token]);
        // LINK CHIA SẺ dựng từ share_token ỔN ĐỊNH — không đổi suốt lúc phiên mở (copy/gửi được).
        // Fallback về qr_token cho phiên cũ chưa có share_token (đề phòng chưa migrate).
        $attendanceLink = route('attendance.check-in.guest', ['token' => $session->share_token ?: $session->qr_token]);

        // Các device_id được từ 2 sinh viên trở lên dùng chung trong phiên (điểm danh hộ nghi vấn).
        $sharedDeviceIds = $this->sharedDeviceIds();
        $sharedDeviceNames = $this->sharedDeviceMemberNames($sharedDeviceIds);

        // ===== Danh sách hiển thị trong bảng, sau khi áp bộ lọc + tìm kiếm + sắp xếp =====
        $records = $session->attendanceRecords()
            ->whereHas('classMember')
            ->with('classMember.user')
            // Lọc "cùng 1 máy" theo device_id; "ngoài bán kính" tính lại theo khoảng cách đã lưu
            // (scope outOfRadius) chứ không theo cờ — cột gps_fraud_flag chỉ giữ được MỘT cờ nên
            // trùng thiết bị sẽ đè mất out_of_radius khi một học viên dính cả hai lỗi.
            // when($dk, $callback): chỉ áp thêm điều kiện khi $dk đúng — tránh viết if lồng nhau.
            ->when($this->statusFilter === 'same_device', fn (Builder $q) => $q->whereIn('device_id', $sharedDeviceIds ?: ['__none__']))
            ->when($this->statusFilter === 'out_of_radius', fn (Builder $q) => $q->outOfRadius($session->gps_radius))
            ->when(
                ! in_array($this->statusFilter, ['all', 'same_device', 'out_of_radius'], true),
                fn (Builder $q) => $q->where('status', $this->statusFilter),
            )
            ->when($this->search !== '', function (Builder $query): void {
                // where(function(){...}): bọc các điều kiện OR trong ngoặc đơn, để không "phá"
                // các điều kiện AND phía trên (SQL: ... AND (email LIKE ? OR name LIKE ?)).
                $query->where(function (Builder $query): void {
                    $query->whereHas('classMember.profile', fn (Builder $p) => $p->where('email', 'like', '%'.$this->search.'%')->orWhere('full_name', 'like', '%'.$this->search.'%'))
                        ->orWhereHas('classMember.user', fn (Builder $u) => $u->where('name', 'like', '%'.$this->search.'%'));
                });
            })
            // Ở chế độ "cùng 1 máy": gom các SV cùng device_id đứng cạnh nhau cho dễ đối chiếu.
            ->when($this->statusFilter === 'same_device', fn (Builder $query) => $query->orderBy('device_id'))
            // orderByRaw + CASE WHEN: đẩy các dòng NGHI VẤN (có cờ gian lận hoặc ghi chú cảnh báo)
            // lên đầu bảng (giá trị 0 đứng trước 1), phần còn lại giữ thứ tự theo id.
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
        // dispatch(): bắn một sự kiện từ PHP xuống trình duyệt cho Alpine/JS lắng nghe.
        $gpsScanLog = $this->gpsScanLog($session);
        $this->dispatch('gps-scan-log', scans: $gpsScanLog);

        // Số SV thuộc nhóm dùng chung máy (cho chip bộ lọc).
        $sameDeviceCount = $sharedDeviceIds === []
            ? 0
            : $session->attendanceRecords()->whereHas('classMember')->whereIn('device_id', $sharedDeviceIds)->count();

        // ===== Thống kê cho các thẻ số phía trên =====
        // selectRaw + groupBy + pluck('aggregate','status'): đếm theo trạng thái bằng MỘT câu SQL,
        // trả về mảng dạng ['present' => 12, 'absent' => 3, ...] thay vì kéo hết bản ghi về PHP.
        $stats = $session->attendanceRecords()
            ->whereHas('classMember')
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        // Tương tự nhưng đếm theo loại cờ gian lận GPS.
        $fraudStats = $session->attendanceRecords()
            ->whereHas('classMember')
            ->selectRaw('gps_fraud_flag, COUNT(*) as aggregate')
            ->whereNotNull('gps_fraud_flag')
            ->groupBy('gps_fraud_flag')
            ->pluck('aggregate', 'gps_fraud_flag');

        // Đếm lại "ngoài bán kính" theo khoảng cách thay vì theo cờ: học viên vừa trùng thiết bị
        // vừa ở ngoài bán kính chỉ mang cờ 'device_duplicate' nên nhóm theo cờ sẽ đếm thiếu.
        // put(): ghi đè khoá 'out_of_radius' trong Collection kết quả.
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

        // Tỉ lệ đã điểm danh = (có mặt + muộn) / sĩ số, làm tròn về số nguyên phần trăm.
        $summary['checked_in'] = $summary['present'] + $summary['late'];
        $summary['checked_in_percent'] = $summary['total'] > 0
            ? (int) round(($summary['checked_in'] / $summary['total']) * 100)
            : 0;

        $qrSvg = $this->qrSvg($qrLink);
        $qrCells = $this->fallbackQrCells($qrLink);

        $canExportExcel = app(SubscriptionService::class)->canExportExcel(auth()->user()); // Quyền xuất Excel theo gói (Pro trở lên).

        // compact('a','b'): tạo mảng ['a' => $a, 'b' => $b] để đẩy biến sang Blade.
        // layout(): chỉ định khung giao diện bao ngoài component.
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
            // Chỉ những lần quét thật (có giờ check-in) và có gửi kèm toạ độ.
            ->whereNotNull('check_in_time')
            ->whereNotNull('gps_latitude_recorded')
            ->orderBy('check_in_time')
            ->get()
            // map(): biến mỗi bản ghi thành một mảng gọn, đặt tên khoá tiếng Việt cho dễ đọc trong console.
            ->map(function (AttendanceRecord $record) use ($session): array {
                $distance = $record->distance_meters === null ? null : (float) $record->distance_meters;
                $accuracy = $record->gps_accuracy_meters === null ? null : (float) $record->gps_accuracy_meters;

                return [
                    'id' => $record->id,
                    'ten' => $record->classMember->full_name ?? 'Không xác định',
                    'lat' => (float) $record->gps_latitude_recorded,
                    'lng' => (float) $record->gps_longitude_recorded,
                    'accuracy_m' => $accuracy,
                    // round($x, 1): làm tròn 1 chữ số thập phân cho dễ đọc.
                    'khoang_cach_m' => $distance === null ? null : round($distance, 1),
                    // Đúng con số hệ thống dùng để phán "ngoài bán kính" (đã trừ sai số đo).
                    // max(0.0, ...): không cho ra số âm khi sai số lớn hơn khoảng cách.
                    'khoang_cach_hieu_dung_m' => $distance === null ? null : round(max(0.0, $distance - (float) ($accuracy ?? 0)), 1),
                    'ban_kinh_m' => $session->gps_radius === null ? null : (int) $session->gps_radius,
                    'vuot_m' => $record->metersOutsideRadius($session->gps_radius),
                    'co' => $record->gps_fraud_flag,
                    'luc' => $record->check_in_time?->format('H:i:s'),
                    // Link mở thẳng Google Maps tại vị trí quét để giảng viên kiểm chứng.
                    'google_maps' => 'https://maps.google.com/?q='.$record->gps_latitude_recorded.','.$record->gps_longitude_recorded,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Chốt chặn bảo vệ: mọi thao tác GHI (sửa trạng thái, lưu, đánh dấu tất cả) phải chạy qua đây.
     * Buổi đã chốt hoặc đã quá giờ kết thúc thì chặn 403 — tránh sửa dữ liệu sau khi đã khoá sổ.
     */
    private function ensureSessionIsOpen(): void
    {
        $session = $this->ownedSession($this->sessionId)->load('meeting');
        $meeting = $session->meeting;

        // Phiên lẻ không gắn buổi: chỉ cần xét status của chính phiên.
        if (! $meeting) {
            abort_if($session->status === 'closed', 403);
            return;
        }

        $meeting->closeIfExpired();
        $meeting->refresh();

        abort_if($meeting->status === 'closed' || $meeting->isExpired(), 403);
    }

    /**
     * Tính lại bảng tổng hợp chuyên cần của BUỔI chứa phiên này.
     * Gọi sau mỗi lần dữ liệu điểm danh đổi, để số liệu buổi/lớp không lệch với phiên.
     */
    private function syncCurrentMeetingSummaries(): void
    {
        $meeting = $this->ownedSession($this->sessionId)->load('meeting')->meeting;

        if ($meeting) {
            AttendanceCalculator::syncSummaries($meeting);
        }
    }

    /**
     * Sinh ảnh QR thật (chuỗi SVG) từ link điểm danh, bằng thư viện SimpleSoftwareIO/QrCode.
     * Trả null nếu thư viện chưa cài hoặc sinh lỗi — khi đó view dùng $qrCells làm ảnh giả.
     */
    private function qrSvg(string $attendanceLink): ?string
    {
        // class_exists(): kiểm tra thư viện có mặt trong dự án hay không (tránh vỡ trang nếu thiếu).
        if (! class_exists(QrCode::class)) {
            return null;
        }

        try {
            // format('svg'): xuất vector (nét sắc, không cần thư viện ảnh imagick).
            // size(280): cạnh 280px. margin(1): viền trắng 1 ô — QR cần viền mới quét được.
            // generate($link): nội dung nhúng vào mã, ở đây là URL điểm danh.
            return (string) QrCode::format('svg')
                ->size(280)
                ->margin(1)
                ->generate($attendanceLink);
        } catch (\Throwable) {
            // Bắt MỌI lỗi (kể cả Error) để trang phiên không chết chỉ vì không vẽ được QR.
            return null;
        }
    }

    /**
     * Ảnh QR GIẢ (chỉ để trang trí/giữ chỗ) khi không sinh được QR thật.
     * Tạo lưới 29x29 ô đen/trắng từ mã băm của link — trông giống QR nhưng KHÔNG quét được.
     *
     * @return array<int, bool>  true = ô đen, false = ô trắng, đọc theo thứ tự trái→phải, trên→dưới
     */
    private function fallbackQrCells(string $seed): array
    {
        // hash('sha256', $seed, true): băm link thành 32 byte nhị phân — cùng link thì cùng hình,
        // nên ảnh không nhảy lung tung giữa các lần render.
        $hash = hash('sha256', $seed, true);
        $cells = [];

        for ($index = 0; $index < 29 * 29; $index++) {
            $x = $index % 29;            // cột: phần dư
            $y = intdiv($index, 29);     // hàng: phép chia lấy phần nguyên
            $inFinder = $this->inFinderPattern($x, $y);

            // Ô nằm trong 3 ô vuông định vị thì tô theo khuôn, không tô ngẫu nhiên.
            if ($inFinder !== null) {
                $cells[] = $inFinder;

                continue;
            }

            // ord(): lấy mã số của một byte trong chuỗi hash; trộn thêm x, y để hoa văn không lặp lại.
            // % 5 < 2 => xấp xỉ 40% ô đen, nhìn giống mật độ của QR thật.
            $byte = ord($hash[$index % strlen($hash)]);
            $cells[] = (($byte + $x * 7 + $y * 13) % 5) < 2;
        }

        return $cells;
    }

    /**
     * Vẽ 3 ô vuông ĐỊNH VỊ ở góc trên-trái, trên-phải, dưới-trái của mã QR giả.
     * Mỗi ô là hình 7x7: viền ngoài đen + lõi 3x3 đen, ở giữa là vành trắng.
     *
     * @return bool|null  true/false = ô này thuộc khuôn định vị và nên tô đen/trắng;
     *                    null = không thuộc khuôn nào (để nơi gọi tô ngẫu nhiên).
     */
    private function inFinderPattern(int $x, int $y): ?bool
    {
        // Toạ độ góc trên-trái của 3 khuôn (lưới 29 ô nên khuôn thứ 3 bắt đầu ở 22 = 29 - 7).
        foreach ([[0, 0], [22, 0], [0, 22]] as [$originX, $originY]) {
            // Không nằm trong ô 7x7 này thì xét khuôn tiếp theo.
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
