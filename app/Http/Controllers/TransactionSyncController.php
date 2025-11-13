<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\PaymentOrders;
use App\Models\Package;
use App\Models\Package_store;

class TransactionSyncController extends Controller
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

        // phân tích content để xác định là package hay order sau đó sử lí nghiệp vụ sau thanh toán
        $content = strtoupper($data['content'] ?? '');

        if (str_contains($content, 'SENTO_ORDER')) {
            try {
                $resp = DB::transaction(function () use ($data) {
                    // Tìm đơn hàng và lock đơn hàng không cho các transaction khác sửa đổi cùng lúc
                    $order = Order::query()
                        ->lockForUpdate()
                        ->find((int) $data['orderId']);

                    if (!$order) {
                        return response()->json([
                            'error'        => true,
                            'errorReason'  => 'ORDER_NOT_FOUND',
                            'toastMessage' => 'Order not found for given orderId',
                            'object'       => null
                        ], 404);    
                    
                    }
                    //  kiểm tra đơn hàng đã thanh toán chưa
                    if ($order->order_status_payment === 'paid') {
                        return response()->json([
                            'error'        => false,
                            'errorReason'  => null,
                            'toastMessage' => 'Transaction already processed',
                            'object'       => ['reftransactionid' => 'order_'.$order->id],
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
                    if ((int)$order->total_amount !== $incomingAmount) {
                        return response()->json([
                            'error'        => true,
                            'errorReason'  => 'AMOUNT_MISMATCH',
                            'toastMessage' => 'Amount does not match order total',
                            'object'       => null
                        ], 400);
                    }

                    // kiểm tra kiểu giao dịch và số tiền để xác định đã thanh toán hay chưa
                    $isPaid = strtoupper($data['transType'] ?? '') === 'C' && $incomingAmount > 0 && (int)$order->total_amount === $incomingAmount;

                    // Cập nhật trạng thái đơn
                    if ($isPaid) {
                        $order->order_status = 'success';
                    } else {
                        $order->order_status = 'pending';
                    }
                    if ($isPaid) {
                        $order->order_status_payment = 'paid';
                    } else {
                        $order->order_status_payment = 'unpaid';
                    }
                    $order->save();


                    // Lưu thông tin giao dịch vào bảng payment_orders
                    $payment = PaymentOrders::updateOrCreate(
                        ['order_id' => $data['orderId']],
                        [
                            'bank_account_number'      => $data['bankaccount']     ?? null,
                            'amount'            => $incomingAmount,
                            'content'           => $data['content']         ?? null,
                            'transaction_id'    => $data['transactionid']   ?? null,
                            'reference_number'  => $data['referencenumber'] ?? null,
                            'transaction_time'  => $data['transactiontime'] ?? null,
                            'qrLink'          => $data['urlLink']         ?? null,
                        ]
                    );

                    return response()->json([
                        'error'        => false,
                        'errorReason'  => null,
                        'toastMessage' => 'Transaction processed successfully',
                        'type'       => 'order',
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
        } elseif (str_contains($content, 'SENTO_PACKAGE')) {
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
                        'type'       =>'package',
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
        // Xử lý cập nhật đơn hàng sau khi xác nhận thanh toán thành công sau đó trả về kết quả.
        
    }
}
