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
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->user->id)],
            'status' => ['required', 'in:active,blocked'],
            'role' => ['required', 'in:'.User::ROLE_USER.','.User::ROLE_ADMIN.','.User::ROLE_SUPER_ADMIN],
        ]);

        $authUser = auth()->user();

        if ($this->user->id === $authUser->id && $validatedData['status'] !== $this->user->status) {
            session()->flash('error', 'Bạn không thể tự thay đổi trạng thái của chính mình.');
            return;
        }

        // Logic phân quyền sửa đổi role
        if ($this->user->id !== $authUser->id && $this->user->role !== $validatedData['role']) {
            if (!$authUser->isSuperAdmin() && $this->user->isAdmin()) {
                session()->flash('error', 'Admin không có quyền thay đổi vai trò của một Admin khác. Chỉ Super Admin mới có quyền này.');
                return;
            }
            if ($validatedData['role'] === User::ROLE_SUPER_ADMIN) {
                session()->flash('error', 'Không thể cấp quyền Super Admin cho người dùng khác.');
                return;
            }
        }

        $this->user->update($validatedData);

        session()->flash('success', 'Cập nhật thông tin người dùng thành công.');
        return redirect()->route('admin.users.show', $this->user);
    }

    #[Layout('components.admin-layout')]
    public function render()
    {
        return view('livewire.admin.users.user-edit')->title('Sửa thông tin Người dùng');
    }
}
