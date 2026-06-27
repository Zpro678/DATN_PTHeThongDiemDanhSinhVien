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
     * Công thức tính chuyên cần (xem App\Services\AttendanceCalculator):
     * - Chỉ tính các phiên điểm danh đã chốt sổ (class_sessions.status = closed).
     * - Mỗi phiên được quy đổi theo lesson_count để tính theo tiết học.
     * - Vắng có phép (excused) bị loại khỏi mẫu số.
     * - Đi muộn đếm theo số lần; cứ đủ 3 lần muộn quy thành 1 tiết vắng, phần lẻ vẫn tính có đi học.
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
        $plannedSessions = max((int) ($courseClass->total_sessions ?? 0), 0);
        $allowedAbsentSessions = AttendanceCalculator::allowedAbsentSessions($plannedSessions);

        return [
            'id' => null,
            'class_id' => $courseClass->id,
            'code' => $courseClass->subject_code ?: $courseClass->code,
            'class_code' => $courseClass->code,
            'name' => $courseClass->name ?? 'Lớp học',
            'teacher' => $courseClass->owner?->name ?? 'Chưa cập nhật',
            'semester' => $courseClass->semester ?? 'Chưa cập nhật',
            'total_sessions' => (int) ($courseClass->total_sessions ?? 0),
            'planned_sessions' => $plannedSessions,
            'total' => 0,
            'counted_total' => 0,
            'attended' => 0,
            'present' => 0,
            'late' => 0,
            'late_count' => 0,
            'late_absent_sessions' => 0,
            'excused' => 0,
            'absent' => 0,
            'effective_absent' => 0,
            'allowed_absent_sessions' => $allowedAbsentSessions,
            'safe_absence_sessions' => $allowedAbsentSessions,
            'exceeded_absent_sessions' => 0,
            'absence_budget_label' => $this->absenceBudgetLabel($allowedAbsentSessions, 0),
            'absence_budget_state' => $this->absenceBudgetState($allowedAbsentSessions, 0),
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
                'counted_total' => 0,
                'planned_sessions' => 0,
                'attended' => 0,
                'present' => 0,
                'late' => 0,
                'late_count' => 0,
                'late_absent_sessions' => 0,
                'excused' => 0,
                'absent' => 0,
                'effective_absent' => 0,
                'allowed_absent_sessions' => 0,
                'safe_absence_sessions' => 0,
                'exceeded_absent_sessions' => 0,
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

        // Lấy bản ghi điểm danh thuộc các phiên đã chốt, kèm meeting_id để gộp theo buổi.
        $rows = DB::table('attendance_records as ar')
            ->join('class_sessions as cs', 'cs.id', '=', 'ar.class_session_id')
            ->whereIn('ar.class_member_id', $memberIds)
            ->where('cs.status', 'closed')
            ->whereNotNull('cs.meeting_id')
            ->whereNull('ar.deleted_at')
            ->whereNull('cs.deleted_at')
            ->get(['ar.class_member_id', 'cs.meeting_id', 'ar.status']);

        // Gộp theo buổi cho từng sinh viên (mỗi buổi = 1 đơn vị).
        return $rows
            ->groupBy('class_member_id')
            ->map(function ($memberRows) {
                $counts = AttendanceCalculator::consolidateByMeeting($memberRows);

                return (object) [
                    'total_sessions' => $counts['total'],
                    'present_sessions' => $counts['present'],
                    'late_sessions' => $counts['late'],
                    'late_count' => $counts['late'],
                    'excused_sessions' => $counts['excused'],
                    'absent_sessions' => $counts['absent'],
                ];
            });
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

                $total = (int) ($row->total_sessions ?? 0);
                $present = (int) ($row->present_sessions ?? 0);
                $late = (int) ($row->late_sessions ?? 0);
                $lateCount = (int) ($row->late_count ?? 0);
                $excused = (int) ($row->excused_sessions ?? 0);
                $absent = (int) ($row->absent_sessions ?? 0);

                $deductExcusedAbsence = (bool) ($courseClass?->deduct_excused_absence ?? true);

                // Đơn vị là buổi; vắng có phép bị loại khỏi mẫu số (nếu bật). Không quy đổi muộn.
                $plannedSessions = max((int) ($courseClass?->total_sessions ?? 0), $total);
                $countedTotal = AttendanceCalculator::countedSessions($total, $excused, $deductExcusedAbsence);
                $lateAbsentSessions = 0;
                $effectiveAbsent = max($absent, 0);
                $attended = $present + $late;
                // % chuyên cần tính trên tổng số buổi dự kiến (cả khóa) để nhất quán với quỹ vắng.
                $percent = AttendanceCalculator::percentOfPlanned($plannedSessions, $excused, $absent, $deductExcusedAbsence);

                $allowedAbsentSessions = AttendanceCalculator::allowedAbsentSessions($plannedSessions);
                $safeAbsenceSessions = max($allowedAbsentSessions - $effectiveAbsent, 0);
                $exceededAbsentSessions = max($effectiveAbsent - $allowedAbsentSessions, 0);
                $absenceBudgetState = $this->absenceBudgetState($allowedAbsentSessions, $effectiveAbsent);

                return [
                    'id' => $member->id,
                    'class_id' => $member->class_id,
                    'code' => $courseClass?->subject_code ?: $courseClass?->code,
                    'class_code' => $courseClass?->code,
                    'name' => $courseClass?->name ?? 'Lớp học',
                    'teacher' => $courseClass?->owner?->name ?? 'Chưa cập nhật',
                    'semester' => $courseClass?->semester ?? 'Chưa cập nhật',
                    'total_sessions' => (int) ($courseClass?->total_sessions ?? 0),
                    'planned_sessions' => $plannedSessions,
                    'total' => $total,
                    'counted_total' => $countedTotal,
                    'attended' => $attended,
                    'present' => $present,
                    'late' => $late,
                    'late_count' => $lateCount,
                    'late_absent_sessions' => $lateAbsentSessions,
                    'excused' => $excused,
                    'absent' => $absent,
                    'effective_absent' => $effectiveAbsent,
                    'allowed_absent_sessions' => $allowedAbsentSessions,
                    'safe_absence_sessions' => $safeAbsenceSessions,
                    'exceeded_absent_sessions' => $exceededAbsentSessions,
                    'absence_budget_label' => $this->absenceBudgetLabel($allowedAbsentSessions, $effectiveAbsent),
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
            'counted_total' => (int) $subjectsCollection->sum('counted_total'),
            'planned_sessions' => (int) $subjectsCollection->sum('planned_sessions'),
            'attended' => (int) $subjectsCollection->sum('attended'),
            'present' => (int) $subjectsCollection->sum('present'),
            'late' => (int) $subjectsCollection->sum('late'),
            'late_count' => (int) $subjectsCollection->sum('late_count'),
            'late_absent_sessions' => (int) $subjectsCollection->sum('late_absent_sessions'),
            'excused' => (int) $subjectsCollection->sum('excused'),
            'absent' => (int) $subjectsCollection->sum('absent'),
            'effective_absent' => (int) $subjectsCollection->sum('effective_absent'),
            'allowed_absent_sessions' => (int) $subjectsCollection->sum('allowed_absent_sessions'),
            'safe_absence_sessions' => (int) $subjectsCollection->sum('safe_absence_sessions'),
            'exceeded_absent_sessions' => (int) $subjectsCollection->sum('exceeded_absent_sessions'),
            'warning_count' => (int) $subjectsCollection->where('warning', true)->count(),
        ];

        // % tổng tính trên tổng tiết kế hoạch của tất cả môn (đã bỏ vắng có phép),
        // trừ đi vắng hiệu dụng (gồm muộn quy đổi) — nhất quán với từng môn.
        $plannedCounted = max($totals['planned_sessions'] - $totals['excused'], 0);
        $totals['percent'] = $plannedCounted > 0
            ? (int) round((($plannedCounted - $totals['effective_absent']) / $plannedCounted) * 100)
            : 100;

        return $totals;
    }

    /**
     * Tạo nhãn hiển thị cho quỹ vắng dựa trên số tiết vắng đã ghi nhận.
     */
    private function absenceBudgetLabel(int $allowedAbsentSessions, int $absent): string
    {
        $remaining = $allowedAbsentSessions - $absent;

        if ($remaining < 0) {
            return 'Đã vượt '.abs($remaining).' buổi';
        }

        if ($remaining === 0) {
            return 'Hết quỹ vắng';
        }

        return 'Còn vắng '.$remaining.' buổi';
    }

    /**
     * Phân loại trạng thái để view chỉ quyết định màu, không tự tính dữ liệu.
     */
    private function absenceBudgetState(int $allowedAbsentSessions, int $absent): string
    {
        $remaining = $allowedAbsentSessions - $absent;

        if ($remaining < 0) {
            return 'danger';
        }

        if ($remaining === 0) {
            return 'warning';
        }

        return 'safe';
    }
}
