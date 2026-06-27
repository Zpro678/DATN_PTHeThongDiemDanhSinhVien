<?php

namespace App\Exports;

use App\Models\ClassSession;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClassSessionExport implements FromArray, ShouldAutoSize, WithStyles
{
    private ClassSession $session;

    public function __construct(int $sessionId)
    {
        $this->session = ClassSession::with('courseClass', 'attendanceRecords.classMember')
            ->findOrFail($sessionId);
    }

    public function array(): array
    {
        $className = $this->session->courseClass->name;
        $sessionName = $this->session->name;
        $date = $this->session->date->format('d/m/Y');
        
        $startTime = $this->session->start_time;
        $ca = '';
        if ($startTime) {
            $hour = (int) substr($startTime, 0, 2);
            if ($hour < 12) {
                $ca = 'Sáng';
            } elseif ($hour < 18) {
                $ca = 'Chiều';
            } else {
                $ca = 'Tối';
            }
        }
        
        // Add header rows
        $rows = [
            ['TÊN LỚP:', $className],
            ['BUỔI ĐIỂM DANH:', $sessionName],
            ['NGÀY:', $date],
            ['CA:', $ca],
            [''],
            ['MSSV', 'Tên sinh viên', 'Trạng thái', 'Lý do (Ghi chú)']
        ];

        // Load attendance records ordered by student code.
        // Nạp kèm classMember đã withTrashed để sinh viên đã bị xoá mềm vẫn hiển thị
        // trong báo cáo của buổi (tránh lỗi đọc thuộc tính trên null).
        $records = $this->session->attendanceRecords()
            ->with(['classMember' => fn ($query) => $query->withTrashed()])
            ->join('class_members', 'attendance_records.class_member_id', '=', 'class_members.id')
            ->orderBy('class_members.student_code')
            ->select('attendance_records.*')
            ->get();

        $statusMap = [
            'pending' => 'Chưa điểm danh',
            'present' => 'Có mặt',
            'late' => 'Đi muộn',
            'absent' => 'Vắng mặt',
            'excused' => 'Có phép',
        ];

        foreach ($records as $record) {
            $member = $record->classMember;

            // Bỏ qua bản ghi mồ côi (sinh viên đã bị xoá hẳn) để không làm hỏng file.
            if (! $member) {
                continue;
            }

            $rows[] = [
                $member->student_code,
                $member->full_name,
                $statusMap[$record->status] ?? 'Chưa điểm danh',
                $record->note ?? ''
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        // Styling for the information block (Rows 1-4)
        $sheet->getStyle('A1:D4')->applyFromArray([
            'font' => [
                'size' => 12,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF8FAFC'] // Very light gray/blue
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FFE2E8F0']
                ]
            ],
        ]);

        // Bold for the labels
        $sheet->getStyle('A1:A4')->getFont()->setBold(true);
        $sheet->getStyle('C4')->getFont()->setBold(true);

        // Bold for the values
        $sheet->getStyle('B1:B4')->getFont()->setBold(true);
        $sheet->getStyle('D4')->getFont()->setBold(true);

        // Styling for the table header (Row 6)
        $sheet->getStyle('A6:D6')->applyFromArray([
            'font' => [
                'bold' => true, 
                'color' => ['argb' => 'FFFFFFFF'],
                'size' => 12
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF0d6efd']
            ]
        ]);
        
        // Increase row height for better padding
        for ($i = 1; $i <= 4; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(24);
        }
        $sheet->getRowDimension(6)->setRowHeight(26);

        // Vertical center align everything
        $sheet->getStyle('A1:D6')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        return [];
    }
}
