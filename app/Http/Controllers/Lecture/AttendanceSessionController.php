<?php

namespace App\Http\Controllers\Lecture;

use App\Models\CourseClass;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class AttendanceSessionController extends Controller
{
    public function index() {
        return view('lecture.attendance.management_attendace');
    }

    public function setupQr(Request $request)
    {
        $data = $this->resolveQrAttendanceData($request);

        return view('lecture.attendance.create_qr_attendance', $data);
    }

    public function startQrAttendance(Request $request)
    {
        $data = $this->resolveQrAttendanceData($request);
        $selectedClass = $data['selectedClass'];

        $nextSessionNumber = $selectedClass ? ($selectedClass->sessions()->count() + 1) : 5;
        $sessionTitle = trim((string) $request->input('session_title')) ?: "Buổi {$nextSessionNumber} - Điểm danh QR";
        $sessionDate = $request->input('session_date') ?: now()->toDateString();
        $startLesson = max(1, min(15, (int) $request->input('start_lesson', 1)));
        $endLesson = max($startLesson, min(15, (int) $request->input('end_lesson', 3)));
        $qrRefreshRate = (int) $request->input('qr_refresh_rate', 10);
        $qrRefreshRate = in_array($qrRefreshRate, [5, 10, 15, 30], true) ? $qrRefreshRate : 10;
        $openMinutes = max(1, min(180, (int) $request->input('open_minutes', 15)));
        $gpsEnabled = filter_var($request->input('gps_enabled', true), FILTER_VALIDATE_BOOL);
        $deviceCheck = filter_var($request->input('device_check', true), FILTER_VALIDATE_BOOL);
        $qrToken = Str::upper(Str::random(8));
        $attendanceLink = url('/student/attendance/check-in/' . Str::lower($qrToken));

        return view('lecture.attendance.start_qr_attendance', array_merge($data, [
            'sessionTitle' => $sessionTitle,
            'sessionDate' => $sessionDate,
            'startLesson' => $startLesson,
            'endLesson' => $endLesson,
            'qrRefreshRate' => $qrRefreshRate,
            'openMinutes' => $openMinutes,
            'gpsEnabled' => $gpsEnabled,
            'deviceCheck' => $deviceCheck,
            'attendanceLink' => $attendanceLink,
            'qrToken' => $qrToken,
            'qrCells' => $this->buildQrCells($attendanceLink . $sessionTitle),
        ]));
    }

    private function resolveQrAttendanceData(Request $request): array
    {
        $classes = CourseClass::query()
            ->where('owner_id', auth()->id())
            ->withCount([
                'members as active_members_count' => fn ($query) => $query->where('status', 'active'),
            ])
            ->orderBy('name')
            ->get();

        $selectedClass = null;
        $students = collect();

        if ($classes->isNotEmpty()) {
            $requestedClassId = $request->integer('class_id');
            $selectedClass = $classes->firstWhere('id', $requestedClassId) ?? $classes->first();

            $selectedClass->load([
                'members' => fn ($query) => $query
                    ->where('status', 'active')
                    ->orderBy('full_name'),
            ]);

            $students = $selectedClass->members;
        }

        return compact('classes', 'selectedClass', 'students');
    }

    private function buildQrCells(string $seed): array
    {
        $size = 29;
        $hash = hash('sha256', $seed);
        $cells = [];

        for ($row = 0; $row < $size; $row++) {
            for ($col = 0; $col < $size; $col++) {
                $inFinder = false;
                $darkFinder = false;

                foreach ([[0, 0], [0, $size - 7], [$size - 7, 0]] as [$finderRow, $finderCol]) {
                    $localRow = $row - $finderRow;
                    $localCol = $col - $finderCol;

                    if ($localRow >= 0 && $localRow < 7 && $localCol >= 0 && $localCol < 7) {
                        $inFinder = true;
                        $darkFinder = $localRow === 0
                            || $localRow === 6
                            || $localCol === 0
                            || $localCol === 6
                            || ($localRow >= 2 && $localRow <= 4 && $localCol >= 2 && $localCol <= 4);
                        break;
                    }
                }

                if ($inFinder) {
                    $cells[] = $darkFinder;
                    continue;
                }

                $hashIndex = ($row * $size + $col) % strlen($hash);
                $cells[] = (hexdec($hash[$hashIndex]) + $row + ($col * 2)) % 5 < 2;
            }
        }

        return $cells;
    }
}
