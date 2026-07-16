<?php

namespace App\Notifications;

use App\Notifications\Traits\ChecksNotificationPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class GenericNotification extends Notification implements ShouldBroadcast
{
    use Queueable;
    use ChecksNotificationPreferences {
        via as protected traitVia;
    }

    /**
     * Kênh 'broadcast' được thêm để mọi thông báo đi qua push()/GenericNotification
     * đều bắn realtime tới chuông (server.cjs relay Redis -> socket.io). Trait
     * ChecksNotificationPreferences ánh xạ 'broadcast' về tùy chọn 'database'.
     *
     * @var array<int, string>
     */
    public array $supportedChannels = ['database', 'broadcast', 'mail'];

    public function via(object $notifiable): array
    {
        // Gọi trait để lọc database/mail theo cấu hình user
        $channels = $this->traitVia($notifiable);
        
        // Bổ sung kênh Telegram nếu admin bật và user có cấu hình
        if (!empty($notifiable->telegram_chat_id)) {
            // Kiểm tra user có muốn nhận qua Telegram không, hoặc nếu là kênh bị ép buộc
            $wants = method_exists($notifiable, 'wantsNotificationChannel') ? $notifiable->wantsNotificationChannel('telegram') : true;
            $isForced = in_array('telegram', $this->forceChannels ?? [], true);
            
            if ($wants || $isForced) {
                if (!in_array(\App\Channels\SafeTelegramChannel::class, $channels)) {
                    $channels[] = \App\Channels\SafeTelegramChannel::class;
                }
            }
        }
        
        return $channels;
    }

    public function toTelegram(object $notifiable)
    {
        $title = $this->data['title'] ?? config('app.name', 'Attendia Tech');
        $message = $this->data['message'] ?? 'Bạn có thông báo mới.';
        $url = $this->data['url'] ?? null;

        $telegramMessage = \NotificationChannels\Telegram\TelegramMessage::create()
            ->to($notifiable->telegram_chat_id)
            ->content("*" . $title . "*\n\n" . $message);
            
        if (is_string($url) && $url !== '#') {
            $isAbsoluteUrl = str_starts_with($url, 'http://') || str_starts_with($url, 'https://');
            $telegramMessage->button('Xem chi tiết', $isAbsoluteUrl ? $url : url($url));
        }
        
        return $telegramMessage;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<int, string> $forceChannels Kênh buộc gửi dù người dùng chưa bật tùy chọn
     *        (vd cảnh báo chuyên cần buộc gửi 'mail'). Vẫn tôn trọng điều kiện bắt buộc
     *        (phải có email/chat id) — xem ChecksNotificationPreferences::via().
     */
    public function __construct(
        private readonly string $databaseType,
        private readonly array $data,
        public array $forceChannels = [],
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

    /**
     * Tên sự kiện socket phía client đang lắng nghe (khớp các notification khác).
     */
    public function broadcastAs(): string
    {
        return 'NotificationEvent';
    }

    /**
     * Payload realtime — chỉ là tín hiệu để chuông gọi $wire.$refresh() tải lại từ DB
     * (đã scope theo người dùng), không lộ dữ liệu nhạy cảm qua kênh.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        // Bắn ngay (không qua queue worker) để đảm bảo realtime chỉ cần Redis + server.cjs.
        return (new BroadcastMessage([
            'notification' => $this->data,
        ]))->onConnection('sync');
    }
}
