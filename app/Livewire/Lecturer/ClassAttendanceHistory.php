<?php

namespace App\Livewire\Lecturer;

use App\Exports\ClassAttendanceHistoryExport;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Services\ClassAttendanceHistoryReport;
use App\Services\SubscriptionService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ClassAttendanceHistory extends Component
{
    #[Url(as: 'group')]
    public ?string $initialGroupKey = null;

    public CourseClass $courseClass;

    public function mount(CourseClass $courseClass)
    {
        $this->courseClass = $courseClass;

        if (! $this->courseClass->isManagedBy(auth()->id())) {
            abort(403);
        }
    }

    public function realtimeChannel(): string
    {
        return (string) config('database.redis.options.prefix') . 'class.' . $this->courseClass->id;
    }



    public function closeSession(int $sessionId): void
    {
        $session = ClassSession::query()
            ->where('class_id', $this->courseClass->id)
            ->findOrFail($sessionId);

        $session->update(['status' => 'closed']);
        session()->flash('status', 'Phiên điểm danh đã được chốt.');
    }

    #[On('export-class-history')]
    public function exportExcel()
    {
        if (! app(SubscriptionService::class)->canExportExcel(auth()->user())) {
            session()->flash('upgrade_required', 'Xuất báo cáo Excel là tính năng của gói Pro trở lên. Vui lòng nâng cấp để sử dụng.');

            return $this->redirectRoute('upgrade', navigate: true);
        }

        $fileCode = $this->courseClass->class_code ?: $this->courseClass->join_key ?: $this->courseClass->name;
        $slug = Str::slug($fileCode) ?: 'lop';
        $fileName = 'lich_su_diem_danh_'.$slug.'_'.now()->format('Ymd_His').'.xlsx';

        try {
            return Excel::download(new ClassAttendanceHistoryExport($this->courseClass), $fileName);
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Đã xảy ra lỗi trong quá trình tạo file. Vui lòng thử lại.', type: 'error');

            return null;
        }
    }

    public function render(): View
    {
        $report = app(ClassAttendanceHistoryReport::class);

        $allMembers = $report->activeMembersQuery($this->courseClass)->get();
        
        $members = $allMembers->sortBy(function ($m) {
            $parts = explode(' ', trim((string) $m->full_name));
            return end($parts) . ' ' . $m->full_name;
        })->values();

        $history = $report->build($this->courseClass, $members);
        $sessions = $history['sessions'];
        $groupedSessions = $history['groupedSessions'];
        $matrix = $history['matrix'];
        $groupedSessionsInfo = $history['groupedSessionsInfo'];
        $membersData = $history['membersData'];

        $canExportExcel = app(SubscriptionService::class)->canExportExcel(auth()->user());

        return view('livewire.lecturer.class-attendance-history', compact('members', 'groupedSessions', 'matrix', 'sessions', 'groupedSessionsInfo', 'membersData', 'canExportExcel'))
            ->layout('layouts.fullscreen', [
                'title' => 'Lịch sử điểm danh (' . $sessions->count() . ')',
                'subtitle' => $this->courseClass->join_key . ' - ' . $this->courseClass->name,
                'backUrl' => route('lecturer.classes.show', ['ma_user' => auth()->id(), 'courseClass' => $this->courseClass->id])
            ]);
    }
}
