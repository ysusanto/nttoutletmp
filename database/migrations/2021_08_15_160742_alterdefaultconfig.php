<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Alterdefaultconfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('configs', function (Blueprint $table) {
            //
            $table->string('order_number_prefix')->nullable()->default('O')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('configs', function (Blueprint $table) {
            //
            $table->dropColumn('order_number_prefix');
           
        });
    }
}
