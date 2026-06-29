<?php

namespace Tests\Feature\Database;

use App\Models\AttendanceRecord;
use App\Models\CourseClass;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDataModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_attendance_data_has_consistent_relationships(): void
    {
        $this->seed();

        $teacher = User::query()->where('email', 'teacher@example.com')->firstOrFail();
        $student = User::query()->where('email', 'student1@example.com')->firstOrFail();
        $courseClass = CourseClass::query()->where('join_key', 'WEB-2026-01')->firstOrFail();

        $this->assertCount(3, $teacher->ownedClasses);
        $this->assertCount(11, $courseClass->members);
        $this->assertCount(11, $courseClass->users);
        $this->assertCount(5, $courseClass->sessions);
        $this->assertCount(12, $courseClass->attendanceSummaries);
        $this->assertTrue($student->joinedClasses->contains($courseClass));
        $this->assertSame($teacher->id, $courseClass->owner->id);
        $this->assertSame(12, $courseClass->sessions->first()->attendanceRecords()->count());
        $this->assertDatabaseCount('attendance_records', 48);
        $this->assertDatabaseCount('attendance_summaries', 12);
        $this->assertDatabaseHas('attendance_records', [
            'status' => 'excused',
            'note' => 'Đơn xin nghỉ đã được duyệt.',
        ]);

        $courseClass->members->first()->delete();

        $this->assertCount(10, $courseClass->users()->get());
    }

    public function test_seeded_billing_notifications_and_audit_data_are_accessible(): void
    {
        $this->seed();

        $teacher = User::query()->where('email', 'teacher@example.com')->firstOrFail();

        $this->assertSame('PRO', $teacher->subscriptions()->firstOrFail()->plan->plan_tier);
        $this->assertSame('success', $teacher->transactions()->firstOrFail()->status);
        $this->assertCount(1, $teacher->notifications);
        $this->assertGreaterThan(0, $teacher->auditLogs()->count());
        $this->assertSame(3, Plan::query()->count());
        $this->assertSame(48, AttendanceRecord::query()->count());
    }
}
