<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\MomoService;
use App\Services\PayosService;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncPendingTransactions extends Command
{
    protected $signature = 'transactions:sync-status';
    protected $description = 'Đồng bộ trạng thái các giao dịch đang pending (qua API MoMo, PayOS)';

    public function handle(MomoService $momo, PayosService $payos, SubscriptionService $subscriptions)
    {
        $this->info('Starting sync for pending transactions...');

        $transactions = Transaction::where('status', 'pending')
            ->where('created_at', '<=', now()->subMinutes(15)) // Giao dịch tạo trên 15p
            ->with(['user', 'plan'])
            ->get();

        if ($transactions->isEmpty()) {
            $this->info('No pending transactions to sync.');
            return;
        }

        foreach ($transactions as $tx) {
            $this->info("Checking Transaction ID: {$tx->id} ({$tx->payment_method})");

            try {
                if ($tx->payment_method === 'MOMO') {
                    $res = $momo->checkTransactionStatus($tx->transaction_code, $tx->transaction_code);
                    if ($res && isset($res['resultCode'])) {
                        if ($res['resultCode'] == 0) {
                            $tx->update(['status' => 'success', 'payment_response' => json_encode($res), 'paid_at' => now()]);
                            if ($tx->plan) {
                                $subscriptions->activate($tx->user, $tx->plan);
                            }
                            $this->info(" -> Marked as SUCCESS");
                        } elseif (in_array($res['resultCode'], [1003, 1006, 1017, 1005, 1001, 1002, 1004])) {
                            $tx->update(['status' => 'failed', 'failure_reason' => $res['message'] ?? 'Thất bại từ hệ thống']);
                            $this->info(" -> Marked as FAILED");
                        }
                    }
                } elseif ($tx->payment_method === 'PAYOS') {
                    $info = $payos->getPaymentLinkInformation((int) $tx->transaction_code);
                    if ($info && isset($info['status'])) {
                        if (in_array($info['status'], ['PAID', 'SUCCESS'])) {
                            $tx->update(['status' => 'success', 'payment_response' => json_encode($info), 'paid_at' => now()]);
                            if ($tx->plan) {
                                $subscriptions->activate($tx->user, $tx->plan);
                            }
                            $this->info(" -> Marked as SUCCESS");
                        } elseif ($info['status'] === 'CANCELLED') {
                            $tx->update(['status' => 'failed', 'failure_reason' => 'Bị hủy (CANCELLED) trên hệ thống PayOS']);
                            $this->info(" -> Marked as CANCELLED");
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::error("Failed to sync transaction {$tx->id}: " . $e->getMessage());
                // Fallback nếu lỗi hoặc không tìm thấy
                if ($tx->expired_at && $tx->expired_at->isPast()) {
                    $tx->update(['status' => 'failed', 'failure_reason' => 'Hết hạn thanh toán và không lấy được trạng thái']);
                    $this->info(" -> Marked as FAILED (Expired & Error)");
                }
            }
        }

        $this->info('Done!');
    }
}
