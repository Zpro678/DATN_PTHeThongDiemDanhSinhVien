<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSummary;
use App\Models\AuditLog;
use App\Models\ClassJoinRequest;
use App\Models\ClassMeeting;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Models\LeaveRequest;
use App\Models\MeetingSummary;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Skip seeding if demo data already exists
        if (User::where('email', 'admin@example.com')->exists()) {
            $this->command->info('Demo data already exists. Skipping...');
            return;
        }

        // 1) Tài khoản cố định để kiểm thử thủ công.
        User::factory()->superAdmin()->create(['name' => 'Quản trị hệ thống', 'email' => 'admin@example.com']);

        $teacher = User::factory()->create(['name' => 'Giảng viên Demo', 'email' => 'teacher@example.com']);

        $students = collect(range(1, 10))->map(fn ($i) => User::factory()->create([
            'name' => 'Sinh viên '.$i,
            'email' => 'student'.$i.'@example.com',
        ]));

        // 2) Gói PRO + giao dịch đã thanh toán cho giảng viên.
        $pro = Plan::where('plan_tier', Plan::TIER_PRO)->first();
        Subscription::create([
            'user_id' => $teacher->id,
            'plan_id' => $pro->id,
            'start_date' => now()->subDays(5),
            'end_date' => now()->addDays(25),
            'status' => 'active',
        ]);
        Transaction::create([
            'user_id' => $teacher->id,
            'plan_id' => $pro->id,
            'amount' => $pro->price,
            'currency' => 'VND',
            'payment_method' => 'PAYOS',
            'transaction_code' => 'TXN-'.Str::upper(Str::random(10)),
            'reference_code' => (string) Str::uuid(),
            'status' => 'PAID',
            'paid_at' => now()->subDays(5),
        ]);

        // 3) Thông báo + audit log cho giảng viên.
        Notification::create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\DemoNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $teacher->id,
            'data' => ['title' => 'Chào mừng', 'message' => 'Dữ liệu demo đã sẵn sàng.'],
            'read_at' => null,
        ]);
        AuditLog::create([
            'user_id' => $teacher->id,
            'action' => 'LOGIN_SUCCESS',
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        // 4) Ba lớp do giảng viên quản lý; chỉ lớp WEB có dữ liệu điểm danh.
        $webClass = CourseClass::factory()->create([
            'owner_user_id' => $teacher->id,
            'join_key' => 'WEB-2026-01',
            'name' => 'Lập trình Web',
            'total_sessions' => 15,
        ]);
        CourseClass::factory()->create([
            'owner_user_id' => $teacher->id,
            'join_key' => 'DB-2026-01',
            'name' => 'Cơ sở dữ liệu',
        ]);
        $approvalClass = CourseClass::factory()->create([
            'owner_user_id' => $teacher->id,
            'join_key' => 'SE-2026-01',
            'name' => 'Công nghệ phần mềm',
            'require_approval' => true,
        ]);

        // 5) Thành viên lớp WEB (10 sinh viên có tài khoản).
        $members = $students->values()->map(function (User $student, int $i) use ($webClass) {
            $member = ClassMember::create([
                'class_id' => $webClass->id,
                'user_id' => $student->id,
                'status' => ClassMember::STATUS_ACTIVE,
            ]);
            $member->syncProfile([
                'student_code' => 'SV'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'full_name' => $student->name,
                'email' => $student->email,
            ]);

            return $member;
        });

        // 6) Năm buổi, mỗi buổi một phiên đã chốt; điểm danh toàn bộ thành viên.
        foreach (range(1, 5) as $i) {
            $meeting = ClassMeeting::create([
                'class_id' => $webClass->id,
                'user_Created' => $teacher->id,
                'name' => "Buổi $i",
                'date' => now()->subDays((5 - $i) * 7)->toDateString(),
                'start_time' => '07:00:00',
                'end_time' => '09:30:00',
                'status' => 'closed',
            ]);

            $session = ClassSession::create([
                'meeting_id' => $meeting->id,
                'class_id' => $webClass->id,
                'created_by' => $teacher->id,
                'name' => $meeting->name,
                'date' => $meeting->date,
                'start_time' => $meeting->start_time,
                'end_time' => $meeting->end_time,
                'qr_token' => (string) Str::uuid(),
                'token_expires_at' => now(),
                'status' => 'closed',
            ]);

            foreach ($members as $idx => $member) {
                $status = match (true) {
                    $i === 1 && $idx === 0 => 'excused', // SV1 buổi 1: nghỉ có phép (đơn đã duyệt).
                    $idx % 5 === 0 => 'late',
                    $idx % 7 === 0 => 'absent',
                    default => 'present',
                };

                AttendanceRecord::create([
                    'class_session_id' => $session->id,
                    'class_member_id' => $member->id,
                    'status' => $status,
                    'is_account' => true,
                    'check_in_time' => in_array($status, ['present', 'late'], true) ? now() : null,
                    'note' => $status === 'excused' ? 'Đơn xin nghỉ đã được duyệt.' : null,
                ]);

                MeetingSummary::create([
                    'meeting_id' => $meeting->id,
                    'class_member_id' => $member->id,
                    'status' => $status,
                    'deduction' => match ($status) {
                        'late' => 0.5,
                        'absent' => 1.0,
                        default => 0.0,
                    },
                    'auto_status' => $status,
                ]);
            }
        }

        // 7) Tổng hợp chuyên cần theo lớp (một dòng mỗi thành viên).
        foreach ($members as $member) {
            $records = AttendanceRecord::where('class_member_id', $member->id)->get();
            $absent = $records->where('status', 'absent')->count();
            AttendanceSummary::create([
                'class_id' => $webClass->id,
                'class_member_id' => $member->id,
                'total_present' => $records->where('status', 'present')->count(),
                'total_late' => $records->where('status', 'late')->count(),
                'total_absent' => $absent,
                'total_excused' => $records->where('status', 'excused')->count(),
                'is_banned_from_exam' => $absent >= 3,
                'updated_at' => now(),
            ]);
        }

        // 8) Đơn xin nghỉ: 1 đã duyệt (SV1) + 1 đang chờ (SV2).
        $firstMeeting = ClassMeeting::where('class_id', $webClass->id)->orderBy('id')->first();
        LeaveRequest::create([
            'class_member_id' => $members[0]->id,
            'class_meeting_id' => $firstMeeting->id,
            'reason' => 'Nghỉ ốm có giấy bác sĩ.',
            'status' => 'approved',
            'reviewed_by' => $teacher->id,
            'reviewed_at' => now(),
        ]);
        LeaveRequest::create([
            'class_member_id' => $members[1]->id,
            'class_meeting_id' => ClassMeeting::where('class_id', $webClass->id)->orderBy('id')->skip(1)->first()->id,
            'reason' => 'Xin phép nghỉ vì việc gia đình.',
            'status' => 'pending',
        ]);

        // 9) Yêu cầu vào lớp đang chờ duyệt cho lớp cần duyệt.
        ClassJoinRequest::create([
            'class_id' => $approvalClass->id,
            'user_id' => $students[0]->id,
            'status' => ClassJoinRequest::STATUS_PENDING,
        ]);
    }
}
