<?php

namespace App\Livewire\Student;

use App\Models\AttendanceRecord;
use App\Models\ClassMember;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendanceHistory extends Component
{
    use WithPagination;

    public string $statusFilter = 'all';

    #[Url]
    public string $classFilter = 'all';

    public string $search = '';

    public int $perPage = 10;

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->statusFilter = 'all';
        $this->classFilter = 'all';
        $this->search = '';
    }

    public function render(): View
    {
        $members = ClassMember::query()
            ->with('courseClass.owner')
            ->where('user_id', auth()->id())
            ->get();

        $allRecords = $this->attendanceRecords($members);

        $page = $this->getPage();
        $perPage = $this->perPage;
        
        $paginatedRecords = new LengthAwarePaginator(
            $allRecords->forPage($page, $perPage),
            $allRecords->count(),
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );

        return view('livewire.student.attendance-history', [
            'records' => $paginatedRecords,
            'classes' => $members->pluck('courseClass')->filter()->unique('id')->values(),
            'summary' => $this->summary($allRecords),
            'isDemo' => false,
        ])->layout('layouts.user', ['title' => 'Lịch sử điểm danh']);
    }

    /**
     * @param  Collection<int, ClassMember>  $members
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function attendanceRecords(Collection $members): \Illuminate\Support\Collection
    {
        $memberIds = $members->pluck('id');

        if ($memberIds->isEmpty()) {
            return collect();
        }

        $records = AttendanceRecord::query()
            ->with(['classMember.courseClass.owner', 'classSession'])
            ->whereIn('class_member_id', $memberIds)
            ->get()
            ->sortByDesc(fn (AttendanceRecord $record) => $record->classSession?->date?->timestamp ?? 0)
            ->map(fn (AttendanceRecord $record): array => [
                'id' => $record->id,
                'date' => $record->classSession?->date,
                'session' => $record->classSession?->name ?? 'Buổi điểm danh',
                'class_id' => $record->classMember?->courseClass?->id,
                'class_code' => $record->classMember?->courseClass?->code ?? 'N/A',
                'class_name' => $record->classMember?->courseClass?->name ?? 'Lớp học',
                'subject_code' => $record->classMember?->courseClass?->subject_code,
                'semester' => $record->classMember?->courseClass?->semester,
                'teacher' => $record->classMember?->courseClass?->owner?->name ?? 'Chưa cập nhật',
                'status' => $record->status,
                'method' => filled($record->classSession?->qr_token) ? 'QR + GPS' : 'Thủ công',
                'check_in_time' => $record->check_in_time,
                'verified' => $record->is_verified,
                'distance' => $record->distance_meters,
                'note' => $record->note,
                'demo' => false,
            ])
            ->values();

        if ($this->classFilter !== 'all') {
            $records = $records->where('class_id', (int) $this->classFilter)->values();
        }

        if ($this->statusFilter !== 'all') {
            $records = $records->where('status', $this->statusFilter)->values();
        }

        if (trim($this->search) !== '') {
            $search = str($this->search)->lower()->toString();
            $records = $records->filter(function (array $record) use ($search): bool {
                $dateStr = $record['date'] ? $record['date']->format('d/m/Y') : '';
                return str($record['class_name'])->lower()->contains($search)
                    || str($record['class_code'])->lower()->contains($search)
                    || str((string) $record['subject_code'])->lower()->contains($search)
                    || str($dateStr)->contains($search);
            })->values();
        }

        return $records;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $records
     * @return array<string, int>
     */
    private function summary(\Illuminate\Support\Collection $records): array
    {
        return [
            'total' => $records->count(),
            'present' => $records->where('status', 'present')->count(),
            'late' => $records->where('status', 'late')->count(),
            'excused' => $records->where('status', 'excused')->count(),
            'absent' => $records->where('status', 'absent')->count(),
            'pending' => $records->where('status', 'pending')->count(),
        ];
    }
}
