<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchantOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchant_orders', function (Blueprint $table) {
            $table->id();
            $table->string('store_id');
            $table->integer('platform_id');
            $table->tinyInteger('type')->default(0);
            $table->string('subscribe_tx_id')->nullable();
            $table->string('pyament_tx_id')->nullable();
            $table->text('items')->nullable();
            $table->string('email')->nullable();
            $table->string('phone',30)->nullable();
            $table->decimal('total',15,2);
            $table->decimal('subtotal',15,2)->default(0);
            $table->decimal('discount',15,2)->default(0);
            $table->decimal('tax',15,2)->default(0);
            $table->string('currency',3);
            $table->tinyInteger('payment_status')->default(0);
            $table->integer('payment_method')->default(0);
            $table->tinyInteger('payment_type')->default(0);
            $table->tinyInteger('fulfillment_status')->default(0);
            $table->integer('shipping_method')->default(0);
            // $table->text('shipping_method')->nullable();
            $table->text('billing_person')->nullable();
            $table->text('params')->nullable();
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
        Schema::dropIfExists('merchant_orders');
    }
}
