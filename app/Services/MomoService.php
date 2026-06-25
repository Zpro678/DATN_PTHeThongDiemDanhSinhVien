<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Tích hợp cổng thanh toán MoMo (AIO - requestType captureWallet).
 *
 * Hai việc chính:
 *  - createPayment(): ký HMAC-SHA256 + gọi API MoMo để lấy payUrl redirect.
 *  - verifySignature(): xác thực chữ ký MoMo gửi về (IPN & redirect) chống giả mạo.
 *
 * Chữ ký là chỗ dễ sai nhất: chuỗi raw phải đúng THỨ TỰ ALPHABET của key và
 * không thừa/thiếu trường nào, sau đó hash_hmac('sha256', $raw, secretKey).
 */
class MomoService
{
    /**
     * Tạo đơn trên MoMo, trả về payUrl để redirect người dùng (null nếu lỗi).
     */
    public function createPayment(Transaction $tx, string $orderInfo): ?string
    {
        $c = config('services.momo');

        $orderId = $tx->transaction_code;          // Phải DUY NHẤT mỗi đơn.
        $requestId = $tx->transaction_code;        // Có thể trùng orderId.
        $amount = (string) (int) $tx->amount;      // Số nguyên VND, không thập phân.
        $extraData = base64_encode(json_encode([   // Để IPN biết kích hoạt đơn nào.
            'transaction_id' => $tx->id,
        ]));
        $redirectUrl = route('momo.return');
        $ipnUrl = $c['ipn_url'];
        // payWithMethod: trang thanh toán hiện đủ các phương thức (Ví MoMo, Thẻ ATM,
        // Thẻ Visa/Master) -> test được bằng thẻ test mà KHÔNG cần đăng nhập ví MoMo.
        $requestType = 'payWithMethod';

        // RAW phải đúng thứ tự alphabet của tên trường:
        $rawHash = "accessKey={$c['access_key']}&amount={$amount}&extraData={$extraData}"
            ."&ipnUrl={$ipnUrl}&orderId={$orderId}&orderInfo={$orderInfo}"
            ."&partnerCode={$c['partner_code']}&redirectUrl={$redirectUrl}"
            ."&requestId={$requestId}&requestType={$requestType}";

        $signature = hash_hmac('sha256', $rawHash, $c['secret_key']);

        $response = Http::acceptJson()->post($c['endpoint'], [
            'partnerCode' => $c['partner_code'],
            'accessKey' => $c['access_key'],
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature,
        ]);

        $payUrl = $response->json('payUrl');

        if (! $payUrl) {
            // Ghi log để debug khi chữ ký/tham số sai (MoMo trả resultCode != 0).
            Log::warning('MoMo createPayment failed', [
                'orderId' => $orderId,
                'response' => $response->json(),
            ]);
        }

        return $payUrl;
    }

    /**
     * Xác thực chữ ký MoMo gửi về (dùng cho cả IPN và redirect return).
     *
     * Bộ trường của response KHÁC lúc tạo đơn - cũng theo thứ tự alphabet.
     */
    public function verifySignature(array $d): bool
    {
        $c = config('services.momo');

        $rawHash = "accessKey={$c['access_key']}&amount={$d['amount']}&extraData={$d['extraData']}"
            ."&message={$d['message']}&orderId={$d['orderId']}&orderInfo={$d['orderInfo']}"
            ."&orderType={$d['orderType']}&partnerCode={$d['partnerCode']}&payType={$d['payType']}"
            ."&requestId={$d['requestId']}&responseTime={$d['responseTime']}"
            ."&resultCode={$d['resultCode']}&transId={$d['transId']}";

        $expected = hash_hmac('sha256', $rawHash, $c['secret_key']);

        return hash_equals($expected, $d['signature'] ?? '');
    }
}
