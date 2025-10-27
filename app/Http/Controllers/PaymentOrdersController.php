<?php

namespace App\Http\Controllers;

use App\Models\PaymentOrders;
use Illuminate\Http\Request;

class PaymentOrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         $paymentOrders = PaymentOrders::query()
        ->orderByDesc('id')
        ->get(['id','order_id','bank_account_number','content','amount','transaction_id','reference_number','transaction_time','qrlink']);
    

     if ($paymentOrders->isEmpty()) {
        return response()->json([
            'success' => true,
            'count'   => 0,
            'data'    => [],
            'message' => 'Không có thông tin của đơn hàng nào.',
        ], 200);
    }
    
    return response()->json([
        'success' => true,
        'data'    => $paymentOrders,
    ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
