<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyUserStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_stores', function (Blueprint $table) {
            $table->dateTime('last_sync_orders_at')->nullable();
            $table->dateTime('last_sync_categories_at')->nullable();
            $table->dateTime('last_sync_products_at')->nullable();
            $table->dateTime('last_sync_customer_group_at')->nullable();
            $table->dateTime('last_sync_customers_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_stores', function (Blueprint $table) {
            $table->removeColumn('last_sync_orders_at');
            $table->removeColumn('last_sync_categories_at');
            $table->removeColumn('last_sync_products_at');
            $table->removeColumn('last_sync_customer_group_at');
            $table->removeColumn('last_sync_customers_at');
        });
    }
}
