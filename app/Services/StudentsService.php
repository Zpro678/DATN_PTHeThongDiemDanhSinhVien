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
                'courseClass:id,code,name,subject_code,semester',
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

        $attendedLessons = $presentLessons + $lateLessons + $excusedLessons;
        $attendancePercent = $totalLessons > 0
            ? (int) round(($attendedLessons / $totalLessons) * 100)
            : 100;

        $latestRecord = $records
            ->sortByDesc(fn (AttendanceRecord $record) => $record->classSession?->date?->getTimestamp() ?? 0)
            ->first();
        $latestDate = $latestRecord?->classSession?->date;
        $classLabel = $this->classLabel($member);
        $warnings = [];

        // Dưới 80% chuyên cần thì sinh cảnh báo nguy cơ cấm thi.
        if ($totalLessons > 0 && $attendancePercent < 80) {
            $warnings[] = [
                'type' => 'danger',
                'icon' => 'alert-triangle',
                'title' => 'Nguy cơ cấm thi',
                'message' => "Lớp {$classLabel}. Bạn đã vắng {$absentLessons}/{$totalLessons} tiết học, tỷ lệ chuyên cần hiện là {$attendancePercent}%.",
                'date' => $latestDate?->format('d/m/Y') ?? now()->format('d/m/Y'),
                'sort_date' => $latestDate?->toDateString() ?? now()->toDateString(),
                'route' => 'student.attendance.history',
                'action_label' => 'Xem lịch sử',
            ];
        }

        // Đi muộn nhiều lần được tách thành cảnh báo riêng để học viên dễ chú ý.
        $lateCount = $records->where('status', 'late')->count();

        if ($lateCount >= 3) {
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
     * Hàm này dùng khi:
     * - Không có user hợp lệ.
     * - Sinh viên chưa tham gia lớp nào.
     *
     * Việc trả đủ key mặc định giúp Blade/Livewire luôn có dữ liệu để hiển thị,
     * tránh lỗi undefined index khi view đọc stats hoặc joined_cards.
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
     * Hàm này chỉ xử lý phần hiển thị, không tính lại dữ liệu điểm danh.
     * Mục đích là gom logic màu sắc/trạng thái vào service để view không phải
     * tự viết nhiều điều kiện if/else.
     *
     * @return array{label: string, statusClass: string, bar: string, color: string}
     */
    private function studentAttendanceStyle(int $attendancePercent, int $studiedLessons): array
    {
        // Chưa có buổi học đã chốt thì không nên cảnh báo sinh viên.
        if ($studiedLessons <= 0) {
            return [
                'label' => 'Chưa có dữ liệu',
                'statusClass' => 'bg-slate-100 text-slate-500',
                'bar' => 'bg-slate-300',
                'color' => 'text-slate-500',
            ];
        }

        // Dưới 80% là mốc nguy hiểm vì có thể ảnh hưởng điều kiện dự thi.
        if ($attendancePercent < 80) {
            return [
                'label' => 'Nguy cơ cấm thi',
                'statusClass' => 'bg-error/10 text-error',
                'bar' => 'bg-error',
                'color' => 'text-error',
            ];
        }

        // Từ 80% đến dưới 85% là vùng nhắc nhở sớm để sinh viên chú ý.
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
     * Lấy dữ liệu tổng quan cho dashboard sinh viên từ database.
     *
     * Dữ liệu trả về gồm:
     * - stats: số lớp đã tham gia, % chuyên cần, số tiết vắng, số cảnh báo,
     *   số đơn xin nghỉ đang chờ và nhãn lần điểm danh gần nhất.
     * - joined_cards: danh sách lớp sinh viên đang học, kèm thông tin giảng viên,
     *   mã lớp, học kỳ, chuyên cần và số tiết vắng.
     *
     * @return array<string, mixed>
     */
    public function getDashboardForStudent(int $studentUserId): array
    {
        // User không hợp lệ thì trả dashboard rỗng để view vẫn render an toàn.
        if ($studentUserId <= 0) {
            return $this->emptyStudentDashboard();
        }

        // Lấy các lớp mà sinh viên đang tham gia và nạp sẵn thông tin lớp/giảng viên.
        $members = ClassMember::query()
            ->with([
                'courseClass:id,owner_user_id,code,name,subject_code,semester,status,total_lessons',
                'courseClass.owner:id,name',
            ])
            ->where('user_id', $studentUserId)
            ->where('status', 'active')
            ->get(['id', 'class_id', 'student_code', 'full_name', 'user_id', 'status']);

        // Nếu sinh viên chưa có lớp active thì không cần chạy thêm các query thống kê.
        if ($members->isEmpty()) {
            return $this->emptyStudentDashboard();
        }

        $memberIds = $members->pluck('id');

        // Gom dữ liệu điểm danh theo từng class_member để tránh xử lý từng lớp bằng nhiều query.
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
                SUM(CASE WHEN ar.status IN ('present', 'late', 'excused')
                    THEN COALESCE(NULLIF(cs.lesson_count, 0), 1) ELSE 0 END) as attended_lessons,
                SUM(CASE WHEN ar.status = 'absent'
                    THEN COALESCE(NULLIF(cs.lesson_count, 0), 1) ELSE 0 END) as absent_lessons,
                SUM(CASE WHEN ar.status = 'late' THEN 1 ELSE 0 END) as late_count,
                MAX(cs.date) as latest_session_date
            ")
            ->groupBy('ar.class_member_id')
            ->get()
            ->keyBy('class_member_id');

        // Đếm số đơn xin nghỉ đang chờ theo từng lớp mà sinh viên tham gia.
        $leaveRows = DB::table('leave_requests')
            ->whereIn('class_member_id', $memberIds)
            ->selectRaw("
                class_member_id,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count
            ")
            ->groupBy('class_member_id')
            ->get()
            ->keyBy('class_member_id');

        // Tính thống kê tổng trên tất cả lớp đang học.
        $totalLessons = (int) $attendanceRows->sum('total_lessons');
        $attendedLessons = (int) $attendanceRows->sum('attended_lessons');
        $absentLessons = (int) $attendanceRows->sum('absent_lessons');

        $attendancePercent = $totalLessons > 0
            ? (int) round(($attendedLessons / $totalLessons) * 100)
            : 100;

        // Một lớp được tính là cảnh báo nếu chuyên cần của lớp đó dưới 80%.
        $warningCount = $attendanceRows
            ->filter(function ($row): bool {
                $total = (int) $row->total_lessons;

                if ($total <= 0) {
                    return false;
                }

                $percent = (int) round(((int) $row->attended_lessons / $total) * 100);

                return $percent < 80;
            })
            ->count();

        $pendingLeaveRequests = (int) $leaveRows->sum('pending_count');

        // Lấy ngày điểm danh gần nhất trong tất cả lớp để hiển thị trên thẻ tổng quan.
        $latestAttendanceDate = $attendanceRows
            ->pluck('latest_session_date')
            ->filter()
            ->max();

        // Chuẩn hóa từng lớp thành dữ liệu thẻ để view hiển thị trực tiếp.
        $joinedCards = $members
            ->map(function (ClassMember $member) use ($attendanceRows): array {
                $courseClass = $member->courseClass;
                $row = $attendanceRows->get($member->id);

                $studiedLessons = (int) ($row->total_lessons ?? 0);
                $attendedLessons = (int) ($row->attended_lessons ?? 0);
                $absentLessons = (int) ($row->absent_lessons ?? 0);

                $attendancePercent = $studiedLessons > 0
                    ? (int) round(($attendedLessons / $studiedLessons) * 100)
                    : 100;

                $style = $this->studentAttendanceStyle($attendancePercent, $studiedLessons);
                $totalCourseLessons = max((int) ($courseClass?->total_lessons ?? 0), $studiedLessons);

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
