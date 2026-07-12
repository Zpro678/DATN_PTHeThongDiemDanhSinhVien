<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\ClassMeeting;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Models\User;
use App\Notifications\GenericNotification;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AttendanceWarningTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Dựng một lớp 15 buổi (quỹ vắng cho phép = floor(15*0.2) = 3) với $absentMeetings buổi
     * đã chốt mà sinh viên đều VẮNG, rồi kích hoạt rà soát cảnh báo trên phiên cuối.
     *
     * @return array{0: User, 1: ClassSession}
     */
    private function buildAbsentScenario(int $absentMeetings): array
    {
        $owner = User::factory()->create();
        $class = CourseClass::factory()->create([
            'owner_user_id' => $owner->id,
            'total_sessions' => 15,
        ]);

        $student = User::factory()->create([
            'notification_preferences' => ['database' => true, 'mail' => false],
        ]);
        $member = ClassMember::create([
            'class_id' => $class->id,
            'user_id' => $student->id,
            'status' => ClassMember::STATUS_ACTIVE,
        ]);

        $lastSession = null;
        for ($i = 1; $i <= $absentMeetings; $i++) {
            $meeting = ClassMeeting::factory()->create([
                'class_id' => $class->id, 'user_Created' => $owner->id,
                'date' => sprintf('2026-07-%02d', $i), 'start_time' => '08:00:00', 'end_time' => '10:00:00',
                'status' => 'closed',
            ]);
            $session = ClassSession::factory()->create([
                'class_id' => $class->id, 'meeting_id' => $meeting->id, 'created_by' => $owner->id,
                'date' => sprintf('2026-07-%02d', $i), 'status' => 'closed', 'qr_token' => 'TK'.$i,
            ]);
            AttendanceRecord::factory()->create([
                'class_session_id' => $session->id, 'class_member_id' => $member->id, 'status' => 'absent',
            ]);
            $lastSession = $session;
        }

        return [$student, $lastSession];
    }

    public function test_student_reaching_absence_limit_gets_warning_by_mail(): void
    {
        Notification::fake();

        // Vắng 3/3 buổi cho phép -> remaining = 0 -> cảnh báo "sắp vượt ngưỡng".
        [$student, $session] = $this->buildAbsentScenario(3);

        app(NotificationService::class)->notifyStudentAbsenceWarnings($session);

        Notification::assertSentTo(
            $student,
            GenericNotification::class,
            function (GenericNotification $notification, array $channels, $notifiable): bool {
                $data = $notification->toArray($notifiable);
                return in_array('mail', $channels, true)
                    && ($data['title'] ?? '') === 'Sắp vượt ngưỡng vắng';
            },
        );
    }

    public function test_student_over_absence_limit_gets_exam_risk_warning_by_mail(): void
    {
        Notification::fake();

        // Vắng 4/3 buổi cho phép -> remaining = -1 -> trước đây KHÔNG có cảnh báo nào; nay phải có
        // cảnh báo "nguy cơ cấm thi" và buộc gửi mail dù SV chưa bật tùy chọn mail.
        [$student, $session] = $this->buildAbsentScenario(4);

        app(NotificationService::class)->notifyStudentAbsenceWarnings($session);

        Notification::assertSentTo(
            $student,
            GenericNotification::class,
            function (GenericNotification $notification, array $channels, $notifiable): bool {
                $data = $notification->toArray($notifiable);
                return in_array('mail', $channels, true)
                    && ($data['level'] ?? '') === 'danger'
                    && str_contains($data['message'] ?? '', 'VƯỢT');
            },
        );
    }
}
