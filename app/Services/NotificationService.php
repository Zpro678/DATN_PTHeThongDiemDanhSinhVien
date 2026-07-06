<?php

namespace App\Services;

use App\Events\NotificationReceived;
use App\Models\ClassMeeting;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\AttendanceResultNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Cung cấp dữ liệu thông báo cho giao diện (dropdown trên thanh điều hướng...).
 *
 * Service đọc bảng `notifications` chuẩn của Laravel (DatabaseNotification):
 * mỗi bản ghi gồm `type`, `data` (json: title/message/url/level) và `read_at`.
 * Phần style (icon, màu sắc) được để cho view tự quyết định dựa trên `level`,
 * giúp Tailwind quét được class và tách bạch dữ liệu với trình bày.
 */
class NotificationService
{
    /** Số thông báo tối đa hiển thị trong dropdown thanh điều hướng. */
    private const DROPDOWN_LIMIT = 50;

    /**
     * Danh mục thông báo cho rail lọc ở trang "Tất cả thông báo".
     *
     * Mỗi danh mục gồm nhãn, icon và các từ khóa nhận diện trong `type`
     * (tên lớp App\Notifications\...). Bản ghi không khớp danh mục nào sẽ
     * thuộc nhóm 'system'.
     *
     * @var array<string, array{label: string, icon: string, keywords: array<int, string>}>
     */
    private const CATEGORIES = [
        'attendance' => ['label' => 'Điểm danh', 'icon' => 'calendar-check', 'keywords' => ['attendance', 'qr', 'checkin', 'session', 'reminder']],
        'leave' => ['label' => 'Đơn xin nghỉ', 'icon' => 'file-text', 'keywords' => ['leave']],
        'warning' => ['label' => 'Cảnh báo', 'icon' => 'alert-triangle', 'keywords' => ['warning', 'absence', 'banned', 'risk']],
    ];

    /**
     * Lấy dữ liệu thông báo cho dropdown ở thanh điều hướng.
     *
     * Tối ưu truy vấn:
     * - 1 query nạp đúng DROPDOWN_LIMIT bản ghi mới nhất, chỉ chọn các cột cần dùng.
     * - 1 query đếm số chưa đọc bằng aggregate COUNT (không nạp toàn bộ bản ghi).
     *
     * @return array{items: array<int, array<string, mixed>>, unread_count: int, has_unread: bool}
     */
    public function getDropdownData(?User $user): array
    {
        if (! $user) {
            return ['items' => [], 'unread_count' => 0, 'has_unread' => false];
        }

        // notifications() đã được sắp xếp mới nhất trước (xem App\Models\User).
        $items = $user->notifications()
            ->take(self::DROPDOWN_LIMIT)
            ->get(['id', 'type', 'data', 'read_at', 'created_at'])
            ->map(fn (Notification $notification): array => $this->present($notification))
            ->all();

        $unreadCount = $user->notifications()->whereNull('read_at')->count();

        return [
            'items' => $items,
            'unread_count' => $unreadCount,
            'has_unread' => $unreadCount > 0,
        ];
    }

    /**
     * Xóa một thông báo của người dùng (chỉ chủ sở hữu mới xóa được).
     *
     * @return bool true nếu có bản ghi bị xóa; false nếu không tìm thấy/không thuộc user.
     */
    public function deleteForUser(?User $user, string $notificationId): bool
    {
        if (! $user) {
            return false;
        }

        return $user->notifications()
            ->whereKey($notificationId)
            ->delete() > 0;
    }

    /**
     * Xóa toàn bộ thông báo của người dùng hiện tại.
     *
     * @return int Số bản ghi đã xóa.
     */
    public function deleteAllForUser(?User $user): int
    {
        if (! $user) {
            return 0;
        }

        return $user->notifications()->delete();
    }

