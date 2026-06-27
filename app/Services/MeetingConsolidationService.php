<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\ClassMeeting;
use App\Models\MeetingSummary;
use Illuminate\Support\Collection;

/**
 * Tổng hợp (tổng kết) trạng thái chuyên cần của từng sinh viên qua TẤT CẢ các phiên
 * điểm danh trong một buổi học.
 *
 * Quy tắc tính (sắp theo thứ tự phiên, phiên cuối là phiên chốt):
 *  - Phiên CUỐI vắng  -> Vắng cả buổi (-1). Nếu trước đó từng có mặt thì gọi là "Về sớm".
 *  - Phiên cuối có mặt + TẤT CẢ phiên đều có mặt -> Có mặt (0).
 *  - Phiên cuối có mặt + có ít nhất 1 phiên vắng -> Đi muộn (-0.5).
 *  - Có phép (excused) cả buổi -> Có phép, trừ điểm theo cấu hình lớp.
 *
 * "Có mặt" ở đây gồm các trạng thái phiên: present, late, excused.
 * "Vắng" gồm: absent, pending (chưa điểm danh khi buổi đã kết thúc), invalid.
 */
class MeetingConsolidationService
{
    /** Các trạng thái phiên được coi là "đã có mặt". */
    private const PRESENTISH = ['present', 'late', 'excused'];

    /**
     * Trạng thái phiên có được tính là "có mặt" hay không.
     */
    public function isPresent(string $status): bool
    {
        return in_array($status, self::PRESENTISH, true);
    }

    /**
     * Tính trạng thái tổng kết + điểm trừ từ chuỗi trạng thái các phiên (đã sắp thứ tự).
     *
     * @param  array<int, string>  $statuses  Trạng thái từng phiên theo thứ tự thời gian.
     * @return array{status: string, deduction: float, label: string}
     */
    public function consolidateStatuses(array $statuses, bool $deductExcused = false): array
    {
        if ($statuses === []) {
            return ['status' => 'absent', 'deduction' => 1.0, 'label' => 'Vắng'];
        }

        // Có phép cả buổi.
        if (collect($statuses)->every(fn (string $s) => $s === 'excused')) {
            return [
                'status' => 'excused',
                'deduction' => $deductExcused ? 1.0 : 0.0,
                'label' => 'Có phép',
            ];
        }

        $last = $statuses[array_key_last($statuses)];

        // Phiên cuối vắng -> vắng cả buổi (-1).
        if (! $this->isPresent($last)) {
            // "Về sớm" = có mặt từ đầu nhưng vắng phiên cuối; nếu phiên đầu đã vắng thì coi là "Vắng".
            $startedPresent = $this->isPresent($statuses[0]);

            return [
                'status' => 'absent',
                'deduction' => 1.0,
                'label' => $startedPresent ? 'Về sớm' : 'Vắng',
            ];
        }

        // Phiên cuối có mặt + MỌI phiên đều "có mặt" hoặc "có phép" (không trễ, không vắng) -> Có mặt (0).
        if (collect($statuses)->every(fn (string $s) => in_array($s, ['present', 'excused'], true))) {
            return ['status' => 'present', 'deduction' => 0.0, 'label' => 'Có mặt'];
        }

        // Còn lại (có "đi muộn" ở phiên bất kỳ, hoặc từng vắng nhưng phiên cuối có mặt) -> Đi muộn (-0.5).
        return ['status' => 'late', 'deduction' => 0.5, 'label' => 'Đi muộn'];
    }

    /**
     * Điểm trừ ứng với một trạng thái khi giảng viên chỉnh sửa thủ công.
     */
    public function deductionForStatus(string $status, bool $deductExcused = false): float
    {
        return match ($status) {
            'present' => 0.0,
            'late' => 0.5,
            'excused' => $deductExcused ? 1.0 : 0.0,
            default => 1.0, // absent
        };
    }

    /**
     * Tính tổng kết cho toàn bộ thành viên đang hoạt động của buổi (không ghi DB).
     *
     * @return Collection<int, array{member: \App\Models\ClassMember, statuses: array<int,string>, status: string, deduction: float, label: string}>
     */
    public function consolidateMeeting(ClassMeeting $meeting): Collection
    {
        $deductExcused = (bool) $meeting->courseClass->deduct_excused_absence;

        // Các phiên của buổi theo thứ tự thời gian (phiên cuối = phiên chốt).
        // Kèm qr_token để biết phiên QR hay thủ công khi diễn giải trạng thái "chưa điểm danh".
        $sessions = $meeting->sessions()->orderBy('id')->get(['id', 'qr_token']);
        $sessionIds = $sessions->pluck('id');

        $members = $meeting->courseClass->members()
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get();

        // Map: [member_id][session_id] => status.
        $records = AttendanceRecord::query()
            ->whereIn('class_session_id', $sessionIds)
            ->get(['class_member_id', 'class_session_id', 'status'])
            ->groupBy('class_member_id');

        return $members->map(function ($member) use ($sessions, $records, $deductExcused) {
            $bySession = ($records->get($member->id) ?? collect())->keyBy('class_session_id');

            $statuses = $sessions
                ->map(function ($session) use ($bySession) {
                    $raw = $bySession->get($session->id)?->status ?? 'pending';

                    // Diễn giải "chưa điểm danh" theo loại phiên:
                    // - Phiên QR: chưa quét mã => Vắng.
                    // - Phiên thủ công: chưa đánh dấu => mặc định Có mặt.
                    if ($raw === 'pending') {
                        return $session->qr_token !== null ? 'absent' : 'present';
                    }

                    return $raw;
                })
                ->all();

            $result = $this->consolidateStatuses($statuses, $deductExcused);

            return [
                'member' => $member,
                'statuses' => $statuses,
                'status' => $result['status'],
                'deduction' => $result['deduction'],
                'label' => $result['label'],
            ];
        })->values();
    }

    /**
     * Đồng bộ kết quả tổng kết vào bảng meeting_summaries.
     * Các dòng giảng viên đã chỉnh sửa (is_overridden) chỉ cập nhật auto_status để đối chiếu,
     * không ghi đè lựa chọn thủ công.
     */
    public function syncSummaries(ClassMeeting $meeting): void
    {
        $consolidated = $this->consolidateMeeting($meeting);

        foreach ($consolidated as $row) {
            $summary = MeetingSummary::query()->firstOrNew([
                'meeting_id' => $meeting->id,
                'class_member_id' => $row['member']->id,
            ]);

            $summary->auto_status = $row['status'];

            if (! $summary->is_overridden) {
                $summary->status = $row['status'];
                $summary->deduction = $row['deduction'];
            }

            $summary->save();
        }
    }

    /**
     * Nhãn hiển thị tiếng Việt cho trạng thái tổng kết.
     */
    public function statusLabel(string $status): string
    {
        return match ($status) {
            'present' => 'Có mặt',
            'late' => 'Đi muộn',
            'excused' => 'Có phép',
            default => 'Vắng',
        };
    }
}
