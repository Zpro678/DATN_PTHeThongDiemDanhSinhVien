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
        $configuredDriver = Setting::get('mail_driver');

        $this->mail_driver = $configuredDriver ?: 'smtp';
        $this->mail_host = Setting::get('mail_host', $configuredDriver ? env('MAIL_HOST', 'smtp.gmail.com') : 'smtp.gmail.com');
        $this->mail_port = Setting::get('mail_port', $configuredDriver ? env('MAIL_PORT', '587') : '587');
        $this->mail_encryption = Setting::get('mail_encryption', $configuredDriver ? env('MAIL_ENCRYPTION', 'tls') : 'tls');
        $this->mail_username = Setting::get('mail_username', $configuredDriver ? env('MAIL_USERNAME', '') : '');
        $this->mail_password = Setting::get('mail_password', $configuredDriver ? env('MAIL_PASSWORD', '') : '');
        $this->mail_from_address = Setting::get('mail_from_address', $configuredDriver ? env('MAIL_FROM_ADDRESS', '') : '');
        $this->mail_from_name = Setting::get('mail_from_name', env('MAIL_FROM_NAME', 'SAMS System Notification'));
    }

    protected function mailRules(): array
    {
        return [
            'mail_driver' => ['required', 'string'],
            'mail_host' => ['required', 'string'],
            'mail_port' => ['required', 'numeric'],
            'mail_encryption' => ['nullable', 'string'],
            'mail_username' => ['required', 'email'],
            'mail_password' => ['required', 'string', 'min:8'],
            'mail_from_address' => ['required', 'email'],
            'mail_from_name' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'mail_username.required' => 'Vui lòng nhập Gmail dùng để gửi.',
            'mail_username.email' => 'Mail Username phải là email Gmail hợp lệ.',
            'mail_password.required' => 'Vui lòng nhập Gmail App Password.',
            'mail_password.min' => 'Gmail App Password thường có 16 ký tự.',
            'mail_from_address.required' => 'Vui lòng nhập Email người gửi.',
            'mail_from_address.email' => 'Email người gửi không hợp lệ.',
        ];
    }

    public function applyGmailPreset(): void
    {
        $this->mail_driver = 'smtp';
        $this->mail_host = 'smtp.gmail.com';
        $this->mail_port = '587';
        $this->mail_encryption = 'tls';

        if (blank($this->mail_from_name)) {
            $this->mail_from_name = config('app.name', 'Attendia Tech');
        }
    }

    /**
     * @return array{ready: bool, warning: bool, title: string, message: string}
     */
    public function deliveryStatus(): array
    {
        $activeMailer = (string) config('mail.default', 'log');

        if (! Setting::get('mail_driver')) {
            return [
                'ready' => false,
                'warning' => true,
                'title' => 'Chưa lưu cấu hình SMTP',
                'message' => "Hệ thống hiện vẫn dùng MAIL_MAILER={$activeMailer}. Hãy lưu cấu hình bên dưới và gửi mail test trước khi kỳ vọng Gmail nhận thông báo.",
            ];
        }

        if (in_array($activeMailer, ['log', 'array'], true)) {
            return [
                'ready' => false,
                'warning' => true,
                'title' => 'Email chưa gửi ra Gmail',
                'message' => 'Mailer đang ở chế độ log/array, email chỉ được ghi nội bộ chứ không đi tới hộp thư thật.',
            ];
        }

        if ($this->mail_driver === 'smtp' && in_array($this->mail_host, ['127.0.0.1', 'localhost'], true)) {
            return [
                'ready' => false,
                'warning' => true,
                'title' => 'Đang dùng SMTP local',
                'message' => '127.0.0.1:2525 thường là Mailpit/Mailhog để test local. Gmail thật sẽ không nhận được email từ cấu hình này.',
            ];
        }

        if ($this->mail_driver === 'smtp'
            && (blank($this->mail_host) || blank($this->mail_username) || blank($this->mail_password) || blank($this->mail_from_address))) {
            return [
                'ready' => false,
                'warning' => true,
                'title' => 'Thiếu thông tin SMTP',
                'message' => 'Cần đủ host, username, Gmail App Password và email người gửi.',
            ];
        }

        return [
            'ready' => true,
            'warning' => false,
            'title' => 'SMTP đã sẵn sàng',
            'message' => 'Hãy bấm Gửi mail test để xác nhận Gmail nhận được email.',
        ];
    }

    public function save()
    {
        if (!auth()->user()?->isSuperAdmin()) {
            $this->dispatch('toast', message: 'Chỉ Super Admin mới có quyền lưu cấu hình.', type: 'error');
            return;
        }

        $this->validate($this->mailRules());

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

        $this->validate(array_merge($this->mailRules(), [
            'test_email' => ['required', 'email'],
        ]), array_merge($this->messages(), [
            'test_email.required' => 'Vui lòng nhập email nhận thử nghiệm.',
            'test_email.email' => 'Email không đúng định dạng.',
        ]));

        try {
            $mailScheme = match ($this->mail_encryption) {
                'ssl' => 'smtps',
                'tls' => 'smtp',
                default => null,
            };

            // Override config just for this test
            config([
                'mail.default' => $this->mail_driver,
                'mail.mailers.' . $this->mail_driver . '.host' => $this->mail_host,
                'mail.mailers.' . $this->mail_driver . '.port' => $this->mail_port,
                'mail.mailers.' . $this->mail_driver . '.scheme' => $mailScheme,
                'mail.mailers.' . $this->mail_driver . '.encryption' => $this->mail_encryption,
                'mail.mailers.' . $this->mail_driver . '.username' => $this->mail_username,
                'mail.mailers.' . $this->mail_driver . '.password' => $this->mail_password,
                'mail.from.address' => $this->mail_from_address,
                'mail.from.name' => $this->mail_from_name,
            ]);

            Mail::purge($this->mail_driver);

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
        return view('livewire.admin.settings.email-settings', [
            'deliveryStatus' => $this->deliveryStatus(),
        ]);
    }
}
