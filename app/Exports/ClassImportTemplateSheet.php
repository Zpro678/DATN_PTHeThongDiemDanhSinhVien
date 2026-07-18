<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClassImportTemplateSheet implements FromArray, WithTitle, WithStyles
{
    protected string $title;
    protected array $sampleData;

    public function __construct(string $title, array $sampleData)
    {
        $this->title = $title;
        $this->sampleData = $sampleData;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function array(): array
    {
        return [
            ['Tên lớp', $this->title],
            ['Mã lớp', 'WEB-2026-N01'],
            ['Mô tả', 'Lớp học phần mẫu.'],
            ['Tổng số buổi học dự kiến', 15],
            ['Ngưỡng vắng cho phép (%)', 20],
            ['Cấu hình điểm trừ', 'Đi muộn', 'Vắng không phép', 'Vắng có phép'],
            ['Điểm trừ tương ứng', 0.5, 1.0, 0.0],
            ['Yêu cầu duyệt tham gia (1=Có, 0=Không)', 0],
            [],
            ['Họ và tên', 'Email', '22/06', '29/06'],
            $this->sampleData[0],
            $this->sampleData[1],
            $this->sampleData[2],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $borderThin = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FFCCCCCC'], // Light Gray border
                ],
            ],
        ];

        // 1. Cấu hình cột chiều rộng
        $sheet->getColumnDimension('A')->setWidth(35);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);

        // 2. Định dạng Phần Cấu hình Lớp Học (Hàng 1 - Hàng 8)
        // Set Bold cho Cột nhãn
        $sheet->getStyle('A1:A8')->getFont()->setBold(true);
        $sheet->getStyle('A6:D6')->getFont()->setBold(true);

        // Khung viền cho phần cấu hình lớp
        $sheet->getStyle('A1:B5')->applyFromArray($borderThin);
        $sheet->getStyle('A6:D7')->applyFromArray($borderThin);
        $sheet->getStyle('A8:B8')->applyFromArray($borderThin);

        // Màu nền nhãn cấu hình (Cột A và Hàng 6)
        $configHeaderStyle = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF0F4F8'], // Light gray-blue background
            ],
        ];
        $sheet->getStyle('A1:A5')->applyFromArray($configHeaderStyle);
        $sheet->getStyle('A6:D6')->applyFromArray($configHeaderStyle);
        $sheet->getStyle('A8')->applyFromArray($configHeaderStyle);

        // 3. Định dạng Bảng Học viên & Điểm danh (Từ hàng 10 trở đi)
        // Tiêu đề bảng
        $sheet->getStyle('A10:D10')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1A56DB'], // Primary Blue Background
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF93C5FD'],
                ],
            ],
        ]);
        $sheet->getRowDimension(10)->setRowHeight(25);

        // Khung viền và căn chỉnh dữ liệu học viên
        $sheet->getStyle('A11:D13')->applyFromArray($borderThin);

        // Căn giữa cột email và trạng thái điểm danh
        $sheet->getStyle('B11:D13')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        return [];
    }
}
