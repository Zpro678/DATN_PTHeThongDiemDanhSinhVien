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
            ->with(['courseClass:id,name,join_key,total_sessions,deduct_late,deduct_absent,deduct_excused', 'user:id,name,email', 'profile'])
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
                      ->orWhere('class_member_profiles.email', 'like', '%' . $this->search . '%')
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

        $headers = array_merge($headers, [
            'Tổng số buổi',
            'Có mặt',
            'Đi muộn',
            'Vắng',
            'Có phép',
            'Tổng điểm trừ',
            'Phần trăm có mặt trong lớp',
            'Điểm chuyên cần (/10)'
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

        // NGUỒN DUY NHẤT: cùng service với giao diện web để số chuyên cần KHỚP nhau.
        // (Trước đây Excel tự tính từ MeetingSummary với mẫu số = số buổi đã chốt, còn web dùng
        //  attendance_records thô + mẫu số max(dự kiến, đã học) -> lệch nhau.)
        $statsMap = app(LectureManageStudentService::class)->getStudentsAttendanceStats($members->pluck('id')->all());

        foreach ($members as $member) {
            $memberSummaries = $meetingSummaries->get($member->id, collect())->keyBy('meeting_id');
            $stats = $statsMap[$member->id] ?? [];

            $present      = (int) ($stats['present_sessions'] ?? 0);
            $late         = (int) ($stats['late_sessions'] ?? 0);
            $absent       = (int) ($stats['absent_sessions'] ?? 0);
            $excused      = (int) ($stats['excused_sessions'] ?? 0);
            $studied      = (int) ($stats['studied_sessions'] ?? 0);           // số buổi đã học
            $baseSessions = (int) ($stats['planned_sessions'] ?? 0);           // tổng buổi dự kiến = max(dự kiến, đã học)

            // Điểm trừ theo CẤU HÌNH LỚP (đi muộn/vắng/vắng có phép). "Có mặt" luôn = 0.
            $rules = $member->courseClass
                ? $member->courseClass->getAttendanceRules()
                : (new \App\Models\CourseClass())->getAttendanceRules();

            $totalDeduction = round(
                $late * (float) $rules['late']
                + $absent * (float) $rules['absent']
                + $excused * (float) $rules['excused'],
                2
            );

            // Cột: % VẮNG KHÔNG PHÉP trên tổng buổi dự kiến.
            $absentPercent = $baseSessions > 0 ? (int) round($absent / $baseSessions * 100) : 0;

            // Cột: ĐIỂM CHUYÊN CẦN trên thang 10 = 10 − tổng điểm trừ (không âm).
            $attendanceScore = max(round(10 - $totalDeduction, 2), 0);

            $row = [
                $currentRow - 6, // STT
                $member->full_name,
                $member->email ?? $member->user?->email ?? '',
                $member->courseClass?->join_key ?? '',
            ];

            // Timeline từng buổi vẫn lấy từ MeetingSummary (chi tiết hiển thị).
            foreach ($meetings as $meeting) {
                if ($meeting->class_id === $member->class_id) {
                    $summary = $memberSummaries->get($meeting->id);
                    $statusMap = ['present' => 'c', 'late' => 'm', 'absent' => 'v', 'excused' => 'p'];
                    $row[] = $summary ? ($statusMap[$summary->status] ?? '-') : '-';
                } else {
                    $row[] = '';
                }
            }

            $row = array_merge($row, [
                $baseSessions,
                $present,
                $late,
                $absent,
                $excused,
                $totalDeduction,
                (100 - $absentPercent) . '%',
                $attendanceScore,
            ]);

            $rows[] = $row;
            $currentRow++;
        }

        $this->dataEndRow = $currentRow - 1;



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
