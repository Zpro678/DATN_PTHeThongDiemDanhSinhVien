<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $appName = \App\Models\Setting::get('app_name');
                if ($appName) {
                    config(['app.name' => $appName]);
                } else {
                    config(['app.name' => 'Attendia Tech']);
                }

                \Illuminate\Support\Facades\View::share('app_logo_path', \App\Models\Setting::get('app_logo'));

                $mailDriver = \App\Models\Setting::get('mail_driver');
                if ($mailDriver) {
                    config([
                        'mail.default' => $mailDriver,
                        'mail.mailers.' . $mailDriver . '.host' => \App\Models\Setting::get('mail_host', config("mail.mailers.{$mailDriver}.host")),
                        'mail.mailers.' . $mailDriver . '.port' => \App\Models\Setting::get('mail_port', config("mail.mailers.{$mailDriver}.port")),
                        'mail.mailers.' . $mailDriver . '.encryption' => \App\Models\Setting::get('mail_encryption', config("mail.mailers.{$mailDriver}.encryption")),
                        'mail.mailers.' . $mailDriver . '.username' => \App\Models\Setting::get('mail_username', config("mail.mailers.{$mailDriver}.username")),
                        'mail.mailers.' . $mailDriver . '.password' => \App\Models\Setting::get('mail_password', config("mail.mailers.{$mailDriver}.password")),
                        'mail.from.address' => \App\Models\Setting::get('mail_from_address', config('mail.from.address')),
                        'mail.from.name' => \App\Models\Setting::get('mail_from_name', config('mail.from.name')),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Ignore DB errors during deployment/migrations
        }
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Authenticated::class, function ($event) {
            \Illuminate\Support\Facades\URL::defaults(['ma_user' => $event->user->id]);
        });

        \Illuminate\Support\Facades\Event::listen(function (\Illuminate\Auth\Events\Login $event) {
            \Illuminate\Support\Facades\URL::defaults(['ma_user' => $event->user->id]);
            if ($event->user->email) {
                $memberIds = \App\Models\ClassMemberProfile::where('email', $event->user->email)
                    ->pluck('class_member_id');

                if ($memberIds->isNotEmpty()) {
                    \App\Models\ClassMember::whereIn('id', $memberIds)
                        ->whereNull('user_id')
                        ->update(['user_id' => $event->user->id]);
                }
            }
            
            // Ghi log đăng nhập
            \App\Models\AuditLog::create([
                'user_id' => $event->user->id,
                'action' => 'Đã đăng nhập vào hệ thống',
                'table_name' => 'users',
                'row_id' => $event->user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        });

        \Illuminate\Support\Facades\Event::listen(function (\Illuminate\Auth\Events\Logout $event) {
            if ($event->user) {
                // Ghi log đăng xuất
                \App\Models\AuditLog::create([
                    'user_id' => $event->user->id,
                    'action' => 'Đã đăng xuất khỏi hệ thống',
                    'table_name' => 'users',
                    'row_id' => $event->user->id,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'created_at' => now(),
                ]);
            }
        });

        // Thông báo gửi qua $user->notify() (kênh 'database') cũng phát tín hiệu realtime,
        // để chuông thông báo tự cập nhật giống các thông báo tạo qua NotificationService::push().
        \Illuminate\Support\Facades\Event::listen(function (\Illuminate\Notifications\Events\NotificationSent $event) {
            if ($event->channel === 'database' && $event->notifiable instanceof \App\Models\User) {
                event(new \App\Events\NotificationReceived((int) $event->notifiable->getKey()));
            }
        });
    }
}
