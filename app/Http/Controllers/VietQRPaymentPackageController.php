<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Payments\Services\PaymentPackage;
use App\Models\Store;
use App\Models\User;
use App\Models\Package_store;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VietQRPaymentPackageController extends Controller
{
    public function __construct(private PaymentPackage $svc) {}

    public function createVietqr(Request $request)
    {
      $request->validate(['package_id' => ['required','integer','min:1']]);
      $packageId = (int) $request->input('package_id');

      $package = Package_store::query()
        ->select(['id','price_override'])
        ->find($packageId);

      if (!$package) {
        return response()->json(['message' => 'package not found'], 404);
      }

      $packageArr = [
        'id'       => $package->id,
        'price_override'   => $package->price_override,
      ];

      // 4) Tạo QR
      try {
        $data = $this->svc->createVietqrForPackage($packageArr);
      } catch (\Throwable $e) {
        return response()->json([
          'message' => 'Generate QR failed',
          'detail'  => $e->getMessage()
        ], 502);
      }

      return response()->json([
        'packageId'           => $data['packageId'],
        'transactionRefId' => $data['transactionRefId'],
        'qrCode'          => $data['qrCode'],
        'qrLink'            => $data['qrLink'] ,
        'imgId'        => $data['imgId'] ,
        'content'        => $data['content'],
      ], 200);
    }
}
