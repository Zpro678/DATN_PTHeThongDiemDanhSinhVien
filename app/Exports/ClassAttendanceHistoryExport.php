<?php

namespace App\Exports;

use App\Models\CourseClass;
use App\Services\ClassAttendanceHistoryReport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClassAttendanceHistoryExport implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    private int $headerRow = 6;

    private int $dataStartRow = 7;

    private int $dataEndRow = 6;

    private int $columnCount = 4;

    public function __construct(private CourseClass $courseClass)
    {
    }

    public function title(): string
    {
        $title = preg_replace('/[\/\\\?\*\[\]:]/', '', $this->courseClass->class_code ?: $this->courseClass->join_key ?: 'Lich su');

        return mb_substr($title, 0, 31) ?: 'Lich su';
    }

    public function array(): array
    {
        $courseClass = $this->courseClass->fresh() ?? $this->courseClass;
        $report = app(ClassAttendanceHistoryReport::class);
        $members = $report->activeMembersQuery($courseClass)->get();
        $history = $report->build($courseClass, $members);

        $groupedSessionsInfo = $history['groupedSessionsInfo'];
        $matrix = $history['matrix'];
        $membersData = $history['membersData'];
        $rules = $courseClass->getAttendanceRules();

        $sessionHeaders = $groupedSessionsInfo
            ->map(fn ($info) => $info['name']."\n".$info['date'])
            ->values()
            ->all();

        $headers = array_merge(['STT', 'Họ và tên', 'Email', 'Chuyên cần (%)'], $sessionHeaders);
        $this->columnCount = count($headers);

        $classCode = $courseClass->class_code ?: $courseClass->join_key;
        $className = trim(($classCode ? $classCode.' - ' : '').$courseClass->name);

        $rows = [
            ['LỊCH SỬ ĐIỂM DANH LỚP'],
            ['Lớp:', $className],
            ['Ngày xuất:', now()->format('d/m/Y H:i')],
            ['Công thức chuyên cần:', $this->formulaDescription($rules)],
            [''],
            $headers,
        ];

        $currentRow = $this->dataStartRow;
        foreach ($members as $index => $member) {
            $memberInfo = $membersData[$member->id] ?? [];

            $row = [
                $index + 1,
                $member->full_name,
                $member->email,
                (int) ($memberInfo['attendance_percent'] ?? 100).'%',
            ];

            foreach ($groupedSessionsInfo as $groupKey => $info) {
                $row[] = $matrix[$member->id][$groupKey]['text'] ?? 'Chưa điểm danh';
            }

            $rows[] = $row;
            $currentRow++;
        }

        $this->dataEndRow = $currentRow - 1;

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastCol = Coordinate::stringFromColumnIndex($this->columnCount);

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF1D4ED8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEFF6FF']],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->getStyle('A2:A4')->getFont()->setBold(true);
        $sheet->getStyle("A{$this->headerRow}:{$lastCol}{$this->headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1D4ED8']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFBFDBFE']]],
        ]);
        $sheet->getRowDimension($this->headerRow)->setRowHeight(36);

        if ($this->dataEndRow >= $this->dataStartRow) {
            for ($row = $this->dataStartRow; $row <= $this->dataEndRow; $row++) {
                $fillColor = $row % 2 === 0 ? 'FFF8FAFC' : 'FFFFFFFF';
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $fillColor]],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']]],
                ]);

                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Cột buổi bắt đầu từ cột 5 (sau STT/Họ tên/Email/Chuyên cần) — trước đây là 6 khi còn MSSV.
                for ($column = 5; $column <= $this->columnCount; $column++) {
                    $cell = Coordinate::stringFromColumnIndex($column).$row;
                    $this->styleStatusCell($sheet, $cell, (string) $sheet->getCell($cell)->getValue());
                }
            }

            $sheet->getStyle("A{$this->dataStartRow}:A{$this->dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            // Canh giữa từ cột Chuyên cần (D) tới hết các cột buổi.
            $sheet->getStyle("D{$this->dataStartRow}:{$lastCol}{$this->dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $sheet->getColumnDimension('A')->setWidth(6);   // STT
        $sheet->getColumnDimension('B')->setWidth(26);  // Họ và tên
        $sheet->getColumnDimension('C')->setWidth(30);  // Email
        $sheet->getColumnDimension('D')->setWidth(16);  // Chuyên cần

        for ($column = 5; $column <= $this->columnCount; $column++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($column))->setWidth(16);
        }

        $sheet->freezePane('E7');

        return [];
    }

    private function formulaDescription(array $rules): string
    {
        $late = rtrim(rtrim(number_format((float) ($rules['late'] ?? 0.5), 2), '0'), '.');
        $absent = rtrim(rtrim(number_format((float) ($rules['absent'] ?? 1.0), 2), '0'), '.');
        $excused = rtrim(rtrim(number_format((float) ($rules['excused'] ?? 0.0), 2), '0'), '.');

        return "Theo cài đặt lớp: có mặt -0, đi muộn -{$late}, vắng -{$absent}, có phép -{$excused}; mẫu số = max(tổng buổi dự kiến, số buổi đã học).";
    }

    private function styleStatusCell(Worksheet $sheet, string $cell, string $value): void
    {
        $colors = match ($value) {
            'Có mặt' => ['bg' => 'FFDCFCE7', 'fg' => 'FF047857'],
            'Đi muộn' => ['bg' => 'FFFEF3C7', 'fg' => 'FFD97706'],
            'Vắng' => ['bg' => 'FFFFE4E6', 'fg' => 'FFE11D48'],
            'Có phép' => ['bg' => 'FFDBEAFE', 'fg' => 'FF1D4ED8'],
            default => ['bg' => 'FFF1F5F9', 'fg' => 'FF64748B'],
        };

        $sheet->getStyle($cell)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => $colors['fg']]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $colors['bg']]],
        ]);
    }
}
