<?php

namespace App\Exports;

use App\Models\ClassMeeting;
use App\Services\MeetingConsolidationService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Xuất bảng TỔNG KẾT của một buổi điểm danh (gộp tất cả phiên theo từng sinh viên).
 */
class MeetingSummaryExport implements FromArray, ShouldAutoSize, WithStyles
{
    private ClassMeeting $meeting;

    private int $headerRowIndex = 6;

    private int $columnCount = 5;

    public function __construct(int $meetingId)
    {
        $this->meeting = ClassMeeting::with('courseClass')->findOrFail($meetingId);
    }

    public function array(): array
    {
        $service = app(MeetingConsolidationService::class);

        $sessions = $this->meeting->sessions()->orderBy('id')->get(['id']);
        $sessionCount = $sessions->count();

        $consolidated = $service->consolidateMeeting($this->meeting)->keyBy(fn ($row) => $row['member']->id);
        $summaries = $this->meeting->summaries()->with(['classMember' => fn ($q) => $q->withTrashed()])->get();

        $sessionStatusLabel = [
            'present' => 'Có mặt',
            'late' => 'Đi muộn',
            'absent' => 'Vắng',
            'excused' => 'Có phép',
            'pending' => '—',
            'invalid' => 'Không hợp lệ',
        ];

        // Cột động cho từng phiên (Lần 1..n).
        $sessionHeaders = [];
        for ($i = 1; $i <= $sessionCount; $i++) {
            $sessionHeaders[] = 'Lần '.$i;
        }

        $this->columnCount = 3 + $sessionCount + 2; // MSSV, Tên, [phiên...], Tổng kết, Điểm trừ, Ghi chú

        $rows = [
            ['TÊN LỚP:', $this->meeting->courseClass->name],
            ['BUỔI:', $this->meeting->name],
            ['NGÀY:', $this->meeting->date->format('d/m/Y')],
            ['GIỜ KẾT THÚC:', $this->meeting->end_time ? \Carbon\Carbon::parse($this->meeting->end_time)->format('H:i') : ''],
            [''],
            array_merge(['MSSV', 'Tên sinh viên'], $sessionHeaders, ['Tổng kết', 'Điểm trừ', 'Ghi chú']),
        ];

        // Sắp theo MSSV.
        $summaries = $summaries->filter(fn ($s) => $s->classMember !== null)
            ->sortBy(fn ($s) => $s->classMember->student_code)
            ->values();

        foreach ($summaries as $summary) {
            $member = $summary->classMember;
            $statuses = $consolidated->get($member->id)['statuses'] ?? [];

            $sessionCells = [];
            for ($i = 0; $i < $sessionCount; $i++) {
                $sessionCells[] = $sessionStatusLabel[$statuses[$i] ?? 'pending'] ?? '—';
            }

            $deduction = (float) $summary->deduction;

            $rows[] = array_merge(
                [$member->student_code, $member->full_name],
                $sessionCells,
                [
                    $service->statusLabel($summary->status),
                    $deduction > 0 ? '-'.rtrim(rtrim(number_format($deduction, 1), '0'), '.') : '0',
                    $summary->note ?? '',
                ],
            );
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($this->columnCount);

        // Khối thông tin (hàng 1-4).
        $sheet->getStyle('A1:B4')->getFont()->setBold(true);

        // Hàng tiêu đề bảng.
        $headerRange = 'A'.$this->headerRowIndex.':'.$lastCol.$this->headerRowIndex;
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']]],
        ]);
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($this->headerRowIndex)->setRowHeight(26);

        return [];
    }
}
