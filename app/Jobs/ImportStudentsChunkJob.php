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

    protected int $classId;
    protected array $rows;
    protected array $dateHeaders;
    protected int $emailColIndex;
    protected int $authUserId;
    protected ?string $importToken;

    /**
     * Create a new job instance.
     */
    public function __construct(int $classId, array $rows, array $dateHeaders, int $emailColIndex, int $authUserId, ?string $importToken = null)
    {
        $this->classId = $classId;
        $this->rows = $rows;
        $this->dateHeaders = $dateHeaders;
        $this->emailColIndex = $emailColIndex;
        $this->authUserId = $authUserId;
        $this->importToken = $importToken;
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

            $activeCount = ClassMember::where('class_id', $this->classId)->where('status', 'active')->count();

            $member = ClassMember::withTrashed()
                ->where('class_id', $this->classId)
                ->where('student_code', strtoupper($studentCode))
                ->first();

            $user = null;
            if ($email) {
                $user = User::where('email', $email)->first();
            }

            if ($member) {
                $updateData = [
                    'full_name' => $fullName,
                    'status' => 'active',
                ];
                if ($email) {
                    $updateData['email'] = $email;
                }
                if ($user && is_null($member->user_id)) {
                    $updateData['user_id'] = $user->id;
                }
                $member->update($updateData);
                $member->restore();
            } else {
                if ($activeCount >= $maxStudents) {
                    continue;
                }

                $member = ClassMember::create([
                    'class_id' => $this->classId,
                    'full_name' => $fullName,
                    'email' => $email,
                    'student_code' => strtoupper($studentCode),
                    'user_id' => $user ? $user->id : null,
                    'status' => 'active',
                ]);
            }

            // Gửi email mời tạo tài khoản
            if ($email && !$user) {
                try {
                    Mail::to($email)->send(
                        new StudentImportNotificationMail(
                            $courseClass->name,
                            $courseClass->code,
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
                    'is_verified' => $member->user_id !== null,
                ]);
            }
        }

        if ($this->importToken) {
            $progress = \Illuminate\Support\Facades\Cache::get("import_progress_{$this->importToken}");
            if ($progress) {
                $progress['completed_chunks']++;
                $progress['processed_rows'] += count($this->rows);
                if ($progress['completed_chunks'] >= $progress['total_chunks']) {
                    $progress['status'] = 'completed';
                }
                \Illuminate\Support\Facades\Cache::put("import_progress_{$this->importToken}", $progress, now()->addMinutes(15));
            }
        }
    }
}
