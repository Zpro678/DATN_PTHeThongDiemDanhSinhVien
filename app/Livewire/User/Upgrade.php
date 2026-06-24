<?php

namespace App\Livewire\User;

use App\Models\Plan;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Trang đăng ký / nâng cấp gói dịch vụ cho người dùng (kiểu ChatGPT).
 *
 * Hiển thị các gói đang mở bán dưới dạng thẻ, đánh dấu gói hiện tại và cho
 * phép đăng ký gói mới. Hiện tại kích hoạt gói ngay sau khi xác nhận; phần
 * thanh toán sẽ được tích hợp sau.
 */
class Upgrade extends Component
{
    /** Gói đang chờ người dùng xác nhận trong modal (null = không mở modal). */
    public ?int $confirmingPlanId = null;

    /**
     * Mở hộp xác nhận cho gói được chọn.
     */
    public function selectPlan(int $planId): void
    {
        $this->confirmingPlanId = $planId;
    }

    /**
     * Đóng hộp xác nhận.
     */
    public function cancel(): void
    {
        $this->confirmingPlanId = null;
    }

    /**
     * Áp dụng gói đã chọn cho người dùng.
     *
     * Huỷ các gói đang hoạt động trước, rồi tạo thuê bao mới (trừ gói FREE -
     * gói mặc định nên chỉ cần huỷ gói trả phí hiện tại). Thanh toán làm sau.
     */
    public function subscribe(): void
    {
        $plan = Plan::where('is_active', true)->find($this->confirmingPlanId);

        if (! $plan) {
            $this->confirmingPlanId = null;

            return;
        }

        $user = auth()->user();

        // Kết thúc các gói đang hoạt động để chỉ còn một gói hiệu lực tại một thời điểm.
        $user->subscriptions()->where('status', 'active')->update(['status' => 'expired']);

        // FREE là gói mặc định (currentPlan() tự fallback) nên không cần tạo bản ghi.
        if ($plan->code !== 'FREE') {
            $user->subscriptions()->create([
                'plan_id' => $plan->id,
                'start_date' => now(),
                'end_date' => $plan->duration_days > 0 ? now()->addDays($plan->duration_days) : null,
                'status' => 'active',
            ]);
        }

        $this->confirmingPlanId = null;

        session()->flash('status', $plan->code === 'FREE'
            ? 'Đã chuyển về gói Miễn phí.'
            : "Đăng ký gói {$plan->name} thành công! Thanh toán sẽ được tích hợp sau.");
    }

    public function render(): View
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('price')
            ->get();

        // Truy vấn trực tiếp để luôn lấy trạng thái mới nhất sau khi đăng ký.
        $activeSubscription = auth()->user()
            ->subscriptions()
            ->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('end_date')->orWhere('end_date', '>=', now()))
            ->with('plan')
            ->latest('start_date')
            ->first();

        $currentPlanCode = $activeSubscription?->plan?->code ?? 'FREE';

        return view('livewire.user.upgrade', [
            'plans' => $plans,
            'currentPlanCode' => $currentPlanCode,
            'activeSubscription' => $activeSubscription,
            'confirmingPlan' => $this->confirmingPlanId
                ? $plans->firstWhere('id', $this->confirmingPlanId)
                : null,
        ])->layout('layouts.user', ['title' => 'Nâng cấp gói']);
    }
}
