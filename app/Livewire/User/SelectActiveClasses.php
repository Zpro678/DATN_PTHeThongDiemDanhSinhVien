<?php

namespace App\Livewire\User;

use App\Models\CourseClass;
use App\Services\AuditLogService;
use App\Services\SubscriptionService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Màn hình bắt buộc yêu cầu người dùng chọn các lớp muốn giữ lại
 * khi số lớp hiện có vượt quá giới hạn của gói sau khi hạ cấp.
 *
 * Các lớp không được chọn sẽ chuyển sang trạng thái 'archived'.
 */
class SelectActiveClasses extends Component
{
    /** Danh sách ID lớp được chọn để giữ lại (active). */
    public array $selectedIds = [];

    public function mount(): void
    {
        $user = auth()->user();
        $svc  = app(SubscriptionService::class);

        // Nếu không cần chọn (không vượt hạn) -> redirect về dashboard.
        if (! $svc->isOverClassLimit($user)) {
            $this->redirectRoute('managed-classes', ['ma_user' => $user->id], navigate: true);
            return;
        }

        // Mặc định chọn sẵn N lớp mới nhất (đúng bằng giới hạn gói).
        $max = $svc->maxClasses($user) ?? 0;
        $this->selectedIds = CourseClass::where('owner_user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit($max)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();
    }

    /**
     * Xác nhận lựa chọn: archive các lớp không nằm trong danh sách đã chọn.
     */
    public function confirm(): void
    {
        $user = auth()->user();
        $svc  = app(SubscriptionService::class);
        $max  = $svc->maxClasses($user);

        // Giới hạn số lớp có thể chọn không được vượt quá max.
        if ($max !== null && count($this->selectedIds) > $max) {
            $this->addError('selectedIds', "Bạn chỉ được chọn tối đa {$max} lớp.");
            return;
        }

        if ($max !== null && count($this->selectedIds) < 1) {
            $this->addError('selectedIds', 'Vui lòng chọn ít nhất 1 lớp để giữ lại.');
            return;
        }

        $allOwnedIds = CourseClass::where('owner_user_id', $user->id)->pluck('id')->map(fn ($id) => (string) $id);
        $toArchive   = $allOwnedIds->diff($this->selectedIds);

        // Active các lớp được chọn.
        if (!empty($this->selectedIds)) {
            CourseClass::where('owner_user_id', $user->id)
                ->whereIn('id', $this->selectedIds)
                ->where('status', 'archived')
                ->update(['status' => 'active']);
        }

        // Archive các lớp không được chọn.
        if ($toArchive->isNotEmpty()) {
            CourseClass::where('owner_user_id', $user->id)
                ->whereIn('id', $toArchive->values())
                ->update(['status' => 'archived']);

            app(AuditLogService::class)->log('classes_archived_downgrade', [
                'table_name' => 'classes',
                'new_values' => ['archived_ids' => $toArchive->values()->toArray()],
            ]);
        }

        // Xóa ân hạn (đã chọn xong không cần ân hạn nữa).
        $user->activeSubscription()->first()?->update(['class_limit_grace_ends_at' => null]);

        session()->flash('status', 'Đã lưu lựa chọn. Các lớp không được chọn đã được lưu trữ.');
        $this->redirectRoute('managed-classes', ['ma_user' => $user->id], navigate: true);
    }

    public function render(): View
    {
        $user = auth()->user();
        $svc  = app(SubscriptionService::class);

        $classes = CourseClass::where('owner_user_id', $user->id)
            ->withCount([
                'members as students_count' => fn ($q) => $q->where('status', \App\Models\ClassMember::STATUS_ACTIVE),
            ])
            ->orderByDesc('created_at')
            ->get();

        $max             = $svc->maxClasses($user);
        $gracePeriodEnds = $svc->gracePeriodEndsAt($user);
        $isInGrace       = $svc->isInGracePeriod($user);

        return view('livewire.user.select-active-classes', [
            'classes'         => $classes,
            'maxAllowed'      => $max,
            'gracePeriodEnds' => $gracePeriodEnds,
            'isInGrace'       => $isInGrace,
        ])->layout('layouts.user', ['title' => 'Chọn lớp giữ lại']);
    }
}
