<?php

namespace Tests\Feature;

use App\Livewire\Lecturer\Students\StudentIndex;
use App\Livewire\Student\JoinClass;
use App\Models\AttendanceRecord;
use App\Models\ClassJoinRequest;
use App\Models\ClassMeeting;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Sinh viên bị xoá khỏi lớp chỉ bị SOFT DELETE, trong khi unique(class_id, user_id) KHÔNG
 * tính deleted_at. Vì vậy mọi đường "vào lớp lại" phải KHÔI PHỤC bản ghi cũ, không được
 * INSERT mới (trước đây gây SQLSTATE[23000] 1062 Duplicate entry).
 */
class StudentRejoinClassTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: CourseClass, 1: User, 2: ClassMember} */
    private function makeRemovedMember(bool $requireApproval = false): array
    {
        $owner = User::factory()->create();
        $class = CourseClass::factory()->create([
            'owner_user_id' => $owner->id,
            'join_key' => 'ABC123',
            'require_approval' => $requireApproval,
        ]);

        $student = User::factory()->create();
        $member = ClassMember::create([
            'class_id' => $class->id,
            'user_id' => $student->id,
            'status' => ClassMember::STATUS_ACTIVE,
        ]);
        $member->syncProfile(['full_name' => $student->name, 'email' => $student->email]);

        // Một buổi điểm danh cũ để chứng minh lịch sử được giữ lại sau khi quay lại lớp.
        $meeting = ClassMeeting::factory()->create([
            'class_id' => $class->id, 'user_Created' => $owner->id,
            'date' => '2026-07-01', 'start_time' => '08:00:00', 'end_time' => '10:00:00', 'status' => 'closed',
        ]);
        $session = ClassSession::factory()->create([
            'class_id' => $class->id, 'meeting_id' => $meeting->id, 'created_by' => $owner->id,
            'date' => '2026-07-01', 'status' => 'closed',
        ]);
        AttendanceRecord::factory()->create([
            'class_session_id' => $session->id, 'class_member_id' => $member->id, 'status' => 'present',
        ]);

        // Giảng viên xoá em này khỏi lớp (giống archiveMember()).
        $member->update(['status' => ClassMember::STATUS_REMOVED, 'status_changed_at' => now()]);
        $member->delete();

        return [$class, $student, $member];
    }

    public function test_removed_student_can_rejoin_by_code_without_duplicate_error(): void
    {
        [$class, $student, $member] = $this->makeRemovedMember();

        Livewire::actingAs($student)->test(JoinClass::class)
            ->set('class_code', 'ABC123')
            ->call('checkCode')
            ->call('confirmJoin')
            ->assertHasNoErrors();

        // Không tạo bản ghi thứ hai — vẫn đúng 1 dòng, chính là dòng cũ đã được khôi phục.
        $this->assertSame(1, ClassMember::withTrashed()->where('class_id', $class->id)->count());

        $member->refresh();
        $this->assertNull($member->deleted_at);
        $this->assertSame(ClassMember::STATUS_ACTIVE, $member->status);
        $this->assertNull($member->status_changed_at);
    }

    public function test_rejoining_keeps_previous_attendance_history(): void
    {
        [$class, $student, $member] = $this->makeRemovedMember();

        Livewire::actingAs($student)->test(JoinClass::class)
            ->set('class_code', 'ABC123')
            ->call('checkCode')
            ->call('confirmJoin')
            ->assertHasNoErrors();

        // Giữ nguyên class_member_id nên bản ghi điểm danh cũ vẫn thuộc về em này.
        $this->assertSame(1, AttendanceRecord::where('class_member_id', $member->id)->count());
        $this->assertSame($member->id, ClassMember::where('class_id', $class->id)->first()->id);
    }

    public function test_rejoining_class_with_approval_creates_request_instead_of_restoring(): void
    {
        [$class, $student, $member] = $this->makeRemovedMember(requireApproval: true);

        Livewire::actingAs($student)->test(JoinClass::class)
            ->set('class_code', 'ABC123')
            ->call('checkCode')
            ->call('confirmJoin')
            ->assertHasNoErrors();

        // Lớp cần duyệt: chưa được tự khôi phục, phải chờ giảng viên.
        $member->refresh();
        $this->assertNotNull($member->deleted_at);
        $this->assertSame(1, ClassJoinRequest::where('class_id', $class->id)->where('user_id', $student->id)->count());
    }

    public function test_lecturer_approving_request_restores_removed_member(): void
    {
        [$class, $student, $member] = $this->makeRemovedMember(requireApproval: true);

        $request = ClassJoinRequest::create([
            'class_id' => $class->id,
            'user_id' => $student->id,
            'status' => ClassJoinRequest::STATUS_PENDING,
        ]);

        Livewire::actingAs($class->owner)->test(StudentIndex::class, ['courseClass' => $class])
            ->call('approveRequest', $request->id);

        $this->assertSame(1, ClassMember::withTrashed()->where('class_id', $class->id)->count());
        $member->refresh();
        $this->assertNull($member->deleted_at);
        $this->assertSame(ClassMember::STATUS_ACTIVE, $member->status);
    }

    public function test_lecturer_adding_removed_student_by_email_restores_instead_of_duplicating(): void
    {
        [$class, $student, $member] = $this->makeRemovedMember();

        Livewire::actingAs($class->owner)->test(StudentIndex::class, ['courseClass' => $class])
            ->set('newClassId', $class->id)
            ->set('newName', $student->name)
            ->set('newEmail', $student->email)
            ->call('addMember')
            ->assertHasNoErrors();

        $this->assertSame(1, ClassMember::withTrashed()->where('class_id', $class->id)->count());
        $member->refresh();
        $this->assertNull($member->deleted_at);
        $this->assertSame(ClassMember::STATUS_ACTIVE, $member->status);
    }

    public function test_lecturer_adding_brand_new_student_still_creates_member(): void
    {
        [$class, $student, $member] = $this->makeRemovedMember();

        Livewire::actingAs($class->owner)->test(StudentIndex::class, ['courseClass' => $class])
            ->set('newClassId', $class->id)
            ->set('newName', 'Người Hoàn Toàn Mới')
            ->set('newEmail', 'nguoimoi@example.com')
            ->call('addMember')
            ->assertHasNoErrors();

        // Không được nhận nhầm vào bản ghi cũ đang bị xoá.
        $this->assertSame(2, ClassMember::withTrashed()->where('class_id', $class->id)->count());
        $member->refresh();
        $this->assertNotNull($member->deleted_at);
    }
}
