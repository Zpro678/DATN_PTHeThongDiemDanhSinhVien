<?php

namespace App\Services;

use App\Models\ClassMember;
use App\Models\CourseClass;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StatisticalService
{
    private const MIN_ATTENDANCE_PERCENT = 80;

    private const ABSENCE_LIMIT_RATIO = 0.2;

    /**
     * Lấy toàn bộ dữ liệu thống kê chuyên cần cho một học viên.
     *
     * Service này trả dữ liệu đã chuẩn hóa cho màn "Thống kê chuyên cần":
     * - subjects: thống kê theo từng lớp/môn học.
     * - totals: thống kê tổng hợp trên tất cả lớp đang tham gia.
     * - isDemo: luôn false vì dữ liệu được lấy từ database thật.
     *
     * Công thức tính chuyên cần:
     * - Chỉ tính các phiên điểm danh đã chốt sổ (class_sessions.status = closed).
     * - Mỗi phiên được quy đổi theo lesson_count để tính theo tiết học.
     * - present, late, excused được xem là có chuyên cần.
     * - absent, pending, invalid được xem là vắng/chưa hợp lệ sau khi phiên đã chốt.
     *
     * @return array{subjects: array<int, array<string, mixed>>, totals: array<string, int>, isDemo: bool}
     */
    public function getStudentAttendanceStatistics(int $studentUserId): array
    {
        if ($studentUserId <= 0) {
            return $this->emptyStatistics();
        }

        $members = $this->studentActiveMembers($studentUserId);

        if ($members->isEmpty()) {
            return $this->emptyStatistics();
        }

        $attendanceRows = $this->attendanceRowsByMember($members->pluck('id'));
        $subjects = $this->buildSubjectRows($members, $attendanceRows);

        return [
            'subjects' => $subjects,
            'totals' => $this->buildTotals($subjects),
            'isDemo' => false,
        ];
    }

    /**
     * Tạo dữ liệu chi tiết lớp rỗng theo cùng chuẩn với subjects.
     */
    public function emptyStudentClassAttendanceDetail(CourseClass $courseClass): array
    {
        $plannedLessons = max((int) ($courseClass->total_lessons ?? 0), 0);
        $allowedAbsentLessons = (int) floor($plannedLessons * self::ABSENCE_LIMIT_RATIO);

        return [
            'id' => null,
            'class_id' => $courseClass->id,
            'code' => $courseClass->subject_code ?: $courseClass->code,
            'class_code' => $courseClass->code,
            'name' => $courseClass->name ?? 'Lớp học',
            'teacher' => $courseClass->owner?->name ?? 'Chưa cập nhật',
            'semester' => $courseClass->semester ?? 'Chưa cập nhật',
            'total_lessons' => (int) ($courseClass->total_lessons ?? 0),
            'planned_lessons' => $plannedLessons,
            'total' => 0,
            'attended' => 0,
            'present' => 0,
            'late' => 0,
            'excused' => 0,
            'absent' => 0,
            'allowed_absent_lessons' => $allowedAbsentLessons,
            'safe_absence_lessons' => $allowedAbsentLessons,
            'exceeded_absent_lessons' => 0,
            'absence_budget_label' => $this->absenceBudgetLabel($allowedAbsentLessons, 0),
            'absence_budget_state' => $this->absenceBudgetState($allowedAbsentLessons, 0),
            'percent' => 100,
            'warning' => false,
            'demo' => false,
        ];
    }

    /**
     * Trả dữ liệu rỗng để view vẫn render ổn khi học viên chưa tham gia lớp nào.
     *
     * @return array{subjects: array<int, array<string, mixed>>, totals: array<string, int>, isDemo: bool}
     */
    private function emptyStatistics(): array
    {
        return [
            'subjects' => [],
            'totals' => [
                'records' => 0,
                'planned_lessons' => 0,
                'attended' => 0,
                'present' => 0,
                'late' => 0,
                'excused' => 0,
                'absent' => 0,
                'allowed_absent_lessons' => 0,
                'safe_absence_lessons' => 0,
                'exceeded_absent_lessons' => 0,
                'percent' => 100,
                'warning_count' => 0,
            ],
            'isDemo' => false,
        ];
    }

    /**
     * Lấy danh sách thành viên lớp active của học viên.
     *
     * @return Collection<int, ClassMember>
     */
    private function studentActiveMembers(int $studentUserId): Collection
    {
        return ClassMember::query()
            ->with(['courseClass.owner'])
            ->where('user_id', $studentUserId)
            ->where('status', 'active')
            ->orderBy('class_id')
            ->get();
    }

    /**
     * Gom điểm danh theo từng class_member trong một query.
     *
     * @param  Collection<int, int>  $memberIds
     * @return Collection<int, object>
     */
    private function attendanceRowsByMember(Collection $memberIds): Collection
    {
        if ($memberIds->isEmpty()) {
            return collect();
        }

        $lessonCount = 'COALESCE(NULLIF(cs.lesson_count, 0), 1)';

        return DB::table('attendance_records as ar')
            ->join('class_sessions as cs', 'cs.id', '=', 'ar.class_session_id')
            ->whereIn('ar.class_member_id', $memberIds)
            ->where('cs.status', 'closed')
            ->whereNull('ar.deleted_at')
            ->whereNull('cs.deleted_at')
            ->selectRaw("
                ar.class_member_id,
                COUNT(ar.id) as records_count,
                COALESCE(SUM({$lessonCount}), 0) as total_lessons,
                COALESCE(SUM(CASE WHEN ar.status = 'present' THEN {$lessonCount} ELSE 0 END), 0) as present_lessons,
                COALESCE(SUM(CASE WHEN ar.status = 'late' THEN {$lessonCount} ELSE 0 END), 0) as late_lessons,
                COALESCE(SUM(CASE WHEN ar.status = 'excused' THEN {$lessonCount} ELSE 0 END), 0) as excused_lessons,
                COALESCE(SUM(CASE WHEN ar.status IN ('absent', 'pending', 'invalid') THEN {$lessonCount} ELSE 0 END), 0) as absent_lessons
            ")
            ->groupBy('ar.class_member_id')
            ->get()
            ->keyBy('class_member_id');
    }

    /**
     * Chuẩn hóa thống kê từng môn/lớp cho view.
     *
     * @param  Collection<int, ClassMember>  $members
     * @param  Collection<int, object>  $attendanceRows
     * @return array<int, array<string, mixed>>
     */
    private function buildSubjectRows(Collection $members, Collection $attendanceRows): array
    {
        return $members
            ->map(function (ClassMember $member) use ($attendanceRows): array {
                $courseClass = $member->courseClass;
                $row = $attendanceRows->get($member->id);

                $total = (int) ($row->total_lessons ?? 0);
                $present = (int) ($row->present_lessons ?? 0);
                $late = (int) ($row->late_lessons ?? 0);
                $excused = (int) ($row->excused_lessons ?? 0);
                $absent = (int) ($row->absent_lessons ?? 0);

                $percent = $this->attendancePercent($present, $late, $excused, $total);
                $plannedLessons = max((int) ($courseClass?->total_lessons ?? 0), $total);
                $allowedAbsentLessons = (int) floor($plannedLessons * self::ABSENCE_LIMIT_RATIO);
                $safeAbsenceLessons = max($allowedAbsentLessons - $absent, 0);
                $exceededAbsentLessons = max($absent - $allowedAbsentLessons, 0);
                $absenceBudgetState = $this->absenceBudgetState($allowedAbsentLessons, $absent);

                return [
                    'id' => $member->id,
                    'class_id' => $member->class_id,
                    'code' => $courseClass?->subject_code ?: $courseClass?->code,
                    'class_code' => $courseClass?->code,
                    'name' => $courseClass?->name ?? 'Lớp học',
                    'teacher' => $courseClass?->owner?->name ?? 'Chưa cập nhật',
                    'semester' => $courseClass?->semester ?? 'Chưa cập nhật',
                    'total_lessons' => (int) ($courseClass?->total_lessons ?? 0),
                    'planned_lessons' => $plannedLessons,
                    'total' => $total,
                    'attended' => $present + $late + $excused,
                    'present' => $present,
                    'late' => $late,
                    'excused' => $excused,
                    'absent' => $absent,
                    'allowed_absent_lessons' => $allowedAbsentLessons,
                    'safe_absence_lessons' => $safeAbsenceLessons,
                    'exceeded_absent_lessons' => $exceededAbsentLessons,
                    'absence_budget_label' => $this->absenceBudgetLabel($allowedAbsentLessons, $absent),
                    'absence_budget_state' => $absenceBudgetState,
                    'percent' => $percent,
                    'warning' => $total > 0 && $percent < self::MIN_ATTENDANCE_PERCENT,
                    'demo' => false,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Tính thống kê tổng hợp trên toàn bộ môn/lớp.
     *
     * @param  array<int, array<string, mixed>>  $subjects
     * @return array<string, int>
     */
    private function buildTotals(array $subjects): array
    {
        $subjectsCollection = collect($subjects);

        $totals = [
            'records' => (int) $subjectsCollection->sum('total'),
            'planned_lessons' => (int) $subjectsCollection->sum('planned_lessons'),
            'attended' => (int) $subjectsCollection->sum('attended'),
            'present' => (int) $subjectsCollection->sum('present'),
            'late' => (int) $subjectsCollection->sum('late'),
            'excused' => (int) $subjectsCollection->sum('excused'),
            'absent' => (int) $subjectsCollection->sum('absent'),
            'allowed_absent_lessons' => (int) $subjectsCollection->sum('allowed_absent_lessons'),
            'safe_absence_lessons' => (int) $subjectsCollection->sum('safe_absence_lessons'),
            'exceeded_absent_lessons' => (int) $subjectsCollection->sum('exceeded_absent_lessons'),
            'warning_count' => (int) $subjectsCollection->where('warning', true)->count(),
        ];

        $totals['percent'] = $this->attendancePercent(
            $totals['present'],
            $totals['late'],
            $totals['excused'],
            $totals['records'],
        );

        return $totals;
    }

    /**
     * Tính phần trăm chuyên cần theo công thức:
     * (có mặt + đi muộn + vắng có phép) / tổng tiết đã chốt.
     */
    private function attendancePercent(int $present, int $late, int $excused, int $total): int
    {
        if ($total <= 0) {
            return 100;
        }

        return (int) round((($present + $late + $excused) / $total) * 100);
    }

    /**
     * Tạo nhãn hiển thị cho quỹ vắng dựa trên số tiết vắng đã ghi nhận.
     */
    private function absenceBudgetLabel(int $allowedAbsentLessons, int $absent): string
    {
        $remaining = $allowedAbsentLessons - $absent;

        if ($remaining < 0) {
            return 'Đã vượt '.abs($remaining).' tiết';
        }

        if ($remaining === 0) {
            return 'Hết quỹ vắng';
        }

        return 'Còn vắng '.$remaining.' tiết';
    }

    /**
     * Phân loại trạng thái để view chỉ quyết định màu, không tự tính dữ liệu.
     */
    private function absenceBudgetState(int $allowedAbsentLessons, int $absent): string
    {
        $remaining = $allowedAbsentLessons - $absent;

        if ($remaining < 0) {
            return 'danger';
        }

        if ($remaining === 0) {
            return 'warning';
        }

        return 'safe';
    }
}
