<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;
use App\Http\Requests\UpdateStoreRequest;
use App\Http\Requests\StoreStoreRequest;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stores = Store::select('id', 'name', 'user_id','bank_code', 'bank_account_number', 'bank_account_name')
            ->orderBy('id', 'desc')
            ->get();
        return response()->json([
            'status' => 'ok',
            'total'  => $stores->count(),
            'data'   => $stores
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStoreRequest $request)
    {   
        $data = $request->validated();
        $store = Store::create(
            [ 
                'name'                 => $data['name'],
                'user_id'              => $data['user_id'],
                'bank_code'            => $data['bank_code'],
                'bank_account_number'  => $data['bank_account_number'],
                'bank_account_name'    => $data['bank_account_name'],
            ]
        );
        return response()->json([
            'status' => 'ok',
            'data'   => $store
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStoreRequest $request, string $id)
    {
        $store = Store::findOrFail($id);
        $this->authorize('update', $store);
        $data = $request->validated();
        $store->fill($data)->save();

        return response()->json([
            'status' => 'ok',
            'data'   => $store->only(['id','name','bank_code','bank_account_number','bank_account_name']),
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