    /**
     * Lấy danh sách thông báo có phân trang cho trang "Tất cả thông báo".
     *
     * Dùng phân trang chuẩn của Laravel/Livewire (theme tailwind đã publish ở
     * resources/views/vendor/livewire/tailwind.blade.php) nên view chỉ cần gọi
     * {{ $notifications->links() }}. `through()` chuyển đổi từng bản ghi mà vẫn
     * giữ nguyên thông tin phân trang.
     *
     * @param string $filter Bộ lọc đang chọn: all | unread | system | <khóa danh mục>.
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function paginateForUser(?User $user, string $filter = 'all', int $perPage = 12): LengthAwarePaginator
    {
        if (! $user) {
            return new LengthAwarePaginator([], 0, $perPage, LengthAwarePaginator::resolveCurrentPage());
        }

        $query = $user->notifications();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif (isset(self::CATEGORIES[$filter])) {
            // Lọc theo từ khóa của danh mục (khớp bất kỳ từ khóa nào).
            $query->where(function ($q) use ($filter) {
                foreach (self::CATEGORIES[$filter]['keywords'] as $keyword) {
                    $q->orWhere('type', 'like', '%'.$keyword.'%');
                }
            });
        } elseif ($filter === 'system') {
            // Không khớp từ khóa của bất kỳ danh mục nào.
            $query->where(function ($q) {
                foreach (self::CATEGORIES as $meta) {
                    foreach ($meta['keywords'] as $keyword) {
                        $q->where('type', 'not like', '%'.$keyword.'%');
                    }
                }
            });
        }

        return $query
            ->paginate($perPage, ['id', 'type', 'data', 'read_at', 'created_at'])
            ->through(fn (Notification $notification): array => $this->present($notification));
    }

    /**
     * Xây dựng các mục cho rail lọc bên trái kèm số đếm (1 query nạp 2 cột nhẹ).
     *
     * @return array<int, array{key: string, label: string, icon: string, count: int}>
     */
    public function getNavItems(?User $user): array
    {
        $counts = ['all' => 0, 'unread' => 0, 'system' => 0];
        foreach (array_keys(self::CATEGORIES) as $key) {
            $counts[$key] = 0;
        }

        if ($user) {
            $rows = $user->notifications()->get(['type', 'read_at']);
            $counts['all'] = $rows->count();
            $counts['unread'] = $rows->whereNull('read_at')->count();

            foreach ($rows as $row) {
                $counts[$this->categoryOf($row->type)]++;
            }
        }

        $items = [
            ['key' => 'all', 'label' => 'Tất cả', 'icon' => 'bell', 'count' => $counts['all']],
            ['key' => 'unread', 'label' => 'Chưa đọc', 'icon' => 'mail', 'count' => $counts['unread']],
        ];

        foreach (self::CATEGORIES as $key => $meta) {
            $items[] = ['key' => $key, 'label' => $meta['label'], 'icon' => $meta['icon'], 'count' => $counts[$key]];
        }

        $items[] = ['key' => 'system', 'label' => 'Khác', 'icon' => 'info', 'count' => $counts['system']];

        return $items;
    }

    /**
     * Xác định danh mục của một bản ghi dựa trên `type`.
     */
    public function categoryOf(?string $type): string
    {
        $type = strtolower((string) $type);

        foreach (self::CATEGORIES as $key => $meta) {
            foreach ($meta['keywords'] as $keyword) {
                if (str_contains($type, $keyword)) {
                    return $key;
                }
            }
        }

        return 'system';
    }

    /**
     * Chuẩn hóa một bản ghi notification thành mảng phẳng để view render.
     *
     * @return array<string, mixed>
     */
    private function present(Notification $notification): array
    {
        $data = $notification->data ?? [];

        return [
            'id' => $notification->id,
            'href' => $data['url'] ?? '#',
            'level' => $this->resolveLevel($notification->type, $data),
            'title' => $data['title'] ?? 'Thông báo',
            'message' => $data['message'] ?? '',
            'time' => $notification->created_at?->locale('vi')->diffForHumans() ?? '',
            'date_group' => $this->dateGroup($notification->created_at),
            'unread' => $notification->read_at === null,
            'read' => $notification->read_at !== null,
        ];
    }

