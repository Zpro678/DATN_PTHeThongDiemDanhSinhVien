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
        if ($plan->code === 'FREE') {
            return null;
        }

        return $user->subscriptions()->create([
            'plan_id' => $plan->id,
            'start_date' => now(),
            'end_date' => $plan->duration_days > 0 ? now()->addDays($plan->duration_days) : null,
            'status' => 'active',
        ]);
    }

    /**
     * Lấy gói hiện tại của người dùng; luôn trả về một Plan để gọi an toàn.
     */
    public function planFor(User $user): Plan
    {
        return $user->currentPlan() ?? new Plan([
            'code' => 'FREE',
            'name' => 'Miễn phí',
            'max_classes' => 2,
            'max_students_per_class' => 50,
            'max_gps_radius' => 50,
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
     * Quyền truy cập API (gói Doanh nghiệp).
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

    /**
     * Bán kính định vị GPS tối đa (m) theo gói.
     */
    public function maxGpsRadius(User $user): int
    {
        return (int) $this->planFor($user)->max_gps_radius;
    }
}
