<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LeaveRequestReviewService
{
    public function approve(LeaveRequest $leaveRequest, User $reviewer): void
    {
        DB::transaction(function () use ($leaveRequest, $reviewer): void {
            $leaveRequest->update([
                'status' => 'approved',
                'rejected_reason' => null,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            $record = AttendanceRecord::withTrashed()->firstOrNew([
                'class_session_id' => $leaveRequest->class_session_id,
                'class_member_id' => $leaveRequest->class_member_id,
            ]);

            if ($record->exists && $record->trashed()) {
                $record->restore();
            }

            $record->fill([
                'status' => 'excused',
                'is_verified' => $leaveRequest->classMember->user_id !== null,
                'check_in_time' => null,
                'note' => 'Đơn xin nghỉ đã được duyệt.',
            ])->save();
        });
    }

    public function reject(LeaveRequest $leaveRequest, User $reviewer, string $reason): void
    {
        $leaveRequest->update([
            'status' => 'rejected',
            'rejected_reason' => $reason,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);
    }
}
