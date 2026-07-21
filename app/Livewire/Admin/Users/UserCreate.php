<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\Concerns\DeniesAccess;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class UserCreate extends Component
{
    use DeniesAccess, WithFileUploads;

    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $role = User::ROLE_USER;
    public $status = 'active';
    public $avatar;

    public function messages()
    {
        return [
            'email.unique' => 'Email đã được sử dụng.',
            '*.required' => 'Vui lòng điền đầy đủ thông tin.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
        ];
    }

    public function mount()
    {
        if (! Auth::user()?->isAdmin()) {
            $this->denyAccess('user.dashboard', 'Bạn không có quyền truy cập khu vực quản trị.');
        }
    }

    public function save()
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $validatedData = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:'.User::ROLE_USER.','.User::ROLE_ADMIN],
            'status' => ['required', 'in:active,blocked'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($this->avatar) {
            $validatedData['avatar'] = $this->avatar->store('avatars', 'public');
        }

        $validatedData['password'] = Hash::make($validatedData['password']);

        $user = User::create($validatedData);

        app(\App\Services\AuditLogService::class)->log('user_created', [
            'table_name' => 'users',
            'row_id' => $user->id,
            'new_values' => \Illuminate\Support\Arr::except($user->toArray(), ['password']),
        ]);

        session()->flash('success', 'Thêm tài khoản người dùng thành công.');
        return redirect()->route('admin.users.index');
    }

    #[Layout('components.admin-layout')]
    public function render()
    {
        return view('livewire.admin.users.user-create')->title('Thêm mới Người dùng');
    }
}
