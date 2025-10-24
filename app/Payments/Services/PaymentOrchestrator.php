<?php
namespace App\Payments\Services;

use App\Infrastructure\Vietqr\VietqrClient;
use Illuminate\Support\Str;

// Nhận thông tin đơn hàng từ controller 
 
class PaymentOrchestrator
{

    // Kết nối với class VietqrClient ở App\Infrastructure\Vietqr
    public function __construct(private VietqrClient $vietqr) {}


    // Tạo QR code cho một đơn hàng
    public function createVietqrForOrder(array $order, array $store): array
    {
        // validate content và orderId theo yêu cầu của VietQR
        $content = ('SENTO_'.$order['order_code']);


        // tạo payload từ dữ lieu đơn hàng và thông tin cửa hàng nhận được từ controller
        $payload = [
            'bankCode'     => $store['bank_code'],
            'bankAccount'  => $store['bank_account_number'],
            'userBankName' => $store['bank_account_name'],
            'content'      => $content,
            'qrType'       => 0,             
            'amount'       => (int) $order['total_amount'], 
            'orderId'      => $order['id'],   
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
}
