<?php
$profiles = App\Models\ClassMemberProfile::whereColumn('email', 'full_name')->get();
foreach ($profiles as $p) {
    if (stripos($p->student_code, 'Sinh vi') !== false) {
        $p->full_name = $p->student_code;
        $p->student_code = null;
        $p->save();
    }
}
echo "Done";
