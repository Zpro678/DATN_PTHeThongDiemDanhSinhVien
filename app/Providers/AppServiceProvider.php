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
