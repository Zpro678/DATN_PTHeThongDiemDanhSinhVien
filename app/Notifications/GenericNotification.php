<?php

namespace App\Notifications;

use App\Notifications\Traits\ChecksNotificationPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GenericNotification extends Notification
{
    use Queueable, ChecksNotificationPreferences;

    /** @var array<int, string> */
    public array $supportedChannels = ['database', 'mail'];

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private readonly string $databaseType,
        private readonly array $data,
    ) {}

    public function databaseType(object $notifiable): string
    {
        return $this->databaseType;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->data;
    }
}
