<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            $table->point('location');
            $table->string('device_id')->nullable();
            $table->integer('humidity')->nullable();
            $table->integer('temperature')->nullable();
            $table->integer('pm1_0')->nullable();
            $table->integer('pm2_5')->nullable();
            $table->integer('pm10')->nullable();
            $table->integer('noparticles_0_3')->nullable();
            $table->integer('noparticles_0_5')->nullable();
            $table->integer('noparticles_1_0')->nullable();
            $table->integer('noparticles_2_5')->nullable();
            $table->integer('noparticles_5_0')->nullable();
            $table->integer('noparticles_10')->nullable();
            $table->integer('aqi')->nullable();
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
        Schema::dropIfExists('entries');
    }
}
