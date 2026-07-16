<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('email', 'nguyenkhoi020705@gmail.com')->first();
if (!$user) { 
    echo 'User nguyenkhoi020705 not found'; 
} else {
    echo "Class Owner: nguyenkhoi020705@gmail.com\n";
    echo 'Chat ID: ' . ($user->telegram_chat_id ?: 'NULL') . "\n";
    echo 'Prefers Telegram: ' . ($user->wantsNotificationChannel('telegram') ? 'Yes' : 'No') . "\n";
}

echo "Global Telegram Enabled: " . \App\Models\Setting::get('enable_telegram_notifications') . "\n";

$leaveRequest = \App\Models\LeaveRequest::latest()->first();
if (!$leaveRequest) { echo 'No leave request found to mock.'; exit; }

echo "Simulating notification to " . $user->email . "...\n";

Config::set('queue.default', 'sync'); // Force sync

try {
    $user->notify(new \App\Notifications\LeaveRequestSubmitted($leaveRequest));
    echo 'Notification dispatched successfully.' . "\n";
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
