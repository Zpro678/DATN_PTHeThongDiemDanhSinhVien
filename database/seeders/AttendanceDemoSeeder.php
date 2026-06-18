<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSummary;
use App\Models\AuditLog;
use App\Models\CheckInScan;
use App\Models\ClassJoinRequest;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class AttendanceDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'code' => 'ADMIN001',
            'name' => 'Quản trị hệ thống',
            'email' => 'admin@example.com',
        ]);

        $teacher = User::factory()->create([
            'code' => 'GV001',
            'name' => 'Nguyễn Minh Giảng Viên',
            'email' => 'teacher@example.com',
        ]);

        $students = User::factory()
            ->count(12)
            ->state(new Sequence(fn (Sequence $sequence) => [
                'code' => 'SV'.str_pad((string) ($sequence->index + 1), 6, '0', STR_PAD_LEFT),
                'email' => 'student'.($sequence->index + 1).'@example.com',
            ]))
            ->create();

        $applicant = User::factory()->create([
            'code' => 'SV999999',
            'name' => 'Sinh viên chờ duyệt',
            'email' => 'applicant@example.com',
        ]);

        $courseClass = CourseClass::factory()->create([
            'owner_user_id' => $teacher->id,
            'code' => 'WEB-2026-01',
            'name' => 'Lập trình Web nâng cao',
            'subject_code' => 'WEB401',
            'semester' => 'HK2 2025-2026',
            'require_approval' => true,
        ]);

        CourseClass::factory()->archived()->create([
            'owner_user_id' => $teacher->id,
            'code' => 'DB-2025-01',
            'name' => 'Cơ sở dữ liệu',
            'subject_code' => 'DB301',
            'semester' => 'HK1 2025-2026',
        ]);

        $members = $students->map(fn (User $student) => ClassMember::factory()->create([
            'class_id' => $courseClass->id,
            'student_code' => $student->code,
            'full_name' => $student->name,
            'user_id' => $student->id,
        ]));

        ClassJoinRequest::factory()->create([
            'class_id' => $courseClass->id,
            'user_id' => $applicant->id,
            'student_code' => $applicant->code,
            'full_name' => $applicant->name,
        ]);

        $sessions = collect([
            ClassSession::factory()->closed()->create([
                'class_id' => $courseClass->id,
                'created_by' => $teacher->id,
                'name' => 'Buổi 1 - Tổng quan Laravel',
                'date' => now()->subWeeks(2)->toDateString(),
            ]),
            ClassSession::factory()->closed()->create([
                'class_id' => $courseClass->id,
                'created_by' => $teacher->id,
                'name' => 'Buổi 2 - Eloquent ORM',
                'date' => now()->subWeek()->toDateString(),
            ]),
            ClassSession::factory()->active()->create([
                'class_id' => $courseClass->id,
                'created_by' => $teacher->id,
                'name' => 'Buổi 3 - Livewire',
                'date' => now()->toDateString(),
            ]),
        ]);

        $sessions->each(function (ClassSession $session, int $sessionIndex) use ($members): void {
            $members->each(function (ClassMember $member, int $memberIndex) use ($session, $sessionIndex): void {
                $status = match (($memberIndex + $sessionIndex) % 8) {
                    0 => 'absent',
                    1 => 'late',
                    default => 'present',
                };

                AttendanceRecord::factory()->create([
                    'class_session_id' => $session->id,
                    'class_member_id' => $member->id,
                    'status' => $status,
                    'check_in_time' => in_array($status, ['present', 'late'], true)
                        ? $session->date->copy()->setTime(7, $status === 'late' ? 20 : 0)
                        : null,
                ]);

                if ($memberIndex < 5) {
                    CheckInScan::factory()->create([
                        'class_session_id' => $session->id,
                        'user_id' => $member->user_id,
                        'student_code_attempt' => $member->student_code,
                        'scanned_at' => $session->date->copy()->setTime(7, $memberIndex),
                    ]);
                }
            });
        });

        $leaveMember = $members->get(4);
        $leaveSession = $sessions->get(1);

        LeaveRequest::factory()->approved()->create([
            'class_member_id' => $leaveMember->id,
            'class_session_id' => $leaveSession->id,
            'reason' => 'Nghỉ ốm có xác nhận y tế.',
            'reviewed_by' => $teacher->id,
        ]);

        AttendanceRecord::query()
            ->where('class_session_id', $leaveSession->id)
            ->where('class_member_id', $leaveMember->id)
            ->update([
                'status' => 'excused',
                'check_in_time' => null,
                'note' => 'Đơn xin nghỉ đã được duyệt.',
            ]);

        $members->each(function (ClassMember $member) use ($courseClass): void {
            $counts = AttendanceRecord::query()
                ->where('class_member_id', $member->id)
                ->selectRaw('status, COUNT(*) as aggregate')
                ->groupBy('status')
                ->pluck('aggregate', 'status');

            AttendanceSummary::factory()->create([
                'class_id' => $courseClass->id,
                'class_member_id' => $member->id,
                'total_present' => (int) ($counts['present'] ?? 0),
                'total_late' => (int) ($counts['late'] ?? 0),
                'total_absent' => (int) ($counts['absent'] ?? 0),
                'total_excused' => (int) ($counts['excused'] ?? 0),
                'is_banned_from_exam' => (int) ($counts['absent'] ?? 0) > 3,
            ]);
        });

        AuditLog::factory()->count(4)->create([
            'user_id' => $teacher->id,
            'class_id' => $courseClass->id,
        ]);

        $proPlan = Plan::query()->where('code', 'PRO')->firstOrFail();

        Subscription::factory()->create([
            'user_id' => $teacher->id,
            'plan_id' => $proPlan->id,
            'end_date' => now()->addYear(),
        ]);

        Transaction::factory()->successful()->create([
            'user_id' => $teacher->id,
            'amount' => $proPlan->price,
        ]);

        UserDevice::factory()->create(['user_id' => $teacher->id]);
        $students->take(3)->each(
            fn (User $student) => UserDevice::factory()->create(['user_id' => $student->id]),
        );

        Notification::factory()->create([
            'notifiable_type' => User::class,
            'notifiable_id' => $teacher->id,
            'data' => [
                'title' => 'Lớp học đã sẵn sàng',
                'message' => 'Dữ liệu điểm danh mẫu đã được khởi tạo.',
                'url' => '/classes',
            ],
        ]);

        Notification::factory()->count(3)->create([
            'notifiable_type' => User::class,
            'notifiable_id' => $students->first()->id,
        ]);

        AuditLog::factory()->create([
            'user_id' => $admin->id,
            'class_id' => null,
            'action' => 'DEMO_DATA_SEEDED',
            'table_name' => null,
            'row_id' => null,
            'old_values' => null,
            'new_values' => ['course_class_id' => $courseClass->id],
        ]);
    }
}
