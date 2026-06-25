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
                'courseClass:id,code,name,subject_code,semester,total_lessons',
                'attendanceRecords' => fn ($query) => $query
                    // Chỉ tính các bản ghi thuộc buổi điểm danh đã chốt.
                    ->whereHas('classSession', fn ($sessionQuery) => $sessionQuery->where('status', 'closed'))
                    ->with('classSession:id,class_id,date,status,lesson_count'),
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

        // Tính theo số tiết của từng buổi, không chỉ đếm số buổi điểm danh.
        $totalLessons = (int) $records->sum(fn (AttendanceRecord $record) => $this->lessonCount($record));
        $presentLessons = (int) $records
            ->where('status', 'present')
            ->sum(fn (AttendanceRecord $record) => $this->lessonCount($record));
        $lateLessons = (int) $records
            ->where('status', 'late')
            ->sum(fn (AttendanceRecord $record) => $this->lessonCount($record));
        $excusedLessons = (int) $records
            ->where('status', 'excused')
            ->sum(fn (AttendanceRecord $record) => $this->lessonCount($record));
        $absentLessons = (int) $records
            ->where('status', 'absent')
            ->sum(fn (AttendanceRecord $record) => $this->lessonCount($record));

        // Số lần đi muộn để quy đổi 3 lần = 1 tiết vắng.
        $lateCount = $records->where('status', 'late')->count();

        // Bỏ vắng có phép khỏi mẫu số, quy đổi muộn thành vắng (xem AttendanceCalculator).
        // % tính trên tổng tiết kế hoạch của lớp để nhất quán với quỹ vắng/điều kiện dự thi.
        $plannedLessons = max((int) ($member->courseClass?->total_lessons ?? 0), $totalLessons);
        $latesPerAbsent = (int) ($member->courseClass?->lates_per_absent ?? AttendanceCalculator::LATE_TO_ABSENT_RATIO);
        $deductExcusedAbsence = (bool) ($member->courseClass?->deduct_excused_absence ?? true);

        $allowedAbsentLessons = (int) floor($plannedLessons * AttendanceCalculator::ABSENCE_LIMIT_RATIO);
        $effectiveAbsentLessons = AttendanceCalculator::effectiveAbsentLessons($absentLessons, $lateCount, $latesPerAbsent);
        $attendancePercent = AttendanceCalculator::percentOfPlanned(
            $plannedLessons,
            $excusedLessons,
            $absentLessons,
            $lateCount,
            $latesPerAbsent,
            $deductExcusedAbsence
        );

        $latestRecord = $records
            ->sortByDesc(fn (AttendanceRecord $record) => $record->classSession?->date?->getTimestamp() ?? 0)
            ->first();
        $latestDate = $latestRecord?->classSession?->date;
        $classLabel = $this->classLabel($member);
        $warnings = [];

        // Dưới ngưỡng chuyên cần tối thiểu thì sinh cảnh báo nguy cơ cấm thi.
        if ($totalLessons > 0 && $attendancePercent < AttendanceCalculator::MIN_ATTENDANCE_PERCENT) {
            $warnings[] = [
                'type' => 'danger',
                'icon' => 'alert-triangle',
                'title' => 'Nguy cơ cấm thi',
                'message' => "Lớp {$classLabel}. Bạn đã vắng {$effectiveAbsentLessons}/{$allowedAbsentLessons} tiết được phép (đã tính muộn quy đổi), tỷ lệ chuyên cần còn {$attendancePercent}%.",
                'date' => $latestDate?->format('d/m/Y') ?? now()->format('d/m/Y'),
                'sort_date' => $latestDate?->toDateString() ?? now()->toDateString(),
                'route' => 'student.attendance.history',
                'action_label' => 'Xem lịch sử',
            ];
        }

        // Đi muộn nhiều lần được tách thành cảnh báo riêng để học viên dễ chú ý.
        if ($latesPerAbsent > 0 && $lateCount >= $latesPerAbsent) {
            $warnings[] = [
                'type' => 'warning',
                'icon' => 'clock',
                'title' => 'Điểm danh muộn',
                'message' => "Lớp {$classLabel}. Bạn đã điểm danh muộn {$lateCount} lần. Hãy kiểm tra lại lịch học để tránh ảnh hưởng chuyên cần.",
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
     * Lấy số tiết của bản ghi điểm danh; nếu dữ liệu cũ chưa có lesson_count thì tính là 1 tiết.
     */
    private function lessonCount(AttendanceRecord $record): int
    {
        return max(1, (int) ($record->classSession?->lesson_count ?? 1));
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
                'absent_lessons' => 0,
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
    private function studentAttendanceStyle(int $attendancePercent, int $studiedLessons): array
    {
        if ($studiedLessons <= 0) {
            return [
                'label' => 'Chưa có dữ liệu',
                'statusClass' => 'bg-slate-100 text-slate-500',
                'bar' => 'bg-slate-300',
                'color' => 'text-slate-500',
            ];
        }

        if ($attendancePercent < 80) {
            return [
                'label' => 'Nguy cơ cấm thi',
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
                'courseClass:id,owner_user_id,code,name,subject_code,semester,status,total_lessons',
                'courseClass.owner:id,name',
            ])
            ->where('user_id', $studentUserId)
            ->where('status', 'active')
            ->get(['id', 'class_id', 'student_code', 'full_name', 'user_id', 'status']);

        if ($members->isEmpty()) {
            return $this->emptyStudentDashboard();
        }

        $memberIds = $members->pluck('id');

        $attendanceRows = DB::table('attendance_records as ar')
            ->join('class_sessions as cs', 'cs.id', '=', 'ar.class_session_id')
            ->whereIn('ar.class_member_id', $memberIds)
            ->whereNull('ar.deleted_at')
            ->whereNull('cs.deleted_at')
            ->where('cs.status', 'closed')
            ->selectRaw("
                ar.class_member_id,
                COUNT(*) as records_count,
                SUM(COALESCE(NULLIF(cs.lesson_count, 0), 1)) as total_lessons,
                SUM(CASE WHEN ar.status = 'present'
                    THEN COALESCE(NULLIF(cs.lesson_count, 0), 1) ELSE 0 END) as present_lessons,
                SUM(CASE WHEN ar.status = 'late'
                    THEN COALESCE(NULLIF(cs.lesson_count, 0), 1) ELSE 0 END) as late_lessons,
                SUM(CASE WHEN ar.status = 'excused'
                    THEN COALESCE(NULLIF(cs.lesson_count, 0), 1) ELSE 0 END) as excused_lessons,
                SUM(CASE WHEN ar.status = 'absent'
                    THEN COALESCE(NULLIF(cs.lesson_count, 0), 1) ELSE 0 END) as absent_lessons,
                SUM(CASE WHEN ar.status = 'late' THEN 1 ELSE 0 END) as late_count,
                MAX(cs.date) as latest_session_date
            ")
            ->groupBy('ar.class_member_id')
            ->get()
            ->keyBy('class_member_id');

        $leaveRows = DB::table('leave_requests')
            ->whereIn('class_member_id', $memberIds)
            ->selectRaw("
                class_member_id,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count
            ")
            ->groupBy('class_member_id')
            ->get()
            ->keyBy('class_member_id');

        // Tổng tiết kế hoạch theo từng môn để tính % chuyên cần trên cả khóa.
        $plannedByMember = $members->mapWithKeys(function (ClassMember $member) use ($attendanceRows) {
            $studied = (int) ($attendanceRows->get($member->id)->total_lessons ?? 0);

            return [$member->id => max((int) ($member->courseClass?->total_lessons ?? 0), $studied)];
        });

        // Tổng hợp theo tổng tiết kế hoạch: bỏ vắng có phép, quy đổi 3 lần muộn = 1 tiết vắng.
        $plannedCounted = 0;
        $projectedAttended = 0;
        $absentLessons = 0;
        $warningCount = 0;

        foreach ($attendanceRows as $memberId => $row) {
            $planned = (int) ($plannedByMember[$memberId] ?? 0);
            $courseClass = $members->firstWhere('id', $memberId)?->courseClass;
            $latesPerAbsent = (int) ($courseClass?->lates_per_absent ?? AttendanceCalculator::LATE_TO_ABSENT_RATIO);
            $deductExcusedAbsence = (bool) ($courseClass?->deduct_excused_absence ?? true);

            $counted = AttendanceCalculator::countedLessons($planned, (int) $row->excused_lessons, $deductExcusedAbsence);
            $effectiveAbsent = AttendanceCalculator::effectiveAbsentLessons((int) $row->absent_lessons, (int) $row->late_count, $latesPerAbsent);

            $plannedCounted += $counted;
            $projectedAttended += max($counted - $effectiveAbsent, 0);
            $absentLessons += (int) $row->absent_lessons;

            if ((int) $row->total_lessons > 0
                && AttendanceCalculator::percentOfPlanned($planned, (int) $row->excused_lessons, (int) $row->absent_lessons, (int) $row->late_count, $latesPerAbsent, $deductExcusedAbsence) < AttendanceCalculator::MIN_ATTENDANCE_PERCENT) {
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

                $studiedLessons = (int) ($row->total_lessons ?? 0);
                $absentLessons = (int) ($row->absent_lessons ?? 0);
                $totalCourseLessons = max((int) ($courseClass?->total_lessons ?? 0), $studiedLessons);

                $latesPerAbsent = (int) ($courseClass?->lates_per_absent ?? AttendanceCalculator::LATE_TO_ABSENT_RATIO);
                $deductExcusedAbsence = (bool) ($courseClass?->deduct_excused_absence ?? true);

                // % chuyên cần tính trên tổng tiết kế hoạch để nhất quán với quỹ vắng.
                $attendancePercent = AttendanceCalculator::percentOfPlanned(
                    $totalCourseLessons,
                    (int) ($row->excused_lessons ?? 0),
                    $absentLessons,
                    (int) ($row->late_count ?? 0),
                    $latesPerAbsent,
                    $deductExcusedAbsence
                );

                $style = $this->studentAttendanceStyle($attendancePercent, $studiedLessons);

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
                    // Số tiết vắng trên tổng số tiết của lớp, dùng để hiển thị dạng "x/y tiết".
                    'absent' => "{$absentLessons}/{$totalCourseLessons} tiết",
                    // Tổng số tiết đã học/đã chốt điểm danh của lớp này.
                    'studied_lessons' => $studiedLessons,
                    // Tổng số tiết theo kế hoạch của lớp, dùng làm mẫu số cho tiến độ.
                    'total_lessons' => $totalCourseLessons,
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
                'absent_lessons' => $absentLessons,
                'warning_count' => $warningCount,
                'pending_leave_requests' => $pendingLeaveRequests,
                'latest_attendance_label' => $this->studentDashboardDateLabel($latestAttendanceDate),
            ],
            'joined_cards' => $joinedCards,
        ];
    }
}
