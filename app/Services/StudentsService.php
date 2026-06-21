<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\ClassMember;
use App\Models\LeaveRequest;

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
}
