<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFeildOrderProductItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_product_items', function (Blueprint $table) {
            $table->string('name', 200);
            $table->string('description', 1000)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_product_items', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('description');
        });
    }
}
