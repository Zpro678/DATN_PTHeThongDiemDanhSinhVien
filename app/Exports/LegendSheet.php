<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class LegendSheet implements FromArray, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Chú thích';
    }

    public function array(): array
    {
        return [
            ['Ký hiệu', 'Ý nghĩa', 'Ảnh hưởng chuyên cần'],
            ['c', 'Có mặt',           'Tính đủ 1 buổi'],
            ['m', 'Đi muộn',          'Tính đủ 1 buổi (không trừ hoặc trừ theo CĐ)'],
            ['vg', 'Vắng giữa giờ',   'Trừ chuyên cần theo cài đặt'],
            ['vs', 'Về sớm',          'Trừ chuyên cần theo cài đặt'],
            ['v', 'Vắng không phép',  'Trừ 1 buổi chuyên cần'],
            ['p', 'Vắng có phép',     'Tính đủ 1 buổi (không trừ hoặc trừ theo CĐ)'],
            ['-', 'Chưa điểm danh',   'Không tính'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Header row
        $sheet->getStyle('A1:C1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1a56db']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF93c5fd']]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(26);

        // Data rows
        $colors = [
            2 => ['bg' => 'FFd1fae5', 'fg' => 'FF065f46'], // c - xanh lá
            3 => ['bg' => 'FFfef3c7', 'fg' => 'FF92400e'], // m - vàng
            4 => ['bg' => 'FFffedd5', 'fg' => 'FF9a3412'], // vg - cam
            5 => ['bg' => 'FFfce7f3', 'fg' => 'FF9d174d'], // vs - hồng
            6 => ['bg' => 'FFfee2e2', 'fg' => 'FF991b1b'], // v - đỏ
            7 => ['bg' => 'FFdbeafe', 'fg' => 'FF1e3a8a'], // p - xanh dương
            8 => ['bg' => 'FFf1f5f9', 'fg' => 'FF475569'], // - - xám
        ];

        foreach ($colors as $row => $color) {
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => $color['fg']]],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $color['bg']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getStyle("B{$row}:C{$row}")->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $color['bg']]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFe2e8f0']]],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(22);
        }

        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(22);
        $sheet->getColumnDimension('C')->setWidth(36);

        return [];
    }
}
