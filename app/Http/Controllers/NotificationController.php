<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    /**
     * Đánh dấu tất cả thông báo chưa đọc của người dùng hiện tại là đã đọc.
     */
    public function readAll(): RedirectResponse
    {
        $user = auth()->user();

        if ($user) {
            $user->unreadNotifications()->update(['read_at' => now()]);
        }

        return back();
    }

    /**
     * Đánh dấu một thông báo là đã đọc rồi chuyển hướng tới đích của nó.
     *
     * Dùng cho việc bấm vào từng thông báo trong dropdown: link trỏ về route này
     * để vừa cập nhật read_at vừa đưa người dùng tới trang liên quan (data.url).
     */
    public function read(Notification $notification): RedirectResponse
    {
        $user = auth()->user();

        // Chỉ chủ sở hữu mới được đánh dấu; tránh lộ/đổi trạng thái thông báo người khác.
        abort_unless(
            $user
                && $notification->notifiable_type === User::class
                && (int) $notification->notifiable_id === (int) $user->id,
            403,
        );

        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        $target = $notification->data['url'] ?? null;

        return ($target && $target !== '#')
            ? redirect()->to($target)
            : back();
    }
}
