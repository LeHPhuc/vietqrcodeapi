<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('orders', function ($table) {
        $table->string('order_status_payment')->default('unpaid')->after('order_status');
    });
}

public function down()
{
    Schema::table('orders', function ($table) {
        $table->dropColumn('order_status_payment');
    });
}
};
