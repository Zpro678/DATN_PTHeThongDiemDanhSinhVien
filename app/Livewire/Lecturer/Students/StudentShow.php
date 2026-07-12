<?php

namespace App\Livewire\Lecturer\Students;

use App\Models\ClassMember;
use App\Services\AttendanceCalculator;
use App\Services\LectureManageStudentService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class StudentShow extends Component
{
    public int $memberId;

    public string $classId = '';

    public function mount(int $member): void
    {
        $memberModel = $this->memberQuery()->findOrFail($member);
        $this->memberId = $memberModel->id;
        $this->classId = (string) $memberModel->class_id;
    }

    public function realtimeChannel(): string
    {
        return (string) config('database.redis.options.prefix') . 'class.' . $this->classId;
    }

    private function memberQuery(): Builder
    {
        return ClassMember::query()
            ->withTrashed()
            ->whereHas('courseClass', fn (Builder $query) => $query->managedBy(auth()->id()));
    }

    public function render(): View
    {
        $member = $this->memberQuery()
            ->with(['user', 'courseClass'])
            ->findOrFail($this->memberId);

        // Dùng cùng nguồn dữ liệu với trang danh sách: query live từ attendance_records.
        $stats = app(LectureManageStudentService::class)
            ->getStudentsAttendanceStats([$this->memberId])[$this->memberId] ?? null;

        $rules = $member->courseClass ? $member->courseClass->getAttendanceRules() : (new \App\Models\CourseClass())->getAttendanceRules();

        $records = $member->attendanceRecords()
            ->with('classSession:id,name,date,meeting_id,qr_token')
            ->whereHas('classSession', fn ($q) => $q->where('status', 'closed')->whereNotNull('meeting_id'))
            ->get();

        $history = $records
            ->filter(fn ($record) => $record->classSession !== null)
            ->groupBy(fn ($record) => $record->classSession->meeting_id)
            ->map(function ($group) use ($rules) {
                // Sắp theo id phiên (~ thời gian) để xác định "phiên cuối"; diễn giải pending theo loại phiên.
                $ordered = $group->sortBy('class_session_id')->values();
                $statuses = $ordered
                    ->map(fn ($record) => AttendanceCalculator::interpretStatus(
                        $record->status,
                        $record->classSession->qr_token !== null,
                    ))
                    ->all();

                $result = AttendanceCalculator::consolidateStatuses($statuses, $rules);

                // Lấy giờ/khoảng cách của phiên đã có mặt/đi muộn (nếu có) để hiển thị.
                $attended = $ordered->first(
                    fn ($record) => in_array($record->status, ['present', 'late'], true) && $record->check_in_time,
                );
                $first = $ordered->first();

                return [
                    'name' => $first->classSession->name,
                    'date' => $first->classSession->date,
                    'check_in_time' => $attended?->check_in_time,
                    'distance_meters' => $attended?->distance_meters,
                    'status' => $result['status'],
                    'label' => $result['label'],
                ];
            })
            ->sortByDesc('date')
            ->values();

        return view('livewire.lecturer.students.show', compact('member', 'history', 'stats'))
            ->layout('layouts.user', ['title' => 'Chi tiết sinh viên']);
    }
}
