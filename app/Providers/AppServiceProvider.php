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

                $telegramToken = \App\Models\Setting::get('telegram_bot_token');
                if ($telegramToken) {
                    config(['services.telegram-bot-api.token' => $telegramToken]);
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

            // Lưu ý: KHÔNG ghi audit log đăng nhập tại đây.
            // Việc ghi log đăng nhập do AuditLogService xử lý một nguồn duy nhất
            // (AuthenticatedSessionController + GoogleController) với action 'login',
            // tránh trùng lặp mỗi lần đăng nhập tạo 2 bản ghi.
        });

        // Thông báo gửi qua $user->notify() (kênh 'database') cũng phát tín hiệu realtime,
        // để chuông thông báo tự cập nhật giống các thông báo tạo qua NotificationService::push().
        \Illuminate\Support\Facades\Event::listen(function (\Illuminate\Notifications\Events\NotificationSent $event) {
            if ($event->channel === 'database' && $event->notifiable instanceof \App\Models\User) {
                event(new \App\Events\NotificationReceived((int) $event->notifiable->getKey()));
            }
        });

        \Illuminate\Support\Facades\View::composer('layouts.user', function ($view) {
            $warningCount = 0;

            if (auth()->check()) {
                $studentDashboard = app(\App\Services\StudentsService::class)
                    ->getDashboardForStudent((int) auth()->id());

                $warningCount = (int) ($studentDashboard['stats']['warning_count'] ?? 0);
            }

            $view->with('sidebarWarningCount', $warningCount);
        });
    }
}
