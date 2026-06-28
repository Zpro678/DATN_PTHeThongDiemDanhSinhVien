<?php

namespace Tests\Unit;

use App\Services\AttendanceCalculator;
use PHPUnit\Framework\TestCase;

class AttendanceCalculatorTest extends TestCase
{
    /**
     * Khớp đúng "bảng kết quả tổng hợp" (mục 8 đặc tả). Chuỗi nhị phân: 1 = có mặt, 0 = vắng.
     *
     * @dataProvider tableCases
     */
    public function test_classify_matches_spec_table(string $bits, string $expected): void
    {
        $statuses = array_map(fn ($c) => $c === '1' ? 'present' : 'absent', str_split($bits));
        $result = AttendanceCalculator::consolidateStatuses($statuses, false);

        $this->assertSame($expected, $result['status'], "Chuỗi {$bits} phải ra {$expected}");
    }

    /**
     * @return array<string, array{0:string,1:string}>
     */
    public static function tableCases(): array
    {
        return [
            '1111 → có mặt' => ['1111', 'present'],
            '0000 → vắng' => ['0000', 'absent'],
            '0111 → đi muộn' => ['0111', 'late'],
            '0011 → đi muộn' => ['0011', 'late'],
            '0001 → vắng' => ['0001', 'absent'],
            '1110 → về sớm' => ['1110', 'early_leave'],
            '1100 → về sớm' => ['1100', 'early_leave'],
            '1101 → vắng giữa giờ' => ['1101', 'partial'],
            '1011 → vắng giữa giờ' => ['1011', 'partial'],
            '1010 → vắng giữa giờ' => ['1010', 'partial'],
            '0101 → vắng' => ['0101', 'absent'],
            '0110 → vắng' => ['0110', 'absent'],
            '0010 → vắng' => ['0010', 'absent'],
        ];
    }

    public function test_deductions_match_config(): void
    {
        $this->assertSame(0.0, AttendanceCalculator::consolidateStatuses(['present', 'present'], false)['deduction']);
        $this->assertSame(0.5, AttendanceCalculator::consolidateStatuses(['absent', 'present', 'present'], false)['deduction']); // late
        $this->assertSame(0.5, AttendanceCalculator::consolidateStatuses(['present', 'absent', 'present'], false)['deduction']); // partial
        $this->assertSame(1.0, AttendanceCalculator::consolidateStatuses(['present', 'present', 'absent'], false)['deduction']); // early_leave
        $this->assertSame(1.0, AttendanceCalculator::consolidateStatuses(['absent', 'absent'], false)['deduction']); // absent
    }

    public function test_excused_whole_meeting(): void
    {
        $result = AttendanceCalculator::consolidateStatuses(['excused', 'excused'], false);
        $this->assertSame('excused', $result['status']);
        $this->assertSame(0.0, $result['deduction']);

        // Lớp bật trừ vắng có phép -> tạm trừ 1.0 (cấu hình lớp sẽ quyết định sau).
        $this->assertSame(1.0, AttendanceCalculator::consolidateStatuses(['excused'], true)['deduction']);
    }

    public function test_explicit_late_phien_keeps_late(): void
    {
        // Đánh dấu muộn tường minh ở phiên (vd thủ công) thì buổi tối thiểu là Đi muộn.
        $this->assertSame('late', AttendanceCalculator::consolidateStatuses(['late'], false)['status']);
        $this->assertSame('late', AttendanceCalculator::consolidateStatuses(['present', 'late'], false)['status']);
    }

    public function test_pending_interpreted_by_session_type(): void
    {
        $this->assertSame('absent', AttendanceCalculator::interpretStatus('pending', true));   // QR chưa quét
        $this->assertSame('present', AttendanceCalculator::interpretStatus('pending', false)); // thủ công chưa đánh dấu
    }

    /**
     * @dataProvider percentCases
     *
     * @param  array<string,int>  $counts
     */
    public function test_percent_of_planned(int $planned, array $counts, bool $deduct, int $expected): void
    {
        $this->assertSame($expected, AttendanceCalculator::percentOfPlanned($planned, $counts, $deduct));
    }

    /**
     * @return array<string, array{0:int,1:array<string,int>,2:bool,3:int}>
     */
    public static function percentCases(): array
    {
        return [
            'đủ' => [15, [], true, 100],
            'vắng 1' => [15, ['absent' => 1], true, 93],            // (15-1)/15
            'đi muộn 1' => [15, ['late' => 1], true, 97],           // (15-0.5)/15
            'vắng giữa giờ 2' => [15, ['partial' => 2], true, 93],  // (15-1)/15
            'về sớm 1' => [15, ['early_leave' => 1], true, 93],     // (15-1)/15
            'có phép loại khỏi mẫu (deduct)' => [15, ['excused' => 3], true, 100],   // 12/12
            'có phép như có mặt (không deduct)' => [15, ['excused' => 3], false, 100], // 15/15
        ];
    }
}
