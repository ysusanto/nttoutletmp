<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubdistrictRosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subdistrict_ros', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("id_subdistrict_ro",30)->nullable();;
            $table->string("id_city_ro",30)->nullable();;
            $table->string("subdistrict",200)->nullable();;
            $table->string('type', 100)->nullable();
          
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
        Schema::dropIfExists('subdistrict_ros');
    }
}
