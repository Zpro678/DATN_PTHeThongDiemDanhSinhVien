<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditProfile extends Component
{
    use WithFileUploads;

    public $name;

    public $email;

    public $avatar;

    public $current_password;

    public $password;

    public $password_confirmation;

    public function messages()
    {
        return [
            'avatar.image' => 'File tải lên bắt buộc phải là định dạng hình ảnh.',
            'avatar.max' => 'Kích thước ảnh không được vượt quá 10MB.',
            'name.required' => 'Họ và tên không được để trống.',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã được sử dụng bởi một tài khoản khác.',
            'password.different' => 'Mật khẩu mới phải khác với mật khẩu hiện tại.'
        ];
    }

    public function updatedAvatar()
    {
        $this->validate([
            'avatar' => ['nullable', 'image', 'max:10240'],
        ]);
    }

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function updateProfileInformation()
    {
        $user = Auth::user();

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'avatar' => ['nullable', 'image', 'max:10240'], // 10MB Max
        ]);

        $user->fill([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($this->avatar) {
            $path = $this->avatar->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        $this->dispatch('toast', message: 'Thông tin cá nhân đã được cập nhật thành công.', type: 'success');
    }

    public function updatePassword()
    {
        $user = Auth::user();
        
        $rules = [
            'password' => ['required', \Illuminate\Validation\Rules\Password::defaults(), 'confirmed'],
        ];

        if ($user->password) {
            $rules['current_password'] = ['required', 'current_password'];
            $rules['password'][] = 'different:current_password';
        }

        $this->validate($rules);

        $user->update([
            'password' => Hash::make($this->password),
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);

        $this->dispatch('toast', message: 'Mật khẩu đã được lưu thành công.', type: 'success');
    }

    public function render()
    {
        $layout = Auth::user()->isAdmin() ? 'components.admin-layout' : 'layouts.user';

        return view('livewire.profile.edit-profile')->layout($layout, ['title' => 'Thông tin cá nhân']);
    }
}
