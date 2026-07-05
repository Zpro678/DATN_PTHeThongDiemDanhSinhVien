<?php

namespace App\Livewire\Admin\Packages;

use App\Models\Coupon;
use App\Models\Plan;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class CouponIndex extends Component
{
    public function toggleStatus(Coupon $coupon)
    {
        abort_unless(Auth::user()?->isAdmin(), 403);
        $coupon->is_active = !$coupon->is_active;
        $coupon->save(); 
    }

    public function deleteCoupon(Coupon $coupon)
    {
        abort_unless(Auth::user()?->isAdmin(), 403);
        $coupon->delete();
    }

    #[Layout('components.admin-layout')]
    public function render()
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $coupons = Coupon::query()->with('plan')->orderByDesc('created_at')->get();
        $plans = Plan::all();

        return view('livewire.admin.packages.coupon-index', [
            'coupons' => $coupons,
            'plans' => $plans,
        ])->title('Quản lý Mã giảm giá');
    }
}
