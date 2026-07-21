<?php

namespace App\Livewire\Admin\Packages;

use App\Livewire\Concerns\DeniesAccess;
use App\Models\Plan;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class PackageShow extends Component
{
    use DeniesAccess, WithPagination;

    public Plan $package;

    public function mount(Plan $package)
    {
        if (! Auth::user()?->isAdmin()) {
            $this->denyAccess('user.dashboard', 'Bạn không có quyền truy cập khu vực quản trị.');
            return;
        }
        $this->package = $package;
    }

    /**
     * Số NGƯỜI ĐANG SỬ DỤNG gói: đếm số user riêng biệt có subscription còn hiệu lực
     * (status = active và chưa hết hạn) trỏ tới gói này — khớp định nghĩa "đang dùng gói"
     * ở User::activeSubscription(). Không tính các subscription đã hết hạn/hủy.
     */
    protected function usersCount(): int
    {
        return $this->package->subscriptions()
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
            ->distinct('user_id')
            ->count('user_id');
    }

    #[Layout('components.admin-layout')]
    public function render()
    {
        $subscriptions = $this->package->subscriptions()
            ->with('user')
            ->latest()
            ->paginate(10);

        return view('livewire.admin.packages.package-show', [
            'subscriptions' => $subscriptions,
            'usersCount'    => $this->usersCount(),
        ])->title('Chi tiết Gói dịch vụ');
    }
}
