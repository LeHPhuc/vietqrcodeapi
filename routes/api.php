<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders', [OrderController::class, 'index']);




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
