<?php

namespace App\Models;

use App\Services\AttendanceCalculator;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class ClassMeeting extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'class_meetings';

    protected $fillable = [
        'class_id', // ID của lớp học.
        'user_Created', // ID chủ lớp tạo buổi học (theo DBML).
        'name', // Tên buổi học.
        'date', // Ngày diễn ra buổi học.
        'start_time', // Giờ bắt đầu.
        'end_time', // Giờ kết thúc.
        'status', // Trạng thái buổi active/closed.
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_Created');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ClassSession::class, 'meeting_id');
    }

    public function summaries(): HasMany
    {
        return $this->hasMany(MeetingSummary::class, 'meeting_id');
    }

    /**
     * Thời điểm buổi điểm danh kết thúc (ngày + giờ kết thúc).
     */
    public function endsAt(): ?Carbon
    {
        if (! $this->end_time) {
            return null;
        }

        $time = Carbon::parse($this->end_time)->format('H:i:s');
        $endsAt = Carbon::parse($this->date->toDateString().' '.$time);

        if ($this->start_time) {
            $startTime = Carbon::parse($this->start_time)->format('H:i:s');
            $startsAt = Carbon::parse($this->date->toDateString().' '.$startTime);

            if ($endsAt->lessThanOrEqualTo($startsAt)) {
                $endsAt->addDay();
            }
        }

        return $endsAt;
    }

    /**
     * Giờ kết thúc có rơi sang NGÀY HÔM SAU không (buổi qua đêm).
     * end_time chỉ lưu giờ:phút; khi ≤ start_time nghĩa là buổi kết thúc hôm sau
     * (khớp với logic +1 ngày trong endsAt()). Dùng để hiển thị nhãn "hôm sau",
     * tránh người dùng tưởng nhầm buổi đã "hết giờ".
     */
    public function endsNextDay(): bool
    {
        if (! $this->start_time || ! $this->end_time) {
            return false;
        }

        return Carbon::parse($this->end_time)->format('H:i:s')
            <= Carbon::parse($this->start_time)->format('H:i:s');
    }

    /**
     * Buổi đã quá giờ kết thúc hay chưa.
     */
    public function isExpired(): bool
    {
        $endsAt = $this->endsAt();

        return $endsAt !== null && now()->greaterThan($endsAt);
    }

    /**
     * Còn được thêm phiên điểm danh hay không.
     * Chặn khi: (1) buổi thuộc ngày trước hôm nay, hoặc (2) đã quá giờ kết thúc.
     * Buổi trong ngày, chốt thủ công nhưng còn trong giờ thì vẫn được mở lại.
     */
    public function canAddSession(): bool
    {
        // Buổi của ngày hôm trước coi như đã kết thúc, không mở lại/thêm phiên nữa.
        if ($this->date && $this->date->startOfDay()->lessThan(now()->startOfDay())) {
            return false;
        }

        return ! $this->isExpired();
    }

    /**
     * Tự động chốt buổi (và mọi phiên) khi đã quá giờ kết thúc.
     * Trả về true nếu vừa thực hiện chốt.
     */
    public function closeIfExpired(): bool
    {
        if ($this->status === 'closed' || ! $this->isExpired()) {
            return false;
        }

        $notifier = app(NotificationService::class);

        $sessionsToClose = $this->sessions()->where('status', '!=', 'closed')->get();
        foreach ($sessionsToClose as $session) {
            $session->update(['status' => 'closed']);
            // Mặc định những ai chưa điểm danh (pending) khi khóa phiên QR sẽ thành vắng (absent)
            if (!empty($session->qr_token)) {
                $session->attendanceRecords()->where('status', 'pending')->update(['status' => 'absent']);
                // Báo trạng thái điểm danh của phiên QR cho từng học viên.
                $notifier->notifyQrSessionResults($session);
            }
        }
        $this->update(['status' => 'closed']);

        if ($this->relationLoaded('sessions')) {
            $this->sessions->each(fn (ClassSession $session) => $session->status = 'closed');
        }

        // Dựng/đồng bộ bảng tổng kết rồi báo trạng thái tổng kết buổi cho học viên.
        AttendanceCalculator::syncSummaries($this);
        $notifier->notifyMeetingResults($this);

        return true;
    }

    /**
     * Tạo một phiên điểm danh mới thuộc buổi này và khởi tạo bản ghi cho học viên.
     *
     * @param  array<string, mixed>  $overrides  Ghi đè thuộc tính phiên (vd qr_token, gps...).
     */
    public function createSession(string $status, array $overrides = []): ClassSession
    {
        $session = $this->sessions()->create(array_merge([
            'class_id' => $this->class_id,
            'created_by' => auth()->id(),
            'name' => $this->name,
            'date' => $this->date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $status,
        ], $overrides));

        // Phiên thủ công mặc định "Vắng" (giảng viên đánh dấu ai CÓ MẶT); phiên QR giữ "chưa điểm danh".
        $defaultStatus = empty($overrides['qr_token']) ? 'absent' : 'pending';

        $this->courseClass->members()->where('status', ClassMember::STATUS_ACTIVE)->get()->each(fn ($member) => AttendanceRecord::query()->firstOrCreate([
            'class_session_id' => $session->id,
            'class_member_id' => $member->id,
        ], [
            'status' => $defaultStatus,
            'is_account' => $member->user_id !== null,
        ]));

        return $session;
    }

    /**
     * Buổi còn "đang mở" khi có ít nhất một phiên đang active.
     */
    public function isActive(): bool
    {
        return $this->sessions->contains(fn (ClassSession $s) => $s->status === 'active');
    }

    /**
     * Phiên đang mở gần nhất của buổi (để nút "Tiếp tục điểm danh").
     */
    public function activeSession(): ?ClassSession
    {
        return $this->sessions
            ->where('status', 'active')
            ->sortByDesc('id')
            ->first();
    }

    /**
     * Gộp trạng thái điểm danh theo từng sinh viên qua tất cả phiên của buổi
     * theo quy tắc phiên đầu/phiên cuối.
     *
     * @param  Collection<int, AttendanceRecord>  $records  Bản ghi đã gom theo class_member_id.
     */
    public static function consolidateCounts(Collection $recordsByMember): array
    {
        $counts = ['present' => 0, 'late' => 0, 'absent' => 0];

        foreach ($recordsByMember as $memberRecords) {
            $statuses = $memberRecords
                ->sortBy(fn ($record) => (int) ($record->class_session_id ?? $record->session_id ?? 0))
                ->pluck('status')
                ->all();

            $result = AttendanceCalculator::consolidateStatuses($statuses);
            $counts[$result['status']] = ($counts[$result['status']] ?? 0) + 1;
        }

        return $counts;
    }
}
