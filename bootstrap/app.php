<?php

use App\Http\Middleware\SetUserRouteDefaults;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Khi web chạy sau reverse-proxy/CDN (Cloudflare, Nginx, load balancer), request()->ip()
        // MẶC ĐỊNH trả IP của proxy chứ không phải sinh viên -> mọi logic chống fake GPS theo IP
        // (lệch IP↔GPS, phát hiện VPN/proxy) sẽ SAI. Khai báo dải proxy tin cậy qua env TRUSTED_PROXIES
        // để lấy đúng IP thật của client. An toàn theo mặc định: KHÔNG đặt env -> không tin proxy nào
        // (tránh giả mạo X-Forwarded-For khi chạy trực tiếp không proxy). Đặt '*' nếu tin toàn bộ proxy
        // phía trước, hoặc liệt kê dải CIDR ngăn cách bằng dấu phẩy.
        $trustedProxies = env('TRUSTED_PROXIES');
        if (! empty($trustedProxies)) {
            $middleware->trustProxies(
                at: $trustedProxies === '*' ? '*' : array_map('trim', explode(',', $trustedProxies)),
                headers: Request::HEADER_X_FORWARDED_FOR
                    | Request::HEADER_X_FORWARDED_HOST
                    | Request::HEADER_X_FORWARDED_PORT
                    | Request::HEADER_X_FORWARDED_PROTO,
            );
        }

        $middleware->alias([
            'user.route'  => SetUserRouteDefaults::class,
            'admin'       => \App\Http\Middleware\CheckIsAdmin::class,
            'class.owner' => \App\Http\Middleware\CheckClassOwner::class,
            'plan'        => \App\Http\Middleware\CheckSubscriptionPlan::class,
            'class.limit' => \App\Http\Middleware\CheckClassLimitExceeded::class,
            'not.admin'   => \App\Http\Middleware\RedirectAdminFromUserArea::class,
        ]);
        $middleware->web(append: [
            SetUserRouteDefaults::class,
            \App\Http\Middleware\CheckSystemMaintenance::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        // Fix redirect for authenticated users (e.g. remember me)
        $middleware->redirectUsersTo(function (\Illuminate\Http\Request $request) {
            if (auth()->check() && auth()->user()->isAdmin()) {
                return route('admin.dashboard', ['ma_user' => auth()->id()]);
            }
            return route('dashboard', ['ma_user' => auth()->id()]);
        });

        // MoMo gọi POST server-to-server, không có CSRF token -> phải loại trừ.
        $middleware->validateCsrfTokens(except: [
            'payment/momo/ipn',
            'payment/payos/webhook',
            'telegram/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(route('login'));
        });
    })->create();
