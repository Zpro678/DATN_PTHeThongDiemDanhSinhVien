<?php

namespace App\Livewire\Lecturer\Attendance;

use App\Livewire\Lecturer\Attendance\Concerns\OwnsAttendanceSessions;
use App\Models\AttendanceRecord;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrAttendanceSession extends Component
{
    use OwnsAttendanceSessions, WithPagination;

    public int $sessionId;

    public string $search = '';

    public string $statusFilter = 'all';

    public bool $isClosed = false;

    public function mount(int $session): void
    {
        $model = $this->ownedSession($session);
        $this->sessionId = $model->id;
        $this->isClosed = $model->status === 'closed';
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
        $session->update([
            'qr_token' => Str::upper(Str::random(24)),
            'token_expires_at' => now()->addMinutes(15),
        ]);
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

    public function closeSession(): void
    {
        $session = $this->ownedSession($this->sessionId);
        $session->update(['status' => 'closed']);
        $this->isClosed = true;

        session()->flash('status', 'Phiên QR đã được chốt.');
    }

    public function deleteSession()
    {
        $session = $this->ownedSession($this->sessionId);
        
        abort_if($session->status === 'closed', 403, 'Không thể xóa phiên điểm danh đã chốt.');

        $classId = $session->class_id;
        $session->delete();

        session()->flash('status', 'Buổi điểm danh đã được xóa thành công.');
        return redirect()->route('lecturer.classes.show', $classId);
    }

    public function exportExcel()
    {
        $session = $this->ownedSession($this->sessionId)->load('courseClass');
        
        $date = $session->date->format('Y-m-d');
        $className = \Illuminate\Support\Str::slug($session->courseClass->name);
        $startLesson = 1;
        $endLesson = max(1, $session->lesson_count);
        $tiet = "Tiet_{$startLesson}-{$endLesson}";

        $fileName = "{$date}_{$className}_{$tiet}.xlsx";

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ClassSessionExport($this->sessionId),
            $fileName
        );
    }

    public function render(): View
    {
        $session = $this->ownedSession($this->sessionId)->load('courseClass');
        $attendanceLink = route('attendance.check-in.guest', ['token' => $session->qr_token]);
        $records = $session->attendanceRecords()
            ->with('classMember.user')
            ->when($this->statusFilter !== 'all', fn (Builder $query) => $query->where('status', $this->statusFilter))
            ->when($this->search !== '', function (Builder $query): void {
                $query->whereHas('classMember', function (Builder $query): void {
                    $query->where('student_code', 'like', '%'.$this->search.'%')
                        ->orWhere('full_name', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('id')
            ->paginate(10);

        $stats = $session->attendanceRecords()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $summary = [
            'present' => (int) ($stats['present'] ?? 0),
            'late' => (int) ($stats['late'] ?? 0),
            'excused' => (int) ($stats['excused'] ?? 0),
            'absent' => (int) ($stats['absent'] ?? 0),
            'pending' => (int) ($stats['pending'] ?? 0),
            'total' => $session->attendanceRecords()->count(),
        ];

        $summary['checked_in'] = $summary['present'] + $summary['late'];
        $summary['checked_in_percent'] = $summary['total'] > 0
            ? (int) round(($summary['checked_in'] / $summary['total']) * 100)
            : 0;

        $qrSvg = $this->qrSvg($attendanceLink);
        $qrCells = $this->fallbackQrCells($session->qr_token ?? $attendanceLink);

        return view('livewire.lecturer.attendance.qr-session', compact('session', 'records', 'attendanceLink', 'summary', 'qrSvg', 'qrCells'))
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
