<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PayOS\Models\V2\PaymentRequests\CreatePaymentLinkRequest;
use PayOS\PayOS;

/**
 * Tích hợp cổng thanh toán VietQR (PayOS).
 */
class PayosService
{
    protected PayOS $payOS;

    public function __construct()
    {
        $c = config('services.payos');
        $this->payOS = new PayOS($c['client_id'], $c['api_key'], $c['checksum_key']);
    }

    /**
     * Tạo link thanh toán PayOS (trả về URL checkout).
     */
    public function createPaymentLink(Transaction $tx, string $description): ?string
    {
        $c = config('services.payos');

        // PayOS yêu cầu orderCode phải là số nguyên dương (<= 9007199254740991).
        $orderCode = (int) $tx->transaction_code;

        // Chuẩn hóa description thành ASCII không dấu, tối đa 25 ký tự an toàn
        $safeDescription = substr(preg_replace('/[^a-zA-Z0-9 ]/', '', Str::ascii($description)), 0, 25);
        if (empty(trim($safeDescription))) {
            $safeDescription = "Thanh toan don " . $orderCode;
        }

        $requestData = new CreatePaymentLinkRequest(
            orderCode: $orderCode,
            amount: (int) $tx->amount,
            description: trim($safeDescription),
            returnUrl: $c['return_url'],
            cancelUrl: $c['cancel_url']
        );

        try {
            $response = $this->payOS->paymentRequests->create($requestData);
            return $response->checkoutUrl ?? null;
        } catch (\Throwable $e) {
            Log::error('PayOS createPaymentLink failed: ' . $e->getMessage(), [
                'orderCode' => $orderCode,
                'description' => $safeDescription,
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Xác thực Webhook data trả về từ PayOS.
     * 
     * Trả về mảng dữ liệu webhook đã parse nếu thành công, ném Exception nếu thất bại.
     */
    public function verifyWebhookData(array $webhookData): array
    {
        return $this->payOS->webhooks->verify($webhookData, ['asArray' => true]);
    }
}

