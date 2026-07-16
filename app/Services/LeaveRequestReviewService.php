<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\LeaveRequestApproved;
use App\Notifications\LeaveRequestRejected;
use Illuminate\Support\Facades\DB;
use App\Jobs\SaveAuditLogJob;

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

            // Lấy tất cả các phiên thuộc về buổi học (Meeting)
            $sessions = \App\Models\ClassSession::where('meeting_id', $leaveRequest->class_meeting_id)->get();

            foreach ($sessions as $session) {
                $record = AttendanceRecord::withTrashed()->firstOrNew([
                    'class_session_id' => $session->id,
                    'class_member_id' => $leaveRequest->class_member_id,
                ]);

                if ($record->exists && $record->trashed()) {
                    $record->restore();
                }

                $record->fill([
                    'status' => 'excused',
                    'is_account' => $leaveRequest->classMember->user_id !== null,
                    'check_in_time' => null,
                    'note' => 'Đơn xin nghỉ đã được duyệt.',
                ])->save();
            }

            SaveAuditLogJob::dispatch([
                'user_id' => $reviewer->id,
                'class_id' => $leaveRequest->classMember->class_id,
                'action' => 'Duyệt đơn xin phép',
                'table_name' => 'leave_requests',
                'row_id' => $leaveRequest->id,
                'old_values' => json_encode(['status' => 'pending']),
                'new_values' => json_encode(['status' => 'approved', 'reviewed_by' => $reviewer->id]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        event(new \App\Events\ClassDataUpdated((string) $leaveRequest->classMember->class_id));

        // Đồng bộ lại kết quả tổng kết (vì có thể làm thay đổi trạng thái của buổi học thành 'excused')
        if ($leaveRequest->classMeeting) {
            \App\Services\AttendanceCalculator::syncSummaries($leaveRequest->classMeeting);
        }

        // Gửi thông báo duyệt đơn cho học viên
        $studentUser = $leaveRequest->classMember?->user;
        if ($studentUser) {
            $studentUser->notify(new LeaveRequestApproved($leaveRequest->fresh(['classMember.courseClass', 'classMeeting'])));
        }

        // Kiểm tra và gửi thông báo nếu sinh viên đã vượt ngưỡng vắng có phép
        app(\App\Services\NotificationService::class)->notifyExcusedAbsenceWarningIfExceeded($leaveRequest);
    }

    public function reject(LeaveRequest $leaveRequest, User $reviewer, string $reason): void
    {
        $leaveRequest->update([
            'status' => 'rejected',
            'rejected_reason' => $reason,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        SaveAuditLogJob::dispatch([
            'user_id' => $reviewer->id,
            'class_id' => $leaveRequest->classMember->class_id,
            'action' => 'Từ chối đơn xin phép',
            'table_name' => 'leave_requests',
            'row_id' => $leaveRequest->id,
            'old_values' => json_encode(['status' => 'pending']),
            'new_values' => json_encode(['status' => 'rejected', 'reviewed_by' => $reviewer->id, 'rejected_reason' => $reason]),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        event(new \App\Events\ClassDataUpdated((string) $leaveRequest->classMember->class_id));

        // Gửi thông báo cho học viên.
        $studentUser = $leaveRequest->classMember?->user;
        if ($studentUser) {
            $studentUser->notify(new LeaveRequestRejected($leaveRequest->fresh(['classMember.courseClass', 'classMeeting'])));
        }
    }
}
