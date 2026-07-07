<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\MomoService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Nhận kết quả thanh toán từ MoMo.
 *
 * - ipn():    MoMo gọi server-to-server (NGUỒN TIN CẬY) -> kích hoạt gói tại đây.
 * - return(): Trình duyệt người dùng được MoMo redirect về -> CHỈ hiển thị, không
 *             kích hoạt gói (vì query có thể bị giả mạo).
 */
class MomoController extends Controller
{
    public function ipn(Request $request, MomoService $momo, SubscriptionService $subscriptions): JsonResponse
    {
        $data = $request->all();

        // 1. Xác thực chữ ký để chắc chắn dữ liệu thực sự đến từ MoMo.
        if (! $momo->verifySignature($data)) {
            return response()->json(['resultCode' => 1, 'message' => 'Invalid signature']);
        }

        $transaction = Transaction::where('transaction_code', $data['orderId'] ?? '')->first();

        if (! $transaction) {
            return response()->json(['resultCode' => 1, 'message' => 'Order not found']);
        }

        // 2. Khoá bản ghi + chỉ xử lý khi còn pending (idempotent - MoMo có thể gọi lại nhiều lần).
        DB::transaction(function () use ($data, $transaction, $subscriptions) {
            $fresh = Transaction::whereKey($transaction->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (! $fresh) {
                return; // Đã xử lý trước đó.
            }

            $paidOk = (int) ($data['resultCode'] ?? -1) === 0
                && (int) ($data['amount'] ?? 0) === (int) $fresh->amount;

            if ($paidOk) {
                $fresh->update([
                    'status' => 'success',
                    'gateway_transaction_id' => (string) ($data['transId'] ?? ''),
                    'payment_response' => json_encode($data),
                ]);

                // Kích hoạt gói qua đúng service mà luồng FREE cũng dùng.
                if ($fresh->plan) {
                    $subscriptions->activate($fresh->user, $fresh->plan);
                }

                if ($fresh->coupon_id) {
                    $fresh->coupon?->increment('used_count');
                }
            } else {
                $fresh->update(['status' => 'failed']);
            }
        });

        // 3. Bắt buộc trả HTTP 200 + resultCode để MoMo ngừng gọi lại.
        return response()->json(['resultCode' => 0, 'message' => 'Received']);
    }

    public function return(Request $request, MomoService $momo, SubscriptionService $subscriptions): RedirectResponse
    {
        $success = (int) $request->query('resultCode', -1) === 0;
        $orderId = $request->query('orderId');

        if ($success && $orderId) {
            try {
                // Verify signature from redirect
                $data = $request->all();
                if ($momo->verifySignature($data)) {
                    // Check transaction status from MoMo to ensure it's actually paid
                    // In MomoService, requestId is the same as orderId
                    $momoStatus = $momo->checkTransactionStatus($orderId, $orderId);

                    if ($momoStatus && (int) ($momoStatus['resultCode'] ?? -1) === 0) {
                        $transaction = Transaction::where('transaction_code', (string) $orderId)->first();
                        
                        if ($transaction && $transaction->status === 'pending') {
                            DB::transaction(function () use ($transaction, $subscriptions, $momoStatus) {
                                $fresh = Transaction::whereKey($transaction->id)->where('status', 'pending')->lockForUpdate()->first();
                                if ($fresh && (int) ($momoStatus['amount'] ?? 0) === (int) $fresh->amount) {
                                    $fresh->update([
                                        'status' => 'success',
                                        'gateway_transaction_id' => (string) ($momoStatus['transId'] ?? ''),
                                        'payment_response' => json_encode($momoStatus),
                                    ]);
                                    if ($fresh->plan) {
                                        $subscriptions->activate($fresh->user, $fresh->plan);
                                    }
                                }
                            });
                        }
                    }
                }
            } catch (\Exception $e) {
                // Ignore and let IPN handle it if local verification fails
                \Illuminate\Support\Facades\Log::error('MoMo return verification error: ' . $e->getMessage());
            }
        }

        return redirect()
            ->route('upgrade')
            ->with(
                $success ? 'status' : 'error',
                $success
                    ? 'Thanh toán thành công! Gói của bạn đã được kích hoạt.'
                    : 'Thanh toán bị huỷ hoặc thất bại. Vui lòng thử lại.'
            );
    }
}