    /**
     * Nhãn nhóm theo thời gian để view gom nhóm danh sách (Hôm nay, Hôm qua...).
     */
    private function dateGroup(?Carbon $date): string
    {
        if (! $date) {
            return 'Khác';
        }

        return match (true) {
            $date->isToday() => 'Hôm nay',
            $date->isYesterday() => 'Hôm qua',
            $date->greaterThanOrEqualTo(now()->subDays(7)) => 'Trong tuần',
            default => 'Trước đó',
        };
    }

    /**
     * Suy ra mức độ (info/success/warning/danger) để view chọn icon và màu.
     *
     * Ưu tiên khóa `level` được nhúng sẵn trong payload; nếu không có thì đoán
     * theo tên lớp notification (App\Notifications\...).
     */
    private function resolveLevel(?string $type, array $data): string
    {
        $explicit = strtolower((string) ($data['level'] ?? ''));

        if (in_array($explicit, ['info', 'success', 'warning', 'danger'], true)) {
            return $explicit;
        }

        $type = strtolower((string) $type);

        return match (true) {
            str_contains($type, 'approved') || str_contains($type, 'success') => 'success',
            str_contains($type, 'rejected') || str_contains($type, 'banned') || str_contains($type, 'danger') => 'danger',
            str_contains($type, 'warning') || str_contains($type, 'absence') || str_contains($type, 'reminder') => 'warning',
            default => 'info',
        };
    }

    /* ====================================================================
     * TẠO THÔNG BÁO (ghi vào bảng notifications)
     * ==================================================================== */

    /** Tỉ lệ số tiết được phép vắng trên tổng số tiết của lớp (20%). */
    private const ABSENCE_LIMIT_RATIO = 0.2;

    /** Còn lại tối đa bao nhiêu tiết trong quỹ vắng thì coi là "sắp vượt ngưỡng". */
    private const NEAR_ABSENCE_LESSONS = 2;

    /**
     * Ghi một bản ghi thông báo cho người dùng (bảng notifications chuẩn của Laravel).
     *
     * @param array<string, mixed> $extra Dữ liệu phụ nhúng vào payload (vd class_id để chống trùng).
     */
    public function push(
        int $userId,
        string $type,
        string $title,
        string $message,
        string $url = '#',
        string $level = 'info',
        array $extra = [],
    ): void {
        if ($userId <= 0) {
            return;
        }

        Notification::query()->create([
            'id' => (string) Str::uuid(),
            'type' => $type,
            'notifiable_type' => User::class,
            'notifiable_id' => $userId,
            'data' => array_merge([
                'title' => $title,
                'message' => $message,
                'url' => $url,
                'level' => $level,
            ], $extra),
            'read_at' => null,
        ]);

        // Phát tín hiệu realtime để chuông thông báo của người nhận tự cập nhật (không reload).
        event(new NotificationReceived($userId));
    }

    /**
     * Dựng URL tới trang phiên điểm danh (QR hoặc thủ công) cho đúng người nhận.
     */
    private function sessionUrl(int $userId, ClassSession $session, bool $isQr): string
    {
        return route(
            $isQr ? 'lecturer.attendance.qr.session' : 'lecturer.attendance.manual.session',
            ['ma_user' => $userId, 'session' => $session->id],
        );
    }

    /**
     * Báo cho giảng viên khi tạo buổi điểm danh thành công (thủ công hoặc QR).
     */
    public function attendanceSessionCreated(int $lecturerUserId, ClassSession $session, bool $isQr): void
    {
        $className = $session->courseClass?->name ?? 'lớp học';
        $url = $this->sessionUrl($lecturerUserId, $session, $isQr);

        $this->push(
            $lecturerUserId,
            $isQr ? 'App\\Notifications\\QrSessionOpened' : 'App\\Notifications\\AttendanceSessionCreated',
            $isQr ? 'Đã mở buổi điểm danh QR' : 'Đã tạo buổi điểm danh thủ công',
            "Buổi \"{$session->name}\" của lớp {$className} đã được tạo thành công.",
            $url,
            'success',
        );
    }

