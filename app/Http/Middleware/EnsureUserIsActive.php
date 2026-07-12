<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Đăng xuất ngay lập tức người dùng đã bị khóa (status != 'active') trên MỌI request,
     * kể cả khi họ đang có phiên đăng nhập sẵn. Trước đây chỉ kiểm tra lúc đăng nhập nên
     * tài khoản bị khóa vẫn tiếp tục thao tác cho tới khi tự đăng xuất.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->status !== 'active') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ Admin.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            return redirect()->route('login')->withErrors(['email' => $message]);
        }

        return $next($request);
    }
}
