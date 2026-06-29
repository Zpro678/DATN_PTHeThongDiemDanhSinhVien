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
    public $member_id = '';
    public $status = 'active';

    public function mount(User $user)
    {
        abort_unless(Auth::user()?->is_admin, 403);
        
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->member_id = $user->member_id;
        $this->status = $user->status;
    }

    public function save()
    {
        abort_unless(Auth::user()?->is_admin, 403);

        $validatedData = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->user->id)],
            'member_id' => ['nullable', 'string', 'max:50', Rule::unique('users')->ignore($this->user->id)],
            'status' => ['required', 'in:active,blocked'],
        ]);

        if ($this->user->id === auth()->id() && $validatedData['status'] !== $this->user->status) {
            session()->flash('error', 'Bạn không thể tự thay đổi trạng thái của chính mình.');
            return;
        }

        $this->user->update($validatedData);

        session()->flash('success', 'Cập nhật thông tin người dùng thành công.');
        return redirect()->route('admin.users.index');
    }

    #[Layout('components.admin-layout')]
    public function render()
    {
        return view('livewire.admin.users.user-edit')->title('Sửa thông tin Người dùng');
    }
}
