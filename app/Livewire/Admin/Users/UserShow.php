<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class UserShow extends Component
{
    public User $user;
    public $recentClasses = [];
    public $recentLogs = [];

    public function mount(User $user)
    {
        abort_unless(Auth::user()?->isAdmin(), 403);
        
        $user->loadCount(['ownedClasses', 'joinedClasses', 'subscriptions', 'classJoinRequests']);
        $this->user = $user;
        
        $this->recentClasses = $user->ownedClasses()
            ->withCount(['users', 'sessions'])
            ->latest()
            ->take(4)
            ->get();
            
        $this->recentLogs = $user->auditLogs()
            ->latest('created_at')
            ->take(6)
            ->get();
    }

    #[Layout('components.admin-layout')]
    public function render()
    {
        return view('livewire.admin.users.user-show')->title('Chi tiết Người dùng');
    }
}
