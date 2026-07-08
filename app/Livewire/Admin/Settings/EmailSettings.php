<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class EmailSettings extends Component
{
    public $mail_driver = 'smtp';
    public $mail_host = '';
    public $mail_port = '465';
    public $mail_encryption = 'ssl';
    public $mail_username = '';
    public $mail_password = '';
    public $mail_from_address = '';
    public $mail_from_name = 'SAMS System Notification';

    public $test_email = '';

    public function mount()
    {
        $this->mail_driver = Setting::get('mail_driver', env('MAIL_MAILER', 'smtp'));
        $this->mail_host = Setting::get('mail_host', env('MAIL_HOST', 'smtp.gmail.com'));
        $this->mail_port = Setting::get('mail_port', env('MAIL_PORT', '465'));
        $this->mail_encryption = Setting::get('mail_encryption', env('MAIL_ENCRYPTION', 'ssl'));
        $this->mail_username = Setting::get('mail_username', env('MAIL_USERNAME', ''));
        $this->mail_password = Setting::get('mail_password', env('MAIL_PASSWORD', ''));
        $this->mail_from_address = Setting::get('mail_from_address', env('MAIL_FROM_ADDRESS', ''));
        $this->mail_from_name = Setting::get('mail_from_name', env('MAIL_FROM_NAME', 'SAMS System Notification'));
    }

    protected $rules = [
        'mail_driver' => 'required|string',
        'mail_host' => 'required|string',
        'mail_port' => 'required|numeric',
        'mail_encryption' => 'nullable|string',
        'mail_username' => 'nullable|string',
        'mail_password' => 'nullable|string',
        'mail_from_address' => 'required|email',
        'mail_from_name' => 'required|string',
    ];

    public function save()
    {
        if (!auth()->user()?->isSuperAdmin()) {
            $this->dispatch('toast', message: 'Chỉ Super Admin mới có quyền lưu cấu hình.', type: 'error');
            return;
        }

        $this->validate();

        Setting::set('mail_driver', $this->mail_driver);
        Setting::set('mail_host', $this->mail_host);
        Setting::set('mail_port', $this->mail_port);
        Setting::set('mail_encryption', $this->mail_encryption);
        Setting::set('mail_username', $this->mail_username);
        Setting::set('mail_password', $this->mail_password);
        Setting::set('mail_from_address', $this->mail_from_address);
        Setting::set('mail_from_name', $this->mail_from_name);

        session()->flash('email_success', 'Đã lưu cấu hình Email & Thông báo.');
    }

    public function sendTestEmail()
    {
        if (!auth()->user()?->isSuperAdmin()) {
            $this->dispatch('toast', message: 'Chỉ Super Admin mới có quyền thử nghiệm.', type: 'error');
            return;
        }

        $this->validate([
            'test_email' => 'required|email',
        ], [
            'test_email.required' => 'Vui lòng nhập email nhận thử nghiệm.',
            'test_email.email' => 'Email không đúng định dạng.',
        ]);

        try {
            // Override config just for this test
            config([
                'mail.default' => $this->mail_driver,
                'mail.mailers.' . $this->mail_driver . '.host' => $this->mail_host,
                'mail.mailers.' . $this->mail_driver . '.port' => $this->mail_port,
                'mail.mailers.' . $this->mail_driver . '.encryption' => $this->mail_encryption,
                'mail.mailers.' . $this->mail_driver . '.username' => $this->mail_username,
                'mail.mailers.' . $this->mail_driver . '.password' => $this->mail_password,
                'mail.from.address' => $this->mail_from_address,
                'mail.from.name' => $this->mail_from_name,
            ]);

            Mail::raw('Đây là email thử nghiệm từ hệ thống điểm danh SAMS để kiểm tra kết nối SMTP. Nếu bạn nhận được email này, cấu hình của bạn đã chính xác!', function ($message) {
                $message->to($this->test_email)
                        ->subject('SAMS - Test Email Configuration');
            });

            session()->flash('test_success', 'Gửi email thử nghiệm thành công! Vui lòng kiểm tra hộp thư.');
        } catch (\Exception $e) {
            session()->flash('test_error', 'Lỗi: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.email-settings');
    }
}
