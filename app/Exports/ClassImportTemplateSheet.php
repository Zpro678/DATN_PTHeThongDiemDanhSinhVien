<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ClassImportTemplateSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
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

    public function columnWidths(): array
    {
        return [
            'A' => 38,
            'B' => 22,
            'C' => 18,
            'D' => 18,
        ];
    }

    public function array(): array
    {
        return [
            // Hàng 1 – Tiêu đề sheet (section header)
            ['⚙ CẤU HÌNH LỚP HỌC', '', '', ''],

            // Hàng 2–6: Thông tin cơ bản
            ['Tên lớp *', $this->title, '', ''],
            ['Mã lớp *', 'WEB-2026-N01', '', ''],
            ['Mô tả', 'Lớp học phần web cơ bản.', '', ''],
            ['Tổng số buổi học dự kiến *', 15, '', ''],
            ['Ngưỡng vắng cho phép (%)', 20, '', ''],

            // Hàng 7: Separator – Cấu hình điểm trừ
            ['Cấu hình điểm trừ (bỏ trống = không trừ)', 'Đi muộn', 'Vắng không phép', 'Vắng có phép'],

            // Hàng 8: Điểm trừ
            ['Điểm trừ tương ứng', 0.5, 1, 0],

            // Hàng 9: Yêu cầu duyệt
            ['Yêu cầu duyệt tham gia (1 = Có, 0 = Không)', 0, '', ''],

            // Hàng 10 – Ghi chú hướng dẫn
            ['★ Các trường có dấu (*) là bắt buộc. Mã lớp phải là duy nhất.', '', '', ''],

            // Hàng 11 – Dòng trống phân cách
            ['', '', '', ''],

            // Hàng 12 – Tiêu đề section học viên
            ['👥 DANH SÁCH HỌC VIÊN & ĐIỂM DANH', '', '', ''],

            // Hàng 13 – Tiêu đề cột
            ['Họ và tên *', 'Email *', '22/06', '29/06'],

            // Hàng 14–16: Dữ liệu mẫu
            $this->sampleData[0],
            $this->sampleData[1],
            $this->sampleData[2],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $font = 'Calibri';

        // ─── Màu sắc chủ đề ─────────────────────────────────────
        $colorPrimaryDark   = 'FF1E3A5F'; // Tiêu đề section – xanh navy
        $colorPrimary       = 'FF1A56DB'; // Header bảng học viên
        $colorConfigBg      = 'FFEEF4FF'; // Nền phần cấu hình
        $colorConfigLabel   = 'FF2D3748'; // Nhãn cột A (cấu hình)
        $colorPenaltyBg     = 'FFFFF3CD'; // Nền hàng điểm trừ – vàng nhạt
        $colorApprovalBg    = 'FFECFDF5'; // Nền yêu cầu duyệt – xanh lá nhạt
        $colorNoteBg        = 'FFFFF8E1'; // Nền ghi chú – vàng mật ong nhạt
        $colorNoteText      = 'FF92400E'; // Chữ ghi chú – nâu
        $colorBorder        = 'FFCBD5E1'; // Viền nhạt
        $colorBorderDark    = 'FF94A3B8'; // Viền đậm
        $colorStudentAlt    = 'FFF8FAFC'; // Nền hàng học viên xen kẽ
        $colorSectionSep    = 'FFE2E8F0'; // Separator nền

        // ─── Áp dụng font chung ─────────────────────────────────
        $sheet->getStyle('A1:D17')->getFont()->setName($font)->setSize(10);

        // ════════════════════════════════════════════════════════
        // HÀNG 1: Tiêu đề "CẤU HÌNH LỚP HỌC"
        // ════════════════════════════════════════════════════════
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF'], 'name' => $font],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $colorPrimaryDark]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF3B82F6']]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // ════════════════════════════════════════════════════════
        // HÀNG 2–6: Thông tin cơ bản lớp
        // ════════════════════════════════════════════════════════
        foreach (['B2:D2', 'B3:D3', 'B4:D4', 'B5:D5', 'B6:D6'] as $range) {
            $sheet->mergeCells($range);
        }

        // Nền toàn phần cấu hình cơ bản
        $sheet->getStyle('A2:D6')->applyFromArray([
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $colorConfigBg]],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => $colorBorder]]],
        ]);

        // Nhãn cột A đậm
        $sheet->getStyle('A2:A6')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => $colorConfigLabel]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Giá trị cột B–D căn giữa
        $sheet->getStyle('B2:D6')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Tên lớp – nổi bật
        $sheet->getStyle('B2')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => $colorPrimary]],
        ]);

        foreach (range(2, 6) as $row) {
            $sheet->getRowDimension($row)->setRowHeight(22);
        }

        // ════════════════════════════════════════════════════════
        // HÀNG 7: Cấu hình điểm trừ – header phụ
        // ════════════════════════════════════════════════════════
        $sheet->getStyle('A7:D7')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FF92400E'], 'name' => $font],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF3C7']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFFCD34D']]],
        ]);
        $sheet->getStyle('A7')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);
        $sheet->getRowDimension(7)->setRowHeight(26);

        // ════════════════════════════════════════════════════════
        // HÀNG 8: Điểm trừ tương ứng
        // ════════════════════════════════════════════════════════
        $sheet->getStyle('A8:D8')->applyFromArray([
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $colorPenaltyBg]],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFFCD34D']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('A8')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => $colorConfigLabel]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);
        $sheet->getStyle('B8:D8')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFB45309']],
        ]);
        $sheet->getRowDimension(8)->setRowHeight(22);

        // ════════════════════════════════════════════════════════
        // HÀNG 9: Yêu cầu duyệt
        // ════════════════════════════════════════════════════════
        $sheet->mergeCells('B9:D9');
        $sheet->getStyle('A9:D9')->applyFromArray([
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $colorApprovalBg]],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF6EE7B7']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('A9')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => $colorConfigLabel]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);
        $sheet->getRowDimension(9)->setRowHeight(22);

        // ════════════════════════════════════════════════════════
        // HÀNG 10: Ghi chú (*bắt buộc)
        // ════════════════════════════════════════════════════════
        $sheet->mergeCells('A10:D10');
        $sheet->getStyle('A10')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 9, 'color' => ['argb' => $colorNoteText]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $colorNoteBg]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => [
                'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFFBBF24']],
                'top'    => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFFBBF24']],
            ],
        ]);
        $sheet->getRowDimension(10)->setRowHeight(18);

        // ════════════════════════════════════════════════════════
        // HÀNG 11: Dòng trống phân cách
        // ════════════════════════════════════════════════════════
        $sheet->getRowDimension(11)->setRowHeight(12);
        $sheet->getStyle('A11:D11')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $colorSectionSep]],
        ]);

        // ════════════════════════════════════════════════════════
        // HÀNG 12: Tiêu đề "DANH SÁCH HỌC VIÊN"
        // ════════════════════════════════════════════════════════
        $sheet->mergeCells('A12:D12');
        $sheet->getStyle('A12')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF'], 'name' => $font],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $colorPrimaryDark]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF3B82F6']]],
        ]);
        $sheet->getRowDimension(12)->setRowHeight(28);

        // ════════════════════════════════════════════════════════
        // HÀNG 13: Header cột học viên
        // ════════════════════════════════════════════════════════
        $sheet->getStyle('A13:D13')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'name' => $font, 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $colorPrimary]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF93C5FD']]],
        ]);
        $sheet->getRowDimension(13)->setRowHeight(26);

        // ════════════════════════════════════════════════════════
        // HÀNG 14–16: Dữ liệu học viên mẫu
        // ════════════════════════════════════════════════════════
        foreach ([14, 15, 16] as $i => $row) {
            $bgColor = ($i % 2 === 0) ? 'FFFFFFFF' : $colorStudentAlt;
            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgColor]],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => $colorBorder]]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getStyle("A{$row}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ]);
            $sheet->getStyle("B{$row}:D{$row}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(20);
        }

        // ─── Căn giữa dọc toàn sheet ─────────────────────────
        $sheet->getStyle('A1:D16')->getAlignment()->setWrapText(true);

        return [];
    }
}
