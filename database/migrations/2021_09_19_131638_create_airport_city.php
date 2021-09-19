<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAirportCity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('airport_city', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('icao_code',10);
            $table->string('iata_code',10);
            $table->string('airport_name',100);
            $table->string('City',100);
            $table->string('province',100);
            
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
        Schema::dropIfExists('airport_city');
    }
}
