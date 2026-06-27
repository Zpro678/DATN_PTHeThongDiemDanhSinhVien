<?php

namespace App\Models;

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
        'created_by', // ID chủ lớp tạo buổi học.
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
        return $this->belongsTo(User::class, 'created_by');
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

        return Carbon::parse($this->date->toDateString().' '.$time);
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
     * Chỉ chặn khi đã quá giờ kết thúc; buổi chốt thủ công nhưng còn trong giờ vẫn được mở lại.
     */
    public function canAddSession(): bool
    {
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

        $this->sessions()->where('status', '!=', 'closed')->update(['status' => 'closed']);
        $this->update(['status' => 'closed']);

        if ($this->relationLoaded('sessions')) {
            $this->sessions->each(fn (ClassSession $session) => $session->status = 'closed');
        }

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

        // Phiên thủ công mặc định "Có mặt" (giảng viên chỉ sửa ngoại lệ); phiên QR giữ "chưa điểm danh".
        $defaultStatus = empty($overrides['qr_token']) ? 'present' : 'pending';

        $this->courseClass->members()->where('status', 'active')->get()->each(fn ($member) => AttendanceRecord::query()->firstOrCreate([
            'class_session_id' => $session->id,
            'class_member_id' => $member->id,
        ], [
            'status' => $defaultStatus,
            'is_verified' => $member->user_id !== null,
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
     * Gộp trạng thái điểm danh theo từng sinh viên qua tất cả phiên của buổi.
     * Trả về ['present' => int, 'absent' => int] ở mức buổi.
     *
     * Một sinh viên được tính "có mặt" nếu có mặt/đi trễ/có phép ở bất kỳ phiên nào;
     * tính "vắng" nếu bị đánh vắng và không có mặt ở phiên nào.
     *
     * @param  Collection<int, AttendanceRecord>  $records  Bản ghi đã gom theo class_member_id.
     */
    public static function consolidateCounts(Collection $recordsByMember): array
    {
        $present = 0;
        $absent = 0;

        foreach ($recordsByMember as $memberRecords) {
            $statuses = $memberRecords->pluck('status');

            if ($statuses->contains(fn ($s) => in_array($s, ['present', 'late', 'excused'], true))) {
                $present++;
            } elseif ($statuses->contains('absent')) {
                $absent++;
            }
        }

        return ['present' => $present, 'absent' => $absent];
    }
}
