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
        Schema::create('payment_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unique('order_id');
            $table->string('bank_code', 10)->nullable()->index();       
            $table->string('bank_account_number', 32)->nullable()->index();      
            $table->string('bank_account_name', 100)->nullable();  
            $table->text('content')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->longText('qr_code')->nullable();
            $table->string('qrLink')->nullable();
            $table->string('imgId')->nullable();
            $table->string('transactionRefId',128)->nullable()->unique();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_orders');
    }
};
