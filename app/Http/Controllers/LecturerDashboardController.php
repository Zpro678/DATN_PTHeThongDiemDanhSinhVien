<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSummary;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\CourseClass;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class LecturerDashboardController extends Controller
{
    public function __invoke(): View
    {
        $userId = $this->resolveOwnerId();

        $classIds = CourseClass::query()
            ->where('owner_id', $userId)
            ->pluck('id');

        $classesQuery = CourseClass::query()
            ->where('owner_id', $userId);

        $totalClasses = (clone $classesQuery)->count();
        $totalStudents = ClassMember::query()
            ->whereIn('class_id', $classIds)
            ->where('status', 'active')
            ->count();
        $totalPlannedSessions = (int) (clone $classesQuery)->sum('total_sessions');
        $totalCreatedSessions = ClassSession::query()
            ->whereIn('class_id', $classIds)
            ->count();

        $attendanceQuery = AttendanceRecord::query()
            ->whereHas('classSession', fn (Builder $query) => $query->whereIn('class_id', $classIds));

        $totalPresent = (clone $attendanceQuery)
            ->whereIn('status', ['present', 'late'])
            ->count();
        $totalAbsent = (clone $attendanceQuery)
            ->whereIn('status', ['absent', 'excused'])
            ->count();

        if (($totalPresent + $totalAbsent) === 0) {
            $summaryQuery = AttendanceSummary::query()
                ->whereIn('class_id', $classIds);

            $totalPresent = (int) (clone $summaryQuery)->sum('total_present');
            $totalAbsent = (int) (clone $summaryQuery)->sum('total_absent')
                + (int) (clone $summaryQuery)->sum('total_excused');
        }

        $totalRecorded = $totalPresent + $totalAbsent;
        $attendanceRate = $totalRecorded > 0
            ? round(($totalPresent / $totalRecorded) * 100, 1)
            : 0;
        $absenceRate = $totalRecorded > 0
            ? round(($totalAbsent / $totalRecorded) * 100, 1)
            : 0;
        $sessionProgress = $totalPlannedSessions > 0
            ? min(100, round(($totalCreatedSessions / $totalPlannedSessions) * 100))
            : 0;

        $activeSession = ClassSession::query()
            ->with('courseClass:id,name,subject_code')
            ->whereIn('class_id', $classIds)
            ->where('status', 'active')
            ->withCount([
                'records as present_count' => fn (Builder $query) => $query->whereIn('status', ['present', 'late']),
                'records as absent_count' => fn (Builder $query) => $query->whereIn('status', ['absent', 'excused']),
            ])
            ->orderBy('date')
            ->orderBy('start_time')
            ->first();

        $recentSessions = ClassSession::query()
            ->with('courseClass:id,name,subject_code')
            ->whereIn('class_id', $classIds)
            ->withCount([
                'records as present_count' => fn (Builder $query) => $query->whereIn('status', ['present', 'late']),
                'records as absent_count' => fn (Builder $query) => $query->whereIn('status', ['absent', 'excused']),
            ])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(5)
            ->get()
            ->map(fn (ClassSession $session) => $this->formatSessionRow($session));

        $chartData = ClassSession::query()
            ->with('courseClass:id,name,subject_code')
            ->whereIn('class_id', $classIds)
            ->withCount([
                'records as present_count' => fn (Builder $query) => $query->whereIn('status', ['present', 'late']),
                'records as absent_count' => fn (Builder $query) => $query->whereIn('status', ['absent', 'excused']),
            ])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(6)
            ->get()
            ->reverse()
            ->values()
            ->map(function (ClassSession $session): array {
                $recorded = $session->present_count + $session->absent_count;

                return [
                    'label' => $session->date?->format('d/m') ?: 'N/A',
                    'name' => $session->name,
                    'rate' => $recorded > 0 ? round(($session->present_count / $recorded) * 100) : 0,
                ];
            });

        $classCards = CourseClass::query()
            ->where('owner_id', $userId)
            ->withCount([
                'members as active_members_count' => fn (Builder $query) => $query->where('status', 'active'),
                'sessions as created_sessions_count',
            ])
            ->latest()
            ->limit(4)
            ->get();

        $studentRows = ClassMember::query()
            ->whereIn('class_id', $classIds)
            ->where('status', 'active')
            ->with(['courseClass:id,name,subject_code,total_sessions', 'summary'])
            ->withCount([
                'records as present_records_count' => fn (Builder $query) => $query->whereIn('status', ['present', 'late']),
                'records as absent_records_count' => fn (Builder $query) => $query->whereIn('status', ['absent', 'excused']),
            ])
            ->orderBy('full_name')
            ->limit(10)
            ->get()
            ->map(fn (ClassMember $member) => $this->formatStudentRow($member));

        return view('lecturer.lecturer_dashboard', [
            'totalClasses' => $totalClasses,
            'totalStudents' => $totalStudents,
            'totalPlannedSessions' => $totalPlannedSessions,
            'totalCreatedSessions' => $totalCreatedSessions,
            'totalPresent' => $totalPresent,
            'totalAbsent' => $totalAbsent,
            'attendanceRate' => $attendanceRate,
            'absenceRate' => $absenceRate,
            'sessionProgress' => $sessionProgress,
            'activeSession' => $activeSession,
            'recentSessions' => $recentSessions,
            'chartData' => $chartData,
            'classCards' => $classCards,
            'studentRows' => $studentRows,
        ]);
    }

    private function resolveOwnerId(): ?int
    {
        if (auth()->check()) {
            return auth()->id();
        }

        return CourseClass::query()
            ->whereNotNull('owner_id')
            ->value('owner_id');
    }

    private function formatSessionRow(ClassSession $session): array
    {
        $recorded = $session->present_count + $session->absent_count;
        $rate = $recorded > 0 ? round(($session->present_count / $recorded) * 100) : 0;

        return [
            'id' => $session->id,
            'name' => $session->name,
            'class_name' => $session->courseClass?->name ?: 'Lớp học',
            'subject_code' => $session->courseClass?->subject_code,
            'date' => $session->date?->format('d/m/Y') ?: '--',
            'present_count' => $session->present_count,
            'absent_count' => $session->absent_count,
            'recorded_count' => $recorded,
            'rate' => $rate,
            'status' => $session->status,
            'status_label' => match ($session->status) {
                'active' => 'Đang diễn ra',
                'closed' => 'Đã chốt',
                default => 'Chờ mở',
            },
            'status_class' => match ($session->status) {
                'active' => 'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-950/30 dark:text-blue-300',
                'closed' => 'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-950/30 dark:text-green-300',
                default => 'bg-slate-100 text-slate-700 ring-slate-600/10 dark:bg-slate-800 dark:text-slate-300',
            },
        ];
    }

    private function formatStudentRow(ClassMember $member): array
    {
        $recordPresent = (int) $member->present_records_count;
        $recordAbsent = (int) $member->absent_records_count;
        $summaryPresent = (int) ($member->summary?->total_present ?? 0);
        $summaryAbsent = (int) ($member->summary?->total_absent ?? 0)
            + (int) ($member->summary?->total_excused ?? 0);

        $hasRecordData = ($recordPresent + $recordAbsent) > 0;
        $present = $hasRecordData ? $recordPresent : $summaryPresent;
        $absent = $hasRecordData ? $recordAbsent : $summaryAbsent;
        $recorded = $present + $absent;
        $attendanceRate = $recorded > 0 ? round(($present / $recorded) * 100) : 0;
        $absenceRate = $recorded > 0 ? round(($absent / $recorded) * 100) : 0;

        return [
            'student_code' => $member->student_code,
            'full_name' => $member->full_name,
            'class_name' => $member->courseClass?->name ?: 'Lớp học',
            'subject_code' => $member->courseClass?->subject_code,
            'present' => $present,
            'absent' => $absent,
            'recorded' => $recorded,
            'attendance_rate' => $attendanceRate,
            'risk_label' => match (true) {
                $absenceRate >= 20 => 'Cảnh báo',
                $absenceRate >= 10 => 'Theo dõi',
                default => 'Ổn định',
            },
            'risk_class' => match (true) {
                $absenceRate >= 20 => 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-950/30 dark:text-red-300',
                $absenceRate >= 10 => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-950/30 dark:text-amber-300',
                default => 'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-950/30 dark:text-green-300',
            },
        ];
    }
}
