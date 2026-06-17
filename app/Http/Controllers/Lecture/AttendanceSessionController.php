<?php

namespace App\Http\Controllers\Lecture;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;


class AttendanceSessionController extends Controller
{
    public function index() {
        return view('lecture.attendance.management_attendace');
    }

    public function manualAttendance(){
        $classes = $this->teacherClassOptions();

        return view('lecture.attendance.create_manual_attendance', compact('classes'));
    }

    public function activeManualAttendance(Request $request){
        $classes = $this->teacherClassOptions();
        $selectedClassId = (string) $request->input('class_id', $classes->first()['id'] ?? '');
        $selectedClass = null;

        if (ctype_digit($selectedClassId)) {
            $selectedClass = CourseClass::query()
                ->where('owner_id', auth()->id())
                ->with(['members' => fn ($query) => $query->where('status', 'active')->orderBy('full_name')])
                ->withCount(['members as members_count' => fn ($query) => $query->where('status', 'active')])
                ->find((int) $selectedClassId);
        }

        $classInfo = $selectedClass
            ? [
                'id' => $selectedClass->id,
                'label' => trim($selectedClass->name . ' - ' . $selectedClass->code, ' -'),
                'name' => $selectedClass->name,
                'code' => $selectedClass->code,
                'subject_code' => $selectedClass->subject_code,
                'members_count' => $selectedClass->members_count,
            ]
            : ($classes->firstWhere('id', $selectedClassId) ?? $this->fallbackClasses()->first());

        $students = $selectedClass && $selectedClass->members->isNotEmpty()
            ? $selectedClass->members->values()->map(fn ($member, int $index) => [
                'id' => $member->id,
                'code' => $member->student_code ?: sprintf('SV%03d', $index + 1),
                'name' => $member->full_name,
                'avatarChar' => $this->firstCharacter($member->full_name),
                'status' => 'unmarked',
                'note' => '',
            ])->all()
            : $this->fallbackStudents();

        $sessionDate = $request->input('session_date', now()->toDateString());
        $sessionDateDisplay = $this->formatDate($sessionDate);
        $startPeriod = (int) $request->input('start_period', 1);
        $endPeriod = (int) $request->input('end_period', 3);
        $startTime = $request->input('start_time', '07:00');
        $endTime = $request->input('end_time', '09:30');

        $attendanceMeta = [
            'class' => $classInfo,
            'session_name' => $request->input('session_name', 'Buổi 5 - Điểm danh trên lớp'),
            'session_date' => $sessionDate,
            'session_date_display' => $sessionDateDisplay,
            'period' => sprintf('%d-%d', $startPeriod, $endPeriod),
            'period_label' => sprintf('Tiết %d - %d', $startPeriod, $endPeriod),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'time_range' => "{$startTime} - {$endTime}",
            'duration_minutes' => (int) $request->input('duration_minutes', 0),
            'note' => $request->input('note'),
            'total_students' => count($students),
        ];

        return view('lecture.attendance.active_manual_attendance', compact('attendanceMeta', 'students'));
    }

    private function teacherClassOptions()
    {
        $classes = CourseClass::query()
            ->where('owner_id', auth()->id())
            ->where('status', 'active')
            ->withCount(['members as members_count' => fn ($query) => $query->where('status', 'active')])
            ->orderBy('name')
            ->get()
            ->map(fn (CourseClass $class) => [
                'id' => $class->id,
                'label' => trim($class->name . ' - ' . $class->code, ' -'),
                'name' => $class->name,
                'code' => $class->code,
                'subject_code' => $class->subject_code,
                'members_count' => $class->members_count,
            ]);

        return $classes->isNotEmpty() ? $classes : $this->fallbackClasses();
    }

    private function fallbackClasses()
    {
        return collect([
            [
                'id' => 'demo-web',
                'label' => 'Lập trình Web - CDTH23A',
                'name' => 'Lập trình Web',
                'code' => 'CDTH23A',
                'subject_code' => 'WEB101',
                'members_count' => 50,
            ],
            [
                'id' => 'demo-db',
                'label' => 'Cơ sở dữ liệu - CDTH23B',
                'name' => 'Cơ sở dữ liệu',
                'code' => 'CDTH23B',
                'subject_code' => 'DB101',
                'members_count' => 46,
            ],
            [
                'id' => 'demo-php',
                'label' => 'PHP nâng cao - CDTH23C',
                'name' => 'PHP nâng cao',
                'code' => 'CDTH23C',
                'subject_code' => 'PHP201',
                'members_count' => 42,
            ],
        ]);
    }

    private function fallbackStudents(): array
    {
        return collect([
            ['code' => '22020101', 'name' => 'Nguyễn Văn Anh'],
            ['code' => '22020102', 'name' => 'Trần Thị Bình'],
            ['code' => '22020103', 'name' => 'Lê Hoàng Cường'],
            ['code' => '22020104', 'name' => 'Phạm Minh Đức'],
            ['code' => '22020105', 'name' => 'Võ Thanh Hà'],
            ['code' => '22020106', 'name' => 'Đặng Quốc Huy'],
            ['code' => '22020107', 'name' => 'Phan Kim Ngân'],
            ['code' => '22020108', 'name' => 'Bùi Gia Phúc'],
        ])->map(fn ($student, int $index) => [
            'id' => 'demo-' . ($index + 1),
            'code' => $student['code'],
            'name' => $student['name'],
            'avatarChar' => $this->firstCharacter($student['name']),
            'status' => 'unmarked',
            'note' => '',
        ])->all();
    }

    private function firstCharacter(string $value): string
    {
        $character = function_exists('mb_substr') ? mb_substr($value, 0, 1, 'UTF-8') : substr($value, 0, 1);

        return function_exists('mb_strtoupper') ? mb_strtoupper($character, 'UTF-8') : strtoupper($character);
    }

    private function formatDate(string $date): string
    {
        try {
            return Carbon::parse($date)->format('d/m/Y');
        } catch (\Throwable $exception) {
            return $date;
        }
    }
}
