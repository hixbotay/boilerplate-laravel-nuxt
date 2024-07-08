<?php

use App\Http\Enums\UserBusinessType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // remove unused columns
            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
            $table->dropColumn('country_id');
            $table->dropColumn('state_id');
            $table->dropColumn('address');
            $table->dropColumn('date_of_birth');
            $table->dropColumn('whatsapp');
            $table->dropColumn('company');
            // add new columns
            $table->string('full_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name');
            $table->string('last_name');
            $table->integer('country_id');
            $table->integer('state_id')->nullable();
            $table->string('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('company')->nullable();

            $table->dropColumn('full_name');
            $table->dropColumn('business_type');
            $table->dropColumn('gstin_number');
            $table->dropColumn('coupon_code');
            $table->dropColumn('is_receive_updates_via_whatsapp');
        });
    }
}
