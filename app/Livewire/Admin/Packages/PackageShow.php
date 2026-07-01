<?php

namespace App\Livewire\Admin\Packages;

use App\Models\Plan;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class PackageShow extends Component
{
    use WithPagination;

    public Plan $package;

    public function mount(Plan $package)
    {
        abort_unless(Auth::user()?->isAdmin(), 403);
        $this->package = $package;
        $this->package->loadCount('subscriptions');
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
        ])->title('Chi tiết Gói dịch vụ');
    }
}
