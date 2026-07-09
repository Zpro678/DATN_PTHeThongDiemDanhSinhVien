<?php

namespace App\Notifications\Traits;

use Illuminate\Notifications\Messages\MailMessage;

trait ChecksNotificationPreferences
{
    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = property_exists($this, 'supportedChannels')
            ? $this->supportedChannels
            : ['database'];

        return array_values(array_filter($channels, function (string $channel) use ($notifiable): bool {
            $preferenceChannel = $channel === 'broadcast' ? 'database' : $channel;

            if (method_exists($notifiable, 'wantsNotificationChannel')) {
                return $notifiable->wantsNotificationChannel($preferenceChannel);
            }

            return true;
        }));
    }

    public function toMail(object $notifiable): MailMessage
    {
        $data = method_exists($this, 'toArray') ? $this->toArray($notifiable) : [];
        $title = (string) ($data['title'] ?? config('app.name', 'Attendia Tech'));
        $message = (string) ($data['message'] ?? 'Bạn có thông báo mới.');
        $url = $data['url'] ?? null;

        $mail = (new MailMessage)
            ->subject($title)
            ->greeting('Chào ' . ($notifiable->name ?? 'bạn') . ',')
            ->line($message);

        if (is_string($url) && $url !== '#') {
            $isAbsoluteUrl = str_starts_with($url, 'http://') || str_starts_with($url, 'https://');

            $mail->action('Xem chi tiết', $isAbsoluteUrl ? $url : url($url));
        }

        return $mail->line('Cảm ơn bạn đã sử dụng ' . config('app.name', 'Attendia Tech') . '.');
    }
}
