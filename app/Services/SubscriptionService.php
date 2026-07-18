<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\PlanConfig;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;

/**
 * Cổng kiểm tra quyền lợi theo gói dịch vụ (plan gate).
 *
 * Tập trung mọi logic "gói này được làm gì" ở một nơi để tái sử dụng tại
 * các điểm nghiệp vụ (tạo lớp, xuất Excel, giới hạn GPS, số sinh viên...).
 * Mặc định người dùng chưa đăng ký = gói FREE (xem User::currentPlan()).
 */
class SubscriptionService
{
    /** Số ngày ân hạn cho phép người dùng tự chọn lớp giữ lại sau khi hạ gói. */
    public const GRACE_PERIOD_DAYS = 7;

    /**
     * Kích hoạt gói cho người dùng (nguồn sự thật duy nhất cho việc "đổi gói").
     *
     * Được gọi từ HAI nơi: component Livewire Upgrade (khi chọn gói FREE) và
     * MomoController::ipn (khi MoMo báo thanh toán thành công). Tập trung ở một
     * chỗ để mọi luồng kích hoạt đều: (1) kết thúc gói trả phí đang chạy, rồi
     * (2) tạo thuê bao mới - trừ FREE vốn là gói mặc định nên không cần bản ghi.
     *
     * @return Subscription|null Thuê bao mới tạo, hoặc null nếu chuyển về FREE.
     */
    public function activate(User $user, Plan $plan): ?Subscription
    {
        // Chỉ giữ một gói hiệu lực tại một thời điểm: kết thúc các gói đang active.
        $user->subscriptions()->where('status', 'active')->update(['status' => 'expired']);

        // FREE là gói mặc định (currentPlan() tự fallback) nên không tạo bản ghi.
        if ($plan->plan_tier === 'FREE') {
            $this->autoReactivateClasses($user);
            return null;
        }

        $sub = $user->subscriptions()->create([
            'plan_id'      => $plan->id,
            'paid_plan_id' => $plan->id, // Ghi nhận đây là gói đã trả tiền.
            'start_date'   => now(),
            'end_date'     => $plan->duration_days > 0 ? now()->addDays($plan->duration_days) : null,
            'status'       => 'active',
        ]);

        $this->autoReactivateClasses($user);

        return $sub;
    }

    /**
     * Người dùng còn "cửa sổ quyền lợi" đã mua đang hiệu lực (active + chưa hết hạn) hay không.
     *
     * Đúng cả khi họ đang tạm dùng FREE nhưng thời hạn đã mua vẫn còn.
     */
    public function hasActivePaidPlan(User $user): bool
    {
        return $user->activeSubscription()->exists();
    }

    /**
     * Có được ĐỔI MIỄN PHÍ sang $plan hay không (không cần thanh toán lại).
     *
     * Chỉ cho phép khi còn thời hạn đã mua VÀ đích đến là:
     *  - Gói FREE (tạm hạ cấp, vẫn giữ hạn), hoặc
     *  - ĐÚNG gói đã trả tiền (paid_plan_id) — tức quay lại gói mình đã mua.
     * Mọi gói trả phí KHÁC chưa mua đều phải thanh toán.
     */
    public function canSwitchFreeTo(User $user, Plan $plan): bool
    {
        $current = $user->activeSubscription()->first();

        if (! $current) {
            return false; // Hết hạn / chưa mua -> không có gì để đổi miễn phí.
        }

        return $plan->plan_tier === 'FREE'
            || (int) $plan->id === (int) $current->paid_plan_id;
    }

    /**
     * ĐỔI gói khi còn hạn — KHÔNG thu phí, cập nhật tại chỗ và GIỮ NGUYÊN end_date + paid_plan_id.
     *
     * Chỉ đổi "gói đang dùng" (plan_id): về FREE để tạm hạ cấp, hoặc quay lại đúng gói đã mua.
     * Nhờ giữ paid_plan_id nên về FREE vẫn nhớ được gói đã trả tiền để quay lại; còn muốn lên
     * gói trả phí khác thì phải mua (không đi qua hàm này).
     *
     * Khi hạ xuống gói có giới hạn lớp thấp hơn, tự động đặt thời gian ân hạn 7 ngày
     * để người dùng có thời gian tự chọn lớp giữ lại.
     *
     * @return Subscription|null Thuê bao đang chạy đã đổi gói, hoặc null nếu không còn hạn.
     */
    public function switchTo(User $user, Plan $plan): ?Subscription
    {
        $current = $user->activeSubscription()->first();

        // Không còn hạn -> coi như đăng ký mới (FREE no-op, trả phí cấp hạn đầy đủ).
        if (! $current) {
            return $this->activate($user, $plan);
        }

        // Đảm bảo chỉ một thuê bao hiệu lực.
        $user->subscriptions()
            ->where('status', 'active')
            ->where('id', '!=', $current->id)
            ->update(['status' => 'expired']);

        // Kiểm tra nếu gói mới có giới hạn lớp thấp hơn -> kích hoạt ân hạn.
        $newMaxClasses      = $plan->max_classes ?? null;
        $currentOwnedCount  = $this->ownedClassCount($user);
        $graceEndsAt        = $current->class_limit_grace_ends_at; // giữ nguyên nếu đang trong ân hạn

        if ($newMaxClasses !== null && $currentOwnedCount > $newMaxClasses) {
            // Chỉ đặt mới nếu chưa có hoặc đã qua (tránh reset đồng hồ đếm ngược).
            if (! $graceEndsAt || $graceEndsAt->isPast()) {
                $graceEndsAt = now()->addDays(self::GRACE_PERIOD_DAYS);
            }
        } else {
            // Không vượt giới hạn -> xóa ân hạn nếu có.
            $graceEndsAt = null;
        }

        // Đổi gói đang dùng tại chỗ; giữ nguyên paid_plan_id & end_date đã mua.
        $current->update([
            'plan_id'                   => $plan->id,
            'status'                    => 'active',
            'class_limit_grace_ends_at' => $graceEndsAt,
        ]);

        $this->autoReactivateClasses($user);

        return $current;
    }

