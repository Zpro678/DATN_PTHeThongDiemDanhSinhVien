<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionPlan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $plan = $user->currentPlan();
        if (!$plan) {
            return redirect()->route('upgrade', ['ma_user' => $user->id])->with('error', 'Vui lòng kích hoạt gói dịch vụ.');
        }

        // Kiểm tra giới hạn tạo lớp
        if ($feature === 'create_class' && $user->ownedClasses()->count() >= $plan->max_classes) {
            return redirect()->route('upgrade', ['ma_user' => $user->id])->with('error', 'Bạn đã đạt giới hạn số lớp tối đa của gói hiện tại.');
        }

        // Add other feature checks (e.g. export_excel) here as needed

        return $next($request);
    }
}