    /**
     * Báo cho giảng viên khi chốt sổ buổi điểm danh, đồng thời cảnh báo sinh viên liên quan.
     */
    public function attendanceSessionClosed(int $lecturerUserId, ClassSession $session, bool $isQr): void
    {
        $className = $session->courseClass?->name ?? 'lớp học';
        $url = $this->sessionUrl($lecturerUserId, $session, $isQr);

        $this->push(
            $lecturerUserId,
            $isQr ? 'App\\Notifications\\QrAttendanceClosed' : 'App\\Notifications\\AttendanceClosed',
            $isQr ? 'Đã chốt sổ buổi điểm danh QR' : 'Đã chốt sổ buổi điểm danh thủ công',
            "Buổi \"{$session->name}\" lớp {$className} đã được chốt sổ, dữ liệu chuyên cần đã cập nhật.",
            $url,
            'success',
        );

        $this->notifyStudentAbsenceWarnings($session);
    }

    /** Nhãn tiếng Việt cho từng trạng thái điểm danh (dùng cho thông báo học viên). */
    private const STATUS_LABELS = [
        'present' => 'Có mặt',
        'late' => 'Đi muộn',
        'absent' => 'Vắng',
        'excused' => 'Có phép',
    ];

    /**
     * Báo trạng thái điểm danh của một PHIÊN QR cho từng học viên có tài khoản.
     *
     * Chỉ áp dụng cho phiên QR và được gọi đúng lúc phiên chuyển sang "closed"
     * (khi giảng viên chốt phiên hoặc buổi tự hết giờ), nên mỗi phiên chỉ gửi một lần.
     */
    public function notifyQrSessionResults(ClassSession $session): void
    {
        // Chỉ phiên QR mới báo trạng thái từng phiên cho học viên.
        if (empty($session->qr_token)) {
            return;
        }

        $className = $session->courseClass?->name ?? 'lớp học';
        $date = $session->date ? $session->date->format('d/m/Y') : null;

        $records = $session->attendanceRecords()
            ->whereHas('classMember', fn ($query) => $query->whereNotNull('user_id'))
            ->with('classMember:id,user_id')
            ->get(['id', 'class_session_id', 'class_member_id', 'status']);

        foreach ($records as $record) {
            $userId = (int) ($record->classMember?->user_id ?? 0);
            if ($userId <= 0) {
                continue;
            }

            $label = self::STATUS_LABELS[$record->status] ?? 'Vắng';
            $message = "Bạn được điểm danh [{$label}] ở phiên \"{$session->name}\""
                . ($date ? " ngày {$date}" : '') . " môn {$className}.";

            $this->push(
                $userId,
                'App\\Notifications\\SessionAttendanceResult',
                'Kết quả điểm danh phiên',
                $message,
                route('student.attendance.history', ['ma_user' => $userId]),
                'info',
                ['class_id' => $session->class_id, 'session_id' => $session->id],
            );
        }
    }

    /**
     * Báo trạng thái TỔNG KẾT BUỔI cho từng học viên có tài khoản.
     *
     * Gọi khi giảng viên "Lưu tổng kết" và khi buổi tự hết giờ chốt. Chỉ gửi cho
     * những học viên có trạng thái tổng kết thay đổi so với lần đã báo trước đó
     * (dựa vào cột notified_status), tránh gửi trùng khi lưu/xuất file nhiều lần.
     */
    public function notifyMeetingResults(ClassMeeting $meeting): void
    {
        $summaries = $meeting->summaries()
            ->with(['classMember.user', 'meeting.courseClass'])
            ->get();

        foreach ($summaries as $summary) {
            if ($summary->status === $summary->notified_status) {
                continue; // Trạng thái chưa đổi -> không gửi lại.
            }

            $user = $summary->classMember?->user;
            if ($user) {
                $user->notify(new AttendanceResultNotification($summary));
            }

            // Đánh dấu đã xử lý (kể cả khi học viên chưa có tài khoản) để không lặp lại.
            $summary->forceFill(['notified_status' => $summary->status])->save();
        }
    }

