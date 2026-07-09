<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use Illuminate\Support\Facades\Log;
use Exception;

class SafeTelegramChannel extends TelegramChannel
{
    /**
     * Send the given notification and catch any exceptions so they don't crash the request.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @return mixed|null
     */
    public function send($notifiable, Notification $notification): ?array
    {
        try {
            return parent::send($notifiable, $notification);
        } catch (Exception $e) {
            // Log the error but do not throw it, preventing a 500 error on the frontend
            Log::error('Telegram notification failed: ' . $e->getMessage());
            return null;
        }
    }
}
