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
    public $status = 'active';

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
        abort_unless(Auth::user()?->isAdmin(), 403);

        if ($user->id === Auth::id()) {
            return; // Ngăn chặn tự khóa tài khoản của chính mình
        }

        if ($user->isAdmin() && !Auth::user()->isSuperAdmin()) {
            $this->dispatch('toast', message: 'Bạn không có quyền khóa tài khoản của Quản trị viên khác.', type: 'error');
            return;
        }

        if ($user->isSuperAdmin()) {
            $this->dispatch('toast', message: 'Không ai có thể khóa tài khoản Super Admin.', type: 'error');
            return;
        }

        $oldStatus = $user->status;
        $user->status = $user->status === 'active' ? 'blocked' : 'active';
        $user->save();

        app(\App\Services\AuditLogService::class)->log('user_status_changed', [
            'table_name' => 'users',
            'row_id' => $user->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $user->status],
        ]);

        $message = $user->status === 'active' 
            ? 'Đã mở khóa tài khoản thành công.' 
            : 'Đã khóa tài khoản thành công.';
            
        $this->dispatch('toast', message: $message, type: 'success');
    }

    #[Layout('components.admin-layout')]
    public function render()
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $users = User::query()
            ->withCount(['ownedClasses', 'joinedClasses', 'subscriptions', 'classJoinRequests'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->role, function ($query) {
                if ($this->role === 'admin') {
                    $query->whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
                } elseif ($this->role === 'user') {
                    $query->where('role', User::ROLE_USER);
                }
            })
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('livewire.admin.users.user-index', [
            'users' => $users,
        ])->title('Quản lý Tài khoản');
    }
}
