<?php

use App\Http\Middleware\SetUserRouteDefaults;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'user.route'  => SetUserRouteDefaults::class,
            'admin'       => \App\Http\Middleware\CheckIsAdmin::class,
            'class.owner' => \App\Http\Middleware\CheckClassOwner::class,
            'plan'        => \App\Http\Middleware\CheckSubscriptionPlan::class,
            'class.limit' => \App\Http\Middleware\CheckClassLimitExceeded::class,
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
