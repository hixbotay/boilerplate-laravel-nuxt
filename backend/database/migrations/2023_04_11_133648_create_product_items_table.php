<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_items', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
            $table->tinyInteger('status');
            $table->string('name', 200);
            $table->string('email', 200);
            $table->string('description', 1000)->nullable();
            $table->string('sku', 30);
            $table->timestamps();
            $table->index(['product_id','status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_items');
    }
}
