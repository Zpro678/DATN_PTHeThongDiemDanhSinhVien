<?php

namespace App\Exports;

use App\Models\ClassMember;
use App\Models\CourseClass;
use App\Models\ClassSession;
use App\Models\AttendanceRecord;
use App\Services\AttendanceCalculator;
use App\Services\LectureManageStudentService;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class StudentsSheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    private int $ownerUserId;
    private string $classFilter;
    private string $statusFilter;
    private string $search;
    private string $formula;

    // Lưu lại để dùng trong styles()
    private int $dataStartRow = 7;
    private int $dataEndRow   = 7;
    private int $sessionColStart = 4; // cột D (index 4, column D = 4th col)
    private int $sessionCount = 0;

    public function __construct(int $ownerUserId, string $classFilter, string $statusFilter, string $search, string $formula)
    {
        $this->ownerUserId  = $ownerUserId;
        $this->classFilter  = $classFilter;
        $this->statusFilter = $statusFilter;
        $this->search       = $search;
        $this->formula      = $formula;
    }

    public function title(): string
    {
        if ($this->classFilter !== 'all') {
            $courseClass = CourseClass::find($this->classFilter);
            if ($courseClass) {
                return substr(preg_replace('/[\/\\\?\*\[\]:]/', '', $courseClass->code), 0, 31) ?: 'Class';
            }
        }
        return 'Tất cả lớp học';
    }

    public function array(): array
    {
        $rows = [];

        // ── Thông tin lớp ──
        $className = 'Tất cả lớp học';
        if ($this->classFilter !== 'all') {
            $courseClass = CourseClass::find($this->classFilter);
            if ($courseClass) {
                $className = $courseClass->code . ' - ' . $courseClass->name;
            }
        }

        // ── Truy vấn học viên ──
        $members = ClassMember::query()
            ->with(['courseClass:id,name,code', 'user:id,email'])
            ->whereHas('courseClass', fn (Builder $q) => $q->where('owner_user_id', $this->ownerUserId))
            ->when(
                $this->statusFilter === 'archived',
                fn (Builder $q) => $q->onlyTrashed(),
                fn (Builder $q) => $q->where('status', 'active'),
            )
            ->when($this->classFilter !== 'all', fn (Builder $q) => $q->where('class_id', $this->classFilter))
            ->when($this->search !== '', function (Builder $q) {
                $q->where(function (Builder $q) {
                    $q->where('full_name', 'like', '%' . $this->search . '%')
                      ->orWhere('student_code', 'like', '%' . $this->search . '%')
                      ->orWhereHas('user', fn (Builder $q) => $q->where('email', 'like', '%' . $this->search . '%'));
                });
            })
            ->orderBy('full_name')
            ->get();

        // ── Truy vấn buổi học & điểm danh ──
        $classIds = $members->pluck('class_id')->unique();
        $sessions = ClassSession::whereIn('class_id', $classIds)
            ->where('status', 'closed')
            ->orderBy('date')
            ->get();

        $this->sessionCount = $sessions->count();

        $attendanceRecords = AttendanceRecord::whereIn('class_member_id', $members->pluck('id'))
            ->get()
            ->groupBy('class_member_id');

        $headers = [
            'STT', 
            'MSSV', 
            'Họ và tên', 
            'Email',
            'Lớp học'
        ];

        foreach ($sessions as $session) {
            $headers[] = $session->date->format('d/m');
        }

        $formulaName = 'Kết quả công thức (%)';

        $headers = array_merge($headers, [
            'Tổng số tiết đã học',
            'Có mặt', 
            'Đi muộn', 
            'Vắng không phép', 
            'Vắng có phép',
            'Muộn quy đổi (tiết)',
            'Chuyên cần (% cài đặt lớp)',
            $formulaName
        ]);

        $rows[] = ['BÁO CÁO CHUYÊN CẦN SINH VIÊN'];
        $rows[] = ['Lớp:', $className];
        $rows[] = ['Trạng thái:', $this->statusFilter === 'active' ? 'Đang học' : 'Lưu trữ'];
        $rows[] = ['Ngày xuất:', now()->format('d/m/Y H:i')];
        $rows[] = ['']; // row 5: trống

        // ── Row 6: Cột header ──
        $headerRow = ['Mã SV', 'Họ và Tên', 'Email'];
        foreach ($sessions as $session) {
            $headerRow[] = $session->date->format('d/m');
        }
        $headerRow[] = 'Số buổi có mặt';
        $headerRow[] = 'Số buổi muộn';
        $headerRow[] = 'Số buổi vắng phép';
        $headerRow[] = 'Số buổi vắng KP';
        $headerRow[] = 'Tỉ lệ tham dự';
        $headerRow[] = 'Chuyên Cần (%)';
        $rows[] = $headerRow;

        // ── Data rows bắt đầu từ row 7 ──
        $this->dataStartRow = 7;
        $currentRow = $this->dataStartRow;

        foreach ($members as $member) {
            $stats = $attendanceStats->get($member->id, [
                'studied_lessons' => 0,
                'present_lessons' => 0,
                'late_lessons' => 0,
                'absent_lessons' => 0,
                'excused_lessons' => 0,
            ]);

            // Tính chuyên cần theo cài đặt lớp (lấy từ stats, do LectureManageStudentService đã join classes)
            $latesPerAbsent     = (int) ($stats['lates_per_absent'] ?? 0);
            $deductExcused      = (bool) ($stats['deduct_excused_absence'] ?? false);
            $lateCount          = (int) ($stats['late_count'] ?? 0);
            $presentLessons     = (int) ($stats['present_lessons'] ?? 0);
            $lateLessons        = (int) ($stats['late_lessons'] ?? 0);
            $absentLessons      = (int) ($stats['absent_lessons'] ?? 0);
            $excusedLessons     = (int) ($stats['excused_lessons'] ?? 0);
            $studied            = (int) ($stats['studied_lessons'] ?? 0);

            $lateConvertedLessons = AttendanceCalculator::lateAbsentLessons($lateCount, $latesPerAbsent);
            $classPercent = AttendanceCalculator::percent(
                $presentLessons,
                $lateLessons,
                $excusedLessons,
                $studied,
                $lateCount,
                $latesPerAbsent,
                $deductExcused,
            );

            // Tính phần trăm theo công thức tự nhập
            $percent = 0;
            if ($studied > 0) {
                // Ensure the formula only contains safe characters and allowed math functions
                $formulaStr = strtolower($this->formula);
                
                // Chỉ giữ lại các biến và hàm toán học hợp lệ
                $formulaStr = preg_replace_callback('/[a-z]+/', function($matches) {
                    $word = $matches[0];
                    $allowed = ['c', 'm', 'v', 'p', 't', 'floor', 'ceil', 'round', 'max', 'min', 'abs'];
                    return in_array($word, $allowed) ? $word : '';
                }, $formulaStr);

                // Loại bỏ các ký tự đặc biệt nguy hiểm (chỉ cho phép a-z, số, toán tử, khoảng trắng, dấu phẩy)
                $formulaStr = preg_replace('/[^a-z0-9\+\-\*\/\(\)\.\s,]/', '', $formulaStr);
                
                // Map variables to their values using word boundaries
                $formulaStr = preg_replace('/\bc\b/', $presentLessons, $formulaStr);
                $formulaStr = preg_replace('/\bm\b/', $lateLessons, $formulaStr);
                $formulaStr = preg_replace('/\bv\b/', $absentLessons, $formulaStr);
                $formulaStr = preg_replace('/\bp\b/', $excusedLessons, $formulaStr);
                $formulaStr = preg_replace('/\bt\b/', $studied, $formulaStr);

                if (!empty($formulaStr)) {
                    try {
                        $result = @eval("return $formulaStr;");
                        if (is_numeric($result)) {
                            $percent = round((float) $result, 2);
                        }
                    } catch (\Throwable $e) {
                        $percent = 0;
                    }
                }
            }

            $row = [
                $member->student_code,
                $member->full_name,
                $member->user?->email ?? '',
            ];

            $memberRecords = $attendanceRecords->get($member->id, collect())->keyBy('class_session_id');

            foreach ($sessions as $session) {
                if ($session->class_id === $member->class_id) {
                    $record = $memberRecords->get($session->id);
                    if ($record) {
                        $statusMap = [
                            'present' => 'c',
                            'late'    => 'm',
                            'absent'  => 'v',  // vắng không phép — khớp biến công thức
                            'excused' => 'p',  // có phép — khớp biến công thức
                            'pending' => '-',
                        ];
                        $row[] = $statusMap[$record->status] ?? '-';
                    } else {
                        $row[] = '-';
                    }
                } else {
                    $row[] = '';
                }
            }

            $row = array_merge($row, [
                $stats['studied_lessons'],
                $stats['present_lessons'],
                $stats['late_lessons'],
                $stats['absent_lessons'],
                $stats['excused_lessons'],
                $lateConvertedLessons > 0 ? $lateConvertedLessons : '-',
                $classPercent . '%',
                $percent . '%',
            ]);

            $rows[] = $row;
            $currentRow++;
        }

        $this->dataEndRow = $currentRow - 1;

        // ── Dòng trắng ──
        $rows[] = [''];

        // ── Dòng tổng kết lớp (giống file mẫu) ──
        if ($members->count() > 0) {
            $cColIdx = 3 + $this->sessionCount + 1;
            $mColIdx = $cColIdx + 1;
            $vColIdx = $mColIdx + 1;
            $kColIdx = $vColIdx + 1;
            $ratioColIdx = $kColIdx + 1;
            $ccColIdx    = $ratioColIdx + 1;

            $cCol      = Coordinate::stringFromColumnIndex($cColIdx);
            $mCol      = Coordinate::stringFromColumnIndex($mColIdx);
            $vCol      = Coordinate::stringFromColumnIndex($vColIdx);
            $kCol      = Coordinate::stringFromColumnIndex($kColIdx);
            $ratioCol  = Coordinate::stringFromColumnIndex($ratioColIdx);
            $ccCol     = Coordinate::stringFromColumnIndex($ccColIdx);

            $start = $this->dataStartRow;
            $end   = $this->dataEndRow;

            $rows[] = [
                'TỔNG KẾT LỚP', '', '',
                'TB có mặt',  "=AVERAGE({$cCol}{$start}:{$cCol}{$end})",
                'TB muộn',    "=AVERAGE({$mCol}{$start}:{$mCol}{$end})",
                'TB vắng có phép', "=AVERAGE({$vCol}{$start}:{$vCol}{$end})",
                'TB vắng KP', "=AVERAGE({$kCol}{$start}:{$kCol}{$end})",
                'TB chuyên cần', "=AVERAGE({$ccCol}{$start}:{$ccCol}{$end})",
            ];
        }

        return $rows;
    }

    /**
     * Tính chuyên cần theo công thức tùy chọn (trả về số %)
     */
    private function calcFormulaForRow(array $stats, int $memberId): string
    {
        $presentLessons = (int) ($stats['present_lessons'] ?? 0);
        $lateLessons    = (int) ($stats['late_lessons'] ?? 0);
        $absentLessons  = (int) ($stats['absent_lessons'] ?? 0);
        $excusedLessons = (int) ($stats['excused_lessons'] ?? 0);
        $studied        = (int) ($stats['studied_lessons'] ?? 0);

        if ($studied <= 0) return '0%';

        $latesPerAbsent = (int) ($stats['lates_per_absent'] ?? 0);
        $deductExcused  = (bool) ($stats['deduct_excused_absence'] ?? false);
        $lateCount      = (int) ($stats['late_count'] ?? 0);

        $percent = AttendanceCalculator::percent(
            $presentLessons, $lateLessons, $excusedLessons,
            $studied, $lateCount, $latesPerAbsent, $deductExcused,
        );

        // Tính theo công thức tùy chọn nếu có
        $formulaStr = strtolower($this->formula);
        $formulaStr = preg_replace_callback('/[a-z]+/', function ($m) {
            $allowed = ['c', 'm', 'v', 'p', 't', 'floor', 'ceil', 'round', 'max', 'min', 'abs'];
            return in_array($m[0], $allowed) ? $m[0] : '';
        }, $formulaStr);
        $formulaStr = preg_replace('/[^a-z0-9\+\-\*\/\(\)\.\s,]/', '', $formulaStr);
        $formulaStr = preg_replace('/\bc\b/', $presentLessons, $formulaStr);
        $formulaStr = preg_replace('/\bm\b/', $lateLessons, $formulaStr);
        $formulaStr = preg_replace('/\bv\b/', $absentLessons, $formulaStr);
        $formulaStr = preg_replace('/\bp\b/', $excusedLessons, $formulaStr);
        $formulaStr = preg_replace('/\bt\b/', $studied, $formulaStr);

        if (!empty($formulaStr)) {
            try {
                $result = @eval("return $formulaStr;");
                if (is_numeric($result)) {
                    $percent = round((float) $result, 2);
                }
            } catch (\Throwable $e) {
                // giữ nguyên $percent
            }
        }

        return $percent . '%';
    }

    public function styles(Worksheet $sheet): array
    {
        $lastCol    = $sheet->getHighestColumn();
        $lastColIdx = Coordinate::columnIndexFromString($lastCol);

        // ── Row 1: Tiêu đề chính ──
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF1a56db']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFeff6ff']],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(32);

        // ── Row 2-4: Thông tin lớp ──
        $sheet->getStyle('A2:A4')->getFont()->setBold(true);
        $sheet->getStyle('B2:B4')->getFont()->setItalic(true);

        // ── Row 6: Header cột ──
        $sheet->getStyle("A6:{$lastCol}6")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1a56db']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF93c5fd']]],
        ]);
        $sheet->getRowDimension(6)->setRowHeight(30);

        // ── Data rows: border + xen kẽ màu ──
        if ($this->dataEndRow >= $this->dataStartRow) {
            for ($r = $this->dataStartRow; $r <= $this->dataEndRow; $r++) {
                $bg = ($r % 2 === 0) ? 'FFf0f9ff' : 'FFFFFFFF';
                $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bg]],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFbfdbfe']]],
                ]);
            }

            // Cột session: căn giữa
            if ($this->sessionCount > 0) {
                $sessionStartCol = Coordinate::stringFromColumnIndex(4);
                $sessionEndCol   = Coordinate::stringFromColumnIndex(3 + $this->sessionCount);
                $sheet->getStyle("{$sessionStartCol}6:{$sessionEndCol}{$this->dataEndRow}")
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            // Cột tổng kết (sau sessions): nền xanh nhạt + bold
            $summaryStartIdx = 3 + $this->sessionCount + 1;
            $summaryStartCol = Coordinate::stringFromColumnIndex($summaryStartIdx);
            $sheet->getStyle("{$summaryStartCol}6:{$lastCol}{$this->dataEndRow}")->applyFromArray([
                'font'      => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            // Màu tô đặc biệt cho các cột tổng
            $sheet->getStyle("{$summaryStartCol}6:{$lastCol}6")->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setARGB('FF1e40af');
        }

        // ── Dòng tổng kết lớp (row sau dataEndRow + 1 trắng) ──
        $summaryRow = $this->dataEndRow + 2;
        $sheet->getStyle("A{$summaryRow}:{$lastCol}{$summaryRow}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FF1a56db'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFdbeafe']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF1a56db']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($summaryRow)->setRowHeight(24);

        // ── Cột A-C: căn trái, cố định độ rộng ──
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(24);
        $sheet->getColumnDimension('C')->setWidth(28);

        // ── Cột session: hẹp lại ──
        for ($i = 4; $i <= 3 + $this->sessionCount; $i++) {
            $col = Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($col)->setWidth(8);
        }

        // ── Cột tổng: vừa ──
        for ($i = 3 + $this->sessionCount + 1; $i <= $lastColIdx; $i++) {
            $col = Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($col)->setWidth(16);
        }

        // ── Freeze panes (đóng băng dòng header + 3 cột đầu) ──
        $sheet->freezePane('D7');

        return [];
    }
}