    /**
     * Báo cho giảng viên khi tạo lớp thành công.
     */
    public function classCreated(int $lecturerUserId, CourseClass $class): void
    {
        $url = route('lecturer.classes.show', ['ma_user' => $lecturerUserId, 'courseClass' => $class->id]);

        $this->push(
            $lecturerUserId,
            'App\\Notifications\\ClassCreated',
            'Tạo lớp thành công',
            "Lớp {$class->name} ({$class->join_key}) đã được tạo. Hãy import danh sách sinh viên để bắt đầu điểm danh.",
            $url,
            'success',
        );
    }

    /**
     * Sau khi chốt sổ, rà từng sinh viên (có tài khoản) trong lớp để gửi cảnh báo:
     * - Vắng (đã gồm muộn quy đổi) sắp vượt quỹ tiết được phép.
     * - Vắng có phép quá nhiều (vượt quỹ tiết được phép).
     */
    public function notifyStudentAbsenceWarnings(ClassSession $session): void
    {
        $class = $session->courseClass;

        if (! $class) {
            return;
        }

        $plannedSessions = max((int) ($class->total_sessions ?? 0), 0);

        // Lấy bản ghi điểm danh ở phiên đã chốt của các sinh viên có tài khoản, gộp theo buổi.
        $rowsByUser = DB::table('attendance_records as ar')
            ->join('class_sessions as cs', 'cs.id', '=', 'ar.class_session_id')
            ->join('class_members as cm', 'cm.id', '=', 'ar.class_member_id')
            ->where('cs.class_id', $class->id)
            ->where('cs.status', 'closed')
            ->whereNotNull('cs.meeting_id')
            ->whereNull('ar.deleted_at')
            ->whereNull('cs.deleted_at')
            ->where('cm.status', 'active')
            ->whereNotNull('cm.user_id')
            ->get(['cm.user_id', 'cs.meeting_id', 'ar.class_session_id', 'cs.qr_token', 'ar.status'])
            ->groupBy('user_id');

        $rules = $class->getAttendanceRules();
        foreach ($rowsByUser as $userId => $userRows) {
            $userId = (int) $userId;
            $counts = AttendanceCalculator::consolidateByMeeting($userRows, $rules);
            $excused = $counts['excused'];

            // Quỹ vắng 20% tính trên số buổi cơ sở của từng SV = max(dự kiến, đã diễn ra).
            $baseSessions = AttendanceCalculator::baseSessions($plannedSessions, (int) $counts['total']);
            $allowed = AttendanceCalculator::allowedAbsentSessions($baseSessions);
            if ($allowed <= 0) {
                continue;
            }

            // Vắng quy đổi (đủ 6 trạng thái) để xét quỹ vắng — làm tròn xuống cho thông báo.
            $effectiveAbsent = (int) AttendanceCalculator::effectiveAbsence($counts, $rules);
            $remaining = $allowed - $effectiveAbsent;
            $url = route('student.classes.show', ['ma_user' => $userId, 'courseClass' => $class->id]);

            // Vắng sắp chạm quỹ buổi cho phép nhưng chưa vượt.
            if ($remaining >= 0 && $remaining <= self::NEAR_ABSENCE_LESSONS
                && ! $this->hasUnreadLike($userId, 'App\\Notifications\\AbsenceWarning', $class->id)) {
                $this->push(
                    $userId,
                    'App\\Notifications\\AbsenceWarning',
                    'Sắp vượt ngưỡng vắng',
                    "Lớp {$class->name}: bạn đã vắng {$effectiveAbsent}/{$allowed} buổi được phép. Chỉ còn {$remaining} buổi trước khi có nguy cơ cấm thi.",
                    $url,
                    'warning',
                    ['class_id' => $class->id],
                );
            }

            // Vắng có phép vượt quỹ buổi được phép.
            if ($excused > $allowed
                && ! $this->hasUnreadLike($userId, 'App\\Notifications\\ExcusedAbsenceWarning', $class->id)) {
                $this->push(
                    $userId,
                    'App\\Notifications\\ExcusedAbsenceWarning',
                    'Vắng có phép quá nhiều',
                    "Lớp {$class->name}: bạn đã vắng có phép {$excused} buổi, vượt mức {$allowed} buổi khuyến nghị. Hãy sắp xếp tham gia học đầy đủ hơn.",
                    $url,
                    'warning',
                    ['class_id' => $class->id],
                );
            }
        }

        // Gộp cảnh báo mức lớp cho chủ lớp: một thông báo tổng hợp thay vì rải rác.
        $this->notifyClassAbsenceSummary($class);
    }

