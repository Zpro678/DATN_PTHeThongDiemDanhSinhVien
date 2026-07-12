<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CheckSystemMaintenance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Dùng chung định nghĩa "đang bảo trì" với các thao tác nhạy cảm (vd phục hồi dữ liệu)
        // để khóa user và mở khóa restore luôn khớp nhau tuyệt đối.
        if (Setting::isMaintenanceActive()) {
            {
                // 1. Cho phép truy cập chính trang maintenance để tránh redirect loop
                if ($request->routeIs('maintenance')) {
                    return $next($request);
                }

                // 2. Cho phép các route liên quan đến login/logout để Admin có thể thao tác
                if ($request->routeIs('login') || $request->routeIs('logout') || $request->routeIs('password.*')) {
                    return $next($request);
                }

                // 3. Nếu người dùng ĐÃ ĐĂNG NHẬP
                if (Auth::check()) {
                    $user = Auth::user();
                    
                    // Nếu là SUPER_ADMIN thì cho phép sử dụng bình thường
                    if ($user->role === User::ROLE_SUPER_ADMIN) {
                        return $next($request);
                    }
                    
                    // Nếu KHÔNG phải SUPER_ADMIN, ép đăng xuất ngay lập tức
                    Auth::guard('web')->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    
                    return redirect()->route('maintenance')->with('error', 'Hệ thống đang bảo trì. Bạn đã bị đăng xuất tự động để đảm bảo an toàn dữ liệu.');
                }

                // 4. Các khách truy cập (chưa đăng nhập) cũng đẩy về trang maintenance
                return redirect()->route('maintenance');
            }
        }

        return $next($request);
    }
}
