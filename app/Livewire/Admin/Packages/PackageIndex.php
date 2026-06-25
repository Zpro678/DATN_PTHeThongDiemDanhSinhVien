<?php

namespace App\Livewire\Admin\Packages;

use App\Models\Plan;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PackageIndex extends Component
{
    public function toggleStatus(Plan $package)
    {
        abort_unless(Auth::user()?->is_admin, 403);

        $package->is_active = !$package->is_active;
        $package->save();
    }

    public function deletePackage(Plan $package)
    {
        abort_unless(Auth::user()?->is_admin, 403);

        $package->delete();
    }

    #[Layout('components.admin-layout')]
    public function render()
    {
        abort_unless(Auth::user()?->is_admin, 403);

        $packages = Plan::query()
            ->withCount('subscriptions')
            ->orderByDesc('price')
            ->get();

        return view('livewire.admin.packages.package-index', [
            'packages' => $packages,
        ])->title('Quản lý Gói dịch vụ');
    }
}
