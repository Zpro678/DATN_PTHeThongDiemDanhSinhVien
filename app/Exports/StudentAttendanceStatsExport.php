<?php

namespace App\Exports;

use App\Services\StatisticalService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StudentAttendanceStatsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $studentId;
    protected $subjects;

    public function __construct(int $studentId)
    {
        $this->studentId = $studentId;
        $statistics = app(StatisticalService::class)->getStudentAttendanceStatistics($studentId);
        $this->subjects = $statistics['subjects'];
    }

    public function collection()
    {
        return collect($this->subjects)->filter(fn($s) => ($s['planned_sessions'] ?? $s['total_sessions'] ?? 0) > 0 || ($s['total'] ?? 0) > 0);
    }

    public function headings(): array
    {
        return [
            ['BÁO CÁO THỐNG KÊ CHUYÊN CẦN'],
            ['Tên môn học', 'Số buổi học', 'Có mặt', 'Đi muộn', 'Vắng mặt', 'Có phép', 'Tỷ lệ chuyên cần', 'Giới hạn an toàn'],
        ];
    }

    public function map($subject): array
    {
        return [
            $subject['name'] ?? '',
            $subject['total'] ?? 0,
            $subject['present'] ?? 0,
            $subject['late'] ?? 0,
            $subject['absent'] ?? 0,
            $subject['excused'] ?? 0,
            ($subject['percent'] ?? 0) . '%',
            $subject['absence_budget_label'] ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:H1');
        return [
            1    => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            2    => ['font' => ['bold' => true]],
        ];
    }
}
