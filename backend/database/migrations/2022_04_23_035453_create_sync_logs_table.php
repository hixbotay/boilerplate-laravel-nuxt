<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSyncLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('sync_id');
            $table->integer('store_id');
            $table->string('resource');
            $table->integer('actual_sync_records');
            $table->integer('skipped_sync_records')->default(0);
            $table->integer('total_sync_records');
            $table->tinyInteger('status')->default(0);
            $table->text('query')->nullable();
            $table->text('error_message')->nullable();
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
        Schema::dropIfExists('sync_logs');
    }
}
