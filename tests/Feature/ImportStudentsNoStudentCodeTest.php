<?php

namespace Tests\Feature;

use App\Jobs\ImportStudentsChunkJob;
use App\Models\ClassMember;
use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Import danh sách SV sau khi GỠ cột MSSV: file chỉ còn [Họ và tên, Email].
 * emailColIndex=1, nameColIndex=0 (do HeadingRowImport phát hiện), không còn codeColIndex.
 */
class ImportStudentsNoStudentCodeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Cần gói FREE (giới hạn 50 SV/lớp) để job không bỏ qua do "vượt giới hạn gói".
        $this->seed(\Database\Seeders\PlanSeeder::class);
    }

    public function test_import_two_column_file_creates_members(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $class = CourseClass::factory()->create(['owner_user_id' => $owner->id]);

        $rows = [
            ['Nguyễn Tuấn An', 'annt@gmail.com'],
            ['Trần Thị Bích', 'bichttt@gmail.com'],
            ['Lê Văn Cường', 'cuonglv@gmail.com'],
        ];

        (new ImportStudentsChunkJob(
            (string) $class->id, $rows, [], [], emailColIndex: 1, nameColIndex: 0, authUserId: (int) $owner->id, importToken: 'TOK-IMPORT-1'
        ))->handle();

        $members = ClassMember::where('class_id', $class->id)->with('profile')->get();
        $this->assertCount(3, $members);

        // Bộ đếm SV nhập thành công (để hiển thị "Đã nhập N học viên").
        $this->assertSame(3, (int) cache()->get('import_success_TOK-IMPORT-1'));

        $this->assertDatabaseHas('class_member_profiles', ['full_name' => 'Nguyễn Tuấn An', 'email' => 'annt@gmail.com']);
        $this->assertDatabaseHas('class_member_profiles', ['full_name' => 'Trần Thị Bích', 'email' => 'bichttt@gmail.com']);
        $this->assertDatabaseHas('class_member_profiles', ['full_name' => 'Lê Văn Cường', 'email' => 'cuonglv@gmail.com']);
    }

    public function test_import_links_existing_account_by_email(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $class = CourseClass::factory()->create(['owner_user_id' => $owner->id]);
        $student = User::factory()->create(['email' => 'linkme@gmail.com']);

        (new ImportStudentsChunkJob(
            (string) $class->id,
            [['Trần Minh Hiếu', 'linkme@gmail.com']],
            [], [], emailColIndex: 1, nameColIndex: 0, authUserId: (int) $owner->id
        ))->handle();

        $member = ClassMember::where('class_id', $class->id)->first();
        $this->assertNotNull($member);
        $this->assertSame($student->id, $member->user_id, 'Import phải liên kết SV theo email với tài khoản đã có.');
    }
}
