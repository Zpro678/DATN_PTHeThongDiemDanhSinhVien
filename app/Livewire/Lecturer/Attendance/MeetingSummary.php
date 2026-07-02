<?php

namespace App\Livewire\Lecturer\Attendance;

use App\Exports\MeetingSummaryExport;
use App\Models\ClassMeeting;
use App\Models\MeetingSummary as MeetingSummaryModel;
use App\Services\AttendanceCalculator;
use App\Services\SubscriptionService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class MeetingSummary extends Component
{
    public ClassMeeting $meeting;

    /** @var array<int, string> Trạng thái tổng kết tạm theo class_member_id. */
    public array $draftStatuses = [];

    /** @var array<int, string> Ghi chú tạm theo class_member_id. */
    public array $draftNotes = [];

    /** @var bool Trạng thái khóa chỉnh sửa */
    public bool $isLocked = true;

    public function mount(ClassMeeting $meeting): void
    {
        $meeting->load('courseClass');
        abort_unless($meeting->courseClass->owner_user_id === auth()->id(), 403);

        // Hết giờ thì chốt buổi; sau đó dựng/đồng bộ bảng tổng kết từ các phiên.
        $meeting->closeIfExpired();
        AttendanceCalculator::syncSummaries($meeting);

        $this->meeting = $meeting;
        $this->loadDrafts();
    }

    private function loadDrafts(): void
    {
        $summaries = $this->meeting->summaries()->get();

        foreach ($summaries as $summary) {
            $this->draftStatuses[$summary->class_member_id] = $summary->status;
            $this->draftNotes[$summary->class_member_id] = $summary->note ?? '';
        }
    }

    public function setStatus(int $memberId, string $status): void
    {
        abort_unless(in_array($status, ['present', 'late', 'absent', 'excused'], true), 422);

        if (array_key_exists($memberId, $this->draftStatuses)) {
            $this->draftStatuses[$memberId] = $status;
        }
    }

    /**
     * Lưu chỉnh sửa tổng kết của giảng viên.
     */
    public function save(): void
    {
        $rules = $this->meeting->courseClass->getAttendanceRules();

        $summaries = $this->meeting->summaries()->with('classMember.user')->get()->keyBy('class_member_id');

        foreach ($this->draftStatuses as $memberId => $status) {
            $summary = $summaries->get($memberId);
            if (! $summary) {
                continue;
            }

            $note = trim((string) ($this->draftNotes[$memberId] ?? ''));

            $summary->update([
                'status' => $status,
                'deduction' => AttendanceCalculator::deductionForStatus($status, $rules),
                'is_overridden' => $status !== $summary->auto_status,
                'note' => $note !== '' ? $note : null,
            ]);

            if ($summary->classMember && $summary->classMember->user) {
                $summary->classMember->user->notify(new \App\Notifications\AttendanceResultNotification($summary));
            }
        }

        $this->isLocked = true;
        $this->dispatch('toast', message: 'Đã lưu tổng kết và gửi thông báo cho học viên.', type: 'success');
    }

    public function unlock(): void
    {
        $this->isLocked = false;
    }

    /**
     * Bỏ chỉnh sửa thủ công, tính lại tổng kết từ dữ liệu các phiên.
     */
    public function recompute(): void
    {
        MeetingSummaryModel::query()
            ->where('meeting_id', $this->meeting->id)
            ->update(['is_overridden' => false]);

        AttendanceCalculator::syncSummaries($this->meeting);

        $this->draftStatuses = [];
        $this->draftNotes = [];
        $this->loadDrafts();

        $this->dispatch('toast', message: 'Đã tính lại tổng kết từ các phiên điểm danh.', type: 'success');
    }

    public function exportExcel()
    {
        if (! app(SubscriptionService::class)->canExportExcel(auth()->user())) {
            session()->flash('upgrade_required', 'Xuất báo cáo Excel là tính năng của gói Pro trở lên. Vui lòng nâng cấp để sử dụng.');

            return $this->redirectRoute('upgrade', navigate: true);
        }

        // Lưu trạng thái mới nhất trước khi xuất.
        $this->save();

        $className = Str::slug($this->meeting->courseClass->name);
        $date = $this->meeting->date->format('Y-m-d');
        $fileName = "tong-ket_{$date}_{$className}.xlsx";

        return Excel::download(new MeetingSummaryExport($this->meeting->id), $fileName);
    }

    public function render(): View
    {
        $rules = $this->meeting->courseClass->getAttendanceRules();

        $sessions = $this->meeting->sessions()->orderBy('id')->get(['id', 'name', 'qr_token']);
        $consolidated = AttendanceCalculator::consolidateMeeting($this->meeting)->keyBy(fn ($row) => $row['member']->id);

        $rows = $consolidated->map(function ($row) use ($rules) {
            $memberId = $row['member']->id;
            $status = $this->draftStatuses[$memberId] ?? $row['status'];

            return [
                'member' => $row['member'],
                'session_statuses' => $row['statuses'],
                'auto_label' => $row['label'],
                'auto_status' => $row['status'],
                'status' => $status,
                'deduction' => AttendanceCalculator::deductionForStatus($status, $rules),
                'edited' => $status !== $row['status'],
            ];
        })->sortBy(function ($row) {
            $parts = explode(' ', trim($row['member']->full_name));
            $firstName = end($parts);
            return $firstName . ' ' . $row['member']->full_name;
        })->values();

        // Tổng hợp số liệu mức buổi theo trạng thái đang chọn.
        $totals = ['present' => 0, 'late' => 0, 'absent' => 0, 'excused' => 0, 'deduction' => 0.0];
        foreach ($rows as $row) {
            $totals[$row['status']] = ($totals[$row['status']] ?? 0) + 1;
            $totals['deduction'] += $row['deduction'];
        }

        $canExportExcel = app(SubscriptionService::class)->canExportExcel(auth()->user());

        return view('livewire.lecturer.attendance.meeting-summary', compact('rows', 'sessions', 'totals', 'canExportExcel'))
            ->layout('layouts.user', ['title' => 'Tổng kết buổi điểm danh']);
    }
}
