<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('orders', function (Blueprint $table) {
           $table->id();
           $table->char('order_code', 8)->collation('ascii_bin')->unique();
           $table->foreignId('store_id')->constrained()->cascadeOnDelete();
           $table->string('order_status')->default('pending')->index();
           $table->decimal('total_amount', 12, 2)->unsigned()->default(0);
           $table->string('note')->nullable();
           $table->timestamps();
       });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
