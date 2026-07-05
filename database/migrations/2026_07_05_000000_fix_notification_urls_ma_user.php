<?php

use App\Models\Notification;
use Illuminate\Database\Migrations\Migration;

/**
 * Chuẩn hoá lại `data->url` của các thông báo đã lưu sai.
 *
 * Trước đây một số thông báo (vd AttendanceResultNotification, LeaveRequestSubmitted)
 * dựng URL bằng route() mà KHÔNG truyền `ma_user`, nên URL rơi vào URL::defaults của
 * request tạo thông báo (thường là giảng viên/người thao tác) thay vì người NHẬN.
 * Hậu quả: người nhận bấm vào bị 403 vì URL trỏ tới ma_user của tài khoản khác.
 *
 * Vì mọi route trong app đều dạng /{prefix}/{ma_user}/... và middleware yêu cầu
 * ma_user == id người đăng nhập, nên `ma_user` trong URL của một thông báo LUÔN phải
 * bằng `notifiable_id`. Migration này ghi lại đúng như vậy (idempotent).
 */
return new class extends Migration
{
    public function up(): void
    {
        Notification::query()
            ->where('notifiable_type', \App\Models\User::class)
            ->chunkById(200, function ($rows) {
                foreach ($rows as $notification) {
                    $data = $notification->data;
                    if (! is_array($data) || empty($data['url'])) {
                        continue;
                    }

                    $ownerId = (int) $notification->notifiable_id;
                    $newUrl = $this->rewriteMaUser((string) $data['url'], $ownerId);

                    if ($newUrl !== $data['url']) {
                        $data['url'] = $newUrl;
                        $notification->data = $data;
                        $notification->save();
                    }
                }
            });
    }

    public function down(): void
    {
        // Không thể khôi phục giá trị sai cũ; giữ nguyên dữ liệu đã sửa đúng.
    }

    /**
     * Thay ma_user trong URL (đoạn path /{prefix}/{id}/ hoặc query ?ma_user=)
     * bằng id người nhận.
     */
    private function rewriteMaUser(string $url, int $ownerId): string
    {
        // Dạng path: /user/{id}/..., /admin/{id}/... — thay số ngay sau prefix.
        $url = preg_replace_callback(
            '#/(user|admin)/(\d+)(/|$)#',
            fn ($m) => '/'.$m[1].'/'.$ownerId.$m[3],
            $url,
            1
        );

        // Dạng query: ?ma_user=123 hoặc &ma_user=123.
        $url = preg_replace(
            '#([?&]ma_user=)\d+#',
            '${1}'.$ownerId,
            $url
        );

        return $url;
    }
};
