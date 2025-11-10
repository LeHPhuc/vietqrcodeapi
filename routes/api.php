<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\VietQRPaymentController;
use App\Http\Controllers\VietQRPaymentPackageController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\TransactionSyncController;
use App\Http\Controllers\PaymentOrdersController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\MyStorePackageController;
use App\Http\Controllers\TransactionSyncPackageController;



Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders', [OrderController::class, 'index']);
Route::get('/orders/{id}', [OrderController::class, 'show'])->middleware('auth:sanctum');
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::get('/stores', [StoreController::class, 'index']);
Route::patch('/stores/{id}', [StoreController::class, 'update'])->middleware('auth:sanctum');
Route::post('/stores', [StoreController::class, 'store']);
Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store']);
Route::patch('/products/{id}', [ProductController::class, 'update']);
Route::post('/vqr/payment/order', [VietQRPaymentController::class, 'createVietqr']);
Route::post('/vqr/payment/package', [VietQRPaymentPackageController::class, 'createVietqr']);
Route::get('/payment-orders', [PaymentOrdersController::class, 'index']);
Route::get('/packages', [PackageController::class, 'index']);
Route::post('/packages', [PackageController::class, 'store']);
Route::post('/my-store/add-package', [MyStorePackageController::class, 'attachExisting'])->middleware('auth:sanctum');
Route::get('/my-store/my-package', [MyStorePackageController::class, 'myPackage'])->middleware('auth:sanctum');

Route::post('/vqr/api/token_generate', [TokenController::class, 'generate']);       
Route::post('/vqr/bank/api/transaction-sync', [TransactionSyncController::class, 'handle']); 
Route::post('/vqr/bank/api/package/transaction-sync', [TransactionSyncPackageController::class, 'handle']); 

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::get('/me', function () {
    return auth()->user()
        ->load(['store:id,user_id,name,bank_code,bank_account_number,bank_account_name']);
})->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});








// kiểm tra deploy ở render
Route::get('/__debug_db', function () {
    return response()->json([
        'default_connection' => Config::get('database.default'),
        'database_url'       => env('DATABASE_URL'),
        'current_database'   => DB::selectOne('select current_database() db')->db ?? null,
        'current_schema'     => DB::selectOne('select current_schema() schema')->schema ?? null,
        'tables'             => DB::select("select schemaname, tablename from pg_tables where schemaname = current_schema() order by 1,2"),
        'migrations_last10'  => DB::table('migrations')->orderBy('id','desc')->limit(10)->get(),
    ]);
});

// Chạy migrate ở db render
use Illuminate\Support\Facades\Artisan;
Route::get('/__run_migrate_once', function () {
    Artisan::call('config:clear');
    Artisan::call('migrate', ['--force' => true]);
    return response()->json([
        'ok' => true,
        'output' => Artisan::output(),
    ]);
});

// Factory user on render
Route::get('/seed-users', function () {
    try {
        // Chạy đúng DatabaseSeeder của bạn (mặc định tạo 10 user)
        Artisan::call('db:seed', [
            '--class' => \Database\Seeders\DatabaseSeeder::class,
            '--force' => true,
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Seeded using DatabaseSeeder (default 10 users).',
            'artisan_output' => Artisan::output(),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

