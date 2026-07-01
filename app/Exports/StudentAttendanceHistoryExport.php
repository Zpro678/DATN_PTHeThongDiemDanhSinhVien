<?php

namespace App\Exports;

use App\Models\AttendanceRecord;
use App\Models\ClassMember;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StudentAttendanceHistoryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $studentId;
    protected $classFilter;
    protected $statusFilter;
    protected $search;

    public function __construct(int $studentId, string $classFilter = 'all', string $statusFilter = 'all', string $search = '')
    {
        $this->studentId = $studentId;
        $this->classFilter = $classFilter;
        $this->statusFilter = $statusFilter;
        $this->search = $search;
    }

    public function collection()
    {
        $members = ClassMember::query()
            ->with('courseClass.owner')
            ->where('user_id', $this->studentId)
            ->get();

        $memberIds = $members->pluck('id');

        if ($memberIds->isEmpty()) {
            return collect();
        }

        $records = AttendanceRecord::query()
            ->with(['classMember.courseClass.owner', 'classSession'])
            ->whereIn('class_member_id', $memberIds)
            ->whereHas('classSession', fn ($q) => $q->where('status', 'closed'))
            ->get()
            ->sortByDesc(fn (AttendanceRecord $record) => $record->classSession?->date?->timestamp ?? 0)
            ->values();

        if ($this->classFilter !== 'all') {
            $records = $records->where('classMember.class_id', (int) $this->classFilter)->values();
        }

        if ($this->statusFilter !== 'all') {
            $records = $records->where('status', $this->statusFilter)->values();
        }

        if (trim($this->search) !== '') {
            $search = str($this->search)->lower()->toString();
            $records = $records->filter(function (AttendanceRecord $record) use ($search): bool {
                $dateStr = $record->classSession?->date ? $record->classSession->date->format('d/m/Y') : '';
                return str($record->classMember?->courseClass?->name)->lower()->contains($search)
                    || str($record->classMember?->courseClass?->join_key)->lower()->contains($search)
                    || str($dateStr)->contains($search);
            })->values();
        }

        return $records;
    }

    public function headings(): array
    {
        return [
            ['BÁO CÁO LỊCH SỬ ĐIỂM DANH'],
            ['Ngày', 'Môn học & Buổi', 'Lớp', 'Giảng viên', 'Trạng thái', 'Hình thức', 'Giờ Check-in', 'Ghi chú'],
        ];
    }

    public function map($record): array
    {
        $statusLabels = [
            'present' => 'Có mặt',
            'late' => 'Muộn',
            'excused' => 'Có phép',
            'absent' => 'Vắng',
            'pending' => 'Chưa ĐD',
        ];

        return [
            $record->classSession?->date ? $record->classSession->date->format('d/m/Y') : '--/--/----',
            ($record->classMember?->courseClass?->name ?? 'Lớp học') . ' - ' . ($record->classSession?->name ?? 'Buổi điểm danh'),
            $record->classMember?->courseClass?->join_key ?? 'N/A',
            $record->classMember?->courseClass?->owner?->name ?? 'Chưa cập nhật',
            $statusLabels[$record->status] ?? $record->status,
            filled($record->classSession?->qr_token) ? 'QR + GPS' : 'Thủ công',
            $record->check_in_time ? $record->check_in_time->format('H:i') : 'Chưa check-in',
            $record->note ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:H1');
        return [
            1    => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            2    => ['font' => ['bold' => true]],
        ];
    }
}
