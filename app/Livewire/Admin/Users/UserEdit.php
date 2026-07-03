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
        ]);

        if ($this->user->id === auth()->id() && $validatedData['status'] !== $this->user->status) {
            session()->flash('error', 'Bạn không thể tự thay đổi trạng thái của chính mình.');
            return;
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
