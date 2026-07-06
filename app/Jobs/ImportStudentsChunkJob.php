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

use Illuminate\Bus\Batchable;

class ImportStudentsChunkJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $classId;
    protected array $rows;
    protected array $dateHeaders;
    protected array $meetingHeaders;
    protected int $emailColIndex;
    protected int $nameColIndex;
    protected int $codeColIndex;
    protected int $authUserId;
    protected ?string $importToken;
    protected bool $syncAttendance;

    /**
     * Create a new job instance.
     */
    public function __construct(string $classId, array $rows, array $dateHeaders, array $meetingHeaders, int $emailColIndex, int $nameColIndex, int $codeColIndex, int $authUserId, ?string $importToken = null, bool $syncAttendance = false)
    {
        $this->classId = $classId;
        $this->rows = $rows;
        $this->dateHeaders = $dateHeaders;
        $this->meetingHeaders = $meetingHeaders;
        $this->emailColIndex = $emailColIndex;
        $this->nameColIndex = $nameColIndex;
        $this->codeColIndex = $codeColIndex;
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
            $studentCode = $this->codeColIndex !== -1 ? trim((string) ($row[$this->codeColIndex] ?? '')) : null;
            $fullName = $this->nameColIndex !== -1 ? trim((string) ($row[$this->nameColIndex] ?? '')) : null;
            $email = $this->emailColIndex !== -1 ? trim((string) ($row[$this->emailColIndex] ?? '')) : null;

            if (empty($fullName)) {
                continue;
            }

            if (str_starts_with($fullName, '=')) {
                continue;
            }

            $activeCount = ClassMember::where('class_id', $this->classId)->where('status', ClassMember::STATUS_ACTIVE)->count();

            // Tìm thành viên theo email vì MSSV có thể bị bỏ.
            $member = null;
            if ($email) {
                $member = ClassMember::withTrashed()
                    ->where('class_id', $this->classId)
                    ->whereHas('profile', fn ($q) => $q->where('email', strtolower($email)))
                    ->first();
            }

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
            $profileData = [
                'full_name' => $fullName,
                'email' => $email ?: null,
            ];
            if ($studentCode) {
                $profileData['student_code'] = strtoupper($studentCode);
            }
            $member->syncProfile($profileData);

            // Gửi email thông báo được thêm vào lớp học
            if ($email && $isNewMember) {
                try {
                    Mail::to($email)->send(
                        new StudentImportNotificationMail(
                            $courseClass->name,
                            $courseClass->join_key,
                            $studentCode ? strtoupper($studentCode) : '—',
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

        }
    }
}
