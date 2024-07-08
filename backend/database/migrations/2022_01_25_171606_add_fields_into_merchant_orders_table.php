<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsIntoMerchantOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('merchant_orders', function (Blueprint $table) {
            $table->integer('customer_address_id');
            $table->integer('user_store_id');
            $table->integer('platform_order_id')->nullable(); // order id from original plaform
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('merchant_orders', function (Blueprint $table) {
            $table->dropColumn('customer_address_id');
            $table->dropColumn('user_store_id');
            $table->dropColumn('platform_order_id'); // order id from original plaform
        });
    }
}
