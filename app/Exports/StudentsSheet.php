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

class StudentsSheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    private int $ownerUserId;
    private string $classFilter;
    private string $statusFilter;
    private string $search;
    private string $formula;

    public function __construct(int $ownerUserId, string $classFilter, string $statusFilter, string $search, string $formula)
    {
        $this->ownerUserId = $ownerUserId;
        $this->classFilter = $classFilter;
        $this->statusFilter = $statusFilter;
        $this->search = $search;
        $this->formula = $formula;
    }

    public function title(): string
    {
        if ($this->classFilter !== 'all') {
            $courseClass = CourseClass::find($this->classFilter);
            if ($courseClass) {
                // Ensure sheet name is max 31 characters
                return substr(preg_replace('/[^a-zA-Z0-9\-_]/', '', $courseClass->code), 0, 31) ?: 'Class';
            }
        }
        return 'Tất cả lớp học';
    }

    public function array(): array
    {
        $rows = [];
        
        $className = 'Tất cả lớp học';
        if ($this->classFilter !== 'all') {
            $courseClass = CourseClass::find($this->classFilter);
            if ($courseClass) {
                $className = $courseClass->code . ' - ' . $courseClass->name;
            }
        }

        $members = ClassMember::query()
            ->with(['courseClass:id,name,code', 'user:id,email'])
            ->whereHas('courseClass', fn (Builder $query) => $query->where('owner_user_id', $this->ownerUserId))
            ->when(
                $this->statusFilter === 'archived',
                fn (Builder $query) => $query->onlyTrashed(),
                fn (Builder $query) => $query->where('status', 'active'),
            )
            ->when($this->classFilter !== 'all', fn (Builder $query) => $query->where('class_id', $this->classFilter))
            ->when($this->search !== '', function (Builder $query) {
                $query->where(function (Builder $query) {
                    $query->where('full_name', 'like', '%'.$this->search.'%')
                        ->orWhere('student_code', 'like', '%'.$this->search.'%')
                        ->orWhereHas('user', fn (Builder $query) => $query->where('email', 'like', '%'.$this->search.'%'));
                });
            })
            ->orderBy('full_name')
            ->get();

        $classIds = $members->pluck('class_id')->unique();
        $sessions = ClassSession::whereIn('class_id', $classIds)
            ->orderBy('date')
            ->get();

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
        $rows[] = [''];
        $rows[] = $headers;

        $studentService = app(LectureManageStudentService::class);
        $attendanceStats = collect($studentService->getStudentsAttendanceStats($members->pluck('id')));

        $stt = 1;
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
                $stt++,
                $member->student_code,
                $member->full_name,
                $member->user?->email ?? '',
                $member->courseClass ? $member->courseClass->code . ' - ' . $member->courseClass->name : '',
            ];

            $memberRecords = $attendanceRecords->get($member->id, collect())->keyBy('class_session_id');

            foreach ($sessions as $session) {
                if ($session->class_id === $member->class_id) {
                    $record = $memberRecords->get($session->id);
                    if ($record) {
                        $statusMap = [
                            'present' => 'c',
                            'late' => 'm',
                            'absent' => 'v',
                            'excused' => 'p',
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
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $lastCol = $sheet->getHighestColumn();
        
        $sheet->mergeCells("A1:{$lastCol}1");
        
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['argb' => 'FF0d6efd'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ]
        ]);

        $sheet->getStyle('A2:A4')->getFont()->setBold(true);

        $sheet->getStyle("A6:{$lastCol}6")->applyFromArray([
            'font' => [
                'bold' => true, 
                'color' => ['argb' => 'FFFFFFFF'],
                'size' => 12
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF0d6efd']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ]
        ]);
        
        $sheet->getRowDimension(6)->setRowHeight(26);

        return [];
    }
}
