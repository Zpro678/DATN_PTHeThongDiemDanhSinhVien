<?php

namespace App\Livewire\Lecturer\Attendance;

use App\Exports\ClassSessionExport;
use App\Livewire\Lecturer\Attendance\Concerns\OwnsAttendanceSessions;
use App\Models\AttendanceRecord;
use App\Services\NotificationService;
use App\Services\SubscriptionService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrAttendanceSession extends Component
{
    use OwnsAttendanceSessions, WithPagination;

    public int $sessionId;

    public string $search = '';

    public string $statusFilter = 'all';

    public array $draftStatuses = [];
    public array $draftNotes = [];

    public bool $isClosed = false;
    
    public string $qrAnimationStr = '';

    public function mount(int $session): void
    {
        $model = $this->ownedSession($session);
        // Buổi hết giờ thì tự động chốt phiên này.
        $model->meeting?->closeIfExpired();
        $model->refresh();

        $this->sessionId = $model->id;
        $this->isClosed = $model->status === 'closed';

        $this->initDrafts();
    }

    private function initDrafts(): void
    {
        $records = AttendanceRecord::query()
            ->where('class_session_id', $this->sessionId)
            ->whereHas('classMember')
            ->get(['id', 'status', 'note']);

        foreach ($records as $record) {
            $this->draftNotes[$record->id] = $record->note ?? '';
        }
    }

    public function setStatusFilter(string $status): void
    {
        abort_unless(in_array($status, ['all', 'pending', 'present', 'late', 'absent', 'excused'], true), 422);

        $this->statusFilter = $status;
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->statusFilter = 'all';
    }

    public function refreshToken(): void
    {
        $session = $this->ownedSession($this->sessionId);
        
        // Keep the token valid while the session is running
        $session->update([
            'token_expires_at' => now()->addMinutes(15),
        ]);
        
        // Change the query parameter to force the QR code image to change
        $this->qrAnimationStr = Str::random(8);
    }

    public function setStatus(int $recordId, string $status): void
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
            'is_account' => $record->classMember->user_id !== null,
        ]);
    }

    #[On('echo:attendance.{sessionId},StudentCheckedIn')]
    public function onStudentCheckedIn(): void
    {
        // Livewire v3 automatically re-renders the component when this is hit
    }

    public function markAllPresent(): void
    {
        $this->ensureSessionIsOpen();

        $records = AttendanceRecord::query()
            ->where('class_session_id', $this->sessionId)
            ->whereHas('classSession.courseClass', fn ($query) => $query->where('owner_user_id', auth()->id()))
            ->where('status', 'pending')
            ->get();

        foreach ($records as $record) {
            $record->update([
                'status' => 'present',
                'check_in_time' => now(),
            ]);
        }

        session()->flash('success', 'Đã đánh dấu tất cả học viên chưa điểm danh là có mặt.');
    }

    public function closeSession(): void
    {
        $session = $this->ownedSession($this->sessionId);
        $session->update(['status' => 'closed']);
        
        // Mặc định những ai chưa điểm danh (pending) khi khóa phiên QR sẽ thành vắng (absent)
        $session->attendanceRecords()->where('status', 'pending')->update(['status' => 'absent']);
        
        $this->isClosed = true;

        app(NotificationService::class)->attendanceSessionClosed((int) auth()->id(), $session, isQr: true);

        $this->dispatch('toast', message: 'Phiên QR đã được chốt.', type: 'success');
    }

    /**
     * Lưu phiên QR rồi quay về trang chi tiết buổi.
     * Điểm danh QR đã được ghi trực tiếp khi sinh viên quét, nên ở đây chỉ điều hướng về buổi.
     * Không chốt sổ: phiên vẫn mở, chuyên cần sẽ tổng hợp khi buổi kết thúc.
     */
    public function saveSession(): void
    {
        $this->ensureSessionIsOpen();

        $records = AttendanceRecord::query()
            ->where('class_session_id', $this->sessionId)
            ->whereHas('classSession.courseClass', fn ($query) => $query->where('owner_user_id', auth()->id()))
            ->get();

        foreach ($records as $record) {
            if (isset($this->draftNotes[$record->id])) {
                $note = trim((string) $this->draftNotes[$record->id]);
                if ($note !== ($record->note ?? '')) {
                    $record->update(['note' => $note !== '' ? $note : null]);
                }
            }
        }

        $meetingId = $this->ownedSession($this->sessionId)->meeting_id;

        session()->flash('status', 'Đã lưu phiên điểm danh.');

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
        $session->delete();

        session()->flash('success', 'Phiên điểm danh đã được xóa thành công.');

        return redirect()->route('lecturer.attendance.index', ['ma_user' => auth()->id()]);
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
        
        $attendanceLink = route('attendance.check-in.guest', ['token' => $session->qr_token]);
        if ($this->qrAnimationStr) {
            $attendanceLink .= '?r=' . $this->qrAnimationStr;
        }
        $records = $session->attendanceRecords()
            ->whereHas('classMember')
            ->with('classMember.user')
            ->when($this->statusFilter !== 'all', fn (Builder $query) => $query->where('status', $this->statusFilter))
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query->whereHas('classMember.profile', fn (Builder $p) => $p->where('student_code', 'like', '%'.$this->search.'%')->orWhere('full_name', 'like', '%'.$this->search.'%'))
                        ->orWhereHas('classMember.user', fn (Builder $u) => $u->where('name', 'like', '%'.$this->search.'%'));
                });
            })
            ->orderBy('id')
            ->paginate(10);

        $stats = $session->attendanceRecords()
            ->whereHas('classMember')
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $fraudStats = $session->attendanceRecords()
            ->whereHas('classMember')
            ->selectRaw('gps_fraud_flag, COUNT(*) as aggregate')
            ->whereNotNull('gps_fraud_flag')
            ->groupBy('gps_fraud_flag')
            ->pluck('aggregate', 'gps_fraud_flag');

        $summary = [
            'present' => (int) ($stats['present'] ?? 0),
            'late' => (int) ($stats['late'] ?? 0),
            'excused' => (int) ($stats['excused'] ?? 0),
            'absent' => (int) ($stats['absent'] ?? 0),
            'pending' => (int) ($stats['pending'] ?? 0),
            'total' => $session->attendanceRecords()->whereHas('classMember')->count(),
        ];

        $summary['checked_in'] = $summary['present'] + $summary['late'];
        $summary['checked_in_percent'] = $summary['total'] > 0
            ? (int) round(($summary['checked_in'] / $summary['total']) * 100)
            : 0;

        $qrSvg = $this->qrSvg($attendanceLink);
        $qrCells = $this->fallbackQrCells($attendanceLink);

        $canExportExcel = app(SubscriptionService::class)->canExportExcel(auth()->user()); // Quyền xuất Excel theo gói (Pro trở lên).

        return view('livewire.lecturer.attendance.qr-session', compact('session', 'records', 'attendanceLink', 'summary', 'fraudStats', 'qrSvg', 'qrCells', 'canExportExcel'))
            ->layout('layouts.user', ['title' => 'Điểm danh QR']);
    }

    private function ensureSessionIsOpen(): void
    {
        abort_if($this->ownedSession($this->sessionId)->status === 'closed', 403);
    }

    private function qrSvg(string $attendanceLink): ?string
    {
        if (! class_exists(QrCode::class)) {
            return null;
        }

        try {
            return (string) QrCode::format('svg')
                ->size(280)
                ->margin(1)
                ->generate($attendanceLink);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<int, bool>
     */
    private function fallbackQrCells(string $seed): array
    {
        $hash = hash('sha256', $seed, true);
        $cells = [];

        for ($index = 0; $index < 29 * 29; $index++) {
            $x = $index % 29;
            $y = intdiv($index, 29);
            $inFinder = $this->inFinderPattern($x, $y);

            if ($inFinder !== null) {
                $cells[] = $inFinder;

                continue;
            }

            $byte = ord($hash[$index % strlen($hash)]);
            $cells[] = (($byte + $x * 7 + $y * 13) % 5) < 2;
        }

        return $cells;
    }

    private function inFinderPattern(int $x, int $y): ?bool
    {
        foreach ([[0, 0], [22, 0], [0, 22]] as [$originX, $originY]) {
            if ($x < $originX || $x > $originX + 6 || $y < $originY || $y > $originY + 6) {
                continue;
            }

            $edge = $x === $originX || $x === $originX + 6 || $y === $originY || $y === $originY + 6;
            $center = $x >= $originX + 2 && $x <= $originX + 4 && $y >= $originY + 2 && $y <= $originY + 4;

            return $edge || $center;
        }

        return null;
    }
}
