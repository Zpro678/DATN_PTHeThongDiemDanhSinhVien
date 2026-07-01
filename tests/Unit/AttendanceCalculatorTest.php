<?php

namespace Tests\Unit;

use App\Services\AttendanceCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AttendanceCalculatorTest extends TestCase
{
    /**
     * Khớp đúng quy tắc tổng kết mới. Chuỗi nhị phân: 1 = có mặt, 0 = vắng.
     *
     */
    #[DataProvider('tableCases')]
    public function test_classify_matches_spec_table(string $bits, string $expected): void
    {
        $statuses = array_map(fn ($c) => $c === '1' ? 'present' : 'absent', str_split($bits));
        $result = AttendanceCalculator::consolidateStatuses($statuses);

        $this->assertSame($expected, $result['status'], "Chuỗi {$bits} phải ra {$expected}");
    }

    /**
     * @return array<string, array{0:string,1:string}>
     */
    public static function tableCases(): array
    {
        return [
            '1111 → có mặt' => ['1111', 'present'],
            '1101 → có mặt' => ['1101', 'present'],
            '1011 → có mặt' => ['1011', 'present'],
            '1001 → có mặt' => ['1001', 'present'],
            '11 → có mặt' => ['11', 'present'],
            '0111 → đi muộn' => ['0111', 'late'],
            '0011 → đi muộn' => ['0011', 'late'],
            '0101 → đi muộn' => ['0101', 'late'],
            '0001 → đi muộn' => ['0001', 'late'],
            '0000 → vắng' => ['0000', 'absent'],
            '1110 → vắng' => ['1110', 'absent'],
            '1100 → vắng' => ['1100', 'absent'],
            '1010 → vắng' => ['1010', 'absent'],
            '0010 → vắng' => ['0010', 'absent'],
            '0110 → vắng' => ['0110', 'absent'],
        ];
    }

    public function test_deductions_match_config(): void
    {
        $this->assertSame(0.0, AttendanceCalculator::consolidateStatuses(['present', 'present'])['deduction']);
        $this->assertSame(0.5, AttendanceCalculator::consolidateStatuses(['absent', 'present', 'present'])['deduction']); // late
        $this->assertSame(0.0, AttendanceCalculator::consolidateStatuses(['present', 'absent', 'present'])['deduction']); // present
        $this->assertSame(1.0, AttendanceCalculator::consolidateStatuses(['present', 'present', 'absent'])['deduction']); // absent
        $this->assertSame(1.0, AttendanceCalculator::consolidateStatuses(['absent', 'absent'])['deduction']); // absent
    }

    public function test_excused_sessions_are_presentish_for_auto_summary(): void
    {
        $result = AttendanceCalculator::consolidateStatuses(['excused', 'excused'], ['excused' => 1.0]);
        $this->assertSame('present', $result['status']);
        $this->assertSame(0.0, $result['deduction']);

        $late = AttendanceCalculator::consolidateStatuses(['absent', 'excused']);
        $this->assertSame('late', $late['status']);
    }

    public function test_explicit_late_session_is_preserved_in_summary(): void
    {
        // Late vẫn được tính là có mặt cho điều kiện phiên cuối, nhưng tổng kết không được thành "Có mặt".
        $this->assertSame('late', AttendanceCalculator::consolidateStatuses(['late'])['status']);
        $this->assertSame('late', AttendanceCalculator::consolidateStatuses(['present', 'late'])['status']);
        $this->assertSame('late', AttendanceCalculator::consolidateStatuses(['late', 'present'])['status']);
        $this->assertSame('late', AttendanceCalculator::consolidateStatuses(['absent', 'late'])['status']);
        $this->assertSame('absent', AttendanceCalculator::consolidateStatuses(['late', 'absent'])['status']);
    }

    public function test_pending_interpreted_by_session_type(): void
    {
        $this->assertSame('absent', AttendanceCalculator::interpretStatus('pending', true));   // QR chưa quét
        $this->assertSame('present', AttendanceCalculator::interpretStatus('pending', false)); // thủ công chưa đánh dấu
    }

    /**
     * @param  array<string,int>  $counts
     */
    #[DataProvider('percentCases')]
    public function test_percent_of_planned(int $planned, array $counts, array $rules, int $expected): void
    {
        $this->assertSame($expected, AttendanceCalculator::percentOfPlanned($planned, $counts, $rules));
    }

    /**
     * @return array<string, array{0:int,1:array<string,int>,2:array<string,float>,3:int}>
     */
    public static function percentCases(): array
    {
        return [
            'đủ' => [15, [], [], 100],
            'vắng 1' => [15, ['absent' => 1], [], 93],                       // (15-1)/15
            'đi muộn 1' => [15, ['late' => 1], [], 97],                      // (15-0.5)/15
            'có mặt xen giữa vẫn đủ' => [15, ['present' => 3], [], 100],
            'có phép loại khỏi mẫu' => [15, ['excused' => 3], ['excused' => 1.0], 100], // 12/12
            'có phép như có mặt' => [15, ['excused' => 3], ['excused' => 0.0], 100],    // 15/15
        ];
    }
}
