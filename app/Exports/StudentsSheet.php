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
                return substr(preg_replace('/[\/\\\?\*\[\]:]/', '', $courseClass->join_key), 0, 31) ?: 'Class';
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
                $className = $courseClass->join_key . ' - ' . $courseClass->name;
            }
        }

        // ── Truy vấn học viên ──
        $members = ClassMember::query()
            ->with(['courseClass:id,name,join_key', 'user:id,name,email', 'profile'])
            ->leftJoin('class_member_profiles', 'class_member_profiles.class_member_id', '=', 'class_members.id')
            ->select('class_members.*')
            ->whereHas('courseClass', fn (Builder $q) => $q->managedBy($this->ownerUserId))
            ->when(
                $this->statusFilter === 'archived',
                fn (Builder $q) => $q->onlyTrashed(),
                fn (Builder $q) => $q->where('class_members.status', \App\Models\ClassMember::STATUS_ACTIVE),
            )
            ->when($this->classFilter !== 'all', fn (Builder $q) => $q->where('class_members.class_id', $this->classFilter))
            ->when($this->search !== '', function (Builder $q) {
                $q->where(function (Builder $q) {
                    $q->where('class_member_profiles.full_name', 'like', '%' . $this->search . '%')
                      ->orWhere('class_member_profiles.student_code', 'like', '%' . $this->search . '%')
                      ->orWhereHas('user', fn (Builder $q) => $q->where('email', 'like', '%' . $this->search . '%'));
                });
            })
            ->orderBy('class_member_profiles.full_name')
            ->get();

        // ── Truy vấn buổi học & tổng kết ──
        $classIds = $members->pluck('class_id')->unique();
        $meetings = \App\Models\ClassMeeting::whereIn('class_id', $classIds)
            ->where('status', 'closed')
            ->orderBy('date')
            ->get();

        $this->sessionCount = $meetings->count();

        $meetingSummaries = \App\Models\MeetingSummary::whereIn('class_member_id', $members->pluck('id'))
            ->get()
            ->groupBy('class_member_id');

        $headers = [
            'STT', 
            'Họ và tên', 
            'Email',
            'Lớp học'
        ];

        foreach ($meetings as $meeting) {
            $headers[] = $meeting->date->format('d/m');
        }

        $formulaName = 'Kết quả công thức (%)';

        $headers = array_merge($headers, [
            'Tổng số buổi',
            'Có mặt', 
            'Đi muộn',
            'Vắng', 
            'Có phép',
            'Tổng điểm trừ',
            'Chuyên cần (% cài đặt lớp)',
            $formulaName
        ]);

        $rows[] = ['BÁO CÁO CHUYÊN CẦN SINH VIÊN'];
        $rows[] = ['Lớp:', $className];
        $rows[] = ['Trạng thái:', $this->statusFilter === 'active' ? 'Đang học' : 'Lưu trữ'];
        $rows[] = ['Ngày xuất:', now()->format('d/m/Y H:i')];
        $rows[] = ['']; // row 5: trống

        $rows[] = $headers;

        // ── Data rows bắt đầu từ row 7 ──
        $this->dataStartRow = 7;
        $currentRow = $this->dataStartRow;

        foreach ($members as $member) {
            $memberSummaries = $meetingSummaries->get($member->id, collect())->keyBy('meeting_id');
            $counts = ['present' => 0, 'late' => 0, 'excused' => 0, 'absent' => 0, 'total' => 0, 'deduction' => 0.0];
            
            // Loop through all $meetings to build counts. Only closed meetings of this class are considered.
            foreach ($meetings as $meeting) {
                if ($meeting->class_id === $member->class_id) {
                    $counts['total']++;
                    if ($summary = $memberSummaries->get($meeting->id)) {
                        $counts[$summary->status] = ($counts[$summary->status] ?? 0) + 1;
                        $counts['deduction'] += (float)$summary->deduction;
                    } else {
                        // Trạng thái pending hoặc chưa tổng kết coi như vắng (nếu buổi đã đóng)
                        $counts['absent']++;
                        $counts['deduction'] += \App\Services\AttendanceCalculator::deductionForStatus('absent');
                    }
                }
            }

            $rules = $member->courseClass->getAttendanceRules();
            $plannedSessions = $counts['total'];
            $classPercent = \App\Services\AttendanceCalculator::percentOfPlanned($plannedSessions, $counts, $rules);

            // Tính phần trăm theo công thức tự nhập
            $percent = 0;
            if ($plannedSessions > 0) {
                $formulaStr = strtolower($this->formula);
                
                $formulaStr = preg_replace_callback('/[a-z]+/', function($matches) {
                    $word = $matches[0];
                    $allowed = ['c', 'm', 'v', 'p', 't', 'floor', 'ceil', 'round', 'max', 'min', 'abs'];
                    return in_array($word, $allowed) ? $word : '';
                }, $formulaStr);

                $formulaStr = preg_replace('/[^a-z0-9\+\-\*\/\(\)\.\s,]/', '', $formulaStr);
                
                $formulaStr = preg_replace('/\bc\b/', $counts['present'], $formulaStr);
                $formulaStr = preg_replace('/\bm\b/', $counts['late'], $formulaStr);
                $formulaStr = preg_replace('/\bv\b/', $counts['absent'], $formulaStr);
                $formulaStr = preg_replace('/\bp\b/', $counts['excused'], $formulaStr);
                $formulaStr = preg_replace('/\bt\b/', $counts['total'], $formulaStr);

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
                $currentRow - 6, // STT
                $member->full_name,
                $member->email ?? $member->user?->email ?? '',
                $member->courseClass?->join_key ?? '',
            ];

            foreach ($meetings as $meeting) {
                if ($meeting->class_id === $member->class_id) {
                    $summary = $memberSummaries->get($meeting->id);
                    if ($summary) {
                        $statusMap = [
                            'present'     => 'c',
                            'late'        => 'm',
                            'absent'      => 'v',
                            'excused'     => 'p',
                        ];
                        $row[] = $statusMap[$summary->status] ?? '-';
                    } else {
                        $row[] = '-'; // Không có summary (chưa điểm danh)
                    }
                } else {
                    $row[] = '';
                }
            }

            $row = array_merge($row, [
                $counts['total'],
                $counts['present'],
                $counts['late'],
                $counts['absent'],
                $counts['excused'],
                $counts['deduction'],
                $classPercent . '%',
                $percent . '%',
            ]);

            $rows[] = $row;
            $currentRow++;
        }

        $this->dataEndRow = $currentRow - 1;

        // ── Dòng trắng ──
        $rows[] = [''];

        // ── Dòng tổng kết lớp ──
        if ($members->count() > 0) {
            $start = $this->dataStartRow;
            $end   = $this->dataEndRow;
            
            $colIdx = 4 + $this->sessionCount + 2; // 4 columns before sessions, +1 is "Tổng số buổi", +2 is "Có mặt"
            
            $cCol      = Coordinate::stringFromColumnIndex($colIdx);
            $mCol      = Coordinate::stringFromColumnIndex($colIdx + 1);
            $vCol      = Coordinate::stringFromColumnIndex($colIdx + 2);
            $pCol      = Coordinate::stringFromColumnIndex($colIdx + 3);
            $deductCol = Coordinate::stringFromColumnIndex($colIdx + 4);
            $ccCol     = Coordinate::stringFromColumnIndex($colIdx + 5);

            $rows[] = [
                'TỔNG KẾT LỚP', '', '', '',
                'TB có mặt',  "=AVERAGE({$cCol}{$start}:{$cCol}{$end})",
                'TB muộn',    "=AVERAGE({$mCol}{$start}:{$mCol}{$end})",
                'TB vắng', "=AVERAGE({$vCol}{$start}:{$vCol}{$end})",
                'TB có phép', "=AVERAGE({$pCol}{$start}:{$pCol}{$end})",
                'TB điểm trừ', "=AVERAGE({$deductCol}{$start}:{$deductCol}{$end})",
                'TB chuyên cần', "=AVERAGE({$ccCol}{$start}:{$ccCol}{$end})",
            ];
        }

        return $rows;
    }

    /**
     * Tính chuyên cần theo công thức tùy chọn (nếu cần dùng ở chỗ khác)
     */
    private function calcFormulaForRow(array $stats, int $memberId): string
    {
        return '0%'; // Hàm này có thể bị loại bỏ trong hệ thống mới do đã tính gộp bên trên
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
                $sessionStartCol = Coordinate::stringFromColumnIndex(5);
                $sessionEndCol   = Coordinate::stringFromColumnIndex(4 + $this->sessionCount);
                $sheet->getStyle("{$sessionStartCol}6:{$sessionEndCol}{$this->dataEndRow}")
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            // Cột tổng kết (sau sessions): nền xanh nhạt + bold
            $summaryStartIdx = 4 + $this->sessionCount + 1;
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

        // ── Cột A-D: cố định độ rộng ──
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(24);
        $sheet->getColumnDimension('C')->setWidth(28);
        $sheet->getColumnDimension('D')->setWidth(16);

        // ── Cột session: hẹp lại ──
        for ($i = 5; $i <= 4 + $this->sessionCount; $i++) {
            $col = Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($col)->setWidth(8);
        }

        // ── Cột tổng: vừa ──
        for ($i = 4 + $this->sessionCount + 1; $i <= $lastColIdx; $i++) {
            $col = Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($col)->setWidth(16);
        }

        // ── Freeze panes (đóng băng dòng header + 4 cột đầu) ──
        $sheet->freezePane('E7');

        return [];
    }
}
