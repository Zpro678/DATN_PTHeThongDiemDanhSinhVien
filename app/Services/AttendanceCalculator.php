<?php

namespace App\Services;

/**
 * Tính toán chuyên cần dùng chung cho toàn hệ thống.
 *
 * Quy ước:
 * - Vắng có phép (excused) bị loại khỏi mẫu số, không tính là vắng cũng không tính có mặt.
 * - Đi muộn được đếm theo số LẦN; cứ đủ 3 lần muộn thì quy đổi thành 1 tiết vắng.
 * - Phần muộn lẻ chưa đủ 3 lần vẫn được tính là có đi học.
 *
 */
class AttendanceCalculator
{
    /** Cứ đủ 3 lần đi muộn thì quy đổi thành 1 tiết vắng. */
    public const LATE_TO_ABSENT_RATIO = 3;

    /** Ngưỡng chuyên cần tối thiểu (%) trước khi cảnh báo nguy cơ cấm thi. */
    public const MIN_ATTENDANCE_PERCENT = 80;

    /** Tỉ lệ số tiết được phép vắng trên tổng số tiết kế hoạch của lớp (20%). */
    public const ABSENCE_LIMIT_RATIO = 0.2;

    /**
     * Quy đổi số lần đi muộn thành số tiết vắng (floor(số lần / 3)).
     */
    public static function lateAbsentLessons(int $lateCount): int
    {
        if ($lateCount <= 0) {
            return 0;
        }

        return intdiv($lateCount, self::LATE_TO_ABSENT_RATIO);
    }

    /**
     * Tổng tiết được tính chuyên cần sau khi bỏ vắng có phép khỏi mẫu số.
     */
    public static function countedLessons(int $totalLessons, int $excusedLessons): int
    {
        return max($totalLessons - $excusedLessons, 0);
    }

    /**
     * Số tiết vắng dùng để xét quỹ vắng/cảnh báo: vắng thật + muộn quy đổi.
     */
    public static function effectiveAbsentLessons(int $absentLessons, int $lateCount): int
    {
        return max($absentLessons, 0) + self::lateAbsentLessons($lateCount);
    }

    /**
     * Số tiết được tính là có chuyên cần (có mặt + muộn còn lại sau quy đổi).
     */
    public static function attendedLessons(int $presentLessons, int $lateLessons, int $lateCount): int
    {
        return max($presentLessons + $lateLessons - self::lateAbsentLessons($lateCount), 0);
    }

    /**
     * Phần trăm chuyên cần tính trên các tiết ĐÃ CHỐT làm tròn về số nguyên.
     */
    public static function percent(
        int $presentLessons,
        int $lateLessons,
        int $excusedLessons,
        int $totalLessons,
        int $lateCount,
    ): int {
        $counted = self::countedLessons($totalLessons, $excusedLessons);

        if ($counted <= 0) {
            return 100;
        }

        $attended = self::attendedLessons($presentLessons, $lateLessons, $lateCount);

        return (int) round(($attended / $counted) * 100);
    }

    /**
     * Phần trăm chuyên cần tính trên TỔNG TIẾT KẾ HOẠCH của lớp (cả khóa):
     *   (tổng kế hoạch − vắng có phép − vắng hiệu dụng) / (tổng kế hoạch − vắng có phép).
     *
     * Coi như sinh viên sẽ tham gia các tiết còn lại; mẫu số là cả khóa chứ không
     * chỉ các buổi đã chốt, nên nhất quán với "quỹ vắng" (vắng vs 20% tổng tiết).
     */
    public static function percentOfPlanned(
        int $plannedLessons,
        int $excusedLessons,
        int $absentLessons,
        int $lateCount,
    ): int {
        $counted = self::countedLessons($plannedLessons, $excusedLessons);

        if ($counted <= 0) {
            return 100;
        }

        $effectiveAbsent = self::effectiveAbsentLessons($absentLessons, $lateCount);
        $attended = max($counted - $effectiveAbsent, 0);

        return (int) round(($attended / $counted) * 100);
    }
}
