<?php

namespace App\Services;

use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Models\Notification;
use App\Models\User;
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

        $totalSessions = max((int) ($class->total_sessions ?? 0), 0);
        $allowed = AttendanceCalculator::allowedAbsentSessions($totalSessions);

        if ($allowed <= 0) {
            return;
        }

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
    }

    /**
     * Đã có thông báo cùng loại cho cùng lớp đang chưa đọc hay chưa (chống gửi trùng).
     */
    private function hasUnreadLike(int $userId, string $type, int $classId): bool
    {
        return Notification::query()
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', User::class)
            ->where('type', $type)
            ->whereNull('read_at')
            ->where('data->class_id', $classId)
            ->exists();
    }
}