    /**
     * Gửi một thông báo gộp cho chủ lớp (người đang thao tác điểm danh): tổng số
     * sinh viên đang ở mức cảnh báo chuyên cần — tức "sắp vượt ngưỡng vắng 20%".
     *
     * Số đếm dùng đúng nguồn `is_warning` của LectureManageStudentService để khớp
     * chính xác với nhóm dòng tô vàng khi bấm vào và lọc ở trang chi tiết lớp.
     */
    public function notifyClassAbsenceSummary(CourseClass $class): void
    {
        $ownerUserId = (int) ($class->owner_user_id ?? 0);

        if ($ownerUserId <= 0) {
            return;
        }

        $memberIds = $class->members()
            ->where('status', \App\Models\ClassMember::STATUS_ACTIVE)
            ->pluck('id')
            ->all();

        if (! $memberIds) {
            return;
        }

        $stats = app(LectureManageStudentService::class)->getStudentsAttendanceStats($memberIds);

        $warningCount = 0;
        foreach ($stats as $row) {
            if (! empty($row['is_warning'])) {
                $warningCount++;
            }
        }

        if ($warningCount <= 0
            || $this->hasUnreadLike($ownerUserId, 'App\\Notifications\\ClassAbsenceWarning', $class->id)) {
            return;
        }

        $url = route('lecturer.classes.show', [
            'ma_user' => $ownerUserId,
            'courseClass' => $class->id,
            'filter' => 'warning',
        ]);

        $this->push(
            $ownerUserId,
            'App\\Notifications\\ClassAbsenceWarning',
            'Cảnh báo chuyên cần lớp',
            "Lớp {$class->name}: có {$warningCount} sinh viên sắp vượt ngưỡng vắng 20%. Bấm để xem danh sách.",
            $url,
            'warning',
            ['class_id' => $class->id, 'warning_count' => $warningCount],
        );
    }

    /**
     * Chủ lớp chủ động gửi cảnh báo chuyên cần cho một sinh viên (mức sắp vượt ngưỡng).
     *
     * @return bool true nếu vừa gửi; false nếu SV đã có cảnh báo cùng loại chưa đọc.
     */
    public function sendManualAbsenceWarning(int $studentUserId, CourseClass $class, int $attendancePercent): bool
    {
        if ($studentUserId <= 0
            || $this->hasUnreadLike($studentUserId, 'App\\Notifications\\AbsenceWarning', $class->id)) {
            return false;
        }

        $url = route('student.classes.show', ['ma_user' => $studentUserId, 'courseClass' => $class->id]);

        $this->push(
            $studentUserId,
            'App\\Notifications\\AbsenceWarning',
            'Cảnh báo chuyên cần',
            "Lớp {$class->name}: chuyên cần của bạn còn {$attendancePercent}%, sắp chạm ngưỡng cấm thi 20%. Hãy tham gia học đầy đủ hơn.",
            $url,
            'warning',
            ['class_id' => $class->id],
        );

        return true;
    }

