<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the user's email address as verified (supports unauthenticated requests).
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $user = User::findOrFail($request->route('id'));

        if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            abort(403, 'Invalid verification link.');
        }

        if ($user->hasVerifiedEmail()) {
            if (auth()->check() && auth()->id() === $user->id) {
                return redirect()->intended(route('user.dashboard', ['ma_user' => $user->id], absolute: false).'?verified=1');
            }
            return redirect()->route('login')->with('status', 'Email đã được xác thực thành công! Bạn có thể tiếp tục ở thiết bị cũ hoặc đăng nhập tại đây.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        if (auth()->check() && auth()->id() === $user->id) {
            return redirect()->intended(route('user.dashboard', ['ma_user' => $user->id], absolute: false).'?verified=1');
        }

        return redirect()->route('login')->with('status', 'Email đã được xác thực thành công! Bạn có thể tiếp tục ở thiết bị cũ hoặc đăng nhập tại đây.');
    }
}
