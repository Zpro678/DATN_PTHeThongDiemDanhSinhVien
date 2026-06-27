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
                \App\Models\ClassMember::where('email', $event->user->email)
                    ->whereNull('user_id')
                    ->update(['user_id' => $event->user->id]);
            }
        });
    }
}
