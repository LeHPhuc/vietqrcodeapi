<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Requests\StoreOrderRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\OrderItem;
class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            $total = 0;

            // Tạo trước khung của order
            $order = Order::create([ 
                'store_id'     => $data['store_id'],
                'order_status' => 'pending',
                'order_status_payment' => 'unpaid',
                'total_amount' => 0,
                'note'         => $data['note'] ?? null,
            ]);

            // kiểm tra items tạo OrderItem
            foreach ($data['items'] as $it) {
                $product = Product::where('store_id', $data['store_id'])
                                  ->findOrFail($it['product_id']);

                $lineTotal = round($product->price * $it['quantity'], 2);
                $total     = round($total + $lineTotal, 2);

                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'unit_price'   => $product->price,
                    'quantity'     => $it['quantity'],
                    'line_total'   => $lineTotal,
                ]);
            }

            // Cập nhật tổng tiền của đơn hàng.
            $order->update(['total_amount' => $total]);
            return response()->json([
                'success' => true,
                'data' => $order->load('items'),
                'message' => 'Order created successfully.'
                
            ], 201);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
