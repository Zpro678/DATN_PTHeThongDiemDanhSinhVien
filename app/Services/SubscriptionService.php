<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;

/**
 * Cổng kiểm tra quyền lợi theo gói dịch vụ (plan gate).
 *
 * Tập trung mọi logic "gói này được làm gì" ở một nơi để tái sử dụng tại
 * các điểm nghiệp vụ (tạo lớp, xuất Excel, giới hạn GPS, số sinh viên...).
 * Mặc định người dùng chưa đăng ký = gói FREE (xem User::currentPlan()).
 */
class SubscriptionService
{
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
            return null;
        }

        return $user->subscriptions()->create([
            'plan_id' => $plan->id,
            'paid_plan_id' => $plan->id, // Ghi nhận đây là gói đã trả tiền.
            'start_date' => now(),
            'end_date' => $plan->duration_days > 0 ? now()->addDays($plan->duration_days) : null,
            'status' => 'active',
        ]);
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

        // Đổi gói đang dùng tại chỗ; giữ nguyên paid_plan_id & end_date đã mua.
        $current->update([
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);

        return $current;
    }

    /**
     * Lấy gói hiện tại của người dùng; luôn trả về một Plan để gọi an toàn.
     */
    public function planFor(User $user): Plan
    {
        return $user->currentPlan() ?? new Plan([
            'plan_tier' => 'FREE',
            'name' => 'Miễn phí',
            'max_classes' => 2,
            'max_students_per_class' => 50,
            'can_export_excel' => false,
            'api_access' => false,
        ]);
    }

    /**
     * Quyền xuất báo cáo Excel/CSV (tính năng từ gói Pro trở lên).
     */
    public function canExportExcel(User $user): bool
    {
        return (bool) $this->planFor($user)->can_export_excel;
    }

    /**
     * Quyền truy cập API (gói Cao cấp).
     */
    public function hasApiAccess(User $user): bool
    {
        return (bool) $this->planFor($user)->api_access;
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
     * Số lớp người dùng đang sở hữu.
     */
    public function ownedClassCount(User $user): int
    {
        return $user->ownedClasses()->count();
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


}
