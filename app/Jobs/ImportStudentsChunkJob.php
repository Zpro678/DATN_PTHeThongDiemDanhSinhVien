<?php

namespace App\Jobs;

use App\Models\ClassMember;
use App\Models\ClassMemberProfile;
use App\Models\ClassSession;
use App\Models\AttendanceRecord;
use App\Models\CourseClass;
use App\Models\Notification;
use App\Models\PendingImportNotification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

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
    protected int $authUserId;
    protected ?string $importToken;
    protected bool $syncAttendance;

    /**
     * Create a new job instance.
     */
    public function __construct(string $classId, array $rows, array $dateHeaders, array $meetingHeaders, int $emailColIndex, int $nameColIndex, int $authUserId, ?string $importToken = null, bool $syncAttendance = false)
    {
        $this->classId = $classId;
        $this->rows = $rows;
        $this->dateHeaders = $dateHeaders;
        $this->meetingHeaders = $meetingHeaders;
        $this->emailColIndex = $emailColIndex;
        $this->nameColIndex = $nameColIndex;
        $this->authUserId = $authUserId;
        $this->importToken = $importToken;
        $this->syncAttendance = $syncAttendance;

        // Cùng hàng đợi 'imports' với StartImportJob để cô lập với job realtime khác.
        $this->onQueue('imports');
    }

    /**
     * Middleware của job.
     *
     * WithoutOverlapping theo class_id: các chunk CÙNG một lớp chạy nối tiếp
     * (không song song). Lợi ích kép:
     *  - Công bằng: một tài khoản import lớn chỉ giữ 1 worker tại một thời điểm,
     *    các worker còn lại phục vụ tài khoản khác.
     *  - Đúng giới hạn SV/lớp: vì không có 2 chunk cùng lớp chạy song song nên
     *    bộ đếm activeCount trong bộ nhớ luôn chính xác, không bị vượt nhẹ.
     * Nếu đang bị khoá thì trả job về hàng đợi sau 10s; khoá tự hết hạn sau 15
     * phút để tránh kẹt nếu một worker chết giữa chừng.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('import-class:'.$this->classId))
                ->releaseAfter(10)
                ->expireAfter(900),
        ];
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

        // ===== TỐI ƯU N+1: nạp trước dữ liệu dùng chung cho CẢ CHUNK (1 query/loại)
        // thay vì query lặp lại trên từng dòng. Đây là mấu chốt để chịu tải 1k-10k. =====

        // Gom email (chuẩn hoá chữ thường) xuất hiện trong chunk.
        $chunkEmails = [];
        if ($this->emailColIndex !== -1) {
            foreach ($this->rows as $row) {
                $e = strtolower(trim((string) ($row[$this->emailColIndex] ?? '')));
                if ($e !== '') {
                    $chunkEmails[$e] = true;
                }
            }
        }
        $chunkEmails = array_keys($chunkEmails);

        // Map tài khoản theo email — 1 truy vấn cho cả chunk.
        $usersByEmail = !empty($chunkEmails)
            ? User::whereIn('email', $chunkEmails)->get()->keyBy(fn ($u) => strtolower((string) $u->email))
            : collect();

        // Map thành viên hiện có (kể cả đã xoá mềm) theo email hồ sơ — 1 truy vấn cho cả chunk.
        $membersByEmail = !empty($chunkEmails)
            ? ClassMember::withTrashed()
                ->where('class_id', $this->classId)
                ->whereHas('profile', fn ($q) => $q->whereIn('email', $chunkEmails))
                ->with('profile')
                ->get()
                ->keyBy(fn ($m) => strtolower((string) $m->profile?->email))
            : collect();

        // Map thành viên hiện có theo user_id (SV đã tham gia bằng mã, hồ sơ có thể không
        // trùng email) — cần để tránh vi phạm unique(class_id,user_id) khi bulk insert.
        $userIdsInChunk = $usersByEmail->pluck('id')->all();
        $membersByUserId = !empty($userIdsInChunk)
            ? ClassMember::withTrashed()
                ->where('class_id', $this->classId)
                ->whereIn('user_id', $userIdsInChunk)
                ->with('profile')
                ->get()
                ->keyBy('user_id')
            : collect();

        // Đếm thành viên đang hoạt động MỘT LẦN, sau đó tăng dần trong bộ nhớ
        // (trước đây COUNT lại trên mỗi dòng — cực tốn với danh sách lớn).
        $activeCount = ClassMember::where('class_id', $this->classId)
            ->where('status', ClassMember::STATUS_ACTIVE)
            ->count();

        // ===== Cấu trúc gom cho xử lý theo LÔ (bulk) — mấu chốt throughput 1k-10k =====
        $newMembersInsert = []; // hàng chờ INSERT vào class_members
        $pendingNew = [];       // metadata song song theo thứ tự insert: profile/user/email
        $queuedNewByEmail = []; // emailKey -> chỉ số trong $pendingNew (dedup trong chunk)
        $attendancePlan = [];   // các dòng cần ghi điểm danh (giải quyết member sau khi có id)

        $newAccountUserIds = []; // user_id SV mới đã có tài khoản (điền sau khi materialize)
        $outboxRows = [];        // email chờ gửi cho SV mới chưa có tài khoản (điền sau)
        $importedCount = 0;      // Số SV được thêm/cập nhật thành công (để báo "Đã nhập N học viên").
        $now = now();

        // Chỉ cần ghi điểm danh khi file có cột buổi hoặc yêu cầu đồng bộ (thường import
        // lúc tạo lớp không có buổi -> bỏ qua hẳn nhánh này).
        $hasAttendanceWork = !empty($this->dateHeaders) || $this->syncAttendance;

        foreach ($this->rows as $row) {
            $fullName = $this->nameColIndex !== -1 ? trim((string) ($row[$this->nameColIndex] ?? '')) : null;
            $email = $this->emailColIndex !== -1 ? trim((string) ($row[$this->emailColIndex] ?? '')) : null;
            $emailKey = ($email !== null && $email !== '') ? strtolower($email) : null;

            if (empty($fullName) || str_starts_with($fullName, '=')) {
                continue;
            }

            $user = $emailKey ? $usersByEmail->get($emailKey) : null;

            // Tìm thành viên đã có: ưu tiên theo email hồ sơ, sau đó theo user_id.
            $member = $emailKey ? $membersByEmail->get($emailKey) : null;
            if (!$member && $user) {
                $member = $membersByUserId->get($user->id);
            }

            $profileData = [
                'full_name' => $fullName,
                'email' => $email ?: null,
            ];

            if ($member) {
                // ---- Thành viên ĐÃ CÓ: cập nhật tại chỗ (đường ít gặp: re-import) ----
                $wasActive = $member->status === ClassMember::STATUS_ACTIVE && !$member->trashed();

                $updateData = ['status' => ClassMember::STATUS_ACTIVE];
                if ($user && is_null($member->user_id)) {
                    $updateData['user_id'] = $user->id;
                }
                $member->update($updateData);
                if ($member->trashed()) {
                    $member->restore();
                }
                if (!$wasActive) {
                    $activeCount++;
                }

                $member->syncProfile($profileData);
                $importedCount++;

                if ($hasAttendanceWork) {
                    $attendancePlan[] = ['member' => $member, 'row' => $row];
                }

                continue;
            }

            // ---- Thành viên MỚI ----
            // Dedup trong chunk: email đã xếp hàng thì cập nhật hồ sơ (last-wins), không thêm mới.
            if ($emailKey !== null && isset($queuedNewByEmail[$emailKey])) {
                $idx = $queuedNewByEmail[$emailKey];
                $pendingNew[$idx]['profile'] = $profileData;
                if ($hasAttendanceWork) {
                    $attendancePlan[] = ['newIdx' => $idx, 'row' => $row];
                }
                continue;
            }

            if ($activeCount >= $maxStudents) {
                // Vượt giới hạn SV/lớp của gói: KHÔNG thêm SV mới. Đếm lại số bị bỏ qua
                // (theo import token) để báo cho chủ lớp khi import hoàn tất — trước đây
                // bỏ qua âm thầm nên chủ lớp không biết danh sách bị cắt bớt.
                // Các chunk cùng lớp chạy nối tiếp (WithoutOverlapping) nên đọc-ghi cache
                // theo token là an toàn, không cần khoá.
                if ($this->importToken) {
                    $skippedKey = 'import_skipped_' . $this->importToken;
                    cache()->put($skippedKey, (int) cache()->get($skippedKey, 0) + 1, now()->addHours(6));
                    cache()->put('import_skipped_limit_' . $this->importToken, $maxStudents, now()->addHours(6));
                }
                continue;
            }
            $activeCount++;

            $idx = count($newMembersInsert);
            $newMembersInsert[] = [
                'class_id' => $this->classId,
                'user_id' => $user?->id,
                'status' => ClassMember::STATUS_ACTIVE,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $pendingNew[$idx] = [
                'profile' => $profileData,
                'user' => $user,
                'email' => $email ?: null,
            ];
            if ($emailKey !== null) {
                $queuedNewByEmail[$emailKey] = $idx;
            }
            if ($hasAttendanceWork) {
                $attendancePlan[] = ['newIdx' => $idx, 'row' => $row];
            }
        }

        // ===== BULK INSERT thành viên mới + hồ sơ =====
        if (!empty($newMembersInsert)) {
            // Chèn hàng loạt rồi lấy lại id theo thứ tự chèn. An toàn nhờ: lọc theo class_id
            // (không lẫn lớp khác) + WithoutOverlapping đảm bảo không có 2 chunk cùng lớp
            // chạy song song. Lưu ý: bulk insert BỎ QUA Auditable (không ghi audit từng SV).
            $maxIdBefore = (int) ClassMember::withTrashed()->max('id');
            ClassMember::insert($newMembersInsert);

            $createdMembers = ClassMember::where('class_id', $this->classId)
                ->where('id', '>', $maxIdBefore)
                ->orderBy('id')
                ->get();

            $profilesInsert = [];
            foreach ($createdMembers as $pos => $m) {
                if (!isset($pendingNew[$pos])) {
                    continue;
                }
                $pendingNew[$pos]['member'] = $m;

                $p = $pendingNew[$pos]['profile'];
                $profilesInsert[] = [
                    'class_member_id' => $m->id,
                    'full_name' => $p['full_name'],
                    'email' => $p['email'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                // Gom thông báo theo kết quả cuối cùng (đã dedup).
                // - SV đã có tài khoản: thông báo in-app + email "đã được thêm vào lớp" (hướng dẫn đăng nhập).
                // - SV chưa có tài khoản: email hướng dẫn đăng ký.
                // Cả hai loại email đều đi qua Outbox để được tiết chế tốc độ (không bắn dồn SMTP).
                $accountUser = $pendingNew[$pos]['user'] ?? null;
                if (!empty($accountUser)) {
                    $newAccountUserIds[] = (int) $accountUser->id;
                }

                $notifyEmail = $p['email'] ?: ($accountUser->email ?? null);
                if (!empty($notifyEmail)) {
                    $outboxRows[] = [
                        'class_id' => $this->classId,
                        'email' => $notifyEmail,
                        'full_name' => $p['full_name'],
                        'class_name' => $courseClass->name,
                        'join_key' => $courseClass->join_key,
                        'has_account' => !empty($accountUser),
                        'status' => PendingImportNotification::STATUS_PENDING,
                        'attempts' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            foreach (array_chunk($profilesInsert, 500) as $pc) {
                ClassMemberProfile::insert($pc);
            }

            $importedCount += count($profilesInsert);
        }

        // Đếm dồn số SV nhập thành công theo import token (các chunk cùng lớp chạy nối tiếp nhờ
        // WithoutOverlapping nên đọc-ghi cache an toàn). Bước finalize đọc lại để báo "Đã nhập N học viên".
        if ($this->importToken && $importedCount > 0) {
            $key = 'import_success_' . $this->importToken;
            cache()->put($key, (int) cache()->get($key, 0) + $importedCount, now()->addHours(6));
        }

        // ===== Ghi điểm danh (chỉ khi có cột buổi / cần đồng bộ) =====
        foreach ($attendancePlan as $plan) {
            $member = $plan['member'] ?? ($pendingNew[$plan['newIdx']]['member'] ?? null);
            if (!$member) {
                continue;
            }
            $this->writeAttendanceForRow($member, $plan['row'], $allSessionIds);
        }

        // ===== Thông báo theo LÔ =====
        if (!empty($outboxRows)) {
            foreach (array_chunk($outboxRows, 500) as $oc) {
                PendingImportNotification::insert($oc);
            }
        }
        if (!empty($newAccountUserIds)) {
            $this->pushAddedToClassNotifications($courseClass, $newAccountUserIds, $now);
        }
    }

    /**
     * Ghi bản ghi điểm danh cho một dòng import (đọc trạng thái c/m/v/p ở các cột buổi).
     *
     * @param array<int, mixed> $row
     * @param array<int, mixed> $allSessionIds
     */
    private function writeAttendanceForRow(ClassMember $member, array $row, array $allSessionIds): void
    {
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

        // Đồng bộ các buổi đã có nhưng không được cấu hình từ cột trong Excel.
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

    /**
     * Tạo thông báo in-app "được thêm vào lớp" cho các SV đã có tài khoản (bulk insert).
     *
     * @param array<int, int> $userIds
     */
    private function pushAddedToClassNotifications(CourseClass $courseClass, array $userIds, \Illuminate\Support\Carbon $now): void
    {
        $userIds = array_values(array_unique($userIds));

        $message = "Bạn vừa được thêm vào lớp {$courseClass->name} ({$courseClass->join_key}).";
        $rows = [];
        foreach ($userIds as $uid) {
            $rows[] = [
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\AddedToClass',
                'notifiable_type' => User::class,
                'notifiable_id' => $uid,
                'data' => json_encode([
                    'title' => 'Bạn được thêm vào lớp học',
                    'message' => $message,
                    'url' => route('student.classes.show', ['ma_user' => $uid, 'courseClass' => $courseClass->id]),
                    'level' => 'success',
                    'class_id' => $courseClass->id,
                ], JSON_UNESCAPED_UNICODE),
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 100) as $batch) {
            Notification::insert($batch);
        }
    }
}
