<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CourseClass;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\AttendanceRecord;
use App\Models\User;

$user = User::first();
if (!$user) {
    echo "No users found.\n";
    exit;
}

$class = CourseClass::create([
    "owner_user_id" => $user->id,
    "name" => "Test Class Import",
    "join_key" => "TEST-IMP-" . time(),
    "status" => "active",
    "total_lessons" => 15,
]);
echo "Created class {$class->name}\n";

$session = ClassSession::create([
    "class_id" => $class->id,
    "created_by" => $user->id,
    "name" => "Buổi 1",
    "date" => now()->toDateString(),
    "status" => "active",
]);
echo "Created session {$session->name}\n";

$member1 = ClassMember::create([
    "class_id" => $class->id,
    "full_name" => "Nguyen Van A",
    "student_code" => "SV001",
    "status" => "active",
]);
echo "Created student 1\n";

AttendanceRecord::create([
    "class_session_id" => $session->id,
    "class_member_id" => $member1->id,
    "status" => "present",
]);
echo "Created attendance record for student 1\n";

$member2 = ClassMember::create([
    "class_id" => $class->id,
    "full_name" => "Tran Thi B",
    "student_code" => "SV002",
    "status" => "active",
]);
echo "Created student 2\n";

$sessions = ClassSession::where("class_id", $class->id)->get();
$members = ClassMember::where("class_id", $class->id)->where("status", "active")->get();
$recordsToInsert = [];
$now = now();
foreach ($sessions as $sessionObj) {
    foreach ($members as $member) {
        $recordsToInsert[] = [
            "class_session_id" => $sessionObj->id,
            "class_member_id" => $member->id,
            "status" => "pending",
            "is_verified" => $member->user_id !== null,
            "created_at" => $now,
            "updated_at" => $now,
        ];
    }
}
if (!empty($recordsToInsert)) {
    AttendanceRecord::insertOrIgnore($recordsToInsert);
    echo "Ran insertOrIgnore\n";
}

$records = AttendanceRecord::where("class_session_id", $session->id)->get();
echo "Records found: " . $records->count() . "\n";
foreach ($records as $record) {
    $member = ClassMember::find($record->class_member_id);
    echo "- Student: {$member->full_name}, Status: {$record->status}\n";
}

$class->forceDelete();
echo "Cleaned up\n";

