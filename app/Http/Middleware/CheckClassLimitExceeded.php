<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Chặn các route quản lý lớp khi người dùng vượt quá giới hạn số lớp
 * của gói hiện tại VÀ đã hết thời gian ân hạn 7 ngày.
 *
 * Trong thời gian ân hạn: cho đi qua bình thường (banner cảnh báo hiển thị ở layout).
 * Hết ân hạn: redirect về trang chọn lớp để giữ lại.
 */
class CheckClassLimitExceeded
{
    public function __construct(protected SubscriptionService $subscriptions) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Nếu cần chọn lớp (vượt hạn + hết ân hạn) -> redirect về trang chọn lớp.
        if ($this->subscriptions->needsClassSelection($user)) {
            // Tránh redirect loop khi đang ở trang chọn lớp hoặc upgrade.
            $allowedRoutes = ['select-active-classes', 'upgrade', 'logout'];
            if (in_array($request->route()?->getName(), $allowedRoutes, true)) {
                return $next($request);
            }

            return redirect()->route('select-active-classes', ['ma_user' => $user->id])
                ->with('warning', 'Vui lòng chọn các lớp muốn giữ lại để phù hợp với giới hạn gói hiện tại.');
        }

        return $next($request);
    }
}
