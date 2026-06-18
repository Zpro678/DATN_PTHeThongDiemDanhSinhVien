<?php

$members = \App\Models\ClassMember::take(20)->get();
foreach($members as $m) {
    $session = \App\Models\ClassSession::where('class_id', $m->course_class_id)->first();
    if($session && \App\Models\LeaveRequest::where('class_member_id', $m->id)->count() < 3) {
        \App\Models\LeaveRequest::factory()->create(['class_member_id' => $m->id, 'class_session_id' => $session->id]);
        \App\Models\LeaveRequest::factory()->approved()->create(['class_member_id' => $m->id, 'class_session_id' => $session->id]);
        \App\Models\LeaveRequest::factory()->rejected()->create(['class_member_id' => $m->id, 'class_session_id' => $session->id]);
    }
}
echo "Seeded leave requests successfully!\n";
