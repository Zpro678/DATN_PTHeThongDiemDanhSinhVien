<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Tạo dữ liệu thông báo demo cho trang "Tất cả thông báo".
 *
 * Bộ dữ liệu phủ đủ các danh mục (điểm danh, đơn xin nghỉ, cảnh báo, hệ thống),
 * các mức độ (info/success/warning/danger), trạng thái đọc/chưa đọc và nhiều
 * mốc thời gian khác nhau để rail lọc và phần nhóm theo ngày đều có dữ liệu.
 *
 * Dùng UUID cố định + updateOrCreate nên chạy lại nhiều lần không tạo trùng.
 */
class NotificationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = User::where('email', 'teacher@example.com')->first()
            ?? User::where('is_admin', false)->orderBy('id')->first();

        if (! $teacher) {
            $this->command?->warn('NotificationDemoSeeder: không tìm thấy user để gắn thông báo, bỏ qua.');

            return;
        }

        $this->seedFor($teacher, $this->teacherNotifications());

        $student = User::where('email', 'student1@example.com')->first();

        if ($student) {
            $this->seedFor($student, $this->studentNotifications());
        }

        $this->command?->info('NotificationDemoSeeder: đã tạo thông báo demo.');
    }

    /**
     * Ghi danh sách thông báo cho một người dùng.
     *
     * @param array<int, array<string, mixed>> $rows
     */
    private function seedFor(User $user, array $rows): void
    {
        foreach ($rows as $row) {
            Notification::query()->updateOrCreate(
                ['id' => $row['id']],
                [
                    'type' => $row['type'],
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->id,
                    'data' => $row['data'],
                    'read_at' => $row['read_at'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['created_at'],
                ],
            );
        }
    }

    /**
     * Thông báo demo cho giảng viên (chủ lớp).
     *
     * @return array<int, array<string, mixed>>
     */
    private function teacherNotifications(): array
    {
        return [
            [
                'id' => 'a1000001-0000-4000-8000-000000000001',
                'type' => 'App\\Notifications\\AttendanceReminder',
                'data' => [
                    'title' => 'Nhắc tạo buổi điểm danh hôm nay',
                    'message' => 'Lớp Lập trình Web nâng cao chưa có buổi điểm danh cho hôm nay.',
                    'url' => '/lecturer/attendance',
                    'level' => 'info',
                ],
                'read_at' => null,
                'created_at' => now()->subMinutes(20),
            ],
            [
                'id' => 'a1000002-0000-4000-8000-000000000002',
                'type' => 'App\\Notifications\\QrSessionOpened',
                'data' => [
                    'title' => 'Buổi điểm danh QR đang mở',
                    'message' => 'Buổi 5 - Cơ sở dữ liệu đang mở điểm danh QR, còn 12 sinh viên chưa quét.',
                    'url' => '/lecturer/attendance',
                    'level' => 'warning',
                ],
                'read_at' => null,
                'created_at' => now()->subHours(3),
            ],
            [
                'id' => 'a1000003-0000-4000-8000-000000000003',
                'type' => 'App\\Notifications\\LeaveRequestSubmitted',
                'data' => [
                    'title' => 'Đơn xin nghỉ mới chờ duyệt',
                    'message' => 'Trần Thị B gửi đơn xin nghỉ buổi 6 lớp Lập trình Web, kèm minh chứng.',
                    'url' => '/lecturer/students/leave',
                    'level' => 'info',
                ],
                'read_at' => null,
                'created_at' => now()->subHours(6),
            ],
            [
                'id' => 'a1000004-0000-4000-8000-000000000004',
                'type' => 'App\\Notifications\\ExamBanRisk',
                'data' => [
                    'title' => '3 sinh viên nguy cơ cấm thi',
                    'message' => 'Lớp Lập trình Web có 3 sinh viên vượt 20% số tiết vắng không phép.',
                    'url' => '/lecturer/students',
                    'level' => 'danger',
                ],
                'read_at' => null,
                'created_at' => now()->subDay(),
            ],
            [
                'id' => 'a1000005-0000-4000-8000-000000000005',
                'type' => 'App\\Notifications\\AbsenceWarning',
                'data' => [
                    'title' => 'Sinh viên gần ngưỡng vắng',
                    'message' => 'Lớp Cơ sở dữ liệu có 2 sinh viên sắp chạm ngưỡng cảnh báo chuyên cần.',
                    'url' => '/lecturer/students',
                    'level' => 'warning',
                ],
                'read_at' => now()->subHours(20),
                'created_at' => now()->subDay()->subHours(2),
            ],
            [
                'id' => 'a1000006-0000-4000-8000-000000000006',
                'type' => 'App\\Notifications\\LeaveRequestApproved',
                'data' => [
                    'title' => 'Đã xử lý đơn xin nghỉ',
                    'message' => 'Bạn đã duyệt 4 đơn xin nghỉ trong tuần này.',
                    'url' => '/lecturer/students/leave/approved',
                    'level' => 'success',
                ],
                'read_at' => now()->subDays(2),
                'created_at' => now()->subDays(2),
            ],
            [
                'id' => 'a1000007-0000-4000-8000-000000000007',
                'type' => 'App\\Notifications\\AttendanceClosed',
                'data' => [
                    'title' => 'Đã chốt sổ buổi điểm danh',
                    'message' => 'Buổi 3 lớp Lập trình Web đã được chốt sổ, dữ liệu chuyên cần đã cập nhật.',
                    'url' => '/lecturer/attendance',
                    'level' => 'success',
                ],
                'read_at' => now()->subDays(4),
                'created_at' => now()->subDays(5),
            ],
            [
                'id' => 'a1000008-0000-4000-8000-000000000008',
                'type' => 'App\\Notifications\\SystemAnnouncement',
                'data' => [
                    'title' => 'Cập nhật hệ thống EduTrack v2.1',
                    'message' => 'Đã bổ sung trung tâm thông báo và bộ lọc theo danh mục.',
                    'url' => '#',
                    'level' => 'info',
                ],
                'read_at' => now()->subDays(9),
                'created_at' => now()->subDays(10),
            ],
            [
                'id' => 'a1000009-0000-4000-8000-000000000009',
                'type' => 'App\\Notifications\\SystemAnnouncement',
                'data' => [
                    'title' => 'Chào mừng đến với EduTrack',
                    'message' => 'Cảm ơn bạn đã sử dụng hệ thống điểm danh EduTrack.',
                    'url' => '#',
                    'level' => 'info',
                ],
                'read_at' => now()->subDays(19),
                'created_at' => now()->subDays(20),
            ],
        ];
    }

    /**
     * Thông báo demo cho sinh viên (student1).
     *
     * @return array<int, array<string, mixed>>
     */
    private function studentNotifications(): array
    {
        return [
            [
                'id' => 'b1000001-0000-4000-8000-000000000001',
                'type' => 'App\\Notifications\\QrSessionOpened',
                'data' => [
                    'title' => 'Buổi điểm danh QR đang mở',
                    'message' => 'Buổi 5 - Lập trình Web nâng cao đang mở điểm danh, hãy quét QR trước 21:00.',
                    'url' => '/joined-classes',
                    'level' => 'warning',
                ],
                'read_at' => null,
                'created_at' => now()->subMinutes(45),
            ],
            [
                'id' => 'b1000002-0000-4000-8000-000000000002',
                'type' => 'App\\Notifications\\ExamBanRisk',
                'data' => [
                    'title' => 'Cảnh báo nguy cơ cấm thi',
                    'message' => 'Bạn đã vắng 8/45 tiết môn Lập trình Web, tỷ lệ chuyên cần còn 82%.',
                    'url' => '/student/warnings',
                    'level' => 'danger',
                ],
                'read_at' => null,
                'created_at' => now()->subDay(),
            ],
            [
                'id' => 'b1000003-0000-4000-8000-000000000003',
                'type' => 'App\\Notifications\\LeaveRequestApproved',
                'data' => [
                    'title' => 'Đơn xin nghỉ được duyệt',
                    'message' => 'Đơn xin nghỉ buổi ngày 10/06 của bạn đã được giảng viên duyệt.',
                    'url' => '/student/leave-requests/history',
                    'level' => 'success',
                ],
                'read_at' => now()->subDays(2),
                'created_at' => now()->subDays(2),
            ],
            [
                'id' => 'b1000004-0000-4000-8000-000000000004',
                'type' => 'App\\Notifications\\LeaveRequestRejected',
                'data' => [
                    'title' => 'Đơn xin nghỉ bị từ chối',
                    'message' => 'Đơn xin nghỉ ngày 05/06 bị từ chối do thiếu minh chứng hợp lệ.',
                    'url' => '/student/leave-requests/history',
                    'level' => 'danger',
                ],
                'read_at' => now()->subDays(3),
                'created_at' => now()->subDays(3),
            ],
            [
                'id' => 'b1000005-0000-4000-8000-000000000005',
                'type' => 'App\\Notifications\\SystemAnnouncement',
                'data' => [
                    'title' => 'Chào mừng vào lớp Lập trình Web',
                    'message' => 'Bạn đã tham gia lớp Lập trình Web nâng cao thành công.',
                    'url' => '#',
                    'level' => 'info',
                ],
                'read_at' => now()->subDays(8),
                'created_at' => now()->subDays(8),
            ],
        ];
    }
}
