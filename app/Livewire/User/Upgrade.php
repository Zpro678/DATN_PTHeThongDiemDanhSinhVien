<?php

namespace App\Livewire\User;

use App\Models\Plan;
use App\Services\MomoService;
use App\Services\SubscriptionService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Trang đăng ký / nâng cấp gói dịch vụ cho người dùng (kiểu ChatGPT).
 *
 * Hiển thị các gói đang mở bán dưới dạng thẻ, đánh dấu gói hiện tại và cho
 * phép đăng ký gói mới. Gói FREE kích hoạt ngay; gói trả phí chuyển sang cổng
 * MoMo và chỉ được kích hoạt khi MoMo xác nhận thanh toán (qua IPN).
 */
class Upgrade extends Component
{
    /** Gói đang chờ người dùng xác nhận trong modal (null = không mở modal). */
    public ?int $confirmingPlanId = null;

    /** Phương thức thanh toán đang chọn trong modal: 'momo' | 'vnpay'. */
    public string $paymentMethod = 'momo';

    /**
     * Mở hộp xác nhận cho gói được chọn.
     */
    public function selectPlan(int $planId): void
    {
        $this->confirmingPlanId = $planId;
        $this->paymentMethod = 'momo'; // Mặc định MoMo mỗi lần mở modal.
    }

    /**
     * Đóng hộp xác nhận.
     */
    public function cancel(): void
    {
        $this->confirmingPlanId = null;
    }

    /**
     * Xử lý gói đã chọn.
     *
     * - Gói FREE (hoặc giá 0): kích hoạt ngay qua SubscriptionService.
     * - Gói trả phí: tạo giao dịch pending rồi chuyển sang cổng MoMo; việc kích
     *   hoạt thuê bao diễn ra ở MomoController::ipn khi thanh toán thành công.
     */
    public function subscribe(SubscriptionService $subscriptions, MomoService $momo, \App\Services\PayosService $payos): mixed
    {
        $plan = Plan::where('is_active', true)->find($this->confirmingPlanId);

        if (! $plan) {
            $this->confirmingPlanId = null;

            return null;
        }

        $user = auth()->user();

        // CÒN HẠN + đích hợp lệ (FREE hoặc ĐÚNG gói đã mua): đổi trực tiếp, KHÔNG thu phí,
        // giữ nguyên thời hạn đã mua. Gói trả phí khác chưa mua sẽ rơi xuống luồng thanh toán.
        if ($subscriptions->canSwitchFreeTo($user, $plan)) {
            $subscription = $subscriptions->switchTo($user, $plan);
            $this->confirmingPlanId = null;

            $isFree = $plan->plan_tier === 'FREE' || (float) $plan->price <= 0;
            $endLabel = $subscription?->end_date?->format('d/m/Y');

            if ($isFree) {
                session()->flash('status', $endLabel
                    ? "Đã chuyển về gói Miễn phí. Quyền lợi trả phí vẫn được giữ đến {$endLabel} — bạn có thể quay lại gói cũ bất cứ lúc nào trong thời gian này."
                    : 'Đã chuyển về gói Miễn phí.');
            } else {
                session()->flash('status', $endLabel
                    ? "Đã chuyển sang gói {$plan->name}. Thời hạn giữ nguyên đến {$endLabel}."
                    : "Đã chuyển sang gói {$plan->name}.");
            }

            return null;
        }

        // Không còn hạn + gói miễn phí: kích hoạt ngay, không qua thanh toán.
        if ($plan->plan_tier === 'FREE' || (float) $plan->price <= 0) {
            $subscriptions->activate($user, $plan);
            $this->confirmingPlanId = null;
            session()->flash('status', 'Đã chuyển về gói Miễn phí.');

            return null;
        }

        // VNPay chưa hoàn thiện - báo đang tích hợp, chưa xử lý thanh toán.
        if ($this->paymentMethod === 'vnpay') {
            $this->confirmingPlanId = null;
            session()->flash('status', 'Cổng VNPay đang được tích hợp. Vui lòng chọn phương thức khác.');

            return null;
        }

        // Nếu là PayOS, sinh mã giao dịch dạng số nguyên dương an toàn (9 chữ số). Ngược lại sinh dạng chuỗi.
        $isPayos = $this->paymentMethod === 'payos';
        
        $transactionCode = $isPayos 
            ? random_int(100000000, 999999999)
            : 'TXN'.now()->timestamp.Str::upper(Str::random(5));

        // Gói trả phí: tạo giao dịch chờ thanh toán.
        $transaction = $user->transactions()->create([
            'plan_id' => $plan->id,
            'amount' => $plan->price,
            'payment_method' => $isPayos ? 'PAYOS' : 'MOMO',
            'transaction_code' => (string) $transactionCode,
            'status' => 'pending',
            'expired_at' => now()->addMinutes(15),
            'created_at' => now(),
        ]);

        if ($isPayos) {
            $payUrl = $payos->createPaymentLink($transaction, "Nang cap goi {$plan->name}");
        } else {
            $payUrl = $momo->createPayment($transaction, "Nang cap goi {$plan->name}");
        }

        $this->confirmingPlanId = null;

        if (! $payUrl) {
            $transaction->update(['status' => 'failed']);
            session()->flash('error', 'Không tạo được thanh toán. Vui lòng thử lại sau.');

            return null;
        }

        $transaction->update(['payment_url' => $payUrl]);

        // Chuyển hướng trình duyệt sang trang thanh toán.
        return $this->redirect($payUrl);
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

        $currentPlanCode = $activeSubscription?->plan?->plan_tier ?? 'FREE';

        return view('livewire.user.upgrade', [
            'plans' => $plans,
            'currentPlanCode' => $currentPlanCode,
            'activeSubscription' => $activeSubscription,
            // Gói đã trả tiền (nếu còn hạn) — chỉ gói này + FREE được đổi qua lại miễn phí.
            'paidPlanId' => $activeSubscription?->paid_plan_id,
            'confirmingPlan' => $this->confirmingPlanId
                ? $plans->firstWhere('id', $this->confirmingPlanId)
                : null,
        ])->layout('layouts.upgrade');
    }
}
