<?php

namespace App\Livewire;

use App\Services\NotificationService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Chuông thông báo trên thanh điều hướng.
 *
 * Thay thế component Blade tĩnh <x-notification-dropdown> để hỗ trợ xóa từng
 * thông báo / xóa tất cả và cập nhật badge số chưa đọc mà không cần tải lại trang.
 * Việc bấm vào một thông báo vẫn dùng link GET tới route notifications.read
 * (đánh dấu đã đọc rồi điều hướng), nên phần đọc/điều hướng giữ nguyên hành vi cũ.
 */
class NotificationBell extends Component
{
    /** Id người dùng hiện tại — dùng để dựng tên kênh Echo notifications.{userId}. */
    public int $userId = 0;

    public function mount(): void
    {
        $this->userId = (int) auth()->id();
    }

    /**
     * Tên kênh socket.io mà client cần lắng nghe cho tài khoản hiện tại.
     *
     * Laravel broadcast qua Redis pub/sub với prefix của Redis (Predis áp prefix vào
     * cả channel PUBLISH), server.cjs relay nguyên tên kênh đó sang socket.io. Vì vậy
     * client phải nghe đúng: {redis_prefix}notifications.{userId}.
     */
    public function realtimeChannel(): string
    {
        return (string) config('database.redis.options.prefix') . 'private-App.Models.User.' . $this->userId;
    }

    /**
     * Xóa một thông báo của người dùng hiện tại (Livewire, không reload).
     */
    public function deleteNotification(string $id): void
    {
        app(NotificationService::class)->deleteForUser(auth()->user(), $id);
    }

    /**
     * Xóa toàn bộ thông báo của người dùng hiện tại.
     */
    public function deleteAll(): void
    {
        app(NotificationService::class)->deleteAllForUser(auth()->user());
    }

    /**
     * Đánh dấu tất cả thông báo chưa đọc là đã đọc (không reload).
     */
    public function markAllRead(): void
    {
        auth()->user()?->unreadNotifications()->update(['read_at' => now()]);
    }

    public function render(): View
    {
        $data = app(NotificationService::class)->getDropdownData(auth()->user());

        return view('livewire.notification-bell', [
            'notifications' => $data['items'],
            'unreadCount' => $data['unread_count'],
            'hasUnread' => $data['has_unread'],
        ]);
    }
}
