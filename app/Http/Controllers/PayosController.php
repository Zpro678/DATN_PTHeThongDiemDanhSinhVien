<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\PayosService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Nhận kết quả thanh toán từ PayOS.
 */
class PayosController extends Controller
{
    /**
     * Webhook nhận trạng thái thanh toán từ PayOS (Server-to-Server).
     */
    public function webhook(Request $request, PayosService $payos, SubscriptionService $subscriptions): JsonResponse
    {
        $body = $request->all();

        try {
            // Xác thực chữ ký webhook
            $webhookData = $payos->verifyWebhookData($body);
            
            // webhookData chứa thông tin giao dịch, ví dụ: orderCode, amount, code, success
            // code "00" có nghĩa là thành công
            if ($webhookData['code'] !== '00' && $webhookData['desc'] !== 'success') {
                return response()->json([
                    'error' => 0,
                    'message' => 'Not a successful payment event',
                    'data' => null
                ]);
            }
            
            $orderCode = $webhookData['orderCode'] ?? null;
            $amount = $webhookData['amount'] ?? 0;
            
            if (!$orderCode) {
                return response()->json(['error' => 1, 'message' => 'Missing orderCode', 'data' => null]);
            }

            $transaction = Transaction::where('transaction_code', (string) $orderCode)->first();

            if (! $transaction) {
                return response()->json(['error' => 1, 'message' => 'Order not found', 'data' => null]);
            }

            // Khoá bản ghi + chỉ xử lý khi còn pending
            DB::transaction(function () use ($webhookData, $transaction, $subscriptions, $amount) {
                $fresh = Transaction::whereKey($transaction->id)
                    ->where('status', 'pending')
                    ->lockForUpdate()
                    ->first();

                if (! $fresh) {
                    return; // Đã xử lý trước đó
                }

                $paidOk = (int) $amount === (int) $fresh->amount;

                if ($paidOk) {
                    $fresh->update([
                        'status' => 'success',
                        'gateway_transaction_id' => (string) ($webhookData['reference'] ?? ''),
                        'payment_response' => json_encode($webhookData),
                    ]);

                    if ($fresh->plan) {
                        $subscriptions->activate($fresh->user, $fresh->plan);
                    }
                } else {
                    $fresh->update(['status' => 'failed']);
                }
            });

            return response()->json([
                'error' => 0,
                'message' => 'Ok',
                'data' => $webhookData
            ]);

        } catch (\Throwable $e) {
            Log::error('PayOS webhook error: ' . $e->getMessage());
            return response()->json([
                'error' => 1,
                'message' => $e->getMessage(),
                'data' => null
            ]);
        }
    }

    /**
     * Người dùng được chuyển về sau khi hoàn tất hoặc huỷ thanh toán trên giao diện PayOS.
     */
    public function return(Request $request, PayosService $payos, SubscriptionService $subscriptions): RedirectResponse
    {
        // PayOS trả về cancel=true nếu người dùng bấm huỷ.
        // Còn nếu thanh toán thành công, thường sẽ có code=00 hoặc status=PAID.
        $isCancelled = $request->query('cancel') === 'true';
        $code = $request->query('code');
        $status = $request->query('status');
        $orderCode = $request->query('orderCode');
        
        $success = !$isCancelled && (in_array($code, ['00']) || in_array($status, ['PAID']));

        if ($success && $orderCode) {
            try {
                $paymentInfo = $payos->getPaymentLinkInformation((int) $orderCode);
                if ($paymentInfo && $paymentInfo['status'] === 'PAID') {
                    $transaction = Transaction::where('transaction_code', (string) $orderCode)->first();
                    if ($transaction && $transaction->status === 'pending') {
                        DB::transaction(function () use ($transaction, $subscriptions, $paymentInfo) {
                            $fresh = Transaction::whereKey($transaction->id)->where('status', 'pending')->lockForUpdate()->first();
                            if ($fresh && (int)$paymentInfo['amountPaid'] >= (int)$fresh->amount) {
                                $fresh->update([
                                    'status' => 'success',
                                    'gateway_transaction_id' => (string) ($paymentInfo['id'] ?? ''),
                                ]);
                                if ($fresh->plan) {
                                    $subscriptions->activate($fresh->user, $fresh->plan);
                                }
                            }
                        });
                    }
                }
            } catch (\Exception $e) {
                // Ignore and let webhook handle it if local verification fails
                Log::error('PayOS return verification error: ' . $e->getMessage());
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
