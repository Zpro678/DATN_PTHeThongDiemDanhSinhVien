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

        $forced = property_exists($this, 'forceChannels') ? $this->forceChannels : [];

        return array_values(array_filter($channels, function (string $channel) use ($notifiable, $forced): bool {
            $preferenceChannel = $channel === 'broadcast' ? 'database' : $channel;

            // Kênh bị ÉP (vd cảnh báo chuyên cần buộc gửi mail): bỏ qua tùy chọn người dùng,
            // nhưng vẫn tôn trọng điều kiện bắt buộc (phải có email / telegram chat id mới gửi được).
            if (in_array($preferenceChannel, $forced, true)) {
                if ($preferenceChannel === 'mail') {
                    return filled($notifiable->email ?? null);
                }
                if ($preferenceChannel === 'telegram') {
                    return filled($notifiable->telegram_chat_id ?? null);
                }

                return true;
            }

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
