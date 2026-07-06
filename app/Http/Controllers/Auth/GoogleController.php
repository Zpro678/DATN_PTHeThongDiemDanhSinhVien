<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            \Log::error('Google Login Error: ' . $e->getMessage());
            return redirect()->route('login')->withErrors(['email' => 'Có lỗi xảy ra khi đăng nhập bằng Google: ' . $e->getMessage()]);
        }

        // Kiểm tra xem email này đã tồn tại chưa
        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            // Nếu email đã tồn tại, kiểm tra xem nó có liên kết với Google chưa
            if (! $user->google_id) {
                // Liên kết tài khoản hiện tại với Google ID và cập nhật ảnh nếu chưa có
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $user->avatar ?? $googleUser->getAvatar(),
                ]);
            }
        } else {
            // Nếu chưa tồn tại, tạo tài khoản mới
            $user = User::create([
                'name' => $googleUser->getName() ?? 'Google User',
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => null, // Không có mật khẩu hệ thống
                'role' => User::ROLE_USER, // Mặc định là người dùng
                'status' => 'active',
            ]);
        }

        // Kiểm tra xem tài khoản có bị khóa không
        if ($user->status !== 'active') {
            return redirect()->route('login')->withErrors(['email' => 'Tài khoản của bạn đã bị vô hiệu hóa. Vui lòng liên hệ Admin.']);
        }

        // Đăng nhập người dùng
        Auth::login($user, true);

        app(AuditLogService::class)->log('login', [
            'user_id' => $user->id,
            'new_values' => [
                'method'     => 'google',
                'user_agent' => request()->userAgent(),
                'ip'         => request()->ip(),
            ],
        ]);

        // Chuyển hướng theo logic (admin về admin dashboard, user về user dashboard)
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard', ['ma_user' => $user->id]);
        }

        return redirect()->intended(route('dashboard', ['ma_user' => $user->id], absolute: false));
    }
}
