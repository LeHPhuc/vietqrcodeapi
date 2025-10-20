<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserListController;

Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders', [OrderController::class, 'index']);
Route::get('/users/names', [UserListController::class, 'names']);



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
    // CHỐNG lộ route trên production
    if (app()->environment('production')) {
        abort(403, 'Not allowed in production');
    }

    $count = (int) request('count', 10);   // ?count=20 để đổi số lượng
    $count = max(1, min($count, 1000));    // giới hạn an toàn

    // Tạo user theo factory
    User::factory($count)->create();

    return response()->json([
        'status' => 'ok',
        'created' => $count
    ]);
});

