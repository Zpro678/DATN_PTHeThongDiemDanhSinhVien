<?php

namespace App\Http\Controllers;

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
}
