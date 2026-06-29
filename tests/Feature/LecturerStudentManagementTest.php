<?php

namespace Tests\Feature;

use App\Livewire\Lecturer\Students\LeaveRequestIndex;
use App\Livewire\Lecturer\Students\StudentIndex;
use App\Models\AttendanceRecord;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LecturerStudentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_open_student_and_leave_request_pages(): void
    {
        [$owner, $member, $leaveRequest] = $this->createOwnedLeaveRequest();

        $this->actingAs($owner)->get(route('lecturer.students.index'))
            ->assertOk()
            ->assertSee($member->full_name);

        $this->actingAs($owner)->get(route('lecturer.students.show', $member))
            ->assertOk()
            ->assertSee($member->student_code);

        $this->actingAs($owner)->get(route('lecturer.leave-requests.index'))
            ->assertOk()
            ->assertSee($leaveRequest->reason);

        $this->actingAs($owner)->get(route('lecturer.leave-requests.show', $leaveRequest))
            ->assertOk()
            ->assertSee('Chi tiết đơn xin nghỉ');

        $this->actingAs(User::factory()->create())
            ->get(route('lecturer.students.show', $member))
            ->assertNotFound();
    }

    public function test_owner_can_edit_archive_and_restore_a_student(): void
    {
        $owner = User::factory()->create();
        $courseClass = CourseClass::factory()->create(['owner_user_id' => $owner->id]);
        $member = ClassMember::factory()->create(['class_id' => $courseClass->id]);

        Livewire::actingAs($owner)
            ->test(StudentIndex::class)
            ->call('openEdit', $member->id)
            ->set('editingName', 'Sinh viên đã cập nhật')
            ->set('editingStudentCode', 'SV-UPDATED')
            ->call('saveMember')
            ->assertHasNoErrors()
            ->call('archiveMember', $member->id);

        $this->assertSoftDeleted('class_members', ['id' => $member->id]);

        Livewire::actingAs($owner)
            ->test(StudentIndex::class)
            ->call('restoreMember', $member->id);

        $this->assertDatabaseHas('class_members', [
            'id' => $member->id,
            'full_name' => 'Sinh viên đã cập nhật',
            'student_code' => 'SV-UPDATED',
            'status' => 'active',
            'deleted_at' => null,
        ]);
    }

    public function test_approving_leave_request_updates_attendance_record(): void
    {
        [$owner, $member, $leaveRequest] = $this->createOwnedLeaveRequest();

        AttendanceRecord::factory()->create([
            'class_session_id' => $leaveRequest->class_session_id,
            'class_member_id' => $member->id,
            'status' => 'absent',
        ]);

        Livewire::actingAs($owner)
            ->test(LeaveRequestIndex::class, ['status' => 'pending'])
            ->call('approve', $leaveRequest->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'approved',
            'reviewed_by' => $owner->id,
        ]);
        $this->assertDatabaseHas('attendance_records', [
            'class_session_id' => $leaveRequest->class_session_id,
            'class_member_id' => $member->id,
            'status' => 'excused',
            'note' => 'Đơn xin nghỉ đã được duyệt.',
        ]);
    }

    public function test_rejecting_leave_request_records_the_reason(): void
    {
        [$owner, , $leaveRequest] = $this->createOwnedLeaveRequest();

        Livewire::actingAs($owner)
            ->test(LeaveRequestIndex::class, ['status' => 'pending'])
            ->call('openReject', $leaveRequest->id)
            ->set('rejectedReason', 'Minh chứng chưa đáp ứng yêu cầu.')
            ->call('reject')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'rejected',
            'rejected_reason' => 'Minh chứng chưa đáp ứng yêu cầu.',
            'reviewed_by' => $owner->id,
        ]);
    }

    public function test_dashboard_actions_link_to_student_management_pages(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('lecturer.students.index'), false)
            ->assertSee(route('lecturer.leave-requests.index'), false);
    }

    /**
     * @return array{User, ClassMember, LeaveRequest}
     */
    private function createOwnedLeaveRequest(): array
    {
        $owner = User::factory()->create();
        $student = User::factory()->create();
        $courseClass = CourseClass::factory()->create(['owner_user_id' => $owner->id]);
        $member = ClassMember::factory()->create([
            'class_id' => $courseClass->id,
            'user_id' => $student->id,
            'student_code' => $student->member_id,
            'full_name' => $student->name,
        ]);
        $session = ClassSession::factory()->create([
            'class_id' => $courseClass->id,
            'created_by' => $owner->id,
        ]);
        $leaveRequest = LeaveRequest::factory()->create([
            'class_member_id' => $member->id,
            'class_session_id' => $session->id,
            'reason' => 'Nghỉ học vì lý do sức khỏe.',
        ]);

        return [$owner, $member, $leaveRequest];
    }
}
