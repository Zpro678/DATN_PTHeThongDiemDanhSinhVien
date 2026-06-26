<?php

namespace App\Livewire\Lecturer\Attendance;

use App\Exports\ClassSessionExport;
use App\Livewire\Lecturer\Attendance\Concerns\OwnsAttendanceSessions;
use App\Models\AttendanceRecord;
use App\Models\ClassSession;
use App\Services\NotificationService;
use App\Services\SubscriptionService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class ManualAttendanceSession extends Component
{
    use OwnsAttendanceSessions;

    public int $sessionId;

    public string $search = '';

    public string $statusFilter = 'all';

    public function mount(int $session): void
    {
        $this->sessionId = $this->ownedSession($session)->id;
    }

    public function setStatusFilter(string $status): void
    {
        abort_unless(in_array($status, ['all', 'pending', 'present', 'late', 'absent', 'excused'], true), 422);

        $this->statusFilter = $status;
    }

    public function searchStudents(): void
    {
        $this->search = trim($this->search);
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->statusFilter = 'all';
    }

    public function updateStatus(int $recordId, string $status): void
    {
        abort_unless(in_array($status, ['present', 'late', 'absent', 'excused'], true), 422);
        $this->ensureSessionIsOpen();

        $record = AttendanceRecord::query()
            ->where('class_session_id', $this->sessionId)
            ->whereHas('classSession.courseClass', fn ($query) => $query->where('owner_user_id', auth()->id()))
            ->findOrFail($recordId);

        $record->update([
            'status' => $status,
            'check_in_time' => in_array($status, ['present', 'late'], true) ? now() : null,
            'is_verified' => $record->classMember->user_id !== null,
        ]);
    }

    public function updateNote(int $recordId, string $note): void
    {
        $this->ensureSessionIsOpen();

        AttendanceRecord::query()
            ->where('class_session_id', $this->sessionId)
            ->whereHas('classSession.courseClass', fn ($query) => $query->where('owner_user_id', auth()->id()))
            ->findOrFail($recordId)
            ->update(['note' => trim($note) ?: null]);
    }

    public function markAllPresent(): void
    {
        $this->ensureSessionIsOpen();

        AttendanceRecord::query()
            ->where('class_session_id', $this->sessionId)
            ->where('status', 'pending')
            ->whereHas('classSession.courseClass', fn ($query) => $query->where('owner_user_id', auth()->id()))
            ->whereHas('classMember')
            ->with('classMember:id,user_id')
            ->get()
            ->each(fn (AttendanceRecord $record) => $record->update([
                'status' => 'present',
                'check_in_time' => now(),
                'is_verified' => $record->classMember->user_id !== null,
            ]));

        session()->flash('success', 'Đã đánh dấu tất cả sinh viên chưa điểm danh là có mặt.');
    }

    public function validateBeforeClose(): void
    {
        $session = $this->ownedSession($this->sessionId);
        
        $firstPending = $session->attendanceRecords()
            ->where('status', 'pending')
            ->whereHas('classMember')
            ->with('classMember')
            ->first();

        if ($firstPending) {
            $studentName = $firstPending->classMember->full_name ?? 'không xác định';
            session()->flash('error', "Bạn chưa chọn trạng thái điểm danh của học viên {$studentName}.");
            $this->dispatch('scroll-to-pending');
        } else {
            $this->dispatch('open-close-modal');
        }
    }

    public function closeSession(): void
    {
        $session = $this->ownedSession($this->sessionId);
        $session->update(['status' => 'closed']);

        app(NotificationService::class)->attendanceSessionClosed((int) auth()->id(), $session, isQr: false);

        session()->flash('success', 'Phiên điểm danh đã được chốt sổ.');
    }

    public function deleteSession()
    {
        $session = $this->ownedSession($this->sessionId);

        abort_if($session->status === 'closed', 403, 'Không thể xóa phiên điểm danh đã chốt.');

        $classId = $session->class_id;

        // Xóa mềm các bản ghi điểm danh (AttendanceRecord) nếu cần thiết,
        // nhưng model ClassSession đã cấu hình SoftDeletes nên chỉ cần xóa class session.
        // Để query records không bị mồ côi thì tốt nhất là xóa luôn cascade hoặc kệ nó
        // vì khi truy vấn session đã xóa thì record sẽ ẩn. (Tùy logic hệ thống, nhưng xóa session là đủ).
        $session->delete();

        session()->flash('success', 'Buổi điểm danh đã được xóa thành công.');

        return redirect()->route('lecturer.classes.show', $classId);
    }

    public function createDuplicateManualSession(): void
    {
        $oldSession = $this->ownedSession($this->sessionId)->load('courseClass');
        $courseClass = $oldSession->courseClass;

        $newSession = ClassSession::query()->create([
            'class_id' => $courseClass->id,
            'created_by' => auth()->id(),
            'name' => $oldSession->name,
            'date' => $oldSession->date,
            'start_time' => $oldSession->start_time,
            'end_time' => $oldSession->end_time,
            'start_lesson' => $oldSession->start_lesson,
            'end_lesson' => $oldSession->end_lesson,
            'lesson_count' => $oldSession->lesson_count,
            'status' => 'active',
        ]);

        $courseClass->members()->where('status', 'active')->get()->each(fn ($member) => AttendanceRecord::query()->firstOrCreate([
            'class_session_id' => $newSession->id,
            'class_member_id' => $member->id,
        ], [
            'status' => 'pending',
            'is_verified' => $member->user_id !== null,
        ]));

        app(NotificationService::class)->attendanceSessionCreated((int) auth()->id(), $newSession, isQr: false);

        $this->redirectRoute('lecturer.attendance.manual.session', ['ma_user' => auth()->id(), 'session' => $newSession->id], navigate: true);
    }

    public function exportExcel()
    {
        // Kiểm tra gói: xuất Excel là tính năng từ gói Pro trở lên.
        if (! app(SubscriptionService::class)->canExportExcel(auth()->user())) {
            session()->flash('upgrade_required', 'Xuất báo cáo Excel là tính năng của gói Pro trở lên. Vui lòng nâng cấp để sử dụng.');

            return $this->redirectRoute('upgrade', navigate: true);
        }

        $session = $this->ownedSession($this->sessionId)->load('courseClass');

        $date = $session->date->format('Y-m-d');
        $className = Str::slug($session->courseClass->name);
        $endLesson = max(1, (int) $session->lesson_count);
        $fileName = "{$date}_{$className}_Tiet_1-{$endLesson}.xlsx";

        return Excel::download(
            new ClassSessionExport($this->sessionId),
            $fileName
        );
    }

    public function render(): View
    {
        $session = $this->ownedSession($this->sessionId)->load('courseClass');
        $records = $session->attendanceRecords()
            ->whereHas('classMember')
            ->with('classMember.user')
            ->when($this->statusFilter !== 'all', fn (Builder $query) => $query->where('status', $this->statusFilter))
            ->when($this->search !== '', function (Builder $query): void {
                $query->whereHas('classMember', function (Builder $query): void {
                    $query->where('student_code', 'like', '%'.$this->search.'%')
                        ->orWhere('full_name', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('id')
            ->get();

        $stats = $session->attendanceRecords()
            ->whereHas('classMember')
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $summary = [
            'present' => (int) ($stats['present'] ?? 0),
            'late' => (int) ($stats['late'] ?? 0),
            'excused' => (int) ($stats['excused'] ?? 0),
            'absent' => (int) ($stats['absent'] ?? 0),
            'pending' => (int) ($stats['pending'] ?? 0),
            'total' => $session->attendanceRecords()->whereHas('classMember')->count(),
        ];

        $summary['present_percent'] = $summary['total'] > 0
            ? (int) round(($summary['present'] / $summary['total']) * 100)
            : 0;

        $canExportExcel = app(SubscriptionService::class)->canExportExcel(auth()->user()); // Quyền xuất Excel theo gói (Pro trở lên).

        return view('livewire.lecturer.attendance.manual-session', compact('session', 'records', 'summary', 'canExportExcel'))
            ->layout('layouts.user', ['title' => 'Điểm danh thủ công']);
    }

    private function ensureSessionIsOpen(): void
    {
        abort_if($this->ownedSession($this->sessionId)->status === 'closed', 403);
    }
}
