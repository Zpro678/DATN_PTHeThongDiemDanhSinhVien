<?php

namespace Tests\Feature;

use App\Models\MeetingSummary;
use App\Models\User;
use App\Notifications\AttendanceResultNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class NotificationUrlTargetTest extends TestCase
{
    use RefreshDatabase;

    /**
     * URL của thông báo phải trỏ tới ma_user của NGƯỜI NHẬN, không phải người
     * thao tác (URL::defaults của request tạo thông báo).
     */
    public function test_attendance_result_url_targets_the_recipient(): void
    {
        $recipient = User::factory()->create();

        // Giả lập request của giảng viên: URL::defaults trỏ về một user khác.
        URL::defaults(['ma_user' => 999999]);

        $summary = MeetingSummary::factory()->create();
        $data = (new AttendanceResultNotification($summary))->toArray($recipient);

        $this->assertStringContainsString('/user/'.$recipient->id.'/', $data['url']);
        $this->assertStringNotContainsString('/user/999999/', $data['url']);
    }
}
