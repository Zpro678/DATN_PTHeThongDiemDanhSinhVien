<?php

namespace Tests\Feature;

use App\Livewire\Lecturer\Attendance\AttendanceIndex;
use App\Models\ClassMeeting;
use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Modal "điểm danh nhanh" nhúng danh sách buổi thành JSON rồi Alpine gọi .find()/.some()
 * trên đó. JSON BẮT BUỘC phải là array — nếu là object thì trang nổ
 * "this.meetingsList.find is not a function".
 *
 * Bẫy: Collection::filter() giữ nguyên key. Buổi mới nhất (key 0) hết hạn bị lọc đi
 * thì key còn lại là 1,2,... và toJson() cho ra {"1":{...}} thay vì [{...}].
 */
class AttendanceIndexMeetingsJsonTest extends TestCase
{
    use RefreshDatabase;

    public function test_class_meetings_stays_a_json_array_when_newest_meeting_is_expired(): void
    {
        $owner = User::factory()->create();
        $class = CourseClass::factory()->create(['owner_user_id' => $owner->id, 'status' => 'active']);

        // Buổi MỚI NHẤT (đứng key 0 vì orderBy created_at desc) đã quá giờ kết thúc → bị lọc.
        ClassMeeting::factory()->create([
            'class_id' => $class->id,
            'user_Created' => $owner->id,
            'name' => 'Buổi đã hết hạn',
            'date' => now()->toDateString(),
            'start_time' => '00:05:00',
            'end_time' => '00:10:00',
            'created_at' => now(),
        ]);

        // Buổi cũ hơn nhưng còn hiệu lực → sống sót, nhưng nằm ở key 1.
        ClassMeeting::factory()->create([
            'class_id' => $class->id,
            'user_Created' => $owner->id,
            'name' => 'Buổi còn hiệu lực',
            'date' => now()->toDateString(),
            'start_time' => '00:00:00',
            'end_time' => '23:59:00',
            'created_at' => now()->subHour(),
        ]);

        $component = Livewire::actingAs($owner)
            ->test(AttendanceIndex::class)
            ->set('quickClassId', $class->id);

        $meetings = $component->instance()->classMeetings();

        $this->assertCount(1, $meetings, 'Chỉ buổi còn hiệu lực được giữ lại.');
        $this->assertSame([0], $meetings->keys()->all(), 'Key phải được đánh lại từ 0 sau khi filter.');

        $json = $meetings->values()->map(fn ($m) => ['id' => $m->id, 'name' => $m->name])->toJson();

        $this->assertStringStartsWith('[', $json, 'JSON phải là array thì Alpine mới gọi .find() được.');
        $this->assertJson($json);
        $this->assertIsArray(json_decode($json, true));
    }
}
