<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\ClassMember;
use App\Models\LeaveRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Gom các nghiệp vụ dành cho học viên.
 *
 * Hiện service này dùng để lấy dữ liệu cảnh báo học tập từ database
 * rồi chuẩn hóa thành mảng đơn giản cho Livewire/Blade hiển thị.
 */
class StudentsService
{
    /**
     * Lấy danh sách cảnh báo học tập của một tài khoản học viên.
     *
     * Luồng dữ liệu:
     * - Lấy các lớp mà học viên đang tham gia từ bảng class_members.
     * - Nạp điểm danh đã chốt từ attendance_records + class_sessions.
     * - Nạp đơn xin nghỉ từ leave_requests.
     * - Tạo từng loại cảnh báo và trả về cho view.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getWarningsForStudent(int $studentUserId): array
    {
        if ($studentUserId <= 0) {
            return [];
        }

        // Mỗi ClassMember là quan hệ của học viên với một lớp học cụ thể.
        $members = ClassMember::query()
            ->with([
                'courseClass:id,code,name,subject_code,semester,total_sessions,deduct_excused_absence',
                'attendanceRecords' => fn ($query) => $query
                    // Chỉ tính các bản ghi thuộc buổi điểm danh đã chốt.
                    ->whereHas('classSession', fn ($sessionQuery) => $sessionQuery->where('status', 'closed'))
                    ->with('classSession:id,class_id,date,status,meeting_id'),
                'leaveRequests.classSession:id,class_id,name,date',
            ])
            ->where('user_id', $studentUserId)
            ->where('status', 'active')
            ->get();

        $warnings = [];

        // Ghép cảnh báo điểm danh và cảnh báo đơn xin nghỉ của từng lớp.
        foreach ($members as $member) {
            $warnings = array_merge(
                $warnings,
                $this->attendanceWarnings($member),
                $this->leaveRequestWarnings($member),
            );
        }

        // sort_date chỉ dùng nội bộ để sắp xếp, không cần truyền sang view.
        return collect($warnings)
            ->sortByDesc('sort_date')
            ->values()
            ->map(function (array $warning): array {
                unset($warning['sort_date']);

                return $warning;
            })
            ->all();
    }

    /**
     * Tạo cảnh báo liên quan đến chuyên cần và đi muộn của một sinh viên trong một lớp.
     *
     * @return array<int, array<string, mixed>>
     */
    private function attendanceWarnings(ClassMember $member): array
    {
        $records = $member->attendanceRecords;

        if ($records->isEmpty()) {
            return [];
        }

        // Gộp theo buổi (mỗi buổi = 1 đơn vị).
        $rows = $records->map(fn (AttendanceRecord $record) => (object) [
            'meeting_id' => $record->classSession?->meeting_id,
            'status' => $record->status,
        ]);
        $counts = AttendanceCalculator::consolidateByMeeting($rows);
        $totalSessions = $counts['total'];
        $excusedSessions = $counts['excused'];
        $absentSessions = $counts['absent'];

        // % tính trên tổng số buổi dự kiến của lớp để nhất quán với quỹ vắng.
        $plannedSessions = max((int) ($member->courseClass?->total_sessions ?? 0), $totalSessions);
        $deductExcusedAbsence = (bool) ($member->courseClass?->deduct_excused_absence ?? true);

        $allowedAbsentSessions = AttendanceCalculator::allowedAbsentSessions($plannedSessions);
        $effectiveAbsentSessions = $absentSessions;
        $attendancePercent = AttendanceCalculator::percentOfPlanned(
            $plannedSessions,
            $excusedSessions,
            $absentSessions,
            $deductExcusedAbsence
        );

        $latestRecord = $records
            ->sortByDesc(fn (AttendanceRecord $record) => $record->classSession?->date?->getTimestamp() ?? 0)
            ->first();
        $latestDate = $latestRecord?->classSession?->date;
        $classLabel = $this->classLabel($member);
        $warnings = [];

        // Dưới ngưỡng chuyên cần tối thiểu thì sinh cảnh báo nguy cơ cấm thi.
        if ($totalSessions > 0 && $attendancePercent < AttendanceCalculator::MIN_ATTENDANCE_PERCENT) {
            $warnings[] = [
                'type' => 'danger',
                'icon' => 'alert-triangle',
                'title' => 'Nguy cơ cấm thi',
                'message' => "Lớp {$classLabel}. Bạn đã vắng {$effectiveAbsentSessions}/{$allowedAbsentSessions} buổi được phép, tỷ lệ chuyên cần còn {$attendancePercent}%.",
                'date' => $latestDate?->format('d/m/Y') ?? now()->format('d/m/Y'),
                'sort_date' => $latestDate?->toDateString() ?? now()->toDateString(),
                'route' => 'student.attendance.history',
                'action_label' => 'Xem lịch sử',
            ];
        }

        return $warnings;
    }

