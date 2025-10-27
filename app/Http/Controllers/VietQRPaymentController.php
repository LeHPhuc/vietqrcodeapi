<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Payments\Services\PaymentOrchestrator;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VietQRPaymentController extends Controller
{
    public function __construct(private PaymentOrchestrator $svc) {}

    public function createVietqr(Request $request)
    {
      $request->validate(['order_id' => ['required','integer','min:1']]);
      $orderId = (int) $request->input('order_id');

      $order = Order::query()
        ->select(['id','order_code','store_id','total_amount'])
        ->find($orderId);

      if (!$order) {
        return response()->json(['message' => 'Order not found'], 404);
      }

      $store = Store::query()
        ->select(['id','bank_code','bank_account_number','bank_account_name'])
        ->find($order->store_id);

      if (!$store) {
        return response()->json(['message' => 'Store not found for store of order'], 422);
      }

      if (!$store->bank_code || !$store->bank_account_number || !$store->bank_account_name) {
        throw ValidationException::withMessages([
          'bank' => 'Thiếu thông tin thanh toán của Store (bank_code/bank_account_number/bank_account_name).',
        ]);
      }

      $orderArr = [
        'id'       => $order->id,
        'order_code'     => $order->order_code,
        'total_amount'   => $order->total_amount,
      ];

      $storeArr = [
        'bank_code'       => $store->bank_code,
        'bank_account_number'    => $store->bank_account_number,
        'bank_account_name' => $store->bank_account_name,
      ];

      // 4) Tạo QR
      try {
        $data = $this->svc->createVietqrForOrder($orderArr, $storeArr);
      } catch (\Throwable $e) {
        return response()->json([
          'message' => 'Generate QR failed',
          'detail'  => $e->getMessage()
        ], 502);
      }

      return response()->json([
        'orderId'           => $data['orderId'],
        'transactionRefId' => $data['transactionRefId'],
        'qrCode'          => $data['qrCode'],
        'qrLink'            => $data['qrLink'] ,
        'imgId'        => $data['imgId'] ,
        'content'        => $data['content'],
      ], 200);
    }
}
