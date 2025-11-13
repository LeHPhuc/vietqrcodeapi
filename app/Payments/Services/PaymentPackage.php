<?php
namespace App\Payments\Services;

use App\Infrastructure\Vietqr\VietqrClient;
use Illuminate\Support\Str;

// Nhận thông tin đơn hàng từ controller 
 
class PaymentPackage
{

    // Kết nối với VietqrClient ở App\Infrastructure\Vietqr
    public function __construct(private VietqrClient $vietqr) {}


    // Tạo QR code cho một gói đăng kí
    public function createVietqrForPackage(array $package): array
    {
        // validate content và orderId theo yêu cầu của VietQR
        $packageid = (string) ($package['id']);
        $content = ('SENTO_PACKAGE'.$packageid);
        $amount    = (int) ($package['price_override']);
        $bankCode  = 'MB';
        $bankAcc   = '0337367643';
        $accName   = 'LE HOANG PHUC';


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
            'orderId'      => $packageid,
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
            'packageId'         => $resp['orderId'] ?? null,
        ];
    }
}