    /**
     * Tạo cảnh báo cho các đơn xin nghỉ đang chờ duyệt nhưng thiếu minh chứng.
     *
     * @return array<int, array<string, mixed>>
     */
    private function leaveRequestWarnings(ClassMember $member): array
    {
        $classLabel = $this->classLabel($member);

        return $member->leaveRequests
            ->where('status', 'pending')
            // Chỉ nhắc những đơn chưa có ảnh/tệp minh chứng.
            ->filter(fn (LeaveRequest $leaveRequest) => ! $this->hasProofImage($leaveRequest))
            ->map(function (LeaveRequest $leaveRequest) use ($classLabel): array {
                $warningDate = $leaveRequest->created_at ?? $leaveRequest->classSession?->date ?? now();
                $sessionDate = $leaveRequest->classSession?->date?->format('d/m/Y');
                $message = $sessionDate
                    ? "Đơn xin nghỉ lớp {$classLabel} ngày {$sessionDate} chưa có minh chứng. Vui lòng bổ sung để giảng viên xét duyệt."
                    : "Đơn xin nghỉ lớp {$classLabel} chưa có minh chứng. Vui lòng bổ sung để giảng viên xét duyệt.";

                return [
                    'type' => 'info',
                    'icon' => 'info',
                    'title' => 'Nhắc nhở nộp minh chứng',
                    'message' => $message,
                    'date' => $warningDate->format('d/m/Y'),
                    'sort_date' => $warningDate->toDateString(),
                    'route' => 'student.leave-requests.history',
                    'action_label' => 'Bổ sung ngay',
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Kiểm tra đơn xin nghỉ đã có minh chứng hay chưa.
     */
    private function hasProofImage(LeaveRequest $leaveRequest): bool
    {
        $proofImages = $leaveRequest->proof_image;

        if (is_array($proofImages)) {
            return collect($proofImages)
                ->filter(fn ($path) => filled($path))
                ->isNotEmpty();
        }

        return filled($proofImages);
    }

    /**
     * Tạo nhãn lớp ngắn gọn để đưa vào nội dung cảnh báo.
     */
    private function classLabel(ClassMember $member): string
    {
        $courseClass = $member->courseClass;

        if (! $courseClass) {
            return 'Lớp học';
        }

        $code = $courseClass->subject_code ?: $courseClass->code;

        return $code
            ? "{$courseClass->name} ({$code})"
            : $courseClass->name;
    }

    /**
     * Trả về cấu trúc dashboard rỗng cho sinh viên.
     *
     * Dùng khi user không hợp lệ hoặc sinh viên chưa tham gia lớp nào. Việc trả
     * đủ key mặc định giúp view không bị lỗi undefined index khi render.
     *
     * @return array<string, mixed>
     */
    private function emptyStudentDashboard(): array
    {
        return [
            'stats' => [
                'joined_classes' => 0,
                'attendance_percent' => 100,
                'absent_sessions' => 0,
                'warning_count' => 0,
                'pending_leave_requests' => 0,
                'latest_attendance_label' => 'Chưa có',
            ],
            'joined_cards' => [],
        ];
    }

    /**
     * Chuyển phần trăm chuyên cần thành nhãn trạng thái và class giao diện.
     *
     * @return array{label: string, statusClass: string, bar: string, color: string}
     */
    private function studentAttendanceStyle(int $attendancePercent, int $studiedSessions): array
    {
        if ($studiedSessions <= 0) {
            return [
                'label' => 'Chưa có dữ liệu',
                'statusClass' => 'bg-slate-100 text-slate-500',
                'bar' => 'bg-slate-300',
                'color' => 'text-slate-500',
            ];
        }

        if ($attendancePercent < 80) {
            return [
                'label' => 'Cảnh báo',
                'statusClass' => 'bg-error/10 text-error',
                'bar' => 'bg-error',
                'color' => 'text-error',
            ];
        }

        if ($attendancePercent < 85) {
            return [
                'label' => 'Cảnh báo nhẹ',
                'statusClass' => 'bg-[#F59E0B]/10 text-[#F59E0B]',
                'bar' => 'bg-[#F59E0B]',
                'color' => 'text-[#F59E0B]',
            ];
        }

        return [
            'label' => 'Bình thường',
            'statusClass' => 'bg-tertiary/10 text-tertiary',
            'bar' => 'bg-tertiary',
            'color' => 'text-tertiary',
        ];
    }

    /**
     * Đổi ngày điểm danh gần nhất thành nhãn dễ đọc trên dashboard.
     */
    private function studentDashboardDateLabel(?string $date): string
    {
        if (! $date) {
            return 'Chưa có';
        }

        $value = Carbon::parse($date)->startOfDay();

        if ($value->isToday()) {
            return 'Hôm nay';
        }

        if ($value->isYesterday()) {
            return 'Hôm qua';
        }

        return $value->format('d/m/Y');
    }

    /**
     * Lấy dữ liệu tổng quan cho phần Không gian Học viên.
     *
     * Dữ liệu trả về được dùng trực tiếp ở dashboard:
     * - stats: lớp đang tham gia, chuyên cần trung bình, tổng tiết vắng,
     *   cảnh báo chuyên cần, đơn nghỉ đang chờ, buổi điểm danh gần nhất.
     * - joined_cards: danh sách lớp sinh viên đang tham gia để hiển thị card.
     *
     * @return array<string, mixed>
     */
    public function getDashboardForStudent(int $studentUserId): array
    {
        if ($studentUserId <= 0) {
            return $this->emptyStudentDashboard();
        }

        $members = ClassMember::query()
            ->with([
                'courseClass:id,owner_user_id,code,name,subject_code,semester,status,total_sessions,deduct_excused_absence',
                'courseClass.owner:id,name',
            ])
            ->where('user_id', $studentUserId)
            ->where('status', 'active')
            ->get(['id', 'class_id', 'student_code', 'full_name', 'user_id', 'status']);

        if ($members->isEmpty()) {
            return $this->emptyStudentDashboard();
        }

        $memberIds = $members->pluck('id');

        // Lấy bản ghi điểm danh ở phiên đã chốt, kèm meeting_id + ngày để gộp theo buổi.
        $rawRows = DB::table('attendance_records as ar')
            ->join('class_sessions as cs', 'cs.id', '=', 'ar.class_session_id')
            ->whereIn('ar.class_member_id', $memberIds)
            ->whereNull('ar.deleted_at')
            ->whereNull('cs.deleted_at')
            ->where('cs.status', 'closed')
            ->whereNotNull('cs.meeting_id')
            ->get(['ar.class_member_id', 'cs.meeting_id', 'ar.status', 'cs.date'])
            ->groupBy('class_member_id');

        // Gộp theo buổi cho mỗi sinh viên (đơn vị buổi).
        $attendanceRows = $rawRows->map(function ($memberRows) {
            $counts = AttendanceCalculator::consolidateByMeeting($memberRows);

            return (object) [
                'total_sessions' => $counts['total'],
                'present_sessions' => $counts['present'],
                'late_sessions' => $counts['late'],
                'excused_sessions' => $counts['excused'],
                'absent_sessions' => $counts['absent'],
                'late_count' => $counts['late'],
                'latest_session_date' => $memberRows->max('date'),
            ];
        });

        $leaveRows = DB::table('leave_requests')
            ->whereIn('class_member_id', $memberIds)
            ->selectRaw("
                class_member_id,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count
            ")
            ->groupBy('class_member_id')
            ->get()
            ->keyBy('class_member_id');

        // Tổng số buổi dự kiến theo từng môn để tính % chuyên cần trên cả khóa.
        $plannedByMember = $members->mapWithKeys(function (ClassMember $member) use ($attendanceRows) {
            $studied = (int) ($attendanceRows->get($member->id)->total_sessions ?? 0);

            return [$member->id => max((int) ($member->courseClass?->total_sessions ?? 0), $studied)];
        });

        // Tổng hợp theo tổng buổi dự kiến: bỏ vắng có phép (nếu bật). Không quy đổi muộn.
        $plannedCounted = 0;
        $projectedAttended = 0;
        $absentSessions = 0;
        $warningCount = 0;

        foreach ($attendanceRows as $memberId => $row) {
            $planned = (int) ($plannedByMember[$memberId] ?? 0);
            $courseClass = $members->firstWhere('id', $memberId)?->courseClass;
            $deductExcusedAbsence = (bool) ($courseClass?->deduct_excused_absence ?? true);

            $counted = AttendanceCalculator::countedSessions($planned, (int) $row->excused_sessions, $deductExcusedAbsence);
            $effectiveAbsent = (int) $row->absent_sessions;

            $plannedCounted += $counted;
            $projectedAttended += max($counted - $effectiveAbsent, 0);
            $absentSessions += (int) $row->absent_sessions;

            if ((int) $row->total_sessions > 0
                && AttendanceCalculator::percentOfPlanned($planned, (int) $row->excused_sessions, (int) $row->absent_sessions, $deductExcusedAbsence) < AttendanceCalculator::MIN_ATTENDANCE_PERCENT) {
                $warningCount++;
            }
        }

        $attendancePercent = $plannedCounted > 0
            ? (int) round(($projectedAttended / $plannedCounted) * 100)
            : 100;

        $pendingLeaveRequests = (int) $leaveRows->sum('pending_count');

        $latestAttendanceDate = $attendanceRows
            ->pluck('latest_session_date')
            ->filter()
            ->max();

        $joinedCards = $members
            ->map(function (ClassMember $member) use ($attendanceRows): array {
                $courseClass = $member->courseClass;
                $row = $attendanceRows->get($member->id);

                $studiedSessions = (int) ($row->total_sessions ?? 0);
                $absentSessions = (int) ($row->absent_sessions ?? 0);
                $totalCourseSessions = max((int) ($courseClass?->total_sessions ?? 0), $studiedSessions);

                $deductExcusedAbsence = (bool) ($courseClass?->deduct_excused_absence ?? true);

                // % chuyên cần tính trên tổng buổi dự kiến để nhất quán với quỹ vắng.
                $attendancePercent = AttendanceCalculator::percentOfPlanned(
                    $totalCourseSessions,
                    (int) ($row->excused_sessions ?? 0),
                    $absentSessions,
                    $deductExcusedAbsence
                );

                $style = $this->studentAttendanceStyle($attendancePercent, $studiedSessions);

                return [
                    // ID của bản ghi class_members, đại diện cho quan hệ sinh viên với lớp.
                    'id' => $member->id,
                    // ID lớp học thật trong bảng classes, dùng để điều hướng sang trang chi tiết lớp.
                    'class_id' => $courseClass?->id,
                    // Tên lớp hiển thị trên thẻ; nếu lớp bị thiếu dữ liệu thì dùng nhãn mặc định.
                    'title' => $courseClass?->name ?? 'Lớp học',
                    // Tên giảng viên/chủ lớp; nếu chưa nạp được owner thì hiển thị trạng thái chưa cập nhật.
                    'teacher' => $courseClass?->owner?->name ?? 'Chưa cập nhật',
                    // Mã học phần ưu tiên subject_code, nếu không có thì dùng mã lớp.
                    'code' => $courseClass?->subject_code ?: ($courseClass?->code ?? 'N/A'),
                    // Mã lớp riêng, thường dùng cho hiển thị hoặc tham gia lớp.
                    'class_code' => $courseClass?->code ?? 'N/A',
                    // Học kỳ của lớp để sinh viên biết lớp thuộc kỳ học nào.
                    'semester' => $courseClass?->semester ?? 'Chưa cập nhật',
                    // Nhãn trạng thái chuyên cần, ví dụ: Bình thường, Cảnh báo nhẹ, Nguy cơ cấm thi.
                    'status' => $style['label'],
                    // CSS class cho badge trạng thái chuyên cần.
                    'statusClass' => $style['statusClass'],
                    // Phần trăm chuyên cần của sinh viên trong lớp này.
                    'attendance' => $attendancePercent,
                    // Số buổi vắng trên tổng số buổi của lớp, dùng để hiển thị dạng "x/y buổi".
                    'absent' => "{$absentSessions}/{$totalCourseSessions} buổi",
                    // Tổng số buổi đã học/đã chốt điểm danh của lớp này.
                    'studied_sessions' => $studiedSessions,
                    // Tổng số buổi theo kế hoạch của lớp, dùng làm mẫu số cho tiến độ.
                    'total_sessions' => $totalCourseSessions,
                    // CSS class cho thanh tiến độ chuyên cần.
                    'bar' => $style['bar'],
                    // CSS class màu chữ/số liệu chuyên cần.
                    'color' => $style['color'],
                ];
            })
            ->values()
            ->all();

        return [
            'stats' => [
                'joined_classes' => $members->count(),
                'attendance_percent' => $attendancePercent,
                'absent_sessions' => $absentSessions,
                'warning_count' => $warningCount,
                'pending_leave_requests' => $pendingLeaveRequests,
                'latest_attendance_label' => $this->studentDashboardDateLabel($latestAttendanceDate),
            ],
            'joined_cards' => $joinedCards,
        ];
    }
}
