<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectAdminFromUserArea
{
    /**
     * Giữ tài khoản quản trị (ADMIN / SUPER_ADMIN) TRONG khu vực admin.
     *
     * Nhóm route user (prefix user/{ma_user}) trước đây không chặn admin, nên một tài
     * khoản admin đăng nhập vẫn mở được mọi đường link của user (dashboard, lớp, điểm
     * danh...). Yêu cầu: admin chỉ được ở trang quản trị. Ở đây chuyển hướng admin về
     * admin.dashboard thay vì cho vào khu vực user.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->isAdmin()) {
            $message = 'Tài khoản quản trị chỉ thao tác trong khu vực quản trị.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            return redirect()->route('admin.dashboard', ['ma_user' => $user->id]);
        }

        return $next($request);
    }
}
