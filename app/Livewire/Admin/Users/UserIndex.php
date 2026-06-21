<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class UserIndex extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $role = '';

    #[Url(history: true)]
    public $status = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRole()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function toggleStatus(User $user)
    {
        abort_unless(Auth::user()?->is_admin, 403);

        if ($user->id === Auth::id()) {
            return; // Ngăn chặn tự khóa tài khoản của chính mình
        }

        $user->status = $user->status === 'active' ? 'blocked' : 'active';
        $user->save();
    }

    #[Layout('components.admin-layout')]
    public function render()
    {
        abort_unless(Auth::user()?->is_admin, 403);

        $users = User::query()
            ->withCount(['ownedClasses', 'joinedClasses', 'subscriptions', 'classJoinRequests'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('code', 'like', "%{$this->search}%");
                });
            })
            ->when($this->role, function ($query) {
                if ($this->role === 'admin') {
                    $query->where('is_admin', true);
                } elseif ($this->role === 'user') {
                    $query->where('is_admin', false);
                }
            })
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.users.user-index', [
            'users' => $users,
        ])->title('Quản lý Tài khoản');
    }
}
