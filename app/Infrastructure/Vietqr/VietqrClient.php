<?php

namespace App\Infrastructure\Vietqr;

use Illuminate\Support\Facades\Http;

// Tạo qr code thanh toán bằng bearer token lấy được ở class VietqrAuth
class VietqrClient
{
    // Kết nối thông tin api service vietqr ở config/services.php
    public function __construct(
        private VietqrAuth $auth,
        private ?string $qrUrl = null,
        private int $timeout = 10
    ) {
        $cfg = config('services.vietqr');
        $this->qrUrl   = $cfg['qr_url'];
        $this->timeout = (int)$cfg['timeout'];
    }


    // Gọi Generate QR API để tạo QR code    payload là dữ liệu đơn hàng gửi lên API
    public function generateCustomerQr(array $payload): array
    {
        // lấy token
        $token = $this->auth->getBearer();

        // Gọi api tạo qr code với token và payload
        $resp = Http::withToken($token)
            ->timeout($this->timeout)
            ->acceptJson()
            ->post($this->qrUrl, $payload);


        // Nếu token hết hạn, xin lại token và thử lại lần nữa
        if ($resp->status() === 401) {
            $this->auth->forget();
            $token = $this->auth->getBearer();
            $resp = Http::withToken($token)
                ->timeout($this->timeout)
                ->acceptJson()
                ->post($this->qrUrl, $payload);
        }
 
        // Nếu lỗi khi gọi generate qr api
        if (!$resp->successful()) {
            throw new \RuntimeException(
                'Generate QR failed: '.$resp->status().' '.json_encode($resp->json())
            );
        }

        // Nếu gọi tạo qr code thành công
        return $resp->json();
    }
}
