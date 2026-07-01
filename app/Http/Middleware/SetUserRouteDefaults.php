<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetUserRouteDefaults
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            // Default to user id
            $maUser = $user->id;

            URL::defaults(['ma_user' => $maUser]);

            $routeMaUser = $request->route('ma_user');

            // Allow access to own route, or if user is admin
            if ($routeMaUser && $routeMaUser !== (string) $maUser && ! $user->isAdmin()) {
                // If the user is trying to access another user's route, redirect them to their own dashboard
                // or abort with 403. We'll abort for security.
                abort(403, 'Bạn không có quyền truy cập vào đường dẫn của người dùng khác.');
            }
        }

        // Remove the parameter from the route so it doesn't get injected into controllers/Livewire methods
        if ($request->route()) {
            $request->route()->forgetParameter('ma_user');
        }

        return $next($request);
    }
}
