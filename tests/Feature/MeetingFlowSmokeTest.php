<?php

namespace Tests\Feature;

use App\Livewire\Lecturer\Attendance\AttendanceCreate;
use App\Livewire\Lecturer\Attendance\AttendanceIndex;
use App\Livewire\Lecturer\Attendance\ManualAttendanceSession;
use App\Livewire\Lecturer\Attendance\MeetingSessions;
use App\Livewire\Lecturer\Attendance\MeetingSummary;
use App\Livewire\Lecturer\Attendance\QuickAttendanceModal;
use App\Livewire\Lecturer\Attendance\QrAttendanceCreate;
use App\Livewire\Lecturer\ClassShow as LecturerClassShow;
use App\Models\AttendanceRecord;
use App\Models\ClassMeeting;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class MeetingFlowSmokeTest extends TestCase
{
    use RefreshDatabase;

    private function makeMeetingWithClosedSession(): array
    {
        $owner = User::factory()->create();
        URL::defaults(['ma_user' => $owner->id]);
        $courseClass = CourseClass::factory()->create(['owner_user_id' => $owner->id, 'total_sessions' => 15]);

        $members = collect([['SV01', 'An'], ['SV02', 'Binh']])->map(function ($s) use ($courseClass) {
            $member = ClassMember::factory()->withoutProfile()->create([
                'class_id' => $courseClass->id,
                'status' => ClassMember::STATUS_ACTIVE,
            ]);
            $member->syncProfile(['student_code' => $s[0], 'full_name' => $s[1]]);

            return $member;
        });

        $meeting = ClassMeeting::query()->create([
            'class_id' => $courseClass->id,
            'user_Created' => $owner->id,
            'name' => 'Buổi 1 - Demo',
            'date' => now()->toDateString(),
            'start_time' => '07:00:00',
            'end_time' => '09:30:00',
            'status' => 'closed',
        ]);

        $session = ClassSession::factory()->create([
            'class_id' => $courseClass->id,
            'meeting_id' => $meeting->id,
            'created_by' => $owner->id,
            'qr_token' => null,
            'status' => 'closed',
        ]);

        AttendanceRecord::factory()->create(['class_session_id' => $session->id, 'class_member_id' => $members[0]->id, 'status' => 'present']);
        AttendanceRecord::factory()->create(['class_session_id' => $session->id, 'class_member_id' => $members[1]->id, 'status' => 'absent']);

        return [$owner, $courseClass, $meeting];
    }

    public function test_index_lists_meetings_with_consolidated_counts(): void
    {
        [$owner, , $meeting] = $this->makeMeetingWithClosedSession();

        Livewire::actingAs($owner)
            ->test(AttendanceIndex::class)
            ->assertOk()
            ->assertSee('Buổi 1 - Demo')
            ->assertSee('1 phiên')
            ->assertSee('Đã chốt');
    }

    public function test_meeting_sessions_lists_sessions_not_students(): void
    {
        [$owner, , $meeting] = $this->makeMeetingWithClosedSession();

        Livewire::actingAs($owner)
            ->test(MeetingSessions::class, ['meeting' => $meeting])
            ->assertOk()
            ->assertSee('Danh sách phiên')
            ->assertSee('Lần 1');
    }

    public function test_class_show_renders_with_shared_quick_modal(): void
    {
        $owner = User::factory()->create();
        URL::defaults(['ma_user' => $owner->id]);
        $courseClass = CourseClass::factory()->create(['owner_user_id' => $owner->id]);
        ClassMember::factory()->create([
            'class_id' => $courseClass->id,
            'status' => ClassMember::STATUS_ACTIVE,
        ]);

        Livewire::actingAs($owner)
            ->test(LecturerClassShow::class, ['courseClass' => $courseClass])
            ->assertOk()
            ->assertSee('QR');
    }

    public function test_meeting_summary_keeps_explicit_late_status(): void
    {
        [$owner, , $meeting] = $this->makeMeetingWithClosedSession();

        $record = AttendanceRecord::query()
            ->whereHas('classMember.profile', fn ($query) => $query->where('student_code', 'SV01'))
            ->whereHas('classSession', fn ($query) => $query->where('meeting_id', $meeting->id))
            ->firstOrFail();
        $record->update(['status' => 'late']);

        Livewire::actingAs($owner)
            ->test(MeetingSummary::class, ['meeting' => $meeting])
            ->assertSet("draftStatuses.{$record->class_member_id}", 'late')
            ->assertSee('Đi muộn');

        $this->assertDatabaseHas('meeting_summaries', [
            'meeting_id' => $meeting->id,
            'class_member_id' => $record->class_member_id,
            'status' => 'late',
            'auto_status' => 'late',
        ]);
    }

    public function test_add_session_keeps_same_meeting(): void
    {
        [$owner, , $meeting] = $this->makeMeetingWithClosedSession();
        // Buổi còn trong giờ (ngày mai) nên vẫn được mở lại thêm phiên.
        $meeting->update(['date' => now()->addDay()->toDateString()]);

        Livewire::actingAs($owner)
            ->test(AttendanceIndex::class)
            ->call('cloneAndStartManual', $meeting->id);

        $meeting->refresh();
        $this->assertSame(2, $meeting->sessions()->count());
        $newSession = $meeting->sessions()->latest('id')->first();
        $this->assertSame('active', $newSession->status);
        $this->assertSame(2, AttendanceRecord::where('class_session_id', $newSession->id)->count());
    }

    public function test_create_manual_makes_meeting_and_first_session(): void
    {
        $owner = User::factory()->create();
        URL::defaults(['ma_user' => $owner->id]);
        $courseClass = CourseClass::factory()->create(['owner_user_id' => $owner->id, 'total_sessions' => 15]);
        ClassMember::factory()->create(['class_id' => $courseClass->id, 'status' => ClassMember::STATUS_ACTIVE]);

        Livewire::actingAs($owner)
            ->test(AttendanceCreate::class)
            ->set('classId', (string) $courseClass->id)
            ->set('name', 'Buổi mới')
            ->set('date', now()->toDateString())
            ->call('createManualSession')
            ->assertHasNoErrors();

        $meeting = ClassMeeting::where('class_id', $courseClass->id)->firstOrFail();
        $this->assertSame('Buổi mới', $meeting->name);
        $this->assertSame(1, $meeting->sessions()->count());
    }

    public function test_qr_create_adds_session_to_existing_meeting(): void
    {
        [$owner, , $meeting] = $this->makeMeetingWithClosedSession();
        // Buổi còn trong giờ (ngày mai) nên vẫn được thêm phiên QR.
        $meeting->update(['date' => now()->addDay()->toDateString()]);

        Livewire::actingAs($owner)
            ->test(QrAttendanceCreate::class)
            ->set('meetingId', $meeting->id)
            ->set('classId', (string) $meeting->class_id)
            ->set('name', $meeting->name)
            ->set('date', $meeting->date->toDateString())
            ->set('gpsEnabled', false)
            ->call('save')
            ->assertHasNoErrors();

        $meeting->refresh();
        $this->assertSame(2, $meeting->sessions()->count());
        $newSession = $meeting->sessions()->latest('id')->first();
        $this->assertNotNull($newSession->qr_token);
    }

    public function test_quick_modal_adds_qr_session_to_current_meeting(): void
    {
        [$owner, , $meeting] = $this->makeMeetingWithClosedSession();
        // Buổi còn trong giờ (ngày mai) nên modal được phép tạo thêm phiên QR.
        $meeting->update(['date' => now()->addDay()->toDateString(), 'status' => 'active']);

        Livewire::actingAs($owner)
            ->test(QuickAttendanceModal::class)
            ->call('open', 'qr', null, $meeting->id)
            ->assertSet('showQuickStart', true)
            ->assertSet('lockClassSelector', true)
            ->assertSet('lockMeetingSelector', true)
            ->assertSet('quickMeetingId', (string) $meeting->id)
            ->set('gpsEnabled', false)
            ->call('startQuick')
            ->assertHasNoErrors();

        $meeting->refresh();
        $this->assertSame(2, $meeting->sessions()->count());
        $newSession = $meeting->sessions()->latest('id')->first();
        $this->assertNotNull($newSession->qr_token);
        $this->assertSame('Phiên 2', $newSession->name);
    }

    public function test_cannot_add_session_after_meeting_ended(): void
    {
        [$owner, , $meeting] = $this->makeMeetingWithClosedSession();
        // Buổi đã quá giờ kết thúc (hôm qua) -> không được thêm phiên.
        $meeting->update(['date' => now()->subDay()->toDateString()]);

        $this->assertFalse($meeting->fresh()->canAddSession());

        Livewire::actingAs($owner)
            ->test(AttendanceIndex::class)
            ->call('cloneAndStartManual', $meeting->id);

        $this->assertSame(1, $meeting->sessions()->count());
    }

    public function test_manual_marking_is_temporary_until_save_session(): void
    {
        $owner = User::factory()->create();
        URL::defaults(['ma_user' => $owner->id]);
        $courseClass = CourseClass::factory()->create(['owner_user_id' => $owner->id, 'total_sessions' => 15]);
        $member = ClassMember::factory()->create(['class_id' => $courseClass->id, 'status' => ClassMember::STATUS_ACTIVE]);

        $meeting = ClassMeeting::query()->create([
            'class_id' => $courseClass->id, 'user_Created' => $owner->id, 'name' => 'Buổi 1',
            'date' => now()->toDateString(), 'status' => 'active',
        ]);
        $session = ClassSession::factory()->create([
            'class_id' => $courseClass->id, 'meeting_id' => $meeting->id, 'created_by' => $owner->id,
            'qr_token' => null, 'status' => 'active',
        ]);
        $record = AttendanceRecord::factory()->create([
            'class_session_id' => $session->id, 'class_member_id' => $member->id, 'status' => 'pending',
        ]);

        $component = Livewire::actingAs($owner)
            ->test(ManualAttendanceSession::class, ['session' => $session->id])
            ->call('setStatus', $record->id, 'present');

        // Đánh dấu tạm thời: chưa ghi DB.
        $this->assertSame('pending', $record->fresh()->status);

        // Lưu phiên: ghi DB, phiên KHÔNG bị chốt.
        $component->call('saveSession')->assertHasNoErrors();
        $this->assertSame('present', $record->fresh()->status);
        $this->assertSame('active', $session->fresh()->status);
    }
}
