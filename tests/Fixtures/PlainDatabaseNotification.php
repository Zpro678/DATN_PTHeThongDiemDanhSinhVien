<?php

namespace Tests\Fixtures;

use Illuminate\Notifications\Notification;

class PlainDatabaseNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return ['title' => 'x', 'message' => 'y', 'url' => '#'];
    }
}
