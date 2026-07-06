<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class UserCreate extends Component
{
    use WithFileUploads;

    public $name = '';
    public $email = '';
    public $password = '';
    public $role = User::ROLE_USER;
    public $status = 'active';
    public $avatar;

    public function mount()
    {
        abort_unless(Auth::user()?->isAdmin(), 403);
    }

    public function save()
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $validatedData = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:'.User::ROLE_USER.','.User::ROLE_ADMIN],
            'status' => ['required', 'in:active,blocked'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($this->avatar) {
            $validatedData['avatar'] = $this->avatar->store('avatars', 'public');
        }

        $validatedData['password'] = Hash::make($validatedData['password']);

        User::create($validatedData);

        session()->flash('success', 'Thêm tài khoản người dùng thành công.');
        return redirect()->route('admin.users.index');
    }

    #[Layout('components.admin-layout')]
    public function render()
    {
        return view('livewire.admin.users.user-create')->title('Thêm mới Người dùng');
    }
}
