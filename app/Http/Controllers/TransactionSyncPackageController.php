<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Package;
use App\Models\Package_store;

class TransactionSyncPackageController extends Controller
{
     public function handle(Request $request)
    {
        // lấy Bearer token từ header Authorization và kiểm tra
        $auth = $request->header('Authorization');
        if (!$auth || !str_starts_with($auth, 'Bearer ')) {
            return response()->json([
                'error'        => true,
                'errorReason'  => 'INVALID_AUTH_HEADER',
                'toastMessage' => 'Authorization header is missing or invalid',
                'object'       => null
            ], 401);
        }
        $token = trim(substr($auth, 7));

        // Xác thực JWT token nhận được
        try {
            $secret = config('services.vietqr.jwt_secret'); //lấy secret để xác thực
            $secretRaw = base64_decode($secret, true);
            if ($secretRaw === false) {
                \Log::error('Invalid base64 secret configured.');
                return response()->json([
                    'error'        => true,
                    'errorReason'  => 'SERVER_MISCONFIG',
                    'toastMessage' => 'Server secret misconfigured',
                    'object'       => null
                ], 500);
            }
            \Firebase\JWT\JWT::$leeway = 10; // thêm thời gian trượt 10 giây để tránh lỗi đồng bộ thời gian
            JWT::decode($token, new Key($secretRaw, 'HS512'));
        } catch (\Throwable $e) {
            \Log::warning('JWT decode failed', ['msg'=>$e->getMessage()]);
            return response()->json([
                'error'        => true,
                'errorReason'  => 'INVALID_TOKEN',
                'toastMessage' => 'Invalid or expired token',
                'object'       => null
            ], 401);
        }

        // Lấy dữ liệu từ request và validate
        $data = $request->validate([
            'bankaccount'     => ['required','string'],
            'amount'          => ['required','numeric'],
            'transType'       => ['required','string'], 
            'content'         => ['required','string'],
            'transactionid'   => ['nullable','string'],
            'transactiontime' => ['nullable'], 
            'referencenumber' => ['nullable','string'],
            'orderId'         => ['nullable','string','max:19'], 
            'terminalCode'    => ['nullable','string'],
            'subTerminalCode' => ['nullable','string'],
            'serviceCode'     => ['nullable','string'],
            'urlLink'         => ['nullable','url'],
            'sign'            => ['nullable','string'],
        ]);


        // Xử lý cập nhật đơn hàng sau khi xác nhận thanh toán thành công sau đó trả về kết quả.
        try {
            $resp = DB::transaction(function () use ($data) {
                 // Tìm đơn hàng và lock không cho các transaction khác sửa đổi cùng lúc
                $package = Package_store::query()
                    ->lockForUpdate()
                    ->find((int) $data['orderId']);

                if (!$package) {
                    return response()->json([
                        'error'        => true,
                        'errorReason'  => 'PACKAGE_NOT_FOUND',
                        'toastMessage' => 'Package not found for given Id',
                        'object'       => null
                    ], 404);    
                
                }
              //  kiểm tra đơn hàng đã thanh toán chưa
                if ($package->status === 'active') {
                    return response()->json([
                        'error'        => false,
                        'errorReason'  => null,
                        'toastMessage' => 'Transaction already processed',
                    ], 200);
                }


                // kiểm tra số tiền thanh toán
                $incomingAmount = (int) $data['amount'];
                if ($incomingAmount <= 0) {
                    return response()->json([
                        'error'        => true,
                        'errorReason'  => 'INVALID_AMOUNT',
                        'toastMessage' => 'Amount must be positive',
                        'object'       => null
                    ], 400);
                }
                if ((int)$package->price_override !== $incomingAmount) {
                    return response()->json([
                        'error'        => true,
                        'errorReason'  => 'AMOUNT_MISMATCH',
                        'toastMessage' => 'Amount does not match order total',
                        'object'       => null
                    ], 400);
                }

                // kiểm tra kiểu giao dịch và số tiền để xác định đã thanh toán hay chưa
                 $isPaid = strtoupper($data['transType'] ?? '') === 'C' && $incomingAmount > 0 && (int)$package->price_override === $incomingAmount;

                // Cập nhật trạng thái đơn
                if ($isPaid) {
                    $package->status = 'active';
                } else {
                    $package->status = 'stop';
                }
                $package->save();

                 return response()->json([
                    'error'        => false,
                    'errorReason'  => null,
                    'toastMessage' => 'Transaction processed successfully',
                    'object'       => null,
                ], 200);
            });
            return $resp; 

        } catch (\Throwable $e) {
            return response()->json([
                'error'        => true,
                'errorReason'  => 'TRANSACTION_FAILED',
                'toastMessage' => $e->getMessage(),
                'object'       => null
            ], 400);
        }
    }
}
