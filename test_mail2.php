<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;

try {
    Mail::raw('Thử nghiệm gửi mail bằng Brevo qua cổng 587 thành công!', function ($message) {
        $message->to('cdth23webbnhom3@gmail.com')
                ->subject('Test kết nối Brevo SMTP');
    });
    echo "Gửi thành công!\n";
} catch (\Exception $e) {
    echo "LỖI GỬI MAIL: \n";
    echo $e->getMessage() . "\n";
}
