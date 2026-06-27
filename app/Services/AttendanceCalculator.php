<?php

namespace App\Services;

/**
 * Tính toán chuyên cần dùng chung cho toàn hệ thống.
 *
 * Đơn vị tính là BUỔI (mỗi buổi = 1 đơn vị). Quy ước:
 * - Mỗi buổi đã diễn ra (có phiên đã chốt) được gộp trạng thái của sinh viên qua các phiên:
 *   có mặt nếu present/late ở bất kỳ phiên nào; vắng phép nếu excused và không có mặt;
 *   vắng nếu absent và không có mặt.
 * - Đi muộn được tính như có đi học (KHÔNG quy đổi thành vắng).
 * - Vắng có phép (excused) bị loại khỏi mẫu số nếu lớp bật trừ chuyên cần khi vắng có phép.
 */
class AttendanceCalculator
{
    /** Ngưỡng chuyên cần tối thiểu (%) trước khi cảnh báo nguy cơ cấm thi. */
    public const MIN_ATTENDANCE_PERCENT = 80;

    /** Ngưỡng cảnh báo nhẹ (%). */
    public const WARNING_PERCENT = 85;

    /** Tỉ lệ số buổi được phép vắng trên tổng số buổi dự kiến của lớp (20%). */
    public const ABSENCE_LIMIT_RATIO = 0.2;

    /**
     * Gộp trạng thái điểm danh của MỘT sinh viên theo từng buổi.
     *
     * @param  iterable  $rows  Mỗi phần tử có thuộc tính ->meeting_id và ->status (chỉ gồm phiên đã chốt).
     * @return array{present: int, late: int, excused: int, absent: int, total: int}
     *         present/late/excused/absent là SỐ BUỔI; total = tổng buổi đã diễn ra.
     */
    public static function consolidateByMeeting(iterable $rows): array
    {
        $byMeeting = [];
        foreach ($rows as $row) {
            $meetingId = $row->meeting_id ?? null;
            if ($meetingId === null) {
                continue;
            }
            $byMeeting[$meetingId][] = $row->status;
        }

        $counts = ['present' => 0, 'late' => 0, 'excused' => 0, 'absent' => 0, 'total' => 0];

        foreach ($byMeeting as $statuses) {
            $counts['total']++;
            if (in_array('present', $statuses, true)) {
                $counts['present']++;
            } elseif (in_array('late', $statuses, true)) {
                $counts['late']++;
            } elseif (in_array('excused', $statuses, true)) {
                $counts['excused']++;
            } elseif (in_array('absent', $statuses, true)) {
                $counts['absent']++;
            }
            // các trạng thái khác (pending/invalid) không tính vào 4 nhóm.
        }

        return $counts;
    }

    /**
     * Tổng số buổi được tính chuyên cần (trừ vắng có phép nếu lớp bật trừ).
     */
    public static function countedSessions(int $plannedSessions, int $excusedSessions, bool $deductExcusedAbsence = true): int
    {
        return $deductExcusedAbsence ? max($plannedSessions - $excusedSessions, 0) : $plannedSessions;
    }

    /**
     * Số buổi được phép vắng (20% tổng buổi dự kiến).
     */
    public static function allowedAbsentSessions(int $plannedSessions): int
    {
        return (int) floor($plannedSessions * self::ABSENCE_LIMIT_RATIO);
    }

    /**
     * Phần trăm chuyên cần tính trên TỔNG SỐ BUỔI dự kiến của lớp:
     *   (counted − vắng) / counted, với counted = tổng dự kiến − vắng có phép (nếu bật trừ).
     */
    public static function percentOfPlanned(
        int $plannedSessions,
        int $excusedSessions,
        int $absentSessions,
        bool $deductExcusedAbsence = true,
    ): int {
        $counted = self::countedSessions($plannedSessions, $excusedSessions, $deductExcusedAbsence);

        if ($counted <= 0) {
            return 100;
        }

        $attended = max($counted - max($absentSessions, 0), 0);

        return (int) round(($attended / $counted) * 100);
    }
}
