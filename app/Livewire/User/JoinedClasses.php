<?php

namespace App\Livewire\User;

use App\Models\CourseClass;
use App\Models\ClassMember;
use App\Services\AttendanceCalculator;
use App\Services\LectureManageStudentService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class JoinedClasses extends Component
{
    public string $statusFilter = 'Tất cả';

    public string $search = '';

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
    }

    #[\Livewire\Attributes\On('class-joined')]
    public function refreshClasses(): void
    {
        // Re-renders the component when a class is joined
    }

    public function render(): View
    {
        $query = CourseClass::whereHas('members', function ($q) {
            $q->where('user_id', auth()->id())->where('status', ClassMember::STATUS_ACTIVE);
        })
            ->with(['owner:id,name', 'members' => function ($q) {
                $q->where('user_id', auth()->id())->where('status', ClassMember::STATUS_ACTIVE);
            }]);

        if ($this->statusFilter === 'Đang học') {
            $query->where('status', 'active');
        } elseif ($this->statusFilter === 'Đã kết thúc') {
            $query->where('status', 'ended');
        }

        if (trim($this->search) !== '') {
            $search = str($this->search)->lower()->toString();
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(join_key) LIKE ?', ["%{$search}%"]);
            });
        }

        $courseClasses = $query->orderByDesc('created_at')->get();

        // Lấy tất cả member ID rồi query 1 lần (cùng nguồn với trang giảng viên).
        $memberIds = $courseClasses->map(fn ($c) => $c->members->first()?->id)->filter()->values()->all();
        $statsMap  = $memberIds
            ? app(LectureManageStudentService::class)->getStudentsAttendanceStats($memberIds)
            : [];

        $classes = $courseClasses->map(function ($class) use ($statsMap) {
            $member = $class->members->first();
            // $stats từ LectureManageStudentService — live query, tính theo TIẾT, chỉ buổi đã chốt.
            $stats   = $statsMap[$member?->id] ?? null;

            // Số TIẾT (lesson_count-weighted), chỉ buổi đã chốt — đồng nhất với trang giảng viên.
            $present = (int) ($stats['present_sessions'] ?? 0); // Tiết có mặt đúng giờ.
            $late    = (int) ($stats['late_sessions']    ?? 0); // Tiết đi muộn.
            $absent  = (int) ($stats['absent_sessions']  ?? 0); // Tiết vắng không phép.
            $excused = (int) ($stats['excused_sessions'] ?? 0); // Tiết vắng có phép.

            // % chuyên cần đã được tính sẵn qua AttendanceCalculator::percentOfPlanned trong service.
            $attendance = (int) ($stats['attendance_percent'] ?? 100);

            // Cấm thi / cảnh báo từ service (tính theo ngưỡng 80%/20% tổng tiết kế hoạch).
            $isBanned  = (bool) ($stats['is_banned']  ?? false);
            $isWarning = (bool) ($stats['is_warning'] ?? false);

            // Handle warning filter
            if ($this->statusFilter === 'Cảnh báo chuyên cần' && ! $isWarning) {
                return null;
            }

            return [
                'id' => $class->id,
                'title' => $class->name,
                'code' => $class->join_key,
                'join_code' => $class->join_key,
                'teacher' => $class->owner->name ?? 'Không xác định',
                'schedule' => $class->status === 'ended' ? 'Đã kết thúc' : 'Đang học',
                'attendance' => $attendance,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'excused' => $excused,
                'warning' => $isWarning,
                'banned' => $isBanned,
                'ended' => $class->status === 'ended',
            ];
        })->filter()->values()->toArray();

        return view('livewire.user.joined-classes', compact('classes'))
            ->layout('layouts.user', ['title' => 'Lớp tôi tham gia']);
    }
}