    /**
     * Chủ lớp gửi thông báo cấm thi cho một sinh viên đã vượt ngưỡng vắng cho phép.
     *
     * @return bool true nếu vừa gửi; false nếu SV đã có thông báo cấm thi chưa đọc.
     */
    public function sendExamBanNotice(int $studentUserId, CourseClass $class, int $attendancePercent): bool
    {
        if ($studentUserId <= 0
            || $this->hasUnreadLike($studentUserId, 'App\\Notifications\\ExamBanned', $class->id)) {
            return false;
        }

        $url = route('student.classes.show', ['ma_user' => $studentUserId, 'courseClass' => $class->id]);

        $this->push(
            $studentUserId,
            'App\\Notifications\\ExamBanned',
            'Cấm thi',
            "Lớp {$class->name}: bạn đã bị cấm thi do tỷ lệ chuyên cần chỉ còn {$attendancePercent}% (vắng vượt ngưỡng 20%). Vui lòng liên hệ giảng viên.",
            $url,
            'danger',
            ['class_id' => $class->id],
        );

        return true;
    }

    /**
     * Đã có thông báo cùng loại cho cùng lớp đang chưa đọc hay chưa (chống gửi trùng).
     */
    private function hasUnreadLike(int $userId, string $type, string $classId): bool
    {
        return Notification::query()
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', User::class)
            ->where('type', $type)
            ->whereNull('read_at')
            ->where('data->class_id', $classId)
            ->exists();
    }

    /**
     * Báo cho giảng viên và sinh viên khi phát hiện điểm danh hộ (trùng thiết bị).
     */
    public function notifyDeviceDuplicate(
        int $lecturerUserId,
        ?int $student1UserId,
        ?int $student2UserId,
        ClassSession $session,
        string $student1Name,
        string $student2Name
    ): void {
        $className = $session->courseClass?->name ?? 'lớp học';
        $isQr = !empty($session->qr_token);
        // Link GV mở thẳng phiên kèm bộ lọc "cùng 1 máy" để xem ngay danh sách SV dùng chung thiết bị.
        $url = $this->sessionUrl($lecturerUserId, $session, $isQr);
        $url .= (str_contains($url, '?') ? '&' : '?') . 'filter=same_device';

        // Gửi cho giảng viên
        $this->push(
            $lecturerUserId,
            'App\\Notifications\\FraudWarning',
            'Phát hiện gian lận (Điểm danh hộ)',
            "Phát hiện sinh viên {$student1Name} và {$student2Name} điểm danh trùng thiết bị trong buổi \"{$session->name}\" của lớp {$className}.",
            $url,
            'danger',
            ['class_id' => $session->class_id]
        );

        // Gửi cho sinh viên 1
        if ($student1UserId) {
            $studentUrl = route('student.classes.show', ['ma_user' => $student1UserId, 'courseClass' => $session->class_id]);
            $this->push(
                $student1UserId,
                'App\\Notifications\\FraudWarning',
                'Cảnh báo gian lận (Điểm danh hộ)',
                "Hệ thống phát hiện bạn sử dụng chung thiết bị điểm danh với sinh viên {$student2Name} trong buổi \"{$session->name}\".",
                $studentUrl,
                'danger',
                ['class_id' => $session->class_id]
            );
        }

        // Gửi cho sinh viên 2
        if ($student2UserId) {
            $studentUrl = route('student.classes.show', ['ma_user' => $student2UserId, 'courseClass' => $session->class_id]);
            $this->push(
                $student2UserId,
                'App\\Notifications\\FraudWarning',
                'Cảnh báo gian lận (Điểm danh hộ)',
                "Hệ thống phát hiện bạn sử dụng chung thiết bị điểm danh với sinh viên {$student1Name} trong buổi \"{$session->name}\".",
                $studentUrl,
                'danger',
                ['class_id' => $session->class_id]
            );
        }
    }

