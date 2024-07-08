<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyMerchantOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('merchant_orders', function (Blueprint $table) {
            $table->dropColumn('platform_id');
            $table->dropColumn('items');
            $table->integer('currency_id')->nullable();
            $table->dropColumn('currency');
            $table->string('shipping_person_name')->nullable();
            $table->string('shipping_person_mobile')->nullable();
            $table->string('shipping_person_street')->nullable();
            $table->string('shipping_person_portal_code')->nullable();
            $table->string('shipping_person_state')->nullable();
            $table->string('shipping_person_country_id')->nullable();
            $table->string('billing_person_name')->nullable();
            $table->string('billing_person_mobile')->nullable();
            $table->string('billing_person_street')->nullable();
            $table->string('billing_person_portal_code')->nullable();
            $table->string('billing_person_state')->nullable();
            $table->string('billing_person_country_id')->nullable();
            $table->string('note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('merchant_orders', function (Blueprint $table) {
            $table->integer('platform_id');
            $table->text('items')->nullable();
            $table->dropColumn('currency_id');
            $table->string('currency',3);
            $table->dropColumn('shipping_person_name');
            $table->dropColumn('shipping_person_mobile');
            $table->dropColumn('shipping_person_street');
            $table->dropColumn('shipping_person_portal_code');
            $table->dropColumn('shipping_person_state');
            $table->dropColumn('shipping_person_country_id');
            $table->dropColumn('billing_person_name');
            $table->dropColumn('billing_person_mobile');
            $table->dropColumn('billing_person_street');
            $table->dropColumn('billing_person_portal_code');
            $table->dropColumn('billing_person_state');
            $table->dropColumn('billing_person_country_id');
            $table->dropColumn('note');
        });
    }
}
