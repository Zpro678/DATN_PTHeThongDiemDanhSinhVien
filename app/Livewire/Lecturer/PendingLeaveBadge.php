<?php

namespace App\Livewire\Lecturer;

use App\Models\CourseClass;
use App\Models\LeaveRequest;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Chấm đỏ đếm đơn xin nghỉ đang "chờ duyệt" trên tab "Duyệt đơn xin nghỉ" (drawer & tab ngang).
 * Tự cập nhật realtime qua kênh "class.{id}" của mọi lớp giảng viên quản lý — không cần F5.
 */
class PendingLeaveBadge extends Component
{
    /** @return array<int, string> */
    public function realtimeChannels(): array
    {
        $prefix = (string) config('database.redis.options.prefix');

        return CourseClass::query()
            ->managedBy(auth()->id())
            ->pluck('id')
            ->map(fn ($id) => $prefix . 'class.' . $id)
            ->all();
    }

    public function render(): View
    {
        $pendingCount = LeaveRequest::query()
            ->where('status', 'pending')
            ->whereHas('classMember.courseClass', fn ($query) => $query->managedBy(auth()->id()))
            ->count();

        return view('livewire.lecturer.pending-leave-badge', compact('pendingCount'));
    }
}
