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

    /** @var array<int, string> Trạng thái tạm thời theo từng bản ghi (chưa ghi DB cho tới khi "Lưu phiên"). */
    public array $draftStatuses = [];

    /** @var array<int, string> Ghi chú tạm thời theo từng bản ghi. */
    public array $draftNotes = [];

    public function mount(int $session): void
    {
        $ownedSession = $this->ownedSession($session);
        // Buổi hết giờ thì tự động chốt (kéo theo phiên này) trước khi cho thao tác.
        $ownedSession->meeting?->closeIfExpired();

        $this->sessionId = $ownedSession->id;
        $this->initDrafts();
    }

    /**
     * Nạp trạng thái/ghi chú hiện tại từ DB vào bộ nhớ tạm để chỉnh sửa.
     */
    private function initDrafts(): void
    {
        // Phiên thủ công: học viên chưa đánh dấu mặc định là "Có mặt"; giảng viên chỉ sửa người vắng/trễ.
        $isManual = $this->ownedSession($this->sessionId)->qr_token === null;

        $records = AttendanceRecord::query()
            ->where('class_session_id', $this->sessionId)
            ->whereHas('classMember')
            ->get(['id', 'status', 'note']);

        foreach ($records as $record) {
            $status = $record->status;

            if ($isManual && $status === 'pending') {
                $status = 'present';
            }

            $this->draftStatuses[$record->id] = $status;
            $this->draftNotes[$record->id] = $record->note ?? '';
        }
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

    /**
     * Đánh dấu trạng thái tạm thời (chưa ghi DB) cho một bản ghi.
     */
    public function setStatus(int $recordId, string $status): void
    {
        abort_unless(in_array($status, ['present', 'late', 'absent', 'excused'], true), 422);
        $this->ensureSessionIsOpen();

        if (! array_key_exists($recordId, $this->draftStatuses)) {
            return;
        }

        $this->draftStatuses[$recordId] = $status;
    }

    /**
     * Đánh dấu tạm thời tất cả học viên chưa điểm danh là có mặt.
     */
    public function markAllPresent(): void
    {
        $this->ensureSessionIsOpen();

        foreach ($this->draftStatuses as $recordId => $status) {
            if ($status === 'pending') {
                $this->draftStatuses[$recordId] = 'present';
            }
        }

        session()->flash('success', 'Đã đánh dấu tạm thời tất cả học viên chưa điểm danh là có mặt. Nhấn "Lưu phiên" để lưu lại.');
    }

    /**
     * Lưu toàn bộ trạng thái/ghi chú tạm thời của phiên này vào DB.
     * Không chốt sổ: phiên vẫn mở để chỉnh sửa, chuyên cần sẽ được tổng hợp khi buổi kết thúc.
     */
    public function saveSession(): void
    {
        $this->ensureSessionIsOpen();

        $records = AttendanceRecord::query()
            ->where('class_session_id', $this->sessionId)
            ->whereHas('classSession.courseClass', fn ($query) => $query->where('owner_user_id', auth()->id()))
            ->whereHas('classMember')
            ->with('classMember:id,user_id')
            ->get();

        foreach ($records as $record) {
            $status = $this->draftStatuses[$record->id] ?? $record->status;
            $note = trim((string) ($this->draftNotes[$record->id] ?? ''));

            $record->update([
                'status' => $status,
                'note' => $note !== '' ? $note : null,
                'check_in_time' => in_array($status, ['present', 'late'], true)
                    ? ($record->check_in_time ?? now())
                    : null,
                'is_verified' => $record->classMember?->user_id !== null,
            ]);
        }

        session()->flash('status', 'Đã lưu phiên điểm danh.');

        // Quay về trang chi tiết buổi (danh sách phiên) sau khi lưu.
        $meetingId = $this->ownedSession($this->sessionId)->meeting_id;

        $this->redirectRoute('lecturer.attendance.meeting.sessions', [
            'ma_user' => auth()->id(),
            'meeting' => $meetingId,
        ], navigate: true);
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
        $oldSession = $this->ownedSession($this->sessionId)->load('meeting.courseClass');
        $meeting = $oldSession->meeting;

        if (! $meeting->canAddSession()) {
            session()->flash('error', 'Buổi điểm danh đã kết thúc, không thể thêm phiên mới.');
            return;
        }

        $meeting->update(['status' => 'active']);

        // Thêm một phiên thủ công mới vào cùng buổi học.
        $newSession = $meeting->createSession('active');

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
        $fileName = "{$date}_{$className}.xlsx";

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
            ->when($this->search !== '', function (Builder $query): void {
                $query->whereHas('classMember', function (Builder $query): void {
                    $query->where('student_code', 'like', '%'.$this->search.'%')
                        ->orWhere('full_name', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('id')
            ->get();

        // Lọc theo trạng thái dựa trên đánh dấu TẠM THỜI (chưa lưu cũng được lọc đúng).
        if ($this->statusFilter !== 'all') {
            $records = $records->filter(fn (AttendanceRecord $record) => ($this->draftStatuses[$record->id] ?? $record->status) === $this->statusFilter)
                ->values();
        }

        // Tổng hợp nhanh tính theo đánh dấu tạm thời của toàn bộ phiên.
        $summary = ['present' => 0, 'late' => 0, 'excused' => 0, 'absent' => 0, 'pending' => 0];
        foreach ($this->draftStatuses as $status) {
            if (array_key_exists($status, $summary)) {
                $summary[$status]++;
            }
        }
        $summary['total'] = count($this->draftStatuses);
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
