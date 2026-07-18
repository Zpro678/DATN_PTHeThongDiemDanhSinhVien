<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSummary;
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
use App\Services\AttendanceCalculator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * DemoSeeder — dựng dữ liệu demo khớp với 10 tài khoản thật của nhóm để thuyết trình.
 *
 * Vai trò:
 *  - SUPER ADMIN : nguyenkhoi020705@gmail.com (Nguyễn Quang Lê Khôi).
 *  - USER  : 9 tài khoản còn lại. Trong đó minhhieut947 là CHỦ LỚP (owner),
 *            thaobee2407 là ĐỒNG CHỦ (co-owner), 7 tài khoản còn lại là SINH VIÊN.
 *
 * Mật khẩu mặc định cho MỌI tài khoản: "password".
 *
 * Chạy sạch: php artisan migrate:fresh --seed
 */
class DemoSeeder extends Seeder
{
    /** Mật khẩu chung cho tài khoản demo. */
    private const DEMO_PASSWORD = 'password';

    /** join_key lớp chính — dùng làm cờ chống seed trùng. */
    private const MAIN_JOIN_KEY = 'WEB2026';

    public function run(): void
    {
        if (CourseClass::where('join_key', self::MAIN_JOIN_KEY)->exists()) {
            $this->command->warn('DemoSeeder: dữ liệu demo đã tồn tại (lớp '.self::MAIN_JOIN_KEY.'). Bỏ qua.');
            return;
        }

        // ---------------------------------------------------------------
        // 1) TÀI KHOẢN
        // ---------------------------------------------------------------
        $admin = $this->user('nguyenkhoi020705@gmail.com', 'Nguyễn Quang Lê Khôi', User::ROLE_SUPER_ADMIN);

        $owner   = $this->user('minhhieut947@gmail.com', 'Trần Minh Hiếu', User::ROLE_USER);   // Chủ lớp
        $coOwner = $this->user('thaobee2407@gmail.com', 'Trần Thị Thu Thảo', User::ROLE_USER);  // Đồng chủ

        // 7 sinh viên có tài khoản: [email, họ tên].
        $studentInfo = [
            ['0306231156@caothang.edu.vn', 'Nguyễn Tuấn Khanh'],
            ['0306231120@caothang.edu.vn', 'Lê Hoàng Nam'],
            ['0306231114@caothang.edu.vn', 'Phạm Thị Mai'],
            ['0306231108@caothang.edu.vn', 'Võ Minh Quân'],
            ['minhtranz6789@gmail.com',    'Trần Gia Bảo'],
            ['minhtranz9867@gmail.com',    'Đỗ Thanh Tùng'],
            ['minhhieut1415@gmail.com',    'Bùi Khánh Linh'],
        ];
        $studentUsers = collect($studentInfo)->map(
            fn ($s) => $this->user($s[0], $s[1], User::ROLE_USER)
        );

        // ---------------------------------------------------------------
        // 2) GÓI PRO + GIAO DỊCH cho chủ lớp (để mở khoá xuất Excel).
        // ---------------------------------------------------------------
        $pro = Plan::where('plan_tier', Plan::TIER_PRO)->first();
        if ($pro) {
            Subscription::create([
                'user_id' => $owner->id,
                'plan_id' => $pro->id,
                'paid_plan_id' => $pro->id,
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(25),
                'status' => 'active',
            ]);
            Transaction::create([
                'user_id' => $owner->id,
                'plan_id' => $pro->id,
                'amount' => $pro->price,
                'currency' => 'VND',
                'payment_method' => 'PAYOS',
                'transaction_code' => 'TXN-'.Str::upper(Str::random(10)),
                'reference_code' => (string) Str::uuid(),
                'status' => 'PAID',
                'paid_at' => now()->subDays(5),
            ]);
        }

        // ---------------------------------------------------------------
        // 3) LỚP HỌC
        //    - WEB : lớp chính, đầy đủ dữ liệu điểm danh + đồng chủ.
        //    - DB  : lớp trống (demo tạo lớp / import).
        //    - SE  : lớp cần duyệt (demo duyệt thành viên).
        // ---------------------------------------------------------------
        $webClass = CourseClass::create([
            'owner_user_id' => $owner->id,
            'join_key' => self::MAIN_JOIN_KEY,
            'name' => 'Lập trình Web',
            'description' => 'Lớp demo có đầy đủ dữ liệu điểm danh & chuyên cần.',
            'deduct_excused_absence' => false,
            'require_approval' => false,
            'status' => 'active',
            'total_sessions' => 15,
        ]);

        CourseClass::create([
            'owner_user_id' => $owner->id,
            'join_key' => 'DB2026',
            'name' => 'Cơ sở dữ liệu',
            'description' => 'Lớp trống — dùng để demo tạo lớp / import sinh viên.',
            'deduct_excused_absence' => false,
            'require_approval' => false,
            'status' => 'active',
            'total_sessions' => 15,
        ]);

        $approvalClass = CourseClass::create([
            'owner_user_id' => $owner->id,
            'join_key' => 'SE2026',
            'name' => 'Công nghệ phần mềm',
            'description' => 'Lớp bật duyệt thành viên — demo duyệt yêu cầu vào lớp.',
            'deduct_excused_absence' => false,
            'require_approval' => true,
            'status' => 'active',
            'total_sessions' => 15,
        ]);

        // Đồng chủ cho lớp WEB.
        $webClass->coOwners()->attach($coOwner->id, [
            'role' => 'co_owner',
            'invited_by' => $owner->id,
            'accepted_at' => now(),
        ]);

        // ---------------------------------------------------------------
        // 4) THÀNH VIÊN lớp WEB: 7 SV có tài khoản + 3 SV chỉ có hồ sơ (import, chưa có TK).
        // ---------------------------------------------------------------
        $members = collect();

        foreach ($studentInfo as $i => $s) {
            $member = ClassMember::create([
                'class_id' => $webClass->id,
                'user_id' => $studentUsers[$i]->id,
                'status' => ClassMember::STATUS_ACTIVE,
            ]);
            $member->syncProfile([
                'full_name' => $s[1],
                'email' => $s[0],
            ]);
            $members->push($member);
        }

        // 3 sinh viên chỉ có hồ sơ (import bằng Excel, chưa liên kết tài khoản).
        $profileOnly = [
            'Đặng Hải Yến',
            'Ngô Quốc Việt',
            'Trịnh Bảo Ngọc',
        ];
        foreach ($profileOnly as $po) {
            $member = ClassMember::create([
                'class_id' => $webClass->id,
                'user_id' => null,
                'status' => ClassMember::STATUS_ACTIVE,
            ]);
            $member->syncProfile([
                'full_name' => $po,
                'email' => null,
            ]);
            $members->push($member);
        }

        // ---------------------------------------------------------------
        // 5) 7 BUỔI đã chốt (mỗi buổi 1 phiên), tổng dự kiến 15 buổi.
        //    Ma trận trạng thái dựng sẵn để có SV cấm thi + SV cảnh báo.
        // ---------------------------------------------------------------
        // Chỉ số thành viên có tài khoản: 0..6. (7..9 là hồ sơ, mặc định present.)
        //  m0 (Khanh)  -> CẤM THI  : vắng 4 buổi  -> ~73%.
        //  m1 (Nam)    -> CẢNH BÁO : muộn 1 + vắng 2 -> ~83%.
        //  m3 (Quân)   -> có 1 buổi VẮNG CÓ PHÉP (đơn đã duyệt).
        $P = 'present'; $A = 'absent'; $L = 'late'; $E = 'excused';
        $matrix = [
            // Buổi:     1   2   3   4   5   6   7
            0 => [$P, $A, $A, $P, $A, $A, $P], // cấm thi
            1 => [$L, $A, $P, $P, $P, $A, $P], // cảnh báo
            2 => [$P, $P, $P, $L, $P, $P, $P],
            3 => [$P, $P, $P, $E, $P, $P, $P], // có phép buổi 4
            4 => [$P, $P, $P, $P, $P, $P, $P],
            5 => [$P, $P, $P, $P, $A, $P, $P],
            6 => [$P, $P, $P, $P, $P, $P, $P],
        ];

        $meetings = [];
        foreach (range(1, 7) as $b) {
            $meeting = ClassMeeting::create([
                'class_id' => $webClass->id,
                'user_Created' => $owner->id,
                'name' => "Buổi $b",
                'date' => now()->subDays((7 - $b) * 7)->toDateString(),
                'start_time' => '07:00:00',
                'end_time' => '09:30:00',
                'status' => 'closed',
            ]);
            $meetings[$b] = $meeting;

            $session = ClassSession::create([
                'meeting_id' => $meeting->id,
                'class_id' => $webClass->id,
                'created_by' => $owner->id,
                'name' => $meeting->name,
                'date' => $meeting->date,
                'start_time' => $meeting->start_time,
                'end_time' => $meeting->end_time,
                'qr_token' => (string) Str::uuid(),
                'token_expires_at' => now()->subDays((7 - $b) * 7),
                'status' => 'closed',
            ]);

            foreach ($members as $idx => $member) {
                $status = $matrix[$idx][$b - 1] ?? $P; // hồ sơ-only -> present.

                AttendanceRecord::create([
                    'class_session_id' => $session->id,
                    'class_member_id' => $member->id,
                    'status' => $status,
                    'is_account' => $member->user_id !== null,
                    'check_in_time' => in_array($status, ['present', 'late'], true)
                        ? now()->subDays((7 - $b) * 7)
                        : null,
                    'note' => $status === $E ? 'Đơn xin nghỉ đã được duyệt.' : null,
                ]);
            }

            // Tổng kết buổi bằng chính service của hệ thống (đồng nhất logic).
            AttendanceCalculator::syncSummaries($meeting);
        }

        // ---------------------------------------------------------------
        // 6) Tổng hợp chuyên cần theo lớp (bảng attendance_summaries — dashboard admin).
        // ---------------------------------------------------------------
        foreach ($members as $member) {
            $records = AttendanceRecord::whereHas('classSession', fn ($q) => $q->where('class_id', $webClass->id))
                ->where('class_member_id', $member->id)
                ->get();
            $absent = $records->where('status', 'absent')->count();

            AttendanceSummary::create([
                'class_id' => $webClass->id,
                'class_member_id' => $member->id,
                'total_present' => $records->where('status', 'present')->count(),
                'total_late' => $records->where('status', 'late')->count(),
                'total_absent' => $absent,
                'total_excused' => $records->where('status', 'excused')->count(),
                'is_banned_from_exam' => $absent > (int) floor($webClass->total_sessions * 0.2),
                'updated_at' => now(),
            ]);
        }

        // ---------------------------------------------------------------
        // 7) ĐƠN XIN NGHỈ: 1 đã duyệt (m3, buổi 4) + 1 đang chờ (m0 — SV cấm thi).
        // ---------------------------------------------------------------
        LeaveRequest::create([
            'class_member_id' => $members[3]->id,
            'class_meeting_id' => $meetings[4]->id,
            'reason' => 'Nghỉ ốm có giấy xác nhận của bác sĩ.',
            'status' => 'approved',
            'reviewed_by' => $owner->id,
            'reviewed_at' => now()->subDays(20),
        ]);
        LeaveRequest::create([
            'class_member_id' => $members[0]->id,
            'class_meeting_id' => $meetings[6]->id,
            'reason' => 'Xin phép nghỉ vì việc gia đình.',
            'status' => 'pending',
        ]);

        // ---------------------------------------------------------------
        // 8) YÊU CẦU VÀO LỚP đang chờ duyệt cho lớp SE (demo duyệt thành viên).
        // ---------------------------------------------------------------
        ClassJoinRequest::create([
            'class_id' => $approvalClass->id,
            'user_id' => $studentUsers[4]->id,
            'status' => ClassJoinRequest::STATUS_PENDING,
        ]);
        ClassJoinRequest::create([
            'class_id' => $approvalClass->id,
            'user_id' => $studentUsers[5]->id,
            'status' => ClassJoinRequest::STATUS_PENDING,
        ]);

        // ---------------------------------------------------------------
        // 9) In thông tin đăng nhập demo.
        // ---------------------------------------------------------------
        $this->command->info('====== DỮ LIỆU DEMO ĐÃ SẴN SÀNG ======');
        $this->command->info('Mật khẩu mọi tài khoản: '.self::DEMO_PASSWORD);
        $this->command->info('SUPER ADMIN: '.$admin->email);
        $this->command->info('CHỦ LỚP   : '.$owner->email.' (lớp Lập trình Web / '.self::MAIN_JOIN_KEY.')');
        $this->command->info('ĐỒNG CHỦ  : '.$coOwner->email);
        $this->command->info('SV CẤM THI: '.$studentUsers[0]->email.' (~73%)');
        $this->command->info('SV CẢNH BÁO: '.$studentUsers[1]->email.' (~83%)');
    }

    /**
     * Tạo (hoặc lấy) tài khoản theo email với vai trò cho trước, mật khẩu demo cố định.
     */
    private function user(string $email, string $name, string $role): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'role' => $role,
                'password' => Hash::make(self::DEMO_PASSWORD),
                'email_verified_at' => now(),
                'status' => 'active',
            ],
        );
    }
}
