<?php

namespace Database\Seeders;

use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeaveRequestDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tìm user student@example.com
        $student = User::where('email', 'student@example.com')->first();
        if (!$student) {
            $this->command->error("Không tìm thấy user student@example.com. Vui lòng chạy seeder chính trước.");
            return;
        }

        // 2. Tìm các class membership của sinh viên này
        $memberships = ClassMember::where('user_id', $student->id)
            ->orWhere('student_code', $student->student_code)
            ->get();

        if ($memberships->isEmpty()) {
            $this->command->error("User student@example.com chưa tham gia lớp học nào.");
            return;
        }

        $membership = $memberships->first();
        $classId = $membership->class_id;
        $tenantId = $membership->tenant_id;

        // 3. Lấy hoặc tạo thêm 3 ClassSession phục vụ việc seed đơn xin nghỉ
        $sessions = ClassSession::where('class_id', $classId)->take(3)->get();

        // Nếu thiếu session thì tạo thêm cho đủ để test đơn xin nghỉ
        while ($sessions->count() < 3) {
            $sessionCount = ClassSession::where('class_id', $classId)->count() + 1;
            ClassSession::create([
                'tenant_id' => $tenantId,
                'class_id' => $classId,
                'name' => "Buổi học bổ sung {$sessionCount}",
                'date' => now()->subDays($sessionCount)->toDateString(),
                'start_time' => '07:30:00',
                'end_time' => '10:00:00',
                'status' => 'closed',
            ]);
            $sessions = ClassSession::where('class_id', $classId)->take(3)->get();
        }

        $teacher = User::where('email', 'teacher@example.com')->first();
        $reviewerId = $teacher ? $teacher->id : null;

        // Xóa các leave request cũ của học viên này để tránh trùng lặp khi chạy đi chạy lại
        LeaveRequest::where('class_member_id', $membership->id)->delete();

        // 4. Tạo đơn 1: Đang chờ duyệt (pending)
        LeaveRequest::create([
            'tenant_id' => $tenantId,
            'class_member_id' => $membership->id,
            'class_session_id' => $sessions[0]->id,
            'reason' => 'Em bị sốt cao đột xuất 39 độ, có giấy khám của bác sĩ tại bệnh viện quận.',
            'proof_image' => null,
            'status' => 'pending',
        ]);

        // 5. Tạo đơn 2: Đã duyệt (approved)
        LeaveRequest::create([
            'tenant_id' => $tenantId,
            'class_member_id' => $membership->id,
            'class_session_id' => $sessions[1]->id,
            'reason' => 'Đại diện trường tham gia giải thi đấu Thể thao điện tử toàn quốc.',
            'proof_image' => null,
            'status' => 'approved',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now()->subDays(2),
        ]);

        // 6. Tạo đơn 3: Từ chối (rejected)
        LeaveRequest::create([
            'tenant_id' => $tenantId,
            'class_member_id' => $membership->id,
            'class_session_id' => $sessions[2]->id,
            'reason' => 'Em xin phép nghỉ học đi du lịch cùng gia đình 3 ngày tại Đà Lạt.',
            'proof_image' => null,
            'status' => 'rejected',
            'rejected_reason' => 'Lý do cá nhân đi du lịch không được chấp nhận nghỉ học chính khóa.',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now()->subDays(4),
        ]);

        $this->command->info("Đã seed thành công 3 đơn xin nghỉ học mẫu với các trạng thái khác nhau cho sinh viên Tran Minh Sinh.");
    }
}
