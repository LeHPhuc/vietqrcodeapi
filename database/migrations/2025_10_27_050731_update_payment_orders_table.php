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
        Schema::table('payment_orders', function (Blueprint $table) {
             //đổi tên cột
            $table->renameColumn('transactionRefId', 'transaction_id');
            // thêm cột mới
            $table->string('reference_number', 100)->after('amount');
            $table->string('transaction_time', 100)->after('amount');


            //xóa cột 
            $table->dropColumn('imgId');
            $table->dropColumn('bank_code');
            $table->dropColumn('bank_account_name');
            $table->dropColumn('qr_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_orders', function (Blueprint $table) {
            //
        });
    }
};
