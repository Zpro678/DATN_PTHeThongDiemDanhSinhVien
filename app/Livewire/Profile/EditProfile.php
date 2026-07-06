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
            'avatar' => ['nullable', 'image', 'max:10240'], // 10MB Max
        ]);

        $user->fill([
            'name' => $this->name,
        ]);

        if ($this->avatar) {
            $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
            $image = $manager->read($this->avatar->getRealPath());
            
            // Resize image to max 400x400
            $image->scaleDown(width: 400, height: 400);
            
            $filename = uniqid('avatar_') . '.jpg';
            $relativePath = 'avatars/' . $filename;
            $fullPath = storage_path('app/public/' . $relativePath);
            
            // Đảm bảo thư mục tồn tại
            if (!file_exists(storage_path('app/public/avatars'))) {
                mkdir(storage_path('app/public/avatars'), 0755, true);
            }

            // Nén JPEG (chất lượng 80%) và lưu
            $image->toJpeg(80)->save($fullPath);
            
            $user->avatar = $relativePath;
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

        \App\Models\AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Đã thay đổi mật khẩu cá nhân',
            'table_name' => 'users',
            'row_id' => $user->id,
            'ip_address' => request()->ip(),
            'user_agent' => substr(request()->userAgent() ?? '', 0, 255),
            'created_at' => now(),
        ]);

        Auth::logoutOtherDevices($this->password);

        $this->reset(['current_password', 'password', 'password_confirmation']);

        $this->dispatch('toast', message: 'Mật khẩu đã được lưu thành công.', type: 'success');
    }

    public function render()
    {
        $layout = Auth::user()->isAdmin() ? 'components.admin-layout' : 'layouts.user';

        return view('livewire.profile.edit-profile')->layout($layout, ['title' => 'Thông tin cá nhân']);
    }
}
