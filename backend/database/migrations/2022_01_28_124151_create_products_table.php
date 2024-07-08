<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku');
            $table->string('image')->nullable();
            $table->float('price', 10,2)->nullable();
            $table->float('price_sale', 10,2)->nullable();
            $table->integer('quantity')->nullable();
            $table->text('subname')->nullable();
            $table->text('description')->nullable();
            $table->text('excerpt')->nullable();
            $table->text('raw_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