    /**
     * Leo thang: một thiết bị đã điểm danh cho nhiều SV khác nhau trong lớp (xuyên buổi).
     * Chỉ báo cho giảng viên (chủ lớp), chống spam bằng hasUnreadLike theo lớp.
     */
    public function notifyProxyDeviceAbuse(int $lecturerUserId, ClassSession $session, int $distinctStudents): void
    {
        if ($lecturerUserId <= 0) {
            return;
        }

        if ($this->hasUnreadLike($lecturerUserId, 'App\\Notifications\\ProxyDeviceWarning', (string) $session->class_id)) {
            return;
        }

        $className = $session->courseClass?->name ?? 'lớp học';
        $isQr = !empty($session->qr_token);
        $url = $this->sessionUrl($lecturerUserId, $session, $isQr);
        $url .= (str_contains($url, '?') ? '&' : '?') . 'filter=same_device';

        $this->push(
            $lecturerUserId,
            'App\\Notifications\\ProxyDeviceWarning',
            'Cảnh báo máy điểm danh hộ',
            "Một thiết bị đã điểm danh cho {$distinctStudents} sinh viên khác nhau trong lớp {$className} (qua nhiều buổi). Nghi vấn máy điểm danh hộ — bấm để xem danh sách dùng chung máy.",
            $url,
            'danger',
            ['class_id' => $session->class_id, 'distinct_students' => $distinctStudents],
        );
    }

    /**
     * Báo cho SINH VIÊN lý do điểm danh thất bại do sai vị trí (quá xa lớp).
     *
     * KHÔNG báo giảng viên: điểm danh KHÔNG thành công thì không làm phiền GV — chỉ giúp chính
     * sinh viên biết lý do để xử lý (đến gần lớp và quét lại). Giảng viên chỉ nhận thông báo ở
     * các sự kiện đã-điểm-danh-thành-công/đáng ngờ (vd trùng máy), không nhận với lần quét hỏng.
     */
    public function notifyGpsFraud(
        ?int $studentUserId,
        ClassSession $session,
        float $distanceMeters
    ): void {
        if (! $studentUserId) {
            return;
        }

        $studentUrl = route('student.classes.show', ['ma_user' => $studentUserId, 'courseClass' => $session->class_id]);
        $this->push(
            $studentUserId,
            'App\\Notifications\\FraudWarning',
            'Điểm danh thất bại (Sai vị trí)',
            "Hệ thống phát hiện vị trí của bạn quá xa lớp học (" . round($distanceMeters) . "m) khi điểm danh buổi \"{$session->name}\". Kết quả điểm danh không được công nhận. Vui lòng đến gần lớp học và quét lại.",
            $studentUrl,
            'warning',
            ['class_id' => $session->class_id]
        );
    }

    /**
     * Gửi thông báo bảo trì cho tất cả người dùng trong hệ thống.
     * Sử dụng insert theo lô (chunk) để tối ưu hiệu suất, tránh N+1.
     */
    public function notifySystemMaintenance(string $startTime, string $endTime): void
    {
        $userIds = User::pluck('id');
        $now = now();
        $notifications = [];

        $startDate = \Carbon\Carbon::parse($startTime)->format('H:i d/m/Y');
        $endDate = \Carbon\Carbon::parse($endTime)->format('H:i d/m/Y');

        foreach ($userIds as $userId) {
            $notifications[] = [
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\SystemMaintenance',
                'notifiable_type' => User::class,
                'notifiable_id' => $userId,
                'data' => json_encode([
                    'title' => 'Thông báo bảo trì hệ thống',
                    'message' => "Hệ thống sẽ tạm ngưng hoạt động từ {$startDate} đến {$endDate}. Vui lòng lưu lại công việc của bạn.",
                    'url' => '#',
                    'level' => 'warning',
                ]),
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($notifications, 50) as $chunk) {
            Notification::insert($chunk);
        }
    }
}
