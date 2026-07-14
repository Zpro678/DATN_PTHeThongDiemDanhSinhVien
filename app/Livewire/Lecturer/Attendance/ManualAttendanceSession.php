<?php

namespace App\Livewire\Lecturer\Attendance;

use App\Exports\ClassSessionExport;
use App\Livewire\Lecturer\Attendance\Concerns\OwnsAttendanceSessions;
use App\Models\AttendanceRecord;
use App\Models\ClassSession;
use App\Services\AuditLogService;
use App\Services\AttendanceCalculator;
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

    public string $classId = '';

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
        $this->classId = (string) $ownedSession->class_id;
        $this->initDrafts();
    }

    public function realtimeChannel(): string
    {
        return (string) config('database.redis.options.prefix') . 'class.' . $this->classId;
    }

    /**
     * Nạp trạng thái/ghi chú hiện tại từ DB vào bộ nhớ tạm để chỉnh sửa.
     */
    private function initDrafts(): void
    {
        $isManual = $this->ownedSession($this->sessionId)->qr_token === null;
        $session = $this->ownedSession($this->sessionId);

        // Đảm bảo tất cả sinh viên đang hoạt động đều có bản ghi điểm danh trong phiên này
        // (để khắc phục trường hợp sinh viên được import vào lớp sau khi phiên đã tạo)
        $activeMembers = $session->courseClass->members()->where('status', \App\Models\ClassMember::STATUS_ACTIVE)->pluck('id');
        $existingRecordMemberIds = AttendanceRecord::query()
            ->where('class_session_id', $this->sessionId)
            ->pluck('class_member_id');
            
        $missingMemberIds = $activeMembers->diff($existingRecordMemberIds);
        if ($missingMemberIds->isNotEmpty()) {
            $newRecords = $missingMemberIds->map(fn ($memberId) => [
                'class_session_id' => $this->sessionId,
                'class_member_id' => $memberId,
                'status' => $isManual ? 'absent' : 'pending',
                'is_account' => \App\Models\ClassMember::find($memberId)->user_id !== null,
                'created_at' => now(),
                'updated_at' => now(),
            ])->toArray();
            
            AttendanceRecord::insert($newRecords);
        }

        $records = AttendanceRecord::query()
            ->where('class_session_id', $this->sessionId)
            ->whereHas('classMember')
            ->get(['id', 'status', 'note']);

        // 1. Nạp toàn bộ dữ liệu gốc từ DB để đảm bảo không bị thiếu key (Failsafe)
        foreach ($records as $record) {
            $status = $record->status;
            if ($isManual && $status === 'pending') {
                $status = 'absent';
            }
            $this->draftStatuses[$record->id] = $status;
            $this->draftNotes[$record->id] = $record->note ?? '';
        }

        // 2. Ghi đè bằng dữ liệu đang gõ dở từ Session (nếu có)
        if (session()->has('draft_attendance_' . $this->sessionId . '_has_draft')) {
            $sessionStatuses = session()->get('draft_attendance_' . $this->sessionId . '_statuses', []);
            $sessionNotes = session()->get('draft_attendance_' . $this->sessionId . '_notes', []);
            
            foreach ($sessionStatuses as $id => $st) {
                $this->draftStatuses[$id] = $st;
            }
            foreach ($sessionNotes as $id => $nt) {
                $this->draftNotes[$id] = $nt;
            }
        }
    }

    public function updatedDraftStatuses()
    {
        session()->put('draft_attendance_' . $this->sessionId . '_statuses', $this->draftStatuses);
        session()->put('draft_attendance_' . $this->sessionId . '_has_draft', true);
    }

    public function updatedDraftNotes()
    {
        session()->put('draft_attendance_' . $this->sessionId . '_notes', $this->draftNotes);
        session()->put('draft_attendance_' . $this->sessionId . '_has_draft', true);
    }

    public function setStatusFilter(string $status): void
    {
        abort_unless(in_array($status, ['all', 'pending', 'present', 'late', 'absent', 'excused', 'invalid'], true), 422);

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
        $this->updatedDraftStatuses();
    }

    /**
     * Đánh dấu tạm thời tất cả học viên chưa điểm danh là có mặt.
     */
    public function markAllPresent(): void
    {
        $this->ensureSessionIsOpen();

        foreach ($this->draftStatuses as $recordId => $status) {
            // Mặc định giờ là "Vắng", nên nút này chuyển cả pending lẫn absent -> có mặt (giữ nguyên muộn/có phép đã đánh).
            if (in_array($status, ['pending', 'absent'], true)) {
                $this->draftStatuses[$recordId] = 'present';
            }
        }
        $this->updatedDraftStatuses();

        session()->flash('success', 'Đã đánh dấu tạm thời tất cả học viên là có mặt. Nhấn "Lưu phiên" để lưu lại.');
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
            ->whereHas('classSession.courseClass', fn ($query) => $query->managedBy(auth()->id()))
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
                    ? ($record->check_in_time ?? now('Asia/Ho_Chi_Minh'))
                    : null,
                'is_account' => $record->classMember?->user_id !== null,
            ]);
        }

        $session = $this->ownedSession($this->sessionId)->load('meeting');
        session()->flash('status', 'Đã lưu phiên điểm danh.');
        app(AuditLogService::class)->log('manual_attendance', [
            'class_id'   => $this->ownedSession($this->sessionId)->class_id,
            'table_name' => 'class_sessions',
            'row_id'     => $this->sessionId,
            'new_values' => ['saved_records' => count($this->draftStatuses)],
        ]);

        session()->forget([
            'draft_attendance_' . $this->sessionId . '_statuses',
            'draft_attendance_' . $this->sessionId . '_notes',
            'draft_attendance_' . $this->sessionId . '_has_draft'
        ]);

        // Cập nhật chuyên cần cho buổi học sau khi sửa điểm danh
        $meeting = $this->ownedSession($this->sessionId)->load('meeting')->meeting;
        if ($meeting) {
            \App\Services\AttendanceCalculator::syncSummaries($meeting);
        }

        // Quay về trang chi tiết buổi (danh sách phiên) sau khi lưu.
        $session = $this->ownedSession($this->sessionId);
        $meetingId = $session->meeting_id;

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

        session()->flash('success', 'Phiên điểm danh đã được xóa thành công.');

        return redirect()->route('lecturer.attendance.index', ['ma_user' => auth()->id()]);
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

        $session = $this->ownedSession($this->sessionId)->load(['courseClass', 'meeting']);

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
            ->with(['classMember.user', 'classMember.profile'])
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query->whereHas('classMember.profile', fn (Builder $p) => $p->where('email', 'like', '%'.$this->search.'%')->orWhere('full_name', 'like', '%'.$this->search.'%'))
                        ->orWhereHas('classMember.user', fn (Builder $u) => $u->where('name', 'like', '%'.$this->search.'%'));
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
        $session = $this->ownedSession($this->sessionId)->load('meeting');
        $meeting = $session->meeting;

        if (! $meeting) {
            abort_if($session->status === 'closed', 403);
            return;
        }

        $meeting->closeIfExpired();
        $meeting->refresh();

        abort_if($meeting->status === 'closed' || $meeting->isExpired(), 403);
    }
}
