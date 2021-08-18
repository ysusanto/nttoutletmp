<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCityRosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('city_ros', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_city_ro', 30)->nullable();
            $table->string('id_province_ro', 30)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('type', 100)->nullable();
            $table->string('postal_code', 50)->nullable();
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
        Schema::dropIfExists('city_ros');
    }
}
