<?php
namespace App\Payments\Services;

use App\Infrastructure\Vietqr\VietqrClient;
use Illuminate\Support\Str;

// Nhận thông tin đơn hàng từ controller 
 
class PaymentOrchestrator
{

    // Kết nối với VietqrClient ở App\Infrastructure\Vietqr
    public function __construct(private VietqrClient $vietqr) {}


    // Tạo QR code cho một đơn hàng
    public function createVietqrForOrder(array $order, array $store): array
    {
        // validate content và orderId theo yêu cầu của VietQR
        $orderCode = (string) ($order['order_code']);
        $content = ('SENTO_'.$orderCode);
        $orderId   = $this->asciiLimit((string) $order['id'], 13);
        $amount    = (int) ($order['total_amount']);
        $bankCode  = $this->normalizeBankCode($store['bank_code']);
        $bankAcc   = $this->normalizeAccount($store['bank_account_number']);
        $accName = $this->asciiLimit((string) ($store['bank_account_name'] ?? ''), 50);



        foreach (['bankCode' => $bankCode, 'bankAccount' => $bankAcc, 'userBankName' => $accName, 'content' => $content, 'orderId' => $orderId] as $k => $v) {
            if ($v === null || $v === '') {
                throw new \InvalidArgumentException("Missing/invalid field: {$k}");
            }
        }
        if ($amount <= 0) {
            throw new \InvalidArgumentException("Invalid amount");
        }



        // tạo payload từ dữ lieu đơn hàng và thông tin cửa hàng nhận được từ controller
        $payload = [
            'bankCode'     => $bankCode,
            'bankAccount'  => $bankAcc,
            'userBankName' => $accName,
            'content'      => $content,
            'qrType'       => 0,
            'amount'       => $amount,  
            'orderId'      => $orderId,
            'transType'    => 'C',
        ];

        // Gọi function generateCustomerQr của class VietqrClient để tạo QR code
        $resp = $this->vietqr->generateCustomerQr($payload);

        return [
            'qrCode'          => $resp['qrCode'] ?? null,
            'imgId'           => $resp['imgId'] ?? null,
            'qrLink'            => $resp['qrLink'] ?? null,
            'transactionRefId' => $resp['transactionRefId'] ?? null,
            'content'         => $resp['content'] ?? null,
            'orderId'         => $resp['orderId'] ?? null,
        ];
    }





    // Giúp thống nhất dữ liệu ở mysql và postgres trên render

    private function asciiLimit(?string $s, int $limit): string
    {
        $s = trim((string) $s);                   
        if (function_exists('iconv')) {
            $tmp = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
            if (is_string($tmp) && $tmp !== '') {
                $s = $tmp;
            }
        }
        $s = preg_replace('/[^A-Za-z0-9 _-]/', '', $s);
        // if ($s === '') $s = $fallback;
        return substr($s, 0, $limit);
    }

    private function normalizeBankCode(?string $code): string
    {
        $code = strtoupper(trim((string) $code));
        return preg_replace('/[^A-Z0-9]/', '', $code);
    }

    private function normalizeAccount(?string $acc): string
    {
        $acc = preg_replace('/\s+/', '', (string) $acc);
        return preg_replace('/\D/', '', $acc);
    }
}
