<?php

use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\LeaveRequest;

$members = ClassMember::take(20)->get();
foreach ($members as $m) {
    $session = ClassSession::where('class_id', $m->course_class_id)->first();
    if ($session && LeaveRequest::where('class_member_id', $m->id)->count() < 3) {
        LeaveRequest::factory()->create(['class_member_id' => $m->id, 'class_session_id' => $session->id]);
        LeaveRequest::factory()->approved()->create(['class_member_id' => $m->id, 'class_session_id' => $session->id]);
        LeaveRequest::factory()->rejected()->create(['class_member_id' => $m->id, 'class_session_id' => $session->id]);
    }
}
echo "Seeded leave requests successfully!\n";
