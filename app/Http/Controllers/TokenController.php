<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TokenController extends Controller
{
     public function generate(Request $request)
    {
        // lấy user và pass từ header Authorization
        $auth = $request->header('Authorization');

         //Kiểm tra có header đúng dạng Basic chưa
        if (!$auth || !str_starts_with($auth, 'Basic ')) {
            return response()->json(['error' => 'Authorization header is missing or invalid'], 400);
        }

        // giải mã header thành user và pass
        $base64 = substr($auth, 6);
        $decoded = base64_decode($base64, true) ?: '';
        [$user, $pass] = array_pad(explode(':', $decoded, 2), 2, '');



        // xác thực user và pass vừa giải mã với user và pass trong .env
        $validUser = config('services.vietqr.user_for_vietqr');
        $validPass = config('services.vietqr.pass_for_vietqr');
        if ($user !== $validUser || $pass !== $validPass) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        
        // cấp phát JWT token có thời hạn 300 giây
        $ttl = (int) config('services.vietqr.jwt_ttl', 300);
        $now = time();
        $payload = [
            'iss' => config('app.url'),
            'iat' => $now,
            'exp' => $now + $ttl,
            'username' => $user,
        ];

        // tạo JWT token có chữ kí secret trong .env
        $secret = config('services.vietqr.jwt_secret');
        $secretRaw = base64_decode($secret, true);    
        if ($secretRaw === false) {
            throw new \RuntimeException('Invalid base64 secret');
        }
        $jwt = JWT::encode($payload, $secretRaw, 'HS512');


        return response()->json([
            'access_token' => $jwt,
            'token_type'   => 'Bearer',
            'expires_in'   => $ttl,
        ], 200);
    }
}
