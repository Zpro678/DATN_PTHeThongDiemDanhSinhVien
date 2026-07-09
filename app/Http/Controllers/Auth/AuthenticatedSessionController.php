<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = $request->user();

        // Kiểm tra chế độ bảo trì ngay lúc đăng nhập
        $isMaintenance = \App\Models\Setting::get('maintenance_mode', false);
        $start = \App\Models\Setting::get('maintenance_start');
        $end = \App\Models\Setting::get('maintenance_end');

        if ($isMaintenance && $start && $end) {
            if (now()->between(\Carbon\Carbon::parse($start), \Carbon\Carbon::parse($end))) {
                if ($user->role !== \App\Models\User::ROLE_SUPER_ADMIN) {
                    Auth::guard('web')->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    
                    return redirect()->route('maintenance')->with('error', 'Hệ thống đang bảo trì, bạn không thể đăng nhập lúc này.');
                }
            }
        }

        $request->session()->regenerate();

        app(\App\Services\AuditLogService::class)->log('login_success', [
            'user_id' => $user->id,
            'new_values' => [
                'method'     => 'email',
                'user_agent' => $request->userAgent(),
                'ip'         => $request->ip(),
            ],
        ]);

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard', ['ma_user' => $request->user()->id], absolute: false))
                ->with('success', 'Đăng nhập thành công! Chào mừng Quản trị viên.');
        }

        // Fix: If a normal user's intended URL is an admin route (because they used it previously), redirect them to their user dashboard instead to avoid 403.
        $intended = session()->pull('url.intended', route('dashboard', ['ma_user' => $request->user()->id], absolute: false));
        
        if (str_contains($intended, '/admin/')) {
            return redirect()->route('dashboard', ['ma_user' => $request->user()->id])
                ->with('success', 'Đăng nhập thành công!');
        }

        return redirect()->to($intended)
            ->with('success', 'Đăng nhập thành công!');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Bạn đã đăng xuất thành công');
    }
}
