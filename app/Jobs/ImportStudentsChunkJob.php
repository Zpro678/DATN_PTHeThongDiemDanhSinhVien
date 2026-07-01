<?php

namespace App\Jobs;

use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\AttendanceRecord;
use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\StudentImportNotificationMail;

class ImportStudentsChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $classId;
    protected array $rows;
    protected array $dateHeaders;
    protected array $meetingHeaders;
    protected int $emailColIndex;
    protected int $authUserId;
    protected ?string $importToken;
    protected bool $syncAttendance;

    /**
     * Create a new job instance.
     */
    public function __construct(string $classId, array $rows, array $dateHeaders, array $meetingHeaders, int $emailColIndex, int $authUserId, ?string $importToken = null, bool $syncAttendance = false)
    {
        $this->classId = $classId;
        $this->rows = $rows;
        $this->dateHeaders = $dateHeaders;
        $this->meetingHeaders = $meetingHeaders;
        $this->emailColIndex = $emailColIndex;
        $this->authUserId = $authUserId;
        $this->importToken = $importToken;
        $this->syncAttendance = $syncAttendance;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $courseClass = CourseClass::with('owner')->find($this->classId);
        if (!$courseClass) {
            return;
        }

        $allSessionIds = [];
        if ($this->syncAttendance) {
            $allSessionIds = ClassSession::where('class_id', $this->classId)->pluck('id')->all();
        }

        $maxStudents = $courseClass->owner
            ? app(\App\Services\SubscriptionService::class)->maxStudentsPerClass($courseClass->owner)
            : PHP_INT_MAX;

        foreach ($this->rows as $row) {
            $studentCode = trim((string) ($row[0] ?? ''));
            $fullName = trim((string) ($row[1] ?? ''));
            $email = $this->emailColIndex !== -1 ? trim((string) ($row[$this->emailColIndex] ?? '')) : null;

            if (empty($studentCode) && empty($fullName)) {
                continue;
            }

            if (preg_match('/^\d{1,2}[\/\-\.]\d{1,2}/', $studentCode) || str_starts_with($fullName, '=')) {
                continue;
            }

            if (empty($studentCode) || empty($fullName)) {
                continue;
            }

            $activeCount = ClassMember::where('class_id', $this->classId)->where('status', ClassMember::STATUS_ACTIVE)->count();

            // Tìm thành viên theo MSSV qua hồ sơ danh tính (class_member_profiles).
            $member = ClassMember::withTrashed()
                ->where('class_id', $this->classId)
                ->whereHas('profile', fn ($q) => $q->where('student_code', strtoupper($studentCode)))
                ->first();

            $user = null;
            if ($email) {
                $user = User::where('email', $email)->first();
            }

            $isNewMember = false;
            if ($member) {
                $updateData = ['status' => ClassMember::STATUS_ACTIVE];
                if ($user && is_null($member->user_id)) {
                    $updateData['user_id'] = $user->id;
                }
                $member->update($updateData);
                $member->restore();
            } else {
                if ($activeCount >= $maxStudents) {
                    continue;
                }

                $isNewMember = true;
                $member = ClassMember::create([
                    'class_id' => $this->classId,
                    'user_id' => $user ? $user->id : null,
                    'status' => ClassMember::STATUS_ACTIVE,
                ]);
            }

            // Lưu danh tính (MSSV/tên/email) vào hồ sơ thành viên.
            $member->syncProfile([
                'student_code' => strtoupper($studentCode),
                'full_name' => $fullName,
                'email' => $email ?: null,
            ]);

            // Gửi email thông báo được thêm vào lớp học
            if ($email && $isNewMember) {
                try {
                    Mail::to($email)->send(
                        new StudentImportNotificationMail(
                            $courseClass->name,
                            $courseClass->join_key,
                            strtoupper($studentCode),
                            $fullName,
                            $email
                        )
                    );
                } catch (\Exception $e) {
                    // Ghi log nếu lỗi gửi mail để tránh đứt luồng job
                    \Illuminate\Support\Facades\Log::error("Failed to send import email to {$email}: " . $e->getMessage());
                }
            }

            // Xử lý điểm danh
            $processedSessionIds = [];
            foreach ($this->dateHeaders as $colIndex => $sessionId) {
                $statusChar = mb_strtolower(trim((string) ($row[$colIndex] ?? '')));
                
                $status = 'pending';
                if ($statusChar === 'c') {
                    $status = 'present';
                } elseif ($statusChar === 'm') {
                    $status = 'late';
                } elseif ($statusChar === 'v') {
                    $status = 'absent';
                } elseif ($statusChar === 'p') {
                    $status = 'excused';
                } elseif ($statusChar === '') {
                    continue;
                }

                AttendanceRecord::updateOrCreate([
                    'class_session_id' => $sessionId,
                    'class_member_id' => $member->id,
                ], [
                    'status' => $status,
                    'is_account' => $member->user_id !== null,
                ]);
                
                $processedSessionIds[] = $sessionId;
            }

            // Đồng bộ các buổi điểm danh đã có nhưng không được cấu hình từ cột trong Excel
            if ($this->syncAttendance) {
                foreach ($allSessionIds as $sessId) {
                    if (!in_array($sessId, $processedSessionIds)) {
                        AttendanceRecord::firstOrCreate([
                            'class_session_id' => $sessId,
                            'class_member_id' => $member->id,
                        ], [
                            'status' => 'pending',
                            'is_account' => $member->user_id !== null,
                        ]);
                    }
                }
            }

            // Cập nhật thanh tiến trình ngay sau khi xong 1 sinh viên (tránh frontend bị timeout vì tưởng job chết)
            if ($this->importToken) {
                $progress = \Illuminate\Support\Facades\Cache::get("import_progress_{$this->importToken}");
                if ($progress) {
                    $progress['processed_rows'] += 1;
                    \Illuminate\Support\Facades\Cache::put("import_progress_{$this->importToken}", $progress, now()->addMinutes(15));
                }
            }
        }

        if ($this->importToken) {
            $progress = \Illuminate\Support\Facades\Cache::get("import_progress_{$this->importToken}");
            if ($progress) {
                $progress['completed_chunks']++;
                if ($progress['completed_chunks'] >= $progress['total_chunks']) {
                    $progress['status'] = 'completed';
                    // Đồng bộ tổng kết buổi học sau khi toàn bộ job hoàn thành
                    foreach (array_unique($this->meetingHeaders) as $meetingId) {
                        $meeting = \App\Models\ClassMeeting::with(['courseClass', 'sessions'])->find($meetingId);
                        if ($meeting) {
                            \App\Services\AttendanceCalculator::syncSummaries($meeting);
                        }
                    }
                }
                \Illuminate\Support\Facades\Cache::put("import_progress_{$this->importToken}", $progress, now()->addMinutes(15));
            }
        } else {
            // Không có token -> chỉ chạy 1 lần
            foreach (array_unique($this->meetingHeaders) as $meetingId) {
                $meeting = \App\Models\ClassMeeting::with(['courseClass', 'sessions'])->find($meetingId);
                if ($meeting) {
                    \App\Services\AttendanceCalculator::syncSummaries($meeting);
                }
            }
        }
    }
}
