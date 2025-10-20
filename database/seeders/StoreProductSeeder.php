<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\Product;
use Illuminate\Database\Seeder;

class StoreProductSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo 5 store
        $stores = Store::factory()->count(5)->create();

        foreach ($stores as $store) {
            $store->products()->saveMany(
                Product::factory()->count(30)->make(['store_id' => $store->id])
            );
        }
    }
}
