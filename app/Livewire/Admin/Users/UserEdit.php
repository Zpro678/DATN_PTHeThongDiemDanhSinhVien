<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

class UserEdit extends Component
{
    public User $user;
    
    public $name = '';
    public $email = '';
    public $role = User::ROLE_USER;
    public $status = 'active';

    public function messages()
    {
        return [
            'email.unique' => 'Email đã được sử dụng.',
            '*.required' => 'Vui lòng điền đầy đủ thông tin.',
        ];
    }

    public function mount(User $user)
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->status = $user->status;
    }

    public function save()
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $validatedData = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,blocked'],
            'role' => ['required', 'in:'.User::ROLE_USER.','.User::ROLE_ADMIN.','.User::ROLE_SUPER_ADMIN],
        ]);

        $authUser = auth()->user();

        if ($this->user->id === $authUser->id && $validatedData['status'] !== $this->user->status) {
            $this->dispatch('toast', message: 'Bạn không thể tự thay đổi trạng thái của chính mình.', type: 'error');
            return;
        }

        if ($validatedData['status'] !== $this->user->status) {
            if ($this->user->isAdmin() && !$authUser->isSuperAdmin()) {
                $this->dispatch('toast', message: 'Bạn không có quyền khóa/mở khóa tài khoản của Quản trị viên khác.', type: 'error');
                return;
            }
            if ($this->user->isSuperAdmin()) {
                $this->dispatch('toast', message: 'Không thể khóa/mở khóa tài khoản Super Admin.', type: 'error');
                return;
            }
        }

        // Logic phân quyền sửa đổi role
        if ($this->user->id !== $authUser->id && $this->user->role !== $validatedData['role']) {
            if (!$authUser->isSuperAdmin()) {
                $this->dispatch('toast', message: 'Chỉ có Super Admin mới có quyền thay đổi vai trò của người dùng.', type: 'error');
                return;
            }
        }

        $oldValues = $this->user->toArray();
        $this->user->update($validatedData);

        app(\App\Services\AuditLogService::class)->log('user_edited', [
            'table_name' => 'users',
            'row_id' => $this->user->id,
            'old_values' => \Illuminate\Support\Arr::except($oldValues, ['password']),
            'new_values' => \Illuminate\Support\Arr::except($this->user->toArray(), ['password']),
        ]);

        session()->flash('success', 'Cập nhật thông tin người dùng thành công.');
        return redirect()->route('admin.users.show', $this->user);
    }

    #[Layout('components.admin-layout')]
    public function render()
    {
        return view('livewire.admin.users.user-edit')->title('Sửa thông tin Người dùng');
    }
}