    /**
     * Lấy gói hiện tại của người dùng; luôn trả về một Plan để gọi an toàn.
     *
     * Khi CSDL chưa có gói FREE, dựng một Plan ảo (chưa lưu). Các hạn mức nằm ở
     * quan hệ config (bảng plan_configs) chứ không phải cột của plans, nên phải
     * gắn kèm PlanConfig — nếu không, accessor max_classes/max_students_per_class
     * trả null và mọi giới hạn bị ép về 0 (chặn sạch giảng viên chưa có gói).
     */
    public function planFor(User $user): Plan
    {
        if ($plan = $user->currentPlan()) {
            return $plan;
        }

        $fallback = new Plan([
            'plan_tier' => Plan::TIER_FREE,
            'name'      => 'Miễn phí',
        ]);

        $fallback->setRelation('config', new PlanConfig([
            'max_classes'            => Plan::DEFAULT_MAX_CLASSES,
            'max_students_per_class' => Plan::DEFAULT_MAX_STUDENTS_PER_CLASS,
            'can_export_excel'       => false,
        ]));

        return $fallback;
    }

    /**
     * Quyền xuất báo cáo Excel/CSV (tính năng từ gói Pro trở lên).
     */
    public function canExportExcel(User $user): bool
    {
        return (bool) $this->planFor($user)->can_export_excel;
    }

    /**
     * Giới hạn số lớp được tạo theo gói (null = không giới hạn).
     */
    public function maxClasses(User $user): ?int
    {
        $max = $this->planFor($user)->max_classes;

        return $max === null ? null : (int) $max;
    }

    /**
     * Số lớp người dùng đang sở hữu (không bao gồm lớp đã xóa mềm).
     */
    public function ownedClassCount(User $user): int
    {
        return $user->ownedClasses()->where('status', '!=', 'archived')->count();
    }

    /**
     * Còn được tạo thêm bao nhiêu lớp (null = không giới hạn).
     */
    public function remainingClasses(User $user): ?int
    {
        $max = $this->maxClasses($user);

        if ($max === null) {
            return null;
        }

        return max(0, $max - $this->ownedClassCount($user));
    }

    /**
     * Có được tạo thêm lớp không (chưa chạm giới hạn của gói).
     */
    public function canCreateClass(User $user): bool
    {
        $remaining = $this->remainingClasses($user);

        return $remaining === null || $remaining > 0;
    }

    /**
     * Giới hạn số sinh viên mỗi lớp theo gói.
     */
    public function maxStudentsPerClass(User $user): int
    {
        return (int) $this->planFor($user)->max_students_per_class;
    }

    // =========================================================================
    // Grace Period (Ân hạn số lớp khi hạ cấp gói)
    // =========================================================================

    /**
     * Người dùng có đang trong thời gian ân hạn (số lớp vượt giới hạn gói mới) không?
     * Ân hạn = subscription còn active VÀ class_limit_grace_ends_at chưa qua.
     */
    public function isInGracePeriod(User $user): bool
    {
        $sub = $user->activeSubscription()->first();
        if (! $sub || ! $sub->class_limit_grace_ends_at) {
            return false;
        }

        return $sub->class_limit_grace_ends_at->isFuture();
    }

    /**
     * Thời điểm kết thúc ân hạn, hoặc null nếu không có.
     */
    public function gracePeriodEndsAt(User $user): ?Carbon
    {
        return $user->activeSubscription()->first()?->class_limit_grace_ends_at;
    }

    /**
     * Người dùng có đang vượt quá giới hạn số lớp của gói hiện tại không?
     */
    public function isOverClassLimit(User $user): bool
    {
        $max = $this->maxClasses($user);
        if ($max === null) {
            return false;
        }

        return $this->ownedClassCount($user) > $max;
    }

    /**
     * Người dùng cần phải vào trang chọn lớp để giữ lại không?
     * = Vượt giới hạn VÀ hết thời gian ân hạn (hoặc chưa từng được ân hạn).
     */
    public function needsClassSelection(User $user): bool
    {
        if (! $this->isOverClassLimit($user)) {
            return false;
        }

        // Còn trong ân hạn -> chưa cần chặn.
        if ($this->isInGracePeriod($user)) {
            return false;
        }

        return true;
    }

    /**
     * Tự động kích hoạt lại các lớp đã lưu trữ khi giới hạn của người dùng tăng lên.
     */
    public function autoReactivateClasses(User $user): void
    {
        $max = $this->maxClasses($user);
        if ($max === null) {
            // Không giới hạn -> kích hoạt lại toàn bộ lớp đã lưu trữ
            $user->ownedClasses()->where('status', 'archived')->update(['status' => 'active']);
            return;
        }

        $activeCount = $user->ownedClasses()->where('status', '!=', 'archived')->count();
        $slots = $max - $activeCount;

        if ($slots > 0) {
            // Lấy ra N lớp đã lưu trữ mới nhất để kích hoạt lại
            $archivedIds = $user->ownedClasses()
                ->where('status', 'archived')
                ->orderByDesc('created_at')
                ->limit($slots)
                ->pluck('id');

            if ($archivedIds->isNotEmpty()) {
                \App\Models\CourseClass::whereIn('id', $archivedIds)->update(['status' => 'active']);
            }
        }
    }
}
