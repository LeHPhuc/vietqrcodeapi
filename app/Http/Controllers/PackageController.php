<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Package;
use App\Http\Requests\StorePackageRequest;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $package = Package::select('id', 'name','price','duration_days')
            ->orderBy('id', 'desc')
            ->get();


        if ($package->isEmpty()) {
            return response()->json([
                'success' => true,
                'count'   => 0,
                'data'    => [],
                'message' => 'Không có thông tin gói nào.',
            ], 200);
        }

        return response()->json([
            'status' => 'ok',
            'total'  => $package->count(),
            'data'   => $package
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePackageRequest $request)
    {
        $data = $request->validated();
        $package = Package::create($data);
        return response()->json([
            'status' => 'ok',
            'data'   => $package
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
