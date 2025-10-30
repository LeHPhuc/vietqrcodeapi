<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::select('id', 'name', 'price', 'store_id')
            ->orderBy('id', 'desc')
            ->get();
        return response()->json([
            'status' => 'ok',
            'total'  => $products->count(),
            'data'   => $products
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $product = Product::create($data);
        return response()->json([
            'status' => 'ok',
            'data'   => $product
        ], 201);
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
    public function update(UpdateProductRequest $request, string $id)
    {
        $product = Product::findOrFail($id);
        $data = $request->validated();
        $product->fill($data)->save();

        return response()->json([
            'status' => 'ok',
            'data'   => $product
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
