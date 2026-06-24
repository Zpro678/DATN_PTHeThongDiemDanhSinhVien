<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

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
    private const DROPDOWN_LIMIT = 5;

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
}
