<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tracker_processing', function (Blueprint $table) {
            $table->id();
            $table->integer('tracker_id');
            $table->integer('customer_id');
            $table->integer('status');
            $table->datetime('action_date_time_start');
            $table->datetime('action_date_time_stop')->nullable();
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
        Schema::dropIfExists('tracker_processing');
    }
};
