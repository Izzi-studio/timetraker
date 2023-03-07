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
        Schema::create('tracker', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id');
            $table->integer('current_status')->default(0);
            $table->string('comments')->nullable();
            $table->datetime('date_start')->nullable();
            $table->datetime('date_stop')->nullable();
            $table->integer('pause')->default(0);
            $table->integer('work')->default(0);
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
        Schema::dropIfExists('tracker');
    }
};
