<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCourierRosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courier_ros', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer("parent_id")->nullable();
            $table->string("code", 100)->nullable();
            $table->string("name", 100)->nullable();
            $table->string("path_logo", 255)->nullable();
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
        Schema::dropIfExists('courier_ros');
    }
}
