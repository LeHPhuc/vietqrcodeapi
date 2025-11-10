<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon; 

class MyStorePackageController extends Controller
{

    protected function getMyStoreOrFail(): Store
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        // Nếu quan hệ user->store là hasOne:
        // ví dụ User.php: public function store(){ return $this->hasOne(Store::class); }
        $store = Store::where('user_id', $user->id)->first();

        if (!$store) {
            abort(404, 'Store not found for current user');
        }

        return $store;
    }



    public function attachExisting(Request $request)
    {
        $store = $this->getMyStoreOrFail();

        $data = $request->validate([
            'package_id'     => ['required','exists:packages,id'],
        ]);

        $package   = Package::findOrFail($data['package_id']);
        $startsAt  = Carbon::today();
        $expiresAt = Carbon::today()->addDays((int) $package->duration_days);

        $pivot = [
            'starts_at'      => $startsAt->toDateString(),          
            'expires_at'     => $expiresAt->toDateString(),            
            'price_override' => $package->price,                       
        ];

        
        $store->packages()->attach($package->id, $pivot);
        $pkg = $store->packages()->where('packages.id', $package->id)->first();
        
        return response()->json([
            'status' => 'ok',
            'data'   => $pkg,
        ], 201);
    }


   public function myPackage(Request $request)
    {
        $store = $this->getMyStoreOrFail();

        $status = $request->query('status'); 

        $query = $store->packages()
            ->select('packages.*') 
            ->withPivot(['id','price_override','expires_at','starts_at','status'])
            ->orderByDesc('packages.id');

        if ($status) {
            $query->wherePivot('status', $status);
        }

    
        $packages = $query->get();

        if ($packages->isEmpty()) {
            return response()->json([
                'success' => true,
                'data'    => null,
                'message' => 'Không có gói nào.',
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data'    => $packages, 
        ]);
    }
}