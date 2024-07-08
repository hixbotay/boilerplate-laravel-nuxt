<?php

use App\Http\Enums\UserRoleType;
use App\Models\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeUserRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_roles', function (Blueprint $table) {
            $table->dropColumn('role');
            $table->string('name');
            $table->enum('type', UserRoleType::getAllValue());
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_roles', function (Blueprint $table) {
            $table->integer('role');
            $table->dropColumn('name');
            $table->dropColumn('type');
        });
    }
}
