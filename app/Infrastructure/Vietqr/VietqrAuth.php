<?php

namespace App\Infrastructure\Vietqr;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;


class VietqrAuth
{
    // Kết nối thông tin api service vietqr ở config/services.php
    public function __construct(
        private ?string $user = null,
        private ?string $pass = null,
        private ?string $tokenUrl = null,
        private int $timeout = 10
    ) {
        $cfg = config('services.vietqr');
        $this->user     = $cfg['user'];
        $this->pass     = $cfg['pass'];
        $this->tokenUrl = $cfg['token_url'];
        $this->timeout  = (int)$cfg['timeout'];
    }



    // Lấy Bearer token để tạo QR code
    public function getBearer(): string
    {
        return Cache::remember('vietqr:bearer', 300, function () {
            $resp = Http::withBasicAuth($this->user, $this->pass)
                ->timeout($this->timeout)
                ->acceptJson()
                ->post($this->tokenUrl);

            // Nếu lỗi khi gọi tokenUrl
            if (!$resp->successful()) {
                throw new \RuntimeException(
                    'Get Token failed: '.$resp->status().' '.json_encode($resp->json())
                );
            }

            // Nếu gọi token url thành công
            $data = $resp->json();
            return $data['access_token'] ?? throw new \RuntimeException('Missing access_token');
        });
    }

    public function forget(): void
    {
        Cache::forget('vietqr:bearer');
    }
}
