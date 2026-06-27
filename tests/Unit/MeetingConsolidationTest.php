<?php

namespace Tests\Unit;

use App\Services\MeetingConsolidationService;
use PHPUnit\Framework\TestCase;

class MeetingConsolidationTest extends TestCase
{
    private MeetingConsolidationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MeetingConsolidationService();
    }

    /**
     * @dataProvider consolidationCases
     *
     * @param  array<int, string>  $statuses
     */
    public function test_consolidation_rule(array $statuses, string $expectedStatus, float $expectedDeduction): void
    {
        $result = $this->service->consolidateStatuses($statuses, false);

        $this->assertSame($expectedStatus, $result['status'], 'Trạng thái tổng kết sai cho ['.implode(',', $statuses).']');
        $this->assertSame($expectedDeduction, $result['deduction'], 'Điểm trừ sai cho ['.implode(',', $statuses).']');
    }

    /**
     * @return array<string, array{0: array<int,string>, 1: string, 2: float}>
     */
    public static function consolidationCases(): array
    {
        return [
            // Có mặt: mọi phiên đều present (hoặc có phép xen kẽ).
            'tất cả có mặt' => [['present', 'present', 'present'], 'present', 0.0],
            'present + có phép' => [['present', 'excused', 'present'], 'present', 0.0],

            // Đi muộn: có "đi muộn" ở phiên bất kỳ -> không còn là Có mặt.
            'một phiên đi muộn' => [['late'], 'late', 0.5],
            'đi muộn giữa chừng' => [['present', 'late', 'present'], 'late', 0.5],
            'đi muộn cả ba' => [['late', 'late', 'late'], 'late', 0.5],

            // Đi muộn: từng vắng nhưng phiên cuối có mặt.
            'vắng đầu, có mặt sau' => [['absent', 'present', 'present'], 'late', 0.5],
            'vắng giữa, có mặt lại' => [['present', 'absent', 'present'], 'late', 0.5],

            // Vắng/Về sớm: phiên cuối vắng.
            'về sớm' => [['present', 'present', 'absent'], 'absent', 1.0],
            'vắng hết' => [['absent', 'absent', 'absent'], 'absent', 1.0],

            // Có phép cả buổi.
            'có phép cả buổi' => [['excused', 'excused', 'excused'], 'excused', 0.0],
        ];
    }

    public function test_excused_deducts_when_class_setting_on(): void
    {
        $result = $this->service->consolidateStatuses(['excused', 'excused'], true);

        $this->assertSame('excused', $result['status']);
        $this->assertSame(1.0, $result['deduction']);
    }

    public function test_late_does_not_count_as_full_present(): void
    {
        // Hồi quy: một phiên "đi muộn" KHÔNG được tổng kết thành "Có mặt".
        $result = $this->service->consolidateStatuses(['late'], false);

        $this->assertNotSame('present', $result['status']);
        $this->assertSame('late', $result['status']);
    }
}
