<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('gateway')->default(0);
            $table->bigInteger('order_id');
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('setlement')->default(0);
            $table->bigInteger('user_id')->default(0);
            $table->decimal('total')->default(0);
            $table->decimal('fee')->default(0);
            $table->decimal('saving')->default(0);
            $table->decimal('saving_lost')->default(0);
            $table->text('data')->default('');
            $table->text('history')->default('');
            $table->string('tx_id',100)->default('');
            $table->timestamps();
            $table->index('order_id');
            $table->index('tx_id');
            $table->index(['order_id','created_at']);
            $table->index(['user_id','created_at']);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_transactions');
    }
}
