<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSummary;
use App\Models\AuditLog;
use App\Models\CheckInScan;
use App\Models\ClassJoinRequest;
use App\Models\ClassMeeting;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Models\LeaveRequest;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AttendanceDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = $this->demoUser([
            'code' => 'ADMIN001',
            'name' => 'Quản trị hệ thống',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $teacher = $this->demoUser([
            'code' => 'GV001',
            'name' => 'Nguyễn Minh Giảng Viên',
            'email' => 'teacher@example.com',
        ]);

        $students = collect(range(1, 12))->map(fn (int $index) => $this->demoUser([
            'code' => 'SV'.str_pad((string) $index, 6, '0', STR_PAD_LEFT),
            'name' => $this->studentName($index),
            'email' => 'student'.$index.'@example.com',
        ]));

        $applicant = $this->demoUser([
            'code' => 'SV999999',
            'name' => 'Sinh viên chờ duyệt',
            'email' => 'applicant@example.com',
        ]);

        $webClass = $this->demoClass($teacher, 'WEB-2026-01', [
            'name' => 'Lập trình Web nâng cao',
            'description' => 'Lớp demo để kiểm thử tạo phiên điểm danh, quản lý sinh viên và đơn xin nghỉ.',
            'subject_code' => 'WEB401',
            'semester' => 'HK2 2025-2026',
            'require_approval' => true,
            'status' => 'active',
            'total_sessions' => 15,
        ]);

        $databaseClass = $this->demoClass($teacher, 'DB-2026-01', [
            'name' => 'Cơ sở dữ liệu',
            'description' => 'Lớp demo thứ hai để kiểm thử bộ lọc lớp.',
            'subject_code' => 'DB301',
            'semester' => 'HK2 2025-2026',
            'require_approval' => false,
            'status' => 'active',
            'total_sessions' => 12,
        ]);

        $this->demoClass($teacher, 'UI-2025-01', [
            'name' => 'Thiết kế UI/UX',
            'description' => 'Lớp demo đã kết thúc.',
            'subject_code' => 'UI401',
            'semester' => 'HK1 2025-2026',
            'require_approval' => false,
            'status' => 'archived',
            'total_sessions' => 10,
        ]);

        $webMembers = $students->map(fn (User $student) => $this->demoMember($webClass, $student));
        $students->take(6)->each(fn (User $student) => $this->demoMember($databaseClass, $student));

        $archivedMember = $this->demoMember($webClass, $students->last(), 'dropped');
        $archivedMember->delete();

        ClassJoinRequest::query()->updateOrCreate(
            [
                'class_id' => $webClass->id,
                'student_code' => $applicant->code,
            ],
            [
                'user_id' => $applicant->id,
                'full_name' => $applicant->name,
                'status' => 'pending',
            ],
        );

        // Buổi 1: một phiên thủ công đã chốt.
        $meeting1 = $this->demoMeeting($webClass, $teacher, 'Buổi 1 - Tổng quan Laravel', [
            'date' => now()->subWeeks(3)->toDateString(),
            'start_time' => '07:00:00',
            'end_time' => '09:30:00',
            'status' => 'closed',
        ]);

        // Buổi 2: một phiên QR đã chốt.
        $meeting2 = $this->demoMeeting($webClass, $teacher, 'Buổi 2 - Eloquent ORM', [
            'date' => now()->subWeeks(2)->toDateString(),
            'start_time' => '07:00:00',
            'end_time' => '09:30:00',
            'status' => 'closed',
        ]);

        // Buổi 3: một phiên thủ công đang mở.
        $meeting3 = $this->demoMeeting($webClass, $teacher, 'Buổi 3 - Điểm danh thủ công', [
            'date' => now()->toDateString(),
            'start_time' => '07:00:00',
            'end_time' => '09:30:00',
            'status' => 'active',
        ]);

        // Buổi 4: HAI phiên (1 QR đã chốt + 1 thủ công đang mở) để minh hoạ buổi nhiều phiên.
        $meeting4 = $this->demoMeeting($webClass, $teacher, 'Buổi 4 - Điểm danh QR', [
            'date' => now()->toDateString(),
            'start_time' => '13:00:00',
            'end_time' => '15:30:00',
            'status' => 'active',
        ]);

        $sessions = collect([
            $this->demoSession($meeting1, [
                'status' => 'closed',
                'qr_token' => null,
                'token_expires_at' => null,
            ]),
            $this->demoSession($meeting2, [
                'status' => 'closed',
                'qr_token' => 'demo-qr-eloquent',
                'token_expires_at' => now()->subWeeks(2)->addMinutes(20),
                'gps_latitude' => 10.762622,
                'gps_longitude' => 106.660172,
                'gps_radius' => 100,
            ]),
            $this->demoSession($meeting3, [
                'status' => 'active',
                'qr_token' => null,
                'token_expires_at' => null,
            ]),
            $this->demoSession($meeting4, [
                'status' => 'closed',
                'qr_token' => 'demo-qr-livewire',
                'token_expires_at' => now()->subHour(),
                'gps_latitude' => 10.762622,
                'gps_longitude' => 106.660172,
                'gps_radius' => 120,
            ]),
        ]);

        // Phiên thứ hai của Buổi 4 (thủ công, đang mở) — không cộng dồn số tiết.
        $this->demoSession($meeting4, [
            'name_suffix' => ' (phiên 2)',
            'status' => 'active',
            'qr_token' => null,
            'token_expires_at' => null,
        ]);

        $dbMeeting = $this->demoMeeting($databaseClass, $teacher, 'Buổi 1 - Chuẩn hoá dữ liệu', [
            'date' => now()->subDays(3)->toDateString(),
            'start_time' => '13:00:00',
            'end_time' => '15:30:00',
            'status' => 'closed',
        ]);
        $this->demoSession($dbMeeting, [
            'status' => 'closed',
            'qr_token' => null,
            'token_expires_at' => null,
        ]);

        $sessions->each(fn (ClassSession $session, int $sessionIndex) => $this->seedAttendanceRecords($session, $webMembers, $sessionIndex));

        $this->seedLeaveRequests($teacher, $webMembers, $sessions);
        $this->seedSummaries($webClass, $webMembers);
        $this->seedSupportData($admin, $teacher, $students->first(), $webClass);
    }

    /**
     * @param  array{code: string, name: string, email: string, is_admin?: bool}  $attributes
     */
    private function demoUser(array $attributes): User
    {
        $user = User::withTrashed()->firstOrNew(['email' => $attributes['email']]);
        $user->forceFill([
            'code' => $attributes['code'],
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'is_admin' => $attributes['is_admin'] ?? false,
            'google_id' => null,
            'avatar' => null,
            'status' => 'active',
        ]);
        $user->save();
        $this->restoreIfTrashed($user);

        return $user->fresh();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function demoClass(User $teacher, string $code, array $attributes): CourseClass
    {
        $courseClass = CourseClass::withTrashed()->firstOrNew(['code' => $code]);
        $courseClass->fill([
            'owner_user_id' => $teacher->id,
            'code' => $code,
            ...$attributes,
        ]);
        $courseClass->save();
        $this->restoreIfTrashed($courseClass);

        return $courseClass->fresh();
    }

    private function demoMember(CourseClass $courseClass, User $student, string $status = 'active'): ClassMember
    {
        $member = ClassMember::withTrashed()->firstOrNew([
            'class_id' => $courseClass->id,
            'student_code' => $student->code,
        ]);
        $member->fill([
            'class_id' => $courseClass->id,
            'student_code' => $student->code,
            'full_name' => $student->name,
            'user_id' => $student->id,
            'status' => $status,
        ]);
        $member->save();

        if ($status === 'active') {
            $this->restoreIfTrashed($member);
        }

        return $member->fresh();
    }

    /**
     * Tạo một buổi học (cha của các phiên điểm danh).
     *
     * @param  array<string, mixed>  $attributes
     */
    private function demoMeeting(CourseClass $courseClass, User $teacher, string $name, array $attributes): ClassMeeting
    {
        $meeting = ClassMeeting::withTrashed()->firstOrNew([
            'class_id' => $courseClass->id,
            'name' => $name,
        ]);
        $meeting->fill([
            'class_id' => $courseClass->id,
            'created_by' => $teacher->id,
            'name' => $name,
            ...$attributes,
        ]);
        $meeting->save();
        $this->restoreIfTrashed($meeting);

        return $meeting->fresh();
    }

    /**
     * Tạo một phiên điểm danh thuộc một buổi, sao chép thông tin ngày/giờ/tiết từ buổi.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function demoSession(ClassMeeting $meeting, array $attributes = []): ClassSession
    {
        $suffix = $attributes['name_suffix'] ?? '';
        unset($attributes['name_suffix']);
        $name = $meeting->name.$suffix;

        $session = ClassSession::withTrashed()->firstOrNew([
            'meeting_id' => $meeting->id,
            'name' => $name,
        ]);
        $session->fill([
            'class_id' => $meeting->class_id,
            'meeting_id' => $meeting->id,
            'created_by' => $meeting->created_by,
            'name' => $name,
            'date' => $meeting->date,
            'start_time' => $meeting->start_time,
            'end_time' => $meeting->end_time,
            'gps_latitude' => null,
            'gps_longitude' => null,
            'gps_radius' => null,
            ...$attributes,
        ]);
        $session->save();
        $this->restoreIfTrashed($session);

        return $session->fresh();
    }

    private function seedAttendanceRecords(ClassSession $session, $members, int $sessionIndex): void
    {
        $members->each(function (ClassMember $member, int $memberIndex) use ($session, $sessionIndex): void {
            $status = match (true) {
                $session->status === 'active' && $session->qr_token === null => 'pending',
                $session->status === 'active' && $memberIndex > 6 => 'pending',
                ($memberIndex + $sessionIndex) % 7 === 0 => 'absent',
                ($memberIndex + $sessionIndex) % 5 === 0 => 'late',
                default => 'present',
            };

            $record = AttendanceRecord::withTrashed()->firstOrNew([
                'class_session_id' => $session->id,
                'class_member_id' => $member->id,
            ]);

            $record->fill([
                'status' => $status,
                'is_verified' => $member->user_id !== null && $status !== 'pending',
                'check_in_time' => in_array($status, ['present', 'late'], true)
                    ? $session->date->copy()->setTime($session->qr_token ? 13 : 7, $status === 'late' ? 20 : $memberIndex)
                    : null,
                'ip_address' => $status === 'pending' ? null : '127.0.0.'.($memberIndex + 1),
                'device_fingerprint' => $status === 'pending' ? null : hash('sha256', 'demo-device-'.$member->student_code),
                'distance_meters' => $session->qr_token && $status !== 'pending' ? 35 + $memberIndex : null,
                'note' => null,
            ]);
            $record->save();
            $this->restoreIfTrashed($record);

            if ($session->qr_token && in_array($status, ['present', 'late'], true)) {
                CheckInScan::query()->updateOrCreate(
                    ['payload_signature' => hash('sha256', $session->id.'-'.$member->student_code)],
                    [
                        'class_session_id' => $session->id,
                        'user_id' => $member->user_id,
                        'student_code_attempt' => $member->student_code,
                        'scan_type' => 'qr',
                        'is_valid' => true,
                        'fail_reason' => null,
                        'ip_address' => '127.0.0.'.($memberIndex + 1),
                        'device_fingerprint' => hash('sha256', 'demo-device-'.$member->student_code),
                        'scanned_at' => $session->date->copy()->setTime(13, $memberIndex),
                    ],
                );
            }
        });
    }

    private function seedLeaveRequests(User $teacher, $members, $sessions): void
    {
        $pendingMember = $members->get(2);
        $approvedMember = $members->get(4);
        $rejectedMember = $members->get(6);

        LeaveRequest::query()->updateOrCreate(
            [
                'class_member_id' => $pendingMember->id,
                'class_session_id' => $sessions->get(2)->id,
            ],
            [
                'reason' => 'Em bị sốt nên xin nghỉ buổi học hôm nay.',
                'proof_image' => null,
                'status' => 'pending',
                'rejected_reason' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'created_at' => now()->subHours(2),
            ],
        );

        LeaveRequest::query()->updateOrCreate(
            [
                'class_member_id' => $approvedMember->id,
                'class_session_id' => $sessions->get(1)->id,
            ],
            [
                'reason' => 'Nghỉ ốm có xác nhận y tế.',
                'proof_image' => null,
                'status' => 'approved',
                'rejected_reason' => null,
                'reviewed_by' => $teacher->id,
                'reviewed_at' => now()->subDay(),
                'created_at' => now()->subDays(2),
            ],
        );

        AttendanceRecord::query()
            ->where('class_session_id', $sessions->get(1)->id)
            ->where('class_member_id', $approvedMember->id)
            ->update([
                'status' => 'excused',
                'is_verified' => true,
                'check_in_time' => null,
                'note' => 'Đơn xin nghỉ đã được duyệt.',
            ]);

        LeaveRequest::query()->updateOrCreate(
            [
                'class_member_id' => $rejectedMember->id,
                'class_session_id' => $sessions->get(0)->id,
            ],
            [
                'reason' => 'Em xin nghỉ vì bận việc cá nhân.',
                'proof_image' => null,
                'status' => 'rejected',
                'rejected_reason' => 'Lý do chưa đủ thông tin để duyệt.',
                'reviewed_by' => $teacher->id,
                'reviewed_at' => now()->subDays(3),
                'created_at' => now()->subDays(4),
            ],
        );
    }

    private function seedSummaries(CourseClass $courseClass, $members): void
    {
        $members->each(function (ClassMember $member) use ($courseClass): void {
            $counts = AttendanceRecord::query()
                ->where('class_member_id', $member->id)
                ->selectRaw('status, COUNT(*) as aggregate')
                ->groupBy('status')
                ->pluck('aggregate', 'status');

            AttendanceSummary::query()->updateOrCreate(
                [
                    'class_id' => $courseClass->id,
                    'class_member_id' => $member->id,
                ],
                [
                    'total_present' => (int) ($counts['present'] ?? 0),
                    'total_late' => (int) ($counts['late'] ?? 0),
                    'total_absent' => (int) ($counts['absent'] ?? 0),
                    'total_excused' => (int) ($counts['excused'] ?? 0),
                    'is_banned_from_exam' => (int) ($counts['absent'] ?? 0) > 3,
                    'updated_at' => now(),
                ],
            );
        });
    }

    private function seedSupportData(User $admin, User $teacher, User $firstStudent, CourseClass $courseClass): void
    {
        AuditLog::query()->updateOrCreate(
            [
                'action' => 'DEMO_DATA_SEEDED',
                'table_name' => 'classes',
                'row_id' => $courseClass->id,
            ],
            [
                'user_id' => $admin->id,
                'class_id' => $courseClass->id,
                'old_values' => null,
                'new_values' => ['teacher_email' => $teacher->email, 'password' => 'password'],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'DatabaseSeeder',
                'created_at' => now(),
            ],
        );

        AuditLog::query()->updateOrCreate(
            [
                'action' => 'DEMO_TEACHER_ATTENDANCE_REVIEW',
                'table_name' => 'class_sessions',
                'row_id' => $courseClass->id,
            ],
            [
                'user_id' => $teacher->id,
                'class_id' => $courseClass->id,
                'old_values' => null,
                'new_values' => ['message' => 'Chủ lớp đã mở dữ liệu điểm danh demo.'],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'DatabaseSeeder',
                'created_at' => now(),
            ],
        );

        $proPlan = Plan::query()->where('code', 'PRO')->firstOrFail();

        Subscription::query()->updateOrCreate(
            [
                'user_id' => $teacher->id,
                'status' => 'active',
            ],
            [
                'plan_id' => $proPlan->id,
                'start_date' => now()->subMonth(),
                'end_date' => now()->addYear(),
            ],
        );

        Transaction::query()->updateOrCreate(
            ['transaction_code' => 'DEMO-PRO-TEACHER'],
            [
                'user_id' => $teacher->id,
                'amount' => $proPlan->price,
                'payment_method' => 'bank_transfer',
                'partner_reference_id' => 'demo-pro-teacher',
                'status' => 'success',
                'created_at' => now()->subMonth(),
            ],
        );

        UserDevice::query()->updateOrCreate(
            ['fcm_token' => 'demo-device-teacher'],
            [
                'user_id' => $teacher->id,
                'device_name' => 'Chrome Desktop',
                'last_active_at' => now(),
                'created_at' => now()->subWeek(),
            ],
        );

        UserDevice::query()->updateOrCreate(
            ['fcm_token' => 'demo-device-student-1'],
            [
                'user_id' => $firstStudent->id,
                'device_name' => 'Android Demo',
                'last_active_at' => now()->subHour(),
                'created_at' => now()->subWeek(),
            ],
        );

        // Không gieo thông báo giả ở đây: thông báo sẽ được sinh từ các sự kiện thật
        // (tạo/chốt buổi điểm danh, tạo lớp, cảnh báo vắng...) qua App\Services\NotificationService.
    }

    private function studentName(int $index): string
    {
        return [
            1 => 'Nguyễn Văn An',
            2 => 'Trần Thị Bình',
            3 => 'Lê Minh Cường',
            4 => 'Phạm Thanh Duy',
            5 => 'Hoàng Thị Hà',
            6 => 'Vũ Quốc Huy',
            7 => 'Đặng Thu Lan',
            8 => 'Bùi Đức Long',
            9 => 'Đỗ Mai Linh',
            10 => 'Phan Nhật Nam',
            11 => 'Tạ Ngọc Quỳnh',
            12 => 'Mai Anh Tuấn',
        ][$index] ?? 'Sinh viên Demo '.$index;
    }

    private function restoreIfTrashed(Model $model): void
    {
        if (method_exists($model, 'trashed') && $model->trashed()) {
            $model->restore();
        }
    }
}
