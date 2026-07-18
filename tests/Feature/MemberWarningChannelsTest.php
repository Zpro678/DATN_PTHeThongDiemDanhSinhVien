<?php

namespace Tests\Feature;

use App\Livewire\Lecturer\ClassShow;
use App\Models\ClassMember;
use App\Models\CourseClass;
use App\Models\User;
use App\Notifications\GenericNotification;
use App\Notifications\PlainMailNotification;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Định tuyến kênh khi giảng viên gửi cảnh báo / cấm thi cho một thành viên lớp:
 *  - Đã liên kết tài khoản -> GenericNotification (trong app + mail + Telegram nếu có Chat ID).
 *  - Chưa liên kết         -> PlainMailNotification gửi thẳng tới email trong hồ sơ lớp.
 *
 * Telegram không thể tới SV chưa liên kết vì telegram_chat_id nằm trên bảng users.
 */
class MemberWarningChannelsTest extends TestCase
{
    use RefreshDatabase;

    private function classFor(User $owner): CourseClass
    {
        return CourseClass::factory()->create(['owner_user_id' => $owner->id, 'status' => 'active']);
    }

    private function memberWithProfile(CourseClass $class, string $name, string $email, ?User $user = null): ClassMember
    {
        $member = ClassMember::create([
            'class_id' => $class->id,
            'user_id' => $user?->id,
            'status' => ClassMember::STATUS_ACTIVE,
        ]);

        $member->syncProfile(['full_name' => $name, 'email' => $email]);

        return $member->fresh();
    }

    public function test_unlinked_member_gets_warning_by_email_only(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $class = $this->classFor($owner);
        $member = $this->memberWithProfile($class, 'Lê Hoàng Nam', 'nam.hoso@example.com');

        $this->assertNull($member->user_id, 'Thành viên này phải chưa liên kết tài khoản.');

        $sent = app(NotificationService::class)->sendAbsenceWarningToMember($member, $class, 80);

        $this->assertTrue($sent);

        // Gửi "on-demand" tới email trần, không qua model User.
        Notification::assertSentOnDemand(
            PlainMailNotification::class,
            fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'nam.hoso@example.com'
                && $channels === ['mail'],
        );

        Notification::assertNothingSentTo($owner);
    }

    public function test_linked_member_gets_warning_with_mail_and_telegram_forced(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $class = $this->classFor($owner);
        $student = User::factory()->create(['telegram_chat_id' => '123456789']);
        $member = $this->memberWithProfile($class, 'Nguyễn Tuấn Khanh', 'khanh@example.com', $student);

        $sent = app(NotificationService::class)->sendAbsenceWarningToMember($member, $class, 80);

        $this->assertTrue($sent);

        Notification::assertSentTo(
            $student,
            GenericNotification::class,
            function (GenericNotification $notification) use ($student) {
                // Tùy chọn mail/telegram mặc định TẮT -> phải nằm trong forceChannels
                // thì via() mới cho qua.
                $this->assertContains('mail', $notification->forceChannels);
                $this->assertContains('telegram', $notification->forceChannels);

                $channels = $notification->via($student);

                $this->assertContains('mail', $channels, 'SV đã liên kết phải nhận mail.');
                $this->assertContains('database', $channels, 'SV đã liên kết phải nhận thông báo trong app.');
                $this->assertContains(
                    \App\Channels\SafeTelegramChannel::class,
                    $channels,
                    'Có telegram_chat_id thì phải nhận qua Telegram.',
                );

                return true;
            },
        );

        // SV đã liên kết thì KHÔNG được đi đường mail-trần.
        Notification::assertNotSentTo(new AnonymousNotifiable, PlainMailNotification::class);
    }

    public function test_linked_member_without_telegram_still_gets_mail_and_in_app(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $class = $this->classFor($owner);
        $student = User::factory()->create(['telegram_chat_id' => null]);
        $member = $this->memberWithProfile($class, 'Trần Thị Bích', 'bich@example.com', $student);

        app(NotificationService::class)->sendAbsenceWarningToMember($member, $class, 80);

        Notification::assertSentTo(
            $student,
            GenericNotification::class,
            function (GenericNotification $notification) use ($student) {
                $channels = $notification->via($student);

                $this->assertContains('mail', $channels);
                $this->assertContains('database', $channels);
                $this->assertNotContains(
                    \App\Channels\SafeTelegramChannel::class,
                    $channels,
                    'Không có Chat ID thì tuyệt đối không thêm kênh Telegram.',
                );

                return true;
            },
        );
    }

    public function test_unlinked_member_without_email_is_not_sent(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $class = $this->classFor($owner);
        $member = $this->memberWithProfile($class, 'Không Có Mail', '');

        $sent = app(NotificationService::class)->sendAbsenceWarningToMember($member, $class, 80);

        $this->assertFalse($sent, 'Không có email thì không gửi được.');
        Notification::assertNotSentTo(new AnonymousNotifiable, PlainMailNotification::class);
    }

    public function test_exam_ban_follows_the_same_routing(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $class = $this->classFor($owner);
        $member = $this->memberWithProfile($class, 'Lê Hoàng Nam', 'nam.ban@example.com');

        $sent = app(NotificationService::class)->sendExamBanToMember($member, $class, 73);

        $this->assertTrue($sent);
        Notification::assertSentOnDemand(
            PlainMailNotification::class,
            fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'nam.ban@example.com',
        );
    }

    public function test_invalid_email_does_not_crash_the_request(): void
    {
        $owner = User::factory()->create();
        $class = $this->classFor($owner);
        $member = $this->memberWithProfile($class, 'Email Hỏng', 'khong-phai-email');

        // Không được ném exception ra ngoài — giảng viên sẽ dính lỗi 500 giữa lúc bấm nút.
        $sent = app(NotificationService::class)->sendAbsenceWarningToMember($member, $class, 80);

        $this->assertFalse($sent);
    }
}
