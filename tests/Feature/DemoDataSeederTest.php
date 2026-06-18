<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Models\LeaveRequest;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seed_creates_accounts_and_lesson_data_for_manual_testing(): void
    {
        $this->seed(DatabaseSeeder::class);

        $teacher = User::query()->where('email', 'teacher@example.com')->firstOrFail();

        $this->assertDatabaseHas('users', ['email' => 'admin@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'student1@example.com']);
        $this->assertGreaterThanOrEqual(2, CourseClass::query()->where('owner_user_id', $teacher->id)->count());
        $this->assertGreaterThanOrEqual(4, ClassSession::query()->where('created_by', $teacher->id)->count());
        $this->assertGreaterThanOrEqual(1, LeaveRequest::query()->where('status', 'pending')->count());
        $this->assertGreaterThanOrEqual(20, AttendanceRecord::query()->count());

        foreach ([
            'dashboard',
            'lecturer.students.index',
            'lecturer.leave-requests.index',
            'lecturer.attendance.index',
            'lecturer.attendance.create',
            'lecturer.attendance.manual.create',
            'lecturer.attendance.qr.create',
        ] as $routeName) {
            $this->actingAs($teacher)
                ->get(route($routeName))
                ->assertOk();
        }
    }
}
