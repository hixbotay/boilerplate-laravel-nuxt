<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDimensionFieldsIntoMerchantOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('merchant_orders', function (Blueprint $table) {
            $table->float('package_weight', 8, 2)->nullable();
            $table->float('package_length', 8, 2)->nullable();
            $table->float('package_breadth', 8, 2)->nullable();
            $table->float('package_height', 8, 2)->nullable();
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
            $table->dropColumn('package_weight');
            $table->dropColumn('package_length');
            $table->dropColumn('package_breadth');
            $table->dropColumn('package_height');
        });
    }
}
