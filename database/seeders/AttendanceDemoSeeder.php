<?php

namespace Database\Seeders;

use App\Models\AttendanceMethod;
use App\Models\AttendanceRecord;
use App\Models\AttendanceRule;
use App\Models\AttendanceSummary;
use App\Models\AuditLog;
use App\Models\CheckInScan;
use App\Models\ClassJoinRequest;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Models\LeaveRequest;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AttendanceDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->create([
            'code' => 'ADM001',
            'name' => 'System Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        $admin->assignRole('admin');

        $teacher = User::factory()->create([
            'code' => 'GV001',
            'name' => 'Nguyen Van Giang',
            'email' => 'teacher@example.com',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        $teacher->assignRole('teacher');

        $studentUser = User::factory()->create([
            'code' => 'SV200001',
            'name' => 'Tran Minh Sinh',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        $studentUser->assignRole('student');

        $tenant = Tenant::factory()->create([
            'owner_id' => $teacher->id,
            'name' => 'Khoa Cong nghe thong tin',
            'status' => 'active',
        ]);

        $teacher->update(['tenant_id' => $tenant->id]);

        TenantSetting::factory()->create([
            'tenant_id' => $tenant->id,
            'default_gps_radius' => 50,
            'default_absence_warning' => 20.00,
        ]);

        $freePlan = Plan::query()->create([
            'code' => 'FREE',
            'name' => 'Free',
            'price' => 0,
            'max_classes' => 3,
            'can_export_excel' => false,
        ]);

        $proPlan = Plan::query()->create([
            'code' => 'PRO',
            'name' => 'Pro',
            'price' => 99000,
            'max_classes' => 100,
            'can_export_excel' => true,
        ]);

        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $proPlan->id,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonth(),
            'status' => 'active',
        ]);

        foreach ([
            'manual' => 'Diem danh thu cong',
            'qr' => 'Ma QR',
            'gps' => 'GPS',
            'link' => 'Lien ket diem danh',
        ] as $code => $name) {
            AttendanceMethod::query()->create(compact('code', 'name'));
        }

        $class = CourseClass::factory()->create([
            'tenant_id' => $tenant->id,
            'owner_id' => $teacher->id,
            'code' => 'WEB-2026',
            'name' => 'Lap trinh Web nang cao',
            'description' => 'Lop mau cho he thong diem danh sinh vien.',
            'subject_code' => 'IT401',
            'semester' => '2025-2026 HK2',
            'require_approval' => true,
            'total_sessions' => 15,
            'lessons_per_session' => 3,
        ]);

        $memberData = [
            ['SV200001', 'Tran Minh Sinh', $studentUser->id],
            ['SV200002', 'Le Thu Ha', null],
            ['SV200003', 'Pham Gia Bao', null],
            ['SV200004', 'Vo Thanh Lam', null],
            ['SV200005', 'Dang Nhat Nam', null],
            ['SV200006', 'Bui Quynh Anh', null],
            ['SV200007', 'Hoang Duc Minh', null],
            ['SV200008', 'Nguyen Khanh Linh', null],
        ];

        $members = collect($memberData)->map(fn (array $member) => ClassMember::factory()->create([
            'tenant_id' => $tenant->id,
            'class_id' => $class->id,
            'student_code' => $member[0],
            'full_name' => $member[1],
            'user_id' => $member[2],
            'status' => 'active',
        ]));

        ClassJoinRequest::factory()->create([
            'tenant_id' => $tenant->id,
            'class_id' => $class->id,
            'user_id' => $studentUser->id,
            'student_code' => 'SV200001',
            'full_name' => 'Tran Minh Sinh',
            'status' => 'approved',
        ]);

        $closedSession = ClassSession::factory()->create([
            'tenant_id' => $tenant->id,
            'class_id' => $class->id,
            'name' => 'Buoi 1',
            'date' => now()->subWeek()->toDateString(),
            'start_time' => '07:30:00',
            'end_time' => '10:00:00',
            'qr_token' => Str::uuid()->toString(),
            'token_expires_at' => now()->subWeek()->addMinutes(30),
            'gps_latitude' => 10.762622,
            'gps_longitude' => 106.660172,
            'gps_radius' => 50,
            'status' => 'closed',
        ]);

        $activeSession = ClassSession::factory()->create([
            'tenant_id' => $tenant->id,
            'class_id' => $class->id,
            'name' => 'Buoi 2',
            'date' => now()->toDateString(),
            'start_time' => '07:30:00',
            'end_time' => '10:00:00',
            'qr_token' => Str::uuid()->toString(),
            'token_expires_at' => now()->addMinutes(30),
            'gps_latitude' => 10.762622,
            'gps_longitude' => 106.660172,
            'gps_radius' => 50,
            'status' => 'active',
        ]);

        $qrMethod = AttendanceMethod::query()->where('code', 'qr')->firstOrFail();
        $manualMethod = AttendanceMethod::query()->where('code', 'manual')->firstOrFail();
        $gpsMethod = AttendanceMethod::query()->where('code', 'gps')->firstOrFail();

        $statuses = ['present', 'present', 'late', 'absent', 'excused', 'present', 'present', 'absent'];

        $members->each(function (ClassMember $member, int $index) use ($closedSession, $statuses, $tenant, $qrMethod): void {
            $status = $statuses[$index];

            AttendanceRecord::factory()->create([
                'tenant_id' => $tenant->id,
                'class_session_id' => $closedSession->id,
                'class_member_id' => $member->id,
                'status' => $status,
                'method_id' => $qrMethod->id,
                'is_verified' => $member->user_id !== null,
                'check_in_time' => in_array($status, ['present', 'late'], true) ? $closedSession->date->setTime(7, 35 + $index) : null,
                'ip_address' => '127.0.0.'.($index + 1),
                'device_info' => 'Seeded browser',
                'note' => $status === 'excused' ? 'Co don xin phep' : null,
            ]);
        });

        AttendanceRecord::factory()->create([
            'tenant_id' => $tenant->id,
            'class_session_id' => $activeSession->id,
            'class_member_id' => $members[0]->id,
            'status' => 'present',
            'method_id' => $gpsMethod->id,
            'is_verified' => true,
            'check_in_time' => now(),
            'ip_address' => '127.0.0.10',
            'device_info' => 'Seeded GPS browser',
        ]);

        $members->each(function (ClassMember $member, int $index) use ($class, $tenant, $statuses): void {
            $absent = $statuses[$index] === 'absent' ? 1 : 0;

            AttendanceSummary::factory()->create([
                'tenant_id' => $tenant->id,
                'class_id' => $class->id,
                'class_member_id' => $member->id,
                'total_present' => in_array($statuses[$index], ['present', 'late'], true) ? 1 : 0,
                'total_late' => $statuses[$index] === 'late' ? 1 : 0,
                'total_absent' => $absent,
                'total_excused' => $statuses[$index] === 'excused' ? 1 : 0,
                'is_banned_from_exam' => false,
            ]);
        });

        AttendanceRule::factory()->create([
            'tenant_id' => $tenant->id,
            'class_id' => $class->id,
            'rule_type' => 'warning',
            'threshold_value' => 10.00,
            'condition_operator' => '>=',
            'is_active' => true,
        ]);

        AttendanceRule::factory()->create([
            'tenant_id' => $tenant->id,
            'class_id' => $class->id,
            'rule_type' => 'ban_exam',
            'threshold_value' => 20.00,
            'condition_operator' => '>=',
            'is_active' => true,
        ]);

        CheckInScan::factory()->create([
            'tenant_id' => $tenant->id,
            'class_session_id' => $activeSession->id,
            'user_id' => $studentUser->id,
            'student_code_attempt' => 'SV200001',
            'scan_type' => 'gps',
            'payload_signature' => hash('sha256', 'SV200001-'.$activeSession->id),
            'is_valid' => true,
            'fail_reason' => null,
            'ip_address' => '127.0.0.10',
            'device_info' => 'Seeded GPS browser',
            'scanned_at' => now(),
        ]);

        LeaveRequest::factory()->create([
            'tenant_id' => $tenant->id,
            'class_member_id' => $members[4]->id,
            'class_session_id' => $closedSession->id,
            'reason' => 'Bi om, xin vang co phep.',
            'proof_image' => null,
            'status' => 'approved',
            'reviewed_by' => $teacher->id,
            'reviewed_at' => now()->subDays(5),
        ]);

        UserDevice::factory()->create([
            'user_id' => $teacher->id,
            'device_name' => 'Chrome on Windows',
            'last_active_at' => now(),
        ]);

        Transaction::factory()->create([
            'user_id' => $teacher->id,
            'tenant_id' => $tenant->id,
            'amount' => $proPlan->price,
            'payment_method' => 'payos',
            'transaction_code' => 'DD T'.$tenant->id.' PRO',
            'partner_reference_id' => 'PAYOS-SEED-001',
            'status' => 'success',
        ]);

        AuditLog::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $teacher->id,
            'action' => 'CLASS_CREATED',
            'table_name' => 'classes',
            'row_id' => $class->id,
            'old_values' => null,
            'new_values' => ['code' => $class->code, 'name' => $class->name],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Seeder',
        ]);

        AuditLog::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $teacher->id,
            'action' => 'SUBSCRIPTION_UPGRADED',
            'table_name' => 'subscriptions',
            'row_id' => $subscription->id,
            'old_values' => ['plan' => 'FREE'],
            'new_values' => ['plan' => 'PRO'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Seeder',
        ]);
    }
}
