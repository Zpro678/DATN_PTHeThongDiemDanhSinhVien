<?php
use Illuminate\Support\Facades\Mail;

Mail::raw('Thử nghiệm gửi mail bằng Mailjet qua cổng 2525 thành công!', function ($message) {
    $message->to('cdth23webbnhom3@gmail.com')
            ->subject('Test kết nối Mailjet SMTP');
});

echo "Gửi thành công!\n";
